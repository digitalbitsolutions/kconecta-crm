let map = null;
let marker = null;
let geocoder = null;
let autocomplete = null;

const DEFAULT_COORDINATES = { lat: 41.3728156, lng: 2.1335788 };
const ADDRESS_VALIDATION_MESSAGE = "Selecciona una direccion valida de las sugerencias de Google Maps antes de guardar.";

const myLocationButton = document.getElementById("my-location");
const mapElement = document.getElementById("map");
const addressInput = document.getElementById("address");
const latitudeInput = document.getElementById("latitude");
const longitudeInput = document.getElementById("longitude");
const cityInput = document.getElementById("city");
const provinceInput = document.getElementById("province");
const postalCodeInput = document.getElementById("postal_code");
const countryInput = document.getElementById("country");
const routeMapLabel = document.getElementById("route-map");
const cityMapLabel = document.getElementById("city-map");
const stateMapLabel = document.getElementById("state-map");
const countryMapLabel = document.getElementById("country-map");
const formElement = addressInput ? addressInput.closest("form") : null;

let lastResolvedAddressValue = addressInput ? addressInput.value.trim() : "";
let isProgrammaticAddressChange = false;

function hasGoogleMaps() {
    return typeof window.google !== "undefined" && !!window.google.maps;
}

function hasPlacesLibrary() {
    return hasGoogleMaps() && !!window.google.maps.places;
}

function setInputValue(input, value) {
    if (!input) {
        return;
    }

    input.value = value ?? "";
}

function setLabelText(label, value) {
    if (!label) {
        return;
    }

    label.textContent = value ?? "";
}

function markAddressValid() {
    if (!addressInput) {
        return;
    }

    addressInput.dataset.addressValidated = "1";
    addressInput.setCustomValidity("");
    lastResolvedAddressValue = addressInput.value.trim();
}

function markAddressInvalid(message = ADDRESS_VALIDATION_MESSAGE) {
    if (!addressInput) {
        return;
    }

    addressInput.dataset.addressValidated = "0";
    addressInput.setCustomValidity(message);
}

function setResolvedAddressValue(value) {
    if (!addressInput) {
        return;
    }

    isProgrammaticAddressChange = true;
    addressInput.value = value;
    markAddressValid();

    window.setTimeout(() => {
        isProgrammaticAddressChange = false;
    }, 0);
}

function clearResolvedAddressData() {
    setInputValue(cityInput, "");
    setInputValue(provinceInput, "");
    setInputValue(postalCodeInput, "");
    setInputValue(countryInput, "");
    setInputValue(latitudeInput, "");
    setInputValue(longitudeInput, "");

    setLabelText(routeMapLabel, "");
    setLabelText(cityMapLabel, "");
    setLabelText(stateMapLabel, "");
    setLabelText(countryMapLabel, "");
}

function buildStreet(route, streetNumber) {
    return [route, streetNumber].filter(Boolean).join(" ").trim();
}

function getAddressComponent(components, candidateTypes) {
    const types = Array.isArray(candidateTypes) ? candidateTypes : [candidateTypes];
    const component = components.find((item) => types.some((type) => item.types.includes(type)));

    return component ? component.long_name : "";
}

function updateMapLocation(position) {
    if (!mapElement || !hasGoogleMaps()) {
        return;
    }

    if (!map) {
        map = new google.maps.Map(mapElement, {
            center: position,
            zoom: 16,
            streetViewControl: false,
            styles: [
                {
                    featureType: "poi",
                    stylers: [{ visibility: "off" }],
                },
                {
                    featureType: "transit",
                    stylers: [{ visibility: "off" }],
                },
            ],
        });

        marker = new google.maps.Marker({
            position,
            map,
            draggable: true,
        });

        marker.addListener("dragend", () => {
            const markerPosition = marker.getPosition();
            if (!markerPosition) {
                return;
            }

            reverseGeocode(markerPosition.lat(), markerPosition.lng(), true);
        });

        return;
    }

    map.setCenter(position);
    marker.setPosition(position);
}

function applyResolvedPlace(place, updateTextInput = true) {
    if (!place || !place.geometry || !place.geometry.location) {
        return;
    }

    const components = Array.isArray(place.address_components) ? place.address_components : [];
    const route = getAddressComponent(components, "route");
    const streetNumber = getAddressComponent(components, "street_number");
    const city = getAddressComponent(components, ["locality", "postal_town", "administrative_area_level_3", "sublocality_level_1"]);
    const province = getAddressComponent(components, ["administrative_area_level_2", "administrative_area_level_1"]);
    const postalCode = getAddressComponent(components, "postal_code");
    const country = getAddressComponent(components, "country");
    const street = buildStreet(route, streetNumber);

    setLabelText(routeMapLabel, street);
    setLabelText(cityMapLabel, city);
    setLabelText(stateMapLabel, province);
    setLabelText(countryMapLabel, country);

    setInputValue(cityInput, city);
    setInputValue(provinceInput, province);
    setInputValue(postalCodeInput, postalCode);
    setInputValue(countryInput, country);
    setInputValue(latitudeInput, String(place.geometry.location.lat()));
    setInputValue(longitudeInput, String(place.geometry.location.lng()));

    if (updateTextInput && addressInput) {
        const fallbackAddress = place.formatted_address || place.name || addressInput.value;
        const normalizedAddress = [street, city, province].filter(Boolean).join(", ").trim();
        setResolvedAddressValue(normalizedAddress !== "" ? normalizedAddress : fallbackAddress);
    } else {
        markAddressValid();
    }
}

