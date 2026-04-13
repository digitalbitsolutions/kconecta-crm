@extends('layouts.backoffice')

@section('title', 'Kconecta - Proveedor de servicio')

@section('heading')
    Agregar proveedor de servicio
@endsection

@section('subheading')
    Completa los datos para registrar un proveedor de servicio
@endsection

@section('header_actions')
    <a class="secondary" href="{{ url('/post/services') }}">Ver proveedores</a>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/libraries/bulma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app/map_address.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_text.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_radio.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_checkbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page/property-form.css') }}">
@endsection

@section('content')
    <form action="{{ url('/post/create_service') }}" method="post" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="container-title-page">
            <h2>Complete los datos para registrar &raquo; <span>Proveedor de servicio</span></h2>
        </div>
        <div class="container-main">
            <input type="hidden" name="city" id="city">
            <input type="hidden" name="postal_code" id="postal_code">
            <input type="hidden" name="province" id="province">
            <input type="hidden" name="country" id="country">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <h2 class="title-main-row-section">Datos del usuario</h2>
            <div class="container-row-form box">
                <label>
                    <span class="title-label">Tipo de documento *</span>
                    <div class="select">
                        <select name="document_type" id="document_type" required>
                            <option value="" selected disabled>Seleccione</option>
                            <option value="dni">DNI</option>
                            <option value="nie">NIE</option>
                            <option value="passport">Pasaporte</option>
                            <option value="cif">CIF</option>
                            <option value="otros">Otro</option>
                        </select>
                    </div>
                </label>
                <label>
                    <span class="title-label">N&uacute;mero de documento *</span>
                    <input type="text" class="input" name="document_number" required>
                </label>
                <label>
                    <span class="title-label">Nombre *</span>
                    <input type="text" class="input" name="first_name" required>
                </label>
                <label class="container-label-last_name">
                    <span class="title-label">Apellido *</span>
                    <input type="text" class="input" name="last_name" required>
                </label>
                <label>
                    <span class="title-label">M&oacute;vil (WhatsApp) *</span>
                    <input type="text" class="input" name="phone" required>
                </label>
                <label>
                    <span class="title-label">Tel&eacute;fono fijo</span>
                    <input type="text" class="input" name="landline_phone">
                </label>
                <label>
                    <span class="title-label">E-mail *</span>
                    <input type="email" class="input" name="email" required autocomplete="off">
                </label>
                <label>
                    <span class="title-label">Direcci&oacute;n *</span>
                    <div class="container-google-maps-required-tags">
                        <input type="text" class="input" id="address" name="address" required autocomplete="off">
                        <button class="button" type="button" id="button-open-map-google">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24">
                                <g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666">
                                    <path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/>
                                    <path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/>
                                </g>
                            </svg>
                        </button>
                    </div>
                </label>
                <label class="div-col-1">
                    <span class="title-label">Contrase&ntilde;a *</span>
                    <div class="container-controls-cont">
                        <button type="button" class="icon is-left" id="generate-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32">
                                <path fill="#ffffff" d="M21 2a8.998 8.998 0 0 0-8.612 11.612L2 24v6h6l10.388-10.388A9 9 0 1 0 21 2m0 16a7 7 0 0 1-2.032-.302l-1.147-.348l-.847.847l-3.181 3.181L12.414 20L11 21.414l1.379 1.379l-1.586 1.586L9.414 23L8 24.414l1.379 1.379L7.172 28H4v-3.172l9.802-9.802l.848-.847l-.348-1.147A7 7 0 1 1 21 18"/>
                                <circle cx="22" cy="10" r="2" fill="#ffffff"/>
                            </svg>
                        </button>
                        <input type="password" class="input" name="password" id="password" required autocomplete="off">
                        <button type="button" class="icon is-right" id="view-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32">
                                <path fill="#ffffff" d="M30.94 15.66A16.69 16.69 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68A16.69 16.69 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M16 25c-5.3 0-10.9-3.93-12.93-9C5.1 10.93 10.7 7 16 7s10.9 3.93 12.93 9C26.9 21.07 21.3 25 16 25"/>
                                <path fill="#ffffff" d="M16 10a6 6 0 1 0 6 6a6 6 0 0 0-6-6m0 10a4 4 0 1 1 4-4a4 4 0 0 1-4 4"/>
                            </svg>
                        </button>
                    </div>
                </label>
            </div>

            <div class="container-row-form box">
                <label class="label-col-100">
                    <span class="title-label">Disponibilidad *</span>
                    <input type="text" class="input" name="availability" required>
                </label>
                <label>
                    <span class="title-label">Sitio web</span>
                    <input type="text" class="input" name="page_url">
                </label>
            </div>

            <div class="container-row-form-col-1 box">
                <div class="div-col-1">
                    <label>
                        <span class="title-label">Descripci&oacute;n *</span>
                        <textarea class="textarea" name="description" required></textarea>
                    </label>
                </div>
            </div>

            <h2 class="title-main-row-section">Tipo de servicio</h2>
            <div class="container-row-form-col-1 box">
                <div class="div-col-3">
                    @foreach ($serviceType as $serviceTypeItem)
                        <label class="radio label-radio-checkbox-col-100">
                            <input type="checkbox" class="checkbox-input-ui" hidden name="service_type[]" value="{{ $serviceTypeItem->id }}">
                            <span class="checkmark-checkbox-input-ui"></span>
                            {{ $serviceTypeItem->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <h2 class="title-main-row-section">Fotos y videos</h2>
            <div class="container-row-form-images box">
                <div class="container-main-template-input-simple">
                    <div class="container-image">
                        <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" id="preview_cover_image">
                    </div>
                    <label for="cover_image">
                        <div class="btn-upload-image">
                            Subir imagen de portada *
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                                <g fill="none">
                                    <path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/>
                                    <path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/>
                                </g>
                            </svg>
                        </div>
                        <input type="file" name="cover_image" id="cover_image" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                    </label>
                </div>

                <div class="container-main-template-input-simple">
                    <div class="container-images" id="container-images">
                        <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" width="240" height="180" style="width: 240px; height: 180px; object-fit: cover;">
                        <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" width="240" height="180" style="width: 240px; height: 180px; object-fit: cover;">
                        <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" width="240" height="180" style="width: 240px; height: 180px; object-fit: cover;">
                    </div>
                    <label for="more_images">
                        <div class="btn-upload-image">
                            Subir im&aacute;genes (opcional)
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                                <g fill="none">
                                    <path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/>
                                    <path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/>
                                </g>
                            </svg>
                        </div>
                        <input type="file" name="more_images[]" id="more_images" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp" multiple>
                    </label>
                </div>

                <div class="container-main-template-input-simple">
                    <div class="container-video" id="container-video">
                        <img src="{{ asset('img/play-button-circle-icon.webp') }}" alt="video">
                        <video id="preview_video" width="500" controls style="display: none;"></video>
                    </div>
                    <label for="video">
                        <div class="btn-upload-image">
                            Subir video (max. 200MB) (opcional)
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                                <g fill="none">
                                    <path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/>
                                    <path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/>
                                </g>
                            </svg>
                        </div>
                        <input type="file" name="video" id="video" class="input-simple-main-template" accept=".mp4,.mov,.avi,.mpeg,video/mp4,video/quicktime,video/x-msvideo,video/mpeg">
                    </label>
                </div>
            </div>

            <div class="box">
                <button class="button container-button-save" type="submit">Guardar y publicar</button>
            </div>
        </div>
    </form>

    <div class="modal" id="modal-view-map-select">
        <div class="modal-background"></div>
        <div class="modal-content box">
            <div class="message-title-map">
                <h3>Seleccione ubicaci&oacute;n</h3>
                <p>Arrastre el marcador a la ubicaci&oacute;n exacta del proveedor.</p>
            </div>
            <div class="container-map-google">
                <div id="map"></div>
            </div>
            <div class="container-details-map">
                <div class="container-row-value">
                    <span class="name-attr">Calle:</span>
                    <span class="value-attr" id="route-map"></span>
                </div>
                <div class="container-row-value">
                    <span class="name-attr">Ciudad:</span>
                    <span class="value-attr" id="city-map"></span>
                </div>
                <div class="container-row-value">
                    <span class="name-attr">Provincia:</span>
                    <span class="value-attr" id="state-map"></span>
                </div>
                <div class="container-row-value">
                    <span class="name-attr">Pa&iacute;s:</span>
                    <span class="value-attr" id="country-map"></span>
                </div>
            </div>
            <div class="container-controls-map">
                <button class="button" onclick="closeModal(document.getElementById('modal-view-map-select'))">Cerrar</button>
                <button class="button" id="my-location">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                        <path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/>
                    </svg>
                    Mi ubicaci&oacute;n
                </button>
            </div>
        </div>
        <button class="button modal-close"></button>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/preview_image.js') }}"></script>
    <script>
        preview_image_auto("more_images", "container-images");
        preview_image("cover_image", "preview_cover_image");
        preview_video("video", "preview_video");

        const documentType = document.getElementById("document_type");
        const lastNameContainer = document.querySelector(".container-label-last_name");
        const lastNameLabel = lastNameContainer ? lastNameContainer.querySelector("span") : null;
        const lastNameInput = lastNameContainer ? lastNameContainer.querySelector("input") : null;

        if (documentType && lastNameLabel && lastNameInput) {
            documentType.addEventListener("change", () => {
                if (documentType.value === "cif") {
                    lastNameLabel.textContent = "Apellido";
                    lastNameInput.value = "";
                    lastNameInput.disabled = true;
                    lastNameInput.removeAttribute("required");
                } else {
                    lastNameLabel.textContent = "Apellido *";
                    lastNameInput.disabled = false;
                    lastNameInput.setAttribute("required", true);
                }
            });
        }

        const inputPass = document.getElementById("password");
        const buttonViewPassword = document.getElementById("view-password");

        const generatePassword = () => {
            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#$%()[]";
            let password = "";
            for (let i = 0; i < 10; i += 1) {
                const randomIndex = Math.floor(Math.random() * chars.length);
                password += chars[randomIndex];
            }
            inputPass.value = password;
        };

        document.getElementById("generate-password").addEventListener("click", () => {
            generatePassword();
            inputPass.type = "text";
        });

        buttonViewPassword.addEventListener("click", () => {
            if (buttonViewPassword.dataset.hidden === "hidden") {
                buttonViewPassword.dataset.hidden = "show";
                inputPass.type = "password";
            } else {
                buttonViewPassword.dataset.hidden = "hidden";
                inputPass.type = "text";
            }
        });

        const openModalAddress = document.getElementById("button-open-map-google");
        if (openModalAddress) {
            openModalAddress.addEventListener("click", () => {
                openModal(document.getElementById("modal-view-map-select"));
            });
        }
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places"></script>
    <script src="{{ asset('js/google_maps.js') }}"></script>
@endsection
