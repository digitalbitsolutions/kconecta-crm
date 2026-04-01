let map = null;
let marker = null;
let geocoder = null;
let legacyAutocomplete = null;
let placesLibraryPromise = null;
let autocompleteSessionToken = null;
let autocompletePanel = null;
let autocompleteFieldWrapper = null;
let autocompleteRequestTimeout = null;
let latestAutocompleteRequestId = 0;
let autocompleteMode = "none";
let addressInputEventsBound = false;
let documentClickListenerBound = false;

const DEFAULT_COORDINATES = { lat: 41.3728156, lng: 2.1335788 };
const ADDRESS_VALIDATION_MESSAGE = "Selecciona una direccion valida de las sugerencias de Google Maps antes de guardar.";
const GOOGLE_RESULTS_LABEL = "Google";
const AUTOCOMPLETE_DEBOUNCE_MS = 250;
const AUTOCOMPLETE_MIN_CHARACTERS = 3;

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

function hasPlacesSupport() {
    return hasGoogleMaps() && (!!window.google.maps.importLibrary || !!window.google.maps.places);
}

async function getPlacesLibrary() {
    if (!hasPlacesSupport()) {
        return null;
    }

    if (!window.google.maps.importLibrary) {
        return window.google.maps.places ?? null;
    }

    if (!placesLibraryPromise) {
        placesLibraryPromise = window.google.maps.importLibrary("places").catch(() => null);
    }

    return placesLibraryPromise;
}

