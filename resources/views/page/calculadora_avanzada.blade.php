@extends('layouts.page')

@section('css')
<style>
/* Advanced Calculator Premium CSS */
.avm-container {
    max-width: 800px;
    margin: 40px auto 80px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    font-family: 'Inter', sans-serif;
}
.avm-header {
    text-align: center;
    margin-bottom: 40px;
}
.avm-header h1 {
    font-size: 2.5rem;
    color: #0b1a57;
    margin-bottom: 10px;
    font-weight: 800;
}
.avm-header p {
    color: #4c5871;
    font-size: 1.1rem;
}
.avm-step {
    display: none;
    animation: fadeIn 0.5s ease;
}
.avm-step.active {
    display: block;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.avm-group {
    margin-bottom: 25px;
}
.avm-label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    color: #10203f;
    font-size: 1.1rem;
}
.avm-input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e0e6ed;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}
.avm-input:focus {
    border-color: #0f40cf;
    box-shadow: 0 0 0 4px rgba(15, 64, 207, 0.1);
    outline: none;
}
.avm-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.avm-radio-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}
.avm-radio-card:hover {
    border-color: #0f40cf;
    background: #f4f7ff;
    transform: translateY(-2px);
}
.avm-radio-card input {
    display: none;
}
.avm-radio-card.selected {
    border-color: #0f40cf;
    background: #0f40cf;
}
.avm-radio-card.selected span {
    color: white !important;
}
.avm-radio-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-top: 10px;
    color: #10203f;
    transition: color 0.3s;
}
.avm-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
}
.avm-btn {
    padding: 14px 28px;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1.1rem;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}
.avm-btn-back {
    background: #f0f4f8;
    color: #4c5871;
}
.avm-btn-back:hover {
    background: #e2e8f0;
}
.avm-btn-next {
    background: #0f40cf;
    color: white;
}
.avm-btn-next:hover {
    background: #0c33a6;
    box-shadow: 0 4px 15px rgba(15, 64, 207, 0.3);
}
.avm-progress {
    width: 100%;
    height: 6px;
    background: #e0e6ed;
    border-radius: 3px;
    margin-bottom: 30px;
    overflow: hidden;
}
.avm-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0f40cf, #00d2ff);
    width: 25%;
    transition: width 0.4s ease;
}

/* Toggle Switch for Extras */
.avm-switch-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border: 1px solid #e0e6ed;
    border-radius: 10px;
    margin-bottom: 10px;
}
.avm-switch-wrap span {
    font-weight: 600;
    color: #10203f;
}
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
}
.switch input { 
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #0f40cf;
}
input:checked + .slider:before {
    transform: translateX(22px);
}
</style>
@endsection

