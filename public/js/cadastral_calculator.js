(function () {
    const input = document.getElementById('cadastral-address-input');
    const areaInput = document.getElementById('cadastral-area-input');
    const placeIdInput = document.getElementById('cadastral-address-place-id');
    const postalCodeInput = document.getElementById('cadastral-address-postal-code');
    const button = document.getElementById('cadastral-submit-btn');

    if (!input || !areaInput || !placeIdInput || !postalCodeInput || !button) {
        return;
    }

    let addressValidated = false;

    const lockButton = () => {
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
    };

    const unlockButton = () => {
        button.disabled = false;
        button.setAttribute('aria-disabled', 'false');
    };

    const getPostalCodeFromComponents = (components) => {
        if (!Array.isArray(components)) {
            return '';
        }

        const postalComponent = components.find((component) => Array.isArray(component.types) && component.types.includes('postal_code'));
        return postalComponent && postalComponent.long_name ? String(postalComponent.long_name).trim() : '';
    };

    const markAddressInvalid = () => {
        addressValidated = false;
        placeIdInput.value = '';
        postalCodeInput.value = '';
        if (input.value.trim().length > 0) {
            input.setCustomValidity('Selecciona una direccion valida de las sugerencias de Google.');
        } else {
            input.setCustomValidity('');
        }
    };

    const markAddressValid = (placeId, postalCode) => {
        addressValidated = true;
        placeIdInput.value = placeId;
        postalCodeInput.value = postalCode;
        input.setCustomValidity('');
    };

    const syncButtonState = () => {
        const areaValue = Number(areaInput.value);
        const hasValidArea = Number.isFinite(areaValue) && areaValue > 0;

        if (addressValidated && hasValidArea) {
            unlockButton();
            return;
        }

        lockButton();
    };

    const initGoogleValidation = () => {
        if (!window.google || !google.maps || !google.maps.places || !google.maps.places.Autocomplete) {
            markAddressInvalid();
            syncButtonState();
            return;
        }

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode'],
            fields: ['place_id', 'formatted_address', 'address_components'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            const placeId = place && place.place_id ? String(place.place_id).trim() : '';
            const postalCode = getPostalCodeFromComponents(place ? place.address_components : []);

            if (placeId !== '' && postalCode !== '') {
                input.value = place.formatted_address || input.value;
                markAddressValid(placeId, postalCode);
                syncButtonState();
                return;
            }

            markAddressInvalid();
            syncButtonState();
        });
    };

    input.addEventListener('input', () => {
        markAddressInvalid();
        syncButtonState();
    });
    input.addEventListener('blur', () => {
        if (!addressValidated && input.value.trim().length > 0) {
            input.reportValidity();
        }
    });
    areaInput.addEventListener('input', syncButtonState);

    button.addEventListener('click', () => {
        lockButton();
    });

    markAddressInvalid();
    lockButton();
    initGoogleValidation();
})();
