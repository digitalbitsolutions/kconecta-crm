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
    let currentMunicipality = '';

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
        currentMunicipality = '';
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
            
            const localityComponent = place && place.address_components ? place.address_components.find((component) => Array.isArray(component.types) && component.types.includes('locality')) : null;
            currentMunicipality = localityComponent ? localityComponent.long_name : '';

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

    button.addEventListener('click', async () => {
        lockButton();
        const originalText = button.textContent;
        button.textContent = 'Calculando...';
        
        const resultContainer = document.getElementById('cadastral-result-container');
        const errorContainer = document.getElementById('cadastral-error-container');
        
        if(resultContainer) resultContainer.style.display = 'none';
        if(errorContainer) errorContainer.style.display = 'none';

        try {
            const postalCode = postalCodeInput.value;
            const m2 = areaInput.value;
            
            const response = await fetch(`/api/cadastral/estimate?postal_code=${postalCode}&m2=${m2}&municipality=${encodeURIComponent(currentMunicipality)}`);
            const json = await response.json();
            
            if (response.ok && json.success && json.data) {
                if(resultContainer) {
                    const formatCurrency = (val) => new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(val);
                    
                    document.getElementById('cadastral-estimated-value').textContent = formatCurrency(json.data.estimated_value);
                    document.getElementById('cadastral-min-value').textContent = formatCurrency(json.data.min_value);
                    document.getElementById('cadastral-max-value').textContent = formatCurrency(json.data.max_value);
                    document.getElementById('cadastral-records-count').textContent = json.data.base_stats.total_areas;
                    document.getElementById('cadastral-postal-result').textContent = json.data.base_stats.postal_code;
                    
                    const advancedLink = document.getElementById('advanced-calc-link');
                    if(advancedLink) {
                        const addressText = encodeURIComponent(input.value);
                        advancedLink.href = `/calculadora-avanzada?address=${addressText}&postal_code=${postalCode}&municipality=${encodeURIComponent(currentMunicipality)}&m2=${m2}`;
                    }

                    resultContainer.style.display = 'block';
                }
            } else {
                if(errorContainer) {
                    errorContainer.textContent = json.message || 'Error al obtener la estimación.';
                    errorContainer.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error fetching cadastral data:', error);
            if(errorContainer) {
                errorContainer.textContent = 'Ocurrió un error al intentar conectar con el servidor.';
                errorContainer.style.display = 'block';
            }
        } finally {
            button.textContent = originalText;
            unlockButton();
        }
    });

    markAddressInvalid();
    lockButton();
    initGoogleValidation();
})();