function hasLegacyAutocompleteSupport() {
    return hasGoogleMaps() && !!window.google.maps.places && !!window.google.maps.places.Autocomplete;
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

function getCoordinateValue(location, axis) {
    if (!location) {
        return null;
    }

    const coordinate = location[axis];
    if (typeof coordinate === "function") {
        return coordinate.call(location);
    }

    return typeof coordinate === "number" ? coordinate : null;
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

function createAutocompletePanel() {
    if (!addressInput || autocompletePanel) {
        return;
    }

    const container = addressInput.parentElement;
    if (!container) {
        return;
    }

    autocompleteFieldWrapper = document.createElement("div");
    autocompleteFieldWrapper.className = "google-autocomplete-field";
    container.insertBefore(autocompleteFieldWrapper, addressInput);
    autocompleteFieldWrapper.appendChild(addressInput);

    autocompletePanel = document.createElement("div");
    autocompletePanel.className = "google-autocomplete-panel is-hidden";
    autocompleteFieldWrapper.appendChild(autocompletePanel);
}

function hideAutocompletePanel() {
    latestAutocompleteRequestId += 1;

    if (!autocompletePanel) {
        return;
    }

    autocompletePanel.classList.add("is-hidden");
    autocompletePanel.replaceChildren();
}

function showAutocompletePanel() {
    if (!autocompletePanel || autocompletePanel.childElementCount === 0) {
        return;
    }

    autocompletePanel.classList.remove("is-hidden");
}

function renderAutocompleteSuggestions(suggestions) {
    if (!autocompletePanel) {
        return;
    }

    autocompletePanel.replaceChildren();

    if (!Array.isArray(suggestions) || suggestions.length === 0) {
        hideAutocompletePanel();
        return;
    }

    const brand = document.createElement("div");
    brand.className = "google-autocomplete-brand";
    brand.textContent = GOOGLE_RESULTS_LABEL;
    autocompletePanel.appendChild(brand);

    const list = document.createElement("ul");
    list.className = "google-autocomplete-list";

    suggestions.slice(0, 6).forEach((suggestion) => {
        const placePrediction = suggestion?.placePrediction;
        if (!placePrediction) {
            return;
        }

        const optionItem = document.createElement("li");
        const optionButton = document.createElement("button");
        optionButton.type = "button";
        optionButton.className = "google-autocomplete-option";
        optionButton.textContent = placePrediction.text ? placePrediction.text.toString() : "";
        optionButton.addEventListener("mousedown", (event) => {
            event.preventDefault();
        });
        optionButton.addEventListener("click", async () => {
            await handleSuggestionSelection(suggestion);
        });

        optionItem.appendChild(optionButton);
        list.appendChild(optionItem);
    });

    if (!list.childElementCount) {
        hideAutocompletePanel();
        return;
    }

    autocompletePanel.appendChild(list);
    showAutocompletePanel();
}

async function handleSuggestionSelection(suggestion) {
    const placePrediction = suggestion?.placePrediction;
    hideAutocompletePanel();

    if (!placePrediction) {
        markAddressInvalid("Selecciona una direccion valida de las sugerencias.");
        addressInput?.reportValidity();
        return;
    }

    try {
        const place = placePrediction.toPlace();
        await place.fetchFields({
            fields: ["formattedAddress", "location"],
        });

        const lat = getCoordinateValue(place.location, "lat");
        const lng = getCoordinateValue(place.location, "lng");

        if (lat === null || lng === null) {
            throw new Error("Place location is not available.");
        }

        setResolvedAddressValue(place.formattedAddress || addressInput.value);
        setInputValue(latitudeInput, String(lat));
        setInputValue(longitudeInput, String(lng));
        updateMapLocation({ lat, lng });
        reverseGeocode(lat, lng, true);
        autocompleteSessionToken = null;
    } catch (error) {
        clearResolvedAddressData();
        markAddressInvalid("No se pudo validar la direccion seleccionada.");
        addressInput?.reportValidity();
        console.error("Google Maps Places selection error:", error);
    }
}

async function fetchAutocompleteSuggestions(query) {
    const trimmedQuery = query.trim();

    if (trimmedQuery.length < AUTOCOMPLETE_MIN_CHARACTERS) {
        hideAutocompletePanel();
        return;
    }

    const placesLibrary = await getPlacesLibrary();
    if (!placesLibrary || !placesLibrary.AutocompleteSuggestion) {
        hideAutocompletePanel();
        return;
    }

    const requestId = ++latestAutocompleteRequestId;

    if (!autocompleteSessionToken && placesLibrary.AutocompleteSessionToken) {
        autocompleteSessionToken = new placesLibrary.AutocompleteSessionToken();
    }

    try {
        const request = {
            input: trimmedQuery,
            includedRegionCodes: ["es"],
            language: "es",
            region: "es",
        };

        if (autocompleteSessionToken) {
            request.sessionToken = autocompleteSessionToken;
        }

        const response = await placesLibrary.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
        if (requestId !== latestAutocompleteRequestId) {
            return;
        }

        renderAutocompleteSuggestions(response?.suggestions ?? []);
    } catch (error) {
        if (requestId !== latestAutocompleteRequestId) {
            return;
        }

        hideAutocompletePanel();
        console.error("Google Maps Autocomplete Data API error:", error);
    }
}

function queueAutocompleteSuggestions(query) {
    if (autocompleteRequestTimeout) {
        window.clearTimeout(autocompleteRequestTimeout);
    }

    autocompleteRequestTimeout = window.setTimeout(() => {
        fetchAutocompleteSuggestions(query);
    }, AUTOCOMPLETE_DEBOUNCE_MS);
}

function bindAddressInputEvents() {
    if (!addressInput || addressInputEventsBound) {
        return;
    }

    addressInputEventsBound = true;

    addressInput.addEventListener("input", () => {
        if (isProgrammaticAddressChange) {
            return;
        }

        const currentValue = addressInput.value.trim();

        if (currentValue === "") {
            clearResolvedAddressData();
            hideAutocompletePanel();
            addressInput.setCustomValidity("");
            lastResolvedAddressValue = "";
            autocompleteSessionToken = null;
            return;
        }

        if (currentValue !== lastResolvedAddressValue) {
            clearResolvedAddressData();
            markAddressInvalid();
        }

        if (autocompleteMode === "new") {
            queueAutocompleteSuggestions(currentValue);
        }
    });

    addressInput.addEventListener("focus", () => {
        if (autocompleteMode === "new" && autocompletePanel && autocompletePanel.childElementCount > 0) {
            showAutocompletePanel();
        }
    });

    addressInput.addEventListener("blur", () => {
        if (autocompleteMode !== "new") {
            return;
        }

        window.setTimeout(() => {
            hideAutocompletePanel();
        }, 150);
    });

    addressInput.addEventListener("keydown", (event) => {
        if (autocompleteMode !== "new" || event.key !== "Enter" || !autocompletePanel || autocompletePanel.classList.contains("is-hidden")) {
            return;
        }

        const firstOption = autocompletePanel.querySelector(".google-autocomplete-option");
        if (!firstOption) {
            return;
        }

        event.preventDefault();
        firstOption.click();
    });

    if (!documentClickListenerBound) {
        documentClickListenerBound = true;
        document.addEventListener("click", (event) => {
            if (!autocompleteFieldWrapper || !autocompleteFieldWrapper.contains(event.target)) {
                hideAutocompletePanel();
            }
        });
    }
}

function initLegacyAutocompleteAddress() {
    if (!addressInput || !hasLegacyAutocompleteSupport()) {
        return;
    }

    legacyAutocomplete = new google.maps.places.Autocomplete(addressInput, {
        componentRestrictions: { country: "ES" },
        fields: ["address_components", "formatted_address", "geometry", "name"],
    });

    legacyAutocomplete.addListener("place_changed", () => {
        const place = legacyAutocomplete.getPlace();
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
}

async function initAutocompleteAddress() {
    if (!addressInput || !hasPlacesSupport()) {
        return;
    }

    const placesLibrary = await getPlacesLibrary();
    if (placesLibrary && placesLibrary.AutocompleteSuggestion) {
        autocompleteMode = "new";
        createAutocompletePanel();
        bindAddressInputEvents();
        return;
    }

    if (hasLegacyAutocompleteSupport()) {
        autocompleteMode = "legacy";
        bindAddressInputEvents();
        initLegacyAutocompleteAddress();
    }
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

async function initGoogleMapsAddressControls() {
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
    await initAutocompleteAddress();

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
    document.addEventListener("DOMContentLoaded", () => {
        initGoogleMapsAddressControls();
    });
} else {
    initGoogleMapsAddressControls();
}