function reverseGeocode(lat, lng, updateTextInput = false) {
    if (!geocoder) {
        return;
    }

    geocoder.geocode(
        {
            location: { lat, lng },
            language: "es",
        },
        (results, status) => {
            if (status === "OK" && Array.isArray(results) && results[0]) {
                applyResolvedPlace(results[0], updateTextInput);
                updateMapLocation({ lat, lng });
                return;
            }

            markAddressInvalid("No se pudo validar la direccion seleccionada.");
        }
    );
}

function geocodeTypedAddress() {
    if (!geocoder || !addressInput) {
        return;
    }

    const typedAddress = addressInput.value.trim();
    if (typedAddress === "") {
        return;
    }

    geocoder.geocode(
        {
            address: typedAddress,
            componentRestrictions: { country: "ES" },
            language: "es",
        },
        (results, status) => {
            if (status === "OK" && Array.isArray(results) && results[0]) {
                applyResolvedPlace(results[0], true);
                updateMapLocation({
                    lat: results[0].geometry.location.lat(),
                    lng: results[0].geometry.location.lng(),
                });
            }
        }
    );
}

function initAutocompleteAddress() {
    if (!addressInput || !hasPlacesLibrary()) {
        return;
    }

    autocomplete = new google.maps.places.Autocomplete(addressInput, {
        componentRestrictions: { country: "ES" },
        fields: ["address_components", "formatted_address", "geometry", "name"],
    });

    autocomplete.addListener("place_changed", () => {
        const place = autocomplete.getPlace();
        if (!place || !place.geometry || !place.geometry.location) {
            clearResolvedAddressData();
            markAddressInvalid("Selecciona una direccion valida de las sugerencias.");
            addressInput.reportValidity();
            return;
        }

        applyResolvedPlace(place, true);
        updateMapLocation({
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
        });
    });

    addressInput.addEventListener("input", () => {
        if (isProgrammaticAddressChange) {
            return;
        }

        const currentValue = addressInput.value.trim();

        if (currentValue === "") {
            clearResolvedAddressData();
            addressInput.setCustomValidity("");
            lastResolvedAddressValue = "";
            return;
        }

        if (currentValue !== lastResolvedAddressValue) {
            clearResolvedAddressData();
            markAddressInvalid();
        }
    });
}

function getMyLocation() {
    if (!("geolocation" in navigator)) {
        window.alert("Geolocalizacion no compatible con tu navegador.");
        return;
    }

    if (!myLocationButton) {
        return;
    }

    const originalContent = myLocationButton.innerHTML;
    myLocationButton.textContent = "Espere ...";
    myLocationButton.disabled = true;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            reverseGeocode(position.coords.latitude, position.coords.longitude, true);
            myLocationButton.innerHTML = originalContent;
            myLocationButton.disabled = false;
        },
        (error) => {
            const message = {
                [error.PERMISSION_DENIED]: "Permiso denegado por el usuario.",
                [error.POSITION_UNAVAILABLE]: "Informacion de ubicacion no disponible.",
                [error.TIMEOUT]: "La solicitud tardo demasiado.",
            }[error.code] || "Error desconocido.";

            window.alert(message);
            myLocationButton.innerHTML = originalContent;
            myLocationButton.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
        }
    );
}

function initFormValidation() {
    if (!formElement || !addressInput) {
        return;
    }

    formElement.addEventListener("submit", (event) => {
        if (!addressInput.value.trim()) {
            return;
        }

        if (!latitudeInput || !longitudeInput || !latitudeInput.value || !longitudeInput.value) {
            event.preventDefault();

            if (!hasGoogleMaps()) {
                markAddressInvalid("No se pudo cargar Google Maps. Verifica la API key configurada.");
            } else {
                markAddressInvalid();
            }

            addressInput.reportValidity();
            addressInput.focus();
        } else {
            markAddressValid();
        }
    });
}

function initGoogleMapsAddressControls() {
    if (!addressInput) {
        return;
    }

    initFormValidation();

    if (!hasGoogleMaps()) {
        return;
    }

    geocoder = new google.maps.Geocoder();

    const initialLatitude = latitudeInput && latitudeInput.value ? parseFloat(latitudeInput.value) : DEFAULT_COORDINATES.lat;
    const initialLongitude = longitudeInput && longitudeInput.value ? parseFloat(longitudeInput.value) : DEFAULT_COORDINATES.lng;

    updateMapLocation({ lat: initialLatitude, lng: initialLongitude });
    initAutocompleteAddress();

    if (myLocationButton) {
        myLocationButton.addEventListener("click", getMyLocation);
    }

    if (latitudeInput && longitudeInput && latitudeInput.value && longitudeInput.value) {
        reverseGeocode(initialLatitude, initialLongitude, false);
        markAddressValid();
        return;
    }

    if (addressInput.value.trim() !== "") {
        geocodeTypedAddress();
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initGoogleMapsAddressControls);
} else {
    initGoogleMapsAddressControls();
}