@section('content')
<div style="background-color: #f4f7ff; min-height: calc(100vh - 100px); padding-top: 40px; padding-bottom: 40px;">
    <div class="avm-container">
        <div class="avm-header">
            <h1>Tasación Avanzada</h1>
            <p>Calcula el valor real de mercado de tu inmueble en sencillos pasos.</p>
            @if(request('address') && request('m2'))
            <div style="background: rgba(15, 64, 207, 0.05); padding: 10px 20px; border-radius: 8px; display: inline-block; margin-top: 15px; border: 1px solid rgba(15, 64, 207, 0.2);">
                <span style="font-size: 1.2rem;">📍</span> <strong style="color: #0b1a57;">{{ request('address') }}</strong> | {{ request('m2') }} m²
            </div>
            @endif
        </div>

        <div class="avm-progress">
            <div class="avm-progress-bar" id="avm-progress-bar" style="width: {{ request('address') ? '50%' : '25%' }}"></div>
        </div>

        <form id="avm-form" class="__no-loader">
            <!-- Paso 1: Ubicación -->
            <div class="avm-step {{ request('address') ? '' : 'active' }}" id="step-1">
                <h2 style="color: #0b1a57; margin-bottom: 20px;">1. Ubicación y Superficie</h2>
                <div class="avm-group">
                    <label class="avm-label">Dirección completa</label>
                    <input type="text" id="avm-address" class="avm-input" placeholder="Empieza a escribir tu dirección..." required autocomplete="off" value="{{ request('address') }}">
                    <input type="hidden" id="avm-postal-code" name="postal_code" value="{{ request('postal_code') }}">
                    <input type="hidden" id="avm-municipality" name="municipality" value="{{ request('municipality') }}">
                </div>
                <div class="avm-group">
                    <label class="avm-label">Metros cuadrados construidos</label>
                    <input type="number" id="avm-m2" name="m2" class="avm-input" placeholder="Ej. 90" min="1" required value="{{ request('m2') }}">
                </div>
                <div class="avm-buttons" style="justify-content: flex-end;">
                    <button type="button" class="avm-btn avm-btn-next" onclick="nextStep(2)">Siguiente Paso ➔</button>
                </div>
            </div>

            <!-- Paso 2: Tipología -->
            <div class="avm-step {{ request('address') ? 'active' : '' }}" id="step-2">
                <h2 style="color: #0b1a57; margin-bottom: 20px;">2. Tipo de Inmueble</h2>
                <div class="avm-grid-2">
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="13" checked>
                        <span style="font-size: 2rem;">🏢</span>
                        <span class="avm-radio-title">Piso</span>
                    </label>
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="1">
                        <span style="font-size: 2rem;">🏡</span>
                        <span class="avm-radio-title">Casa o Chalet</span>
                    </label>
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="15">
                        <span style="font-size: 2rem;">🌾</span>
                        <span class="avm-radio-title">Casa rústica</span>
                    </label>
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="4">
                        <span style="font-size: 2rem;">🏪</span>
                        <span class="avm-radio-title">Local o nave</span>
                    </label>
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="14">
                        <span style="font-size: 2rem;">🚗</span>
                        <span class="avm-radio-title">Garaje</span>
                    </label>
                    <label class="avm-radio-card" onclick="selectRadio(this, 'property_type')">
                        <input type="radio" name="property_type" value="9">
                        <span style="font-size: 2rem;">🏔️</span>
                        <span class="avm-radio-title">Terreno</span>
                    </label>
                </div>
                <div class="avm-buttons">
                    <button type="button" class="avm-btn avm-btn-back" onclick="nextStep(1)" style="{{ request('address') ? 'display:none;' : '' }}">← Volver</button>
                    <button type="button" class="avm-btn avm-btn-next" onclick="nextStep(3)">Siguiente Paso ➔</button>
                </div>
            </div>

            <!-- Paso 3: Estado y Distribución -->
            <div class="avm-step" id="step-3">
                <h2 style="color: #0b1a57; margin-bottom: 20px;">3. Estado y Distribución</h2>
                
                <div id="avm-condition-container">
                    <label class="avm-label">Estado de conservación</label>
                    <div class="avm-grid-2" style="margin-bottom: 25px;">
                        <label class="avm-radio-card" onclick="selectRadio(this, 'state_conservation')">
                            <input type="radio" name="state_conservation" value="2">
                            <span class="avm-radio-title">A reformar</span>
                        </label>
                        <label class="avm-radio-card" onclick="selectRadio(this, 'state_conservation')">
                            <input type="radio" name="state_conservation" value="1" checked>
                            <span class="avm-radio-title">Buen estado</span>
                        </label>
                        <label class="avm-radio-card" onclick="selectRadio(this, 'state_conservation')">
                            <input type="radio" name="state_conservation" value="3">
                            <span class="avm-radio-title">Obra nueva</span>
                        </label>
                    </div>
                </div>

                <div class="avm-grid-2" id="avm-distribution-container">
                    <div class="avm-group">
                        <label class="avm-label">Habitaciones</label>
                        <input type="number" name="bedrooms" class="avm-input" value="3" min="1">
                    </div>
                    <div class="avm-group">
                        <label class="avm-label">Baños</label>
                        <input type="number" name="bathrooms" class="avm-input" value="2" min="1">
                    </div>
                </div>
                
                <div class="avm-buttons">
                    <button type="button" class="avm-btn avm-btn-back" onclick="nextStep(2)">← Volver</button>
                    <button type="button" class="avm-btn avm-btn-next" onclick="nextStep(4)">Siguiente Paso ➔</button>
                </div>
            </div>

            <!-- Paso 4: Extras y Contacto -->
            <div class="avm-step" id="step-4">
                <h2 style="color: #0b1a57; margin-bottom: 20px;">4. Extras y Reporte</h2>
                
                <div class="avm-group" id="avm-extras-container">
                    <label class="avm-label" style="margin-bottom:15px;">Características adicionales</label>
                    
                    <div class="avm-switch-wrap">
                        <span>Ascensor en el edificio</span>
                        <label class="switch"><input type="checkbox" name="has_elevator" checked><span class="slider"></span></label>
                    </div>
                    <div class="avm-switch-wrap">
                        <span>Plaza de garaje incluida</span>
                        <label class="switch"><input type="checkbox" name="has_parking"><span class="slider"></span></label>
                    </div>
                    <div class="avm-switch-wrap">
                        <span>Piscina / Zonas comunes</span>
                        <label class="switch"><input type="checkbox" name="has_pool"><span class="slider"></span></label>
                    </div>
                </div>
                
                <div class="avm-buttons" id="avm-action-buttons">
                    <button type="button" class="avm-btn avm-btn-back" onclick="nextStep(3)">← Volver</button>
                    <button type="submit" class="avm-btn avm-btn-next" style="background: linear-gradient(90deg, #0f40cf, #00d2ff); box-shadow: 0 5px 15px rgba(15, 64, 207, 0.4);" id="avm-submit-btn">Calcular Valor 🚀</button>
                </div>

                <!-- Contenedor de Resultados (Oculto inicialmente) -->
                <div id="avm-result-container" style="display: none; margin-top: 20px; padding: 25px; background: #fff; border-radius: 15px; border: 2px solid #0f40cf; text-align: center; box-shadow: 0 10px 25px rgba(15, 64, 207, 0.15); animation: fadeIn 0.5s ease;">
                    <h3 style="margin-top: 0; font-size: 1.4rem; color: #0b1a57;">Tu Estimación de Mercado</h3>
                    <p style="font-size: 2.8rem; font-weight: 800; color: #0f40cf; margin: 15px 0;" id="avm-final-value">-- €</p>
                    <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 0.9rem; color: #666; display: block;">Valor Mínimo</span>
                            <strong style="color: #333; font-size: 1.2rem;" id="avm-min-value">-- €</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.9rem; color: #666; display: block;">Valor Máximo</span>
                            <strong style="color: #333; font-size: 1.2rem;" id="avm-max-value">-- €</strong>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #999; margin: 0; margin-bottom: 25px;">*Estimación algorítmica basada en las características introducidas y el precio medio de la zona.</p>
                    
                    <a href="{{ url('/') }}" class="avm-btn" style="background: #f0f4f8; color: #0b1a57; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid #e0e6ed; transition: all 0.3s ease;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    function initAdvancedAutocomplete() {
        if (!window.google || !window.google.maps || !window.google.maps.places) {
            setTimeout(initAdvancedAutocomplete, 500);
            return;
        }
        const input = document.getElementById('avm-address');
        const autocomplete = new google.maps.places.Autocomplete(input, { types: ['geocode'] });
        
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            let postalCode = '';
            let locality = '';
            
            if (place.address_components) {
                place.address_components.forEach(comp => {
                    if (comp.types.includes('postal_code')) postalCode = comp.long_name;
                    if (comp.types.includes('locality')) locality = comp.long_name;
                });
            }
            document.getElementById('avm-postal-code').value = postalCode;
            document.getElementById('avm-municipality').value = locality;
        });
    }

    function selectRadio(card, name) {
        document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
            input.closest('.avm-radio-card').classList.remove('selected');
            const title = input.closest('.avm-radio-card').querySelector('.avm-radio-title');
            if(title) title.style.setProperty('color', '#10203f', 'important');
        });
        card.classList.add('selected');
        const activeTitle = card.querySelector('.avm-radio-title');
        if(activeTitle) activeTitle.style.setProperty('color', 'white', 'important');
        card.querySelector('input').checked = true;

        if (name === 'property_type') {
            const val = parseInt(card.querySelector('input').value);
            const isRes = [1, 13, 15].includes(val);
            const isTerreno = val === 9;
            
            const distContainer = document.getElementById('avm-distribution-container');
            const extrasContainer = document.getElementById('avm-extras-container');
            const condContainer = document.getElementById('avm-condition-container');

            if (distContainer) distContainer.style.display = isRes ? 'grid' : 'none';
            if (extrasContainer) extrasContainer.style.display = isRes ? 'block' : 'none';
            if (condContainer) condContainer.style.display = isTerreno ? 'none' : 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.avm-radio-card input:checked').forEach(input => {
            selectRadio(input.closest('.avm-radio-card'), input.name);
        });
        initAdvancedAutocomplete();
    });

    function nextStep(step) {
        if (step > 1) {
            const addr = document.getElementById('avm-address').value;
            const m2 = document.getElementById('avm-m2').value;
            if (!addr || !m2) {
                alert('Por favor completa la dirección y los metros cuadrados.');
                return;
            }
        }

        document.querySelectorAll('.avm-step').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        document.getElementById('avm-progress-bar').style.width = (step * 25) + '%';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('avm-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = document.getElementById('avm-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Calculando...';
        submitBtn.disabled = true;

        // Force hide global loader just in case of JS caching
        const globalLoader = document.getElementById('loader-page-change');
        if(globalLoader) globalLoader.style.display = 'none';

        const formData = new FormData(e.target);
        
        // Convert checkbox on/off to boolean (1/0)
        // If type is not residential, send 0 for these fields
        const propType = parseInt(formData.get('property_type'));
        const isResidential = [1, 13, 15].includes(propType);
        
        const payload = {
            postal_code: formData.get('postal_code'),
            municipality: formData.get('municipality'),
            m2: formData.get('m2'),
            property_type: formData.get('property_type'),
            state_conservation: formData.get('state_conservation'),
            bedrooms: isResidential ? formData.get('bedrooms') : 0,
            bathrooms: isResidential ? formData.get('bathrooms') : 0,
            has_elevator: isResidential && formData.get('has_elevator') === 'on' ? 1 : 0,
            has_parking: isResidential && formData.get('has_parking') === 'on' ? 1 : 0,
            has_pool: isResidential && formData.get('has_pool') === 'on' ? 1 : 0,
        };

        try {
            const response = await fetch('/api/cadastral/advanced-estimate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const json = await response.json();

            if (json.success) {
                document.getElementById('avm-action-buttons').style.display = 'none';
                document.getElementById('avm-result-container').style.display = 'block';
                
                const formatter = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });
                
                document.getElementById('avm-final-value').textContent = formatter.format(json.data.estimated_value);
                document.getElementById('avm-min-value').textContent = formatter.format(json.data.min_value);
                document.getElementById('avm-max-value').textContent = formatter.format(json.data.max_value);
            } else {
                alert(json.message || 'Error calculando la tasación avanzada.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error fetching advanced estimate:', error);
            alert('Error de conexión con el servidor.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
</script>

<?php
// Incluir el script de Google Maps si existe en el layout global
$googleMapsApiKey = env('GOOGLE_MAPS_API_KEY'); // Asumiendo que puede estar en el env
?>
@if(!empty($googleMapsApiKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places" async defer></script>
@endif
@endsection
