@extends('layouts.page')
@section('css')
    <link rel="stylesheet" href="<?= base_url("css/ui/loader_circle.css") ?>">
    <link rel="stylesheet" href="<?= base_url("css/page/index_review.css") ?>">
    <link rel="stylesheet" href="<?= base_url("css/libraries/swiper-bundle.min.css") ?>">
    <!-- seo -->
    <meta name="description" content="Plataforma inmobiliaria para publicar propiedades y servicios del hogar gratis y sin límite. Regístrate y gestiona inmuebles fácilmente, sin comisiones.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Kconecta | Publica, alquila o vende tu propiedad fácil y rápido">
    <meta property="og:description" content="Descubre las mejores propiedades y servicios para el hogar en un solo lugar">
    <meta property="og:image" content="<?= base_url()."img/kconecta.webp" ?>">
    <meta property="og:url" content="https://kconecta.com">
    <meta property="og:site_name" content="Kconecta">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9259257545893744" crossorigin="anonymous"></script>

@endsection
@section('nav_option')
    <a href="{{ url('/blogs') }}" aria-label="Novedades">
        <span>Novedades</span>
    </a>
@endsection
@section('content')
    <div class="container-home-main-index-page">
        <section class="banner" >
            <div class="container-title-message">
                <h1>Descubre las mejores <br> propiedades y servicios para el hogar en un solo lugar</h1>
                <span>Maximiza tu presencia y sé la portada destacada en nuestro portal líder de inmuebles.</span>
                <div class="container-images">
                    <img src="<?= base_url()."img/background-image.webp" ?>" alt="kconecta">
                </div>
            </div>
            <div class="search-bar">
                <div class="container-1">
                    <div class="container-title-card">
                        <!-- <h2>Seleccione una opción</h2> -->
                    </div>
                    <div class="container-btns-action-main-ctrl">
                        <button id="redirect-property" style="background-color: var(--color-main-1); color: white;">
                            <img src="<?= base_url("img/casa-1.webp") ?>" alt="propiedades kconecta">
                            Propiedades
                        </button>
                        <button id="redirect-service">
                            <img src="<?= base_url("img/servicio-1.webp") ?>" alt="servicios kconecta">
                            Servicios para el hogar
                        </button>
                    </div>
                </div>
                <div class="container-2 container-search-open-box">
                    <form action="<?= site_url("result") ?>" method="get">
                        <input type="hidden" name="ps" value="1" required>
                        <div class="container-two-col-portrait container-two-col">
                            <label for="">
                                <span>Alquiler o venta </span>
                                <select name="ca" id="category_property_id">
                                    <option value="" selected disabled>Seleccione</option>
                                    <option value="1" >Alquiler</option>
                                    <option value="2">Venta</option>
                                </select>
                            </label>
                            <label for="">
                                <span>Tipo de propiedad </span>
                                <select name="ty" id="type_property_id">
                                    <option value="" selected disabled>Seleccione</option>
                                    <option value="1">Casa o chalet</option>
                                    <option value="15">Casa rústica</option>
                                    <option value="13">Piso</option>
                                    <option value="4">Local o nave</option>
                                    <option value="14">Garaje</option>
                                    <option value="9">Terreno</option>
                                </select>
                            </label>
                        </div>
                        <div class="container-btns">
                            <div class="control has-icons-left has-icons-right">
                                <input class="input is-medium" type="text" name="address" placeholder="Escribe dónde buscas" id="input-search-address-property" />
                                <span class="icon is-medium is-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="#666666" d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0a5.5 5.5 0 0 1 11 0"/></svg>
                                </span>
                            </div>
                            <input type="hidden" name="mode" value="2">
                            <button class="a-link-container-redirect-map" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg>
                                Ver en mapa
                            </button>
                            <div class="container-main-results" id="container-main-results-properties">
                                <!-- <a href="" class="link-container">
                                    <div class="ctn-address">
                                        <h3>Calle las palms, Mallorca Jeronimo</h3>
                                    </div>
                                    <div class="ctn-others-details">
                                        <span class="ctn-location-main">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>
                                            <span>Barcelona</span>
                                        </span>
                                        <span class="ctn-quantity-result">12</span>
                                    </div>
                                </a> -->

                                <!-- <div class="search-not-found">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><g fill="none" stroke="#b8aaaa" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M17.5 17.5L22 22m-2-11a9 9 0 1 0-18 0a9 9 0 0 0 18 0"/><path d="M9.492 7.5c-.716.043-1.172.163-1.5.491c-.33.329-.449.785-.492 1.501M12.508 7.5c.716.043 1.172.163 1.5.491c.33.329.449.785.492 1.501m-.008 3.13c-.049.651-.173 1.076-.483 1.387c-.329.328-.785.448-1.501.491m-3.016 0c-.716-.043-1.172-.163-1.5-.491c-.311-.311-.435-.736-.484-1.388"/></g></svg>
                                    <span>No hay resultados</span>
                                </div> -->
                                
                                <!-- <div class="container-loader-search-property"><div class="loader-ui-verse-simple"></div><span>Buscando...</span></div> -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="container-3 container-search-close-box">
                    <form action="/result/services" method="get">
                        <input type="hidden" name="ps" value="2" required>
                        
                        <label for="">
                            <span>Servicio</span>
                            <select name="sti" id="sti">
                                <option value="" selected disabled>Seleccione</option>
                                <?php foreach($serviceType as $st){ ?>
                                    <option value='<?= $st["id"] ?>'><?= $st["name"] ?></option>
                                <?php } ?>
                            </select>
                        </label>
                        
                        <div class="container-btns">
                            <div class="control has-icons-left has-icons-right">
                                <input class="input is-medium" type="text" name="address" placeholder="Escribe dónde buscas" id="input-search-address-service" />
                                <span class="icon is-medium is-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="#666666" d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0a5.5 5.5 0 0 1 11 0"/></svg>
                                </span>
                            </div>
                            <input type="hidden" name="mode" value="2">
                            <button class="a-link-container-redirect-map" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg>
                                Ver en mapa
                            </button>
                            <div class="container-main-results" id="container-main-results-services">
                                <!-- <a href="" class="link-container">
                                    <div class="ctn-address">
                                        <h3>Calle las palms, Mallorca Jeronimo</h3>
                                    </div>
                                    <div class="ctn-others-details">
                                        <span class="ctn-location-main">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>
                                            <span>Barcelona</span>
                                        </span>
                                        <span class="ctn-quantity-result">12</span>
                                    </div>
                                </a> -->

                                <!-- <div class="search-not-found">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><g fill="none" stroke="#b8aaaa" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M17.5 17.5L22 22m-2-11a9 9 0 1 0-18 0a9 9 0 0 0 18 0"/><path d="M9.492 7.5c-.716.043-1.172.163-1.5.491c-.33.329-.449.785-.492 1.501M12.508 7.5c.716.043 1.172.163 1.5.491c.33.329.449.785.492 1.501m-.008 3.13c-.049.651-.173 1.076-.483 1.387c-.329.328-.785.448-1.501.491m-3.016 0c-.716-.043-1.172-.163-1.5-.491c-.311-.311-.435-.736-.484-1.388"/></g></svg>
                                    <span>No hay resultados</span>
                                </div> -->
                                
                                <!-- <div class="container-loader-search-property"><div class="loader-ui-verse-simple"></div><span>Buscando...</span></div> -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="container-images-partners" style="width: 18rem;">
                    <a href="https://sede.registradores.org/sede/sede-corpme-web/home" target="_blank" aria-label="sede.registradores.org">
                        <img src="<?= base_url()."img/photo_2025-03-01_17-07-14.webp" ?>" style="width: 100%" alt="sede.registradores.org">
                    </a>
    
                    <a href="https://www.sedecatastro.gob.es/" target="_blank" aria-label="sedecatastro.gob.es">
                        <img src="<?= base_url()."img/photo_2025-03-01_17-07-09.webp" ?>" style="width: 100%" alt="sedecatastro.gob.es">
                    </a>
                </div>
            </div>
        </section>
    </div>

    <section id="cadastral-calculator-section" class="cadastral-calculator-section">
        <div class="cadastral-calculator-overlay">
            <div class="cadastral-calculator-card">
                <h2>Valorar una propiedad</h2>
                <p class="cadastral-subtitle">Indica la dirección de la vivienda</p>
                
                <div class="cadastral-input-group">
                    <label for="cadastral-address-input" class="cadastral-calculator-label">Dirección Completa</label>
                    <div class="cadastral-input-wrapper">
                        <svg class="cadastral-input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#667085" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <input
                            id="cadastral-address-input"
                            type="text"
                            name="cadastral_address"
                            placeholder="Dirección"
                            autocomplete="off"
                            required
                        >
                    </div>
                </div>
                
                <input type="hidden" id="cadastral-address-place-id" name="cadastral_address_place_id" value="">
                <input type="hidden" id="cadastral-address-postal-code" name="cadastral_address_postal_code" value="">
                
                <div class="cadastral-input-group">
                    <label for="cadastral-area-input" class="cadastral-calculator-label">Superficie Útil</label>
                    <div class="cadastral-input-wrapper">
                        <svg class="cadastral-input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#667085" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><path d="M9 3v18"></path><path d="M14 8h3"></path><path d="M14 12h3"></path><path d="M14 16h3"></path></svg>
                        <input
                            id="cadastral-area-input"
                            type="number"
                            name="cadastral_area_m2"
                            placeholder="Ej. 85"
                            min="1"
                            step="1"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                        >
                        <span class="cadastral-input-suffix">m²</span>
                    </div>
                </div>
                
                <button id="cadastral-submit-btn" type="button" disabled aria-disabled="true">
                    Ver información vivienda
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>

                <div class="cadastral-badges">
                    <span class="cadastral-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        KCONECTA CERTIFIED
                    </span>
                    <span class="cadastral-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        INSTANT RESULTS
                    </span>
                </div>
                <div id="cadastral-result-container" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0; text-align: center;">
                    <h3 style="margin-top: 0; font-size: 1.2rem; color: #333;">Estimación de Valor</h3>
                    <p style="font-size: 1.8rem; font-weight: bold; color: var(--color-main-1); margin: 10px 0;" id="cadastral-estimated-value"></p>
                    <p style="font-size: 0.9rem; color: #666; margin: 0;">Rango: <span id="cadastral-min-value"></span> - <span id="cadastral-max-value"></span></p>
                    <p style="font-size: 0.8rem; color: #999; margin-top: 5px;">*Basado en <span id="cadastral-records-count"></span> propiedades en el código postal <span id="cadastral-postal-result"></span>.</p>
                    
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <a href="/calculadora-avanzada" id="advanced-calc-link" style="display: inline-block; background-color: #0f40cf; color: white; padding: 10px 20px; border-radius: 6px; font-weight: bold; text-decoration: none; margin-bottom: 5px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#0c33a6'" onmouseout="this.style.backgroundColor='#0f40cf'">Obtener estimación exacta</a>
                        <p style="font-size: 0.8rem; color: #666; margin: 0;">Calcula el valor real añadiendo el estado de tu vivienda, habitaciones, baños y extras.</p>
                    </div>
                </div>
                <div id="cadastral-error-container" style="display: none; margin-top: 15px; padding: 10px; color: #d32f2f; background: #ffebee; border-radius: 8px; font-size: 0.9rem; text-align: center;">
                </div>
            </div>
        </div>
    </section>

    <main>
        <div class="container-button-hogar">
            <!-- <button>Hogar</button> -->
        </div>
        <div class="container-row-2">
            <div class="container-text">
                <h1>Publica casas, terrenos o locales gratis. Gestiona inmuebles sin límite.</h1>
                <p>Encuentra servicios para el hogar: fontanería, electricidad, reformas y más. Regístrate gratis. Plataforma inmobiliaria 100% online. Fácil, rápida y sin comisiones</p>
            </div>
            <div class="container-image" style="background-image: url(<?= base_url()."img/spain-image.webp" ?>);">
                <span class="text-1">14 Propiedades</span>
                <div class="text-2">
                    <h3>España</h3>
                    <a href="<?= base_url("result?ps=1&ca=&ty=&address=Barcelona&mode=1")?>" aria-label="Ver todos los listados">Ver todos los listados</a>
                </div>
            </div>
            <div class="container-text">
                <h2>Sé la portada en nuestro portal inmobiliario líder</h2>
                <p>Tu portal de confianza para encontrar tu próximo hogar o inversión inmobiliaria.</p>
            </div>
        </div>
        <div class="container-properties">
            <div class="card-grid">
                <?php foreach($property as $pr){ ?>
                <a class="redirect-to-details-link" href="<?= site_url("result/".$pr["reference"]) ?>" aria-label="Propiedad Kconecta">
                <div class="card">
                    <div class="card-image">
                        <img src="<?= !empty($pr["cover_image"]["url"]) ? base_url()."img/uploads/".$pr["cover_image"]["url"] : base_url()."img/image-icon-1280x960.png" ?>" alt="kconecta">
                    </div>
                    <div class="badge-container">
                        <span class="badge"><?= $pr["type_name"] ?></span>
                        <span class="badge"><?= $pr["category_name"] ?></span>
                    </div>
                    <div class="ctn-detils-p-m">
                        <span><span class="meters-span"><?= !empty($pr["meters_built"])? $pr["meters_built"] : $pr["land_size"] ?></span> m<sup>2</sup> - <span class="price-span"><?= !empty($pr["sale_price"]) ? $pr["sale_price"] :  (!empty($pr["rental_price"]) ? $pr["rental_price"] : "") ?></span> ?</span>
                    </div>
                    <div class="card-content">
                        <h3><?= $pr["title"] ?></h3>
                        <div class="ctn-location">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="#666666" d="M12 3a7 7 0 0 0-7 7c0 2.862 1.782 5.623 3.738 7.762A26 26 0 0 0 12 20.758q.262-.201.615-.49a26 26 0 0 0 2.647-2.504C17.218 15.623 19 12.863 19 10a7 7 0 0 0-7-7m0 20.214l-.567-.39l-.003-.002l-.006-.005l-.02-.014l-.075-.053l-.27-.197a28 28 0 0 1-3.797-3.44C5.218 16.875 3 13.636 3 9.999a9 9 0 0 1 18 0c0 3.637-2.218 6.877-4.262 9.112a28 28 0 0 1-3.796 3.44a17 17 0 0 1-.345.251l-.021.014l-.006.005l-.002.001zM12 8a2 2 0 1 0 0 4a2 2 0 0 0 0-4m-4 2a4 4 0 1 1 8 0a4 4 0 0 1-8 0"/></svg>
                            <span><?= $pr["address"] ?></span>
                        </div>
                        <div class="card-details">
                            <div>
                                <?php if (!empty($pr["bedrooms"])){ ?>
                                    <span>Dormitorios</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M22 17.5H2M22 21v-5c0-1.886 0-2.828-.586-3.414S19.886 12 18 12H6c-1.886 0-2.828 0-3.414.586S2 14.114 2 16v5m14-9v-1.382c0-.508-.091-.677-.56-.877C14.463 9.324 13.278 9 12 9s-2.463.324-3.44.74c-.468.2-.56.37-.56.878V12"/><path d="M20 12V7.36c0-.691 0-1.037-.17-1.363c-.172-.327-.415-.496-.902-.834A12.1 12.1 0 0 0 12 3c-2.577 0-4.966.8-6.928 2.163c-.487.338-.73.507-.901.834S4 6.669 4 7.36V12"/></g></svg>
                                        <span><?= $pr["bedrooms"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["state_conservation"])){ ?>
                                    <span>Estado de cons.</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="#666666" d="M8.39 1c.699 0 1.4.2 2 .7l2.1 1.8c.1.1.1.3 0 .4s-.1.3 0 .4l.51.45c.098.094.259.08.388-.05c.158-.158.2-.173.399 0l1 .911c.185.181.196.289.096.389l-1.6 1.9c-.11.139-.27.198-.4.1l-.897-.8c-.1-.1-.088-.276 0-.4s.107-.304 0-.4l-.522-.431c-.156-.121-.226-.148-.45.031c-.15.121-.251.105-.351.031c-.1-.073-.086-.062-.195-.152c-.135-.11-.238-.132-.38.021l-2.6 3.2c.3.2.3.6.1.9l-3.79 4.5c-.398.3-.798.5-1.2.5s-.698-.1-.997-.4c-.7-.6-.7-1.5-.2-2.2l3.79-4.5c.1-.1.299-.2.499-.2c.1 0 .299 0 .399.1l2.2-2.6c.399-.4.25-.9-.15-1.3l-.23-.215C7.412 3.185 6.69 3 5.89 3h-.398c-.1 0-.154-.12-.1-.2l.599-.7c.599-.7 1.5-1.1 2.4-1.1zm0-1c-1.2 0-2.4.5-3.09 1.5l-.599.7c-.299.3-.399.8-.2 1.2c.2.4.599.6.998.6h.499c.499 0 .998.2 1.3.5l.2.2l-1.8 2h-.1q-.748 0-1.2.6l-3.79 4.5c-.898 1.1-.799 2.7.3 3.6c.498.4.997.6 1.7.6c.798 0 1.5-.3 1.9-.9l3.79-4.5c.298-.4.398-.9.298-1.4l1.9-2.2c.105-.119.3-.113.3 0c0 .3.2.7.5.9l.897.8q.45.3.898.3c.4 0 .8-.2.998-.5l1.6-1.9c.5-.5.33-1.32-.2-1.8l-.898-.8q-.3-.3-.898-.3h-.1c0-.3-.2-.7-.499-.9l-2.1-1.8c-.699-.7-1.6-1-2.6-1z"/></svg>
                                        <span><?= $pr["state_conservation"][0]["name"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["type_of_terrain"])){ ?>
                                    <span>Tipo de terreno</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512"><path fill="#666666" d="m40.841 312l103.652-112.88l71.904 71.904l76.29 76.289l22.626-22.626l-77.069-77.07l89.494-95.887L470.836 312H496v-19.864L328.262 104.27L215.603 224.976l-72.096-72.096L16 291.741V312zM16 392h480v32H16z"/></svg>
                                        <span><?= $pr["type_of_terrain"][0]["name"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["m_long"])){ ?>
                                    <span>Largo</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 64 64"><path fill="#666666" d="M41.1 1.8H22.9c-3.4 0-6.2 2.8-6.2 6.2v48c0 3.4 2.8 6.2 6.2 6.2h18.2c3.4 0 6.2-2.8 6.2-6.2V8c.1-3.4-2.8-6.2-6.2-6.2M42.9 56c0 1-.8 1.8-1.8 1.8H22.9c-1 0-1.8-.8-1.8-1.8v-3.5h12.5c1.2 0 2.2-1 2.2-2.2s-1-2.2-2.2-2.2H21.1V43h7.4c1.2 0 2.2-1 2.2-2.2s-1-2.2-2.2-2.2h-7.4v-4.9h12.5c1.2 0 2.2-1 2.2-2.2s-1-2.2-2.2-2.2H21.1v-4.9h7.4c1.2 0 2.2-1 2.2-2.2s-1-2.2-2.2-2.2h-7.4v-4.9h12.5c1.2 0 2.2-1 2.2-2.2s-1-2.2-2.2-2.2H21.1V8c0-1 .8-1.8 1.8-1.8h18.2c1 0 1.8.8 1.8 1.8z"/></svg>
                                        <span><?= $pr["m_long"] ?> m</span>
                                    </div>
                                <?php }else{
                                    var_dump($pr["state_conservation"]);}
                                ?>
                            </div>
                            <div>
                                <?php if (!empty($pr["bathrooms"])){ ?>
                                    <span>Baños</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M14.4 14c.972-.912 1.6-2.364 1.6-4c0-2.761-1.79-5-4-5s-4 2.239-4 5c0 1.636.628 3.088 1.6 4m-1.493 0h7.786c.586 0 1.107.414 1.107 1c0 1.51-.67 3.09-1.729 4.126c-.525.514-1.036 1.046-.4 1.743c.095.104.206.195.299.303c.328.376-.024.828-.447.828H9.277c-.423 0-.775-.452-.447-.828c.093-.108.204-.199.3-.303c.635-.697.123-1.23-.401-1.743C7.669 18.09 7 16.51 7 15c0-.586.521-1 1.107-1"/><path d="M18.29 12c.594 0 1.093-.43 1.152-.994l.367-3.504c.214-2.033.32-3.05-.076-3.818c-.987-1.912-3.3-1.675-5.139-1.675H9.406c-1.84 0-4.152-.237-5.139 1.675c-.396.768-.29 1.785-.077 3.818l.368 3.504c.06.564.558.994 1.153.994"/></g></svg>
                                        <span><?= $pr["bathrooms"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["wheeled_access"])){ ?>
                                    <span>Acceso rodado</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 32 32"><path fill="#666666" d="M16 31h-2v-.228a3.01 3.01 0 0 0-1.947-2.81l-3.532-1.324A3.9 3.9 0 0 1 6 23h2a1.895 1.895 0 0 0 1.224 1.766l3.531 1.324A5.02 5.02 0 0 1 16 30.772zm14 0h-2v-.228a3.01 3.01 0 0 0-1.947-2.81l-3.532-1.324A3.9 3.9 0 0 1 20 23h2a1.895 1.895 0 0 0 1.224 1.766l3.531 1.324A5.02 5.02 0 0 1 30 30.772zM11 13h6v2h-6z"/><path fill="#666666" d="m23.44 8l-1.27-4.55A2.01 2.01 0 0 0 20.246 2H7.754a2.01 2.01 0 0 0-1.923 1.45L4.531 8H2v2h2v7a2.003 2.003 0 0 0 2 2v2h2v-2h12v2h2v-2a2.003 2.003 0 0 0 2-2v-7h2V8ZM7.755 4h12.492l1.428 5H6.326ZM22 13h-2v2h2v2H6v-2h2v-2H6v-2h16Z"/></svg>
                                        <span><?= $pr["wheeled_access"][0]["name"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["m_wide"])){ ?>
                                    <span>Ancho</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 15 15"><path fill="#666666" fill-rule="evenodd" d="M.5 4a.5.5 0 0 0-.5.5v6a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5v-6a.5.5 0 0 0-.5-.5zm.5 6V5h1.075v2.5a.425.425 0 0 0 .85 0V5h1.15v1.5a.425.425 0 0 0 .85 0V5h1.15v1.5a.425.425 0 0 0 .85 0V5h1.15v2.5a.425.425 0 0 0 .85 0V5h1.15v1.5a.425.425 0 0 0 .85 0V5h1.15v1.5a.425.425 0 0 0 .85 0V5H14v5z" clip-rule="evenodd"/></svg>
                                        <span><?= $pr["m_wide"] ?> m</span>
                                    </div>
                                <?php }?>
                            </div>
                            <?php if (!empty($pr["showers"])){ ?>
                                <div>
                                    <span>Duchas</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512"><path fill="#666666" d="m240.801 293.826l-23.851 23.851l23.8 47.6l23.417-24.718zm15.177 101.906l22.299 44.6l23.418-24.719l-22.3-44.599zM438.926 202.62L415.8 227.032l44.423 21.246l23.127-24.412zm-96.323-10.596l42.861 20.499l23.127-24.411l-41.992-20.084zm-34.818 149.022l28.523 38.031l22.325-23.565l-28.523-38.031zm2.848-49.534l-28.936-38.582l-22.857 22.857l29.468 39.29zm-9.89-57.628l36.683 29.347l22.085-23.313l-36.001-28.801zm61.758 49.407l36.721 29.377l22.085-23.313l-36.721-29.376zm-13.814-182.604l-26.24 26.239l-24.718-24.718a111.61 111.61 0 0 0-157.839 0c-.342.341-.673.689-1.009 1.034A77.974 77.974 0 0 0 16 166.988V408h32V166.988a45.975 45.975 0 0 1 72.048-37.868a111.81 111.81 0 0 0 19.842 130.929l24.717 24.717l-23.92 23.921l20 20l208-208ZM185.006 259.911l-22.489-22.489A79.611 79.611 0 0 1 275.1 124.835l22.489 22.49Z"/></svg>
                                        <span><?= $pr["showers"] ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                            <div>
                                <?php if (!empty($pr["number_of_plants"])){ ?>
                                    <span>Plantas</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="#666666" d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-7 5.5C13 5.6 14.6 4 16.5 4S20 5.6 20 7.5S18.4 11 16.5 11S13 9.4 13 7.5M4 4h7v7H4zm0 16v-7h7v7zm16 0h-7v-7h7z"/></svg>
                                        <span><?= $pr["number_of_plants"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["elevator"])){ ?>
                                    <span>Ascensor</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#666666" d="M7.885 17.5h2v-3.808h1v-2.576q0-.633-.434-1.067t-1.066-.433h-1q-.633 0-1.067.433q-.433.434-.433 1.067v2.576h1zm1-8.808q.448 0 .752-.305t.305-.753t-.305-.752t-.753-.305t-.752.305t-.305.752q0 .449.305.753t.752.305m4.828 1.808h3.192l-1.596-2.558zm1.596 5.558l1.596-2.558h-3.192zM5.616 20q-.691 0-1.153-.462T4 18.384V5.616q0-.691.463-1.153T5.616 4h12.769q.69 0 1.153.463T20 5.616v12.769q0 .69-.462 1.153T18.384 20zm0-1h12.769q.269 0 .442-.173t.173-.442V5.615q0-.269-.173-.442T18.385 5H5.615q-.269 0-.442.173T5 5.616v12.769q0 .269.173.442t.443.173M5 19V5z"/></svg>
                                        <span><?= $pr["elevator"] == 1 ? "SI" : "NO" ?></span>
                                    </div>
                                <?php }else if (!empty($pr["max_num_tenants"])){ ?>
                                    <span>Max. inquilinos</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24"><path fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 19.5c0-1.657-2.239-3-5-3s-5 1.343-5 3m14-3c0-1.23-1.234-2.287-3-2.75M3 16.5c0-1.23 1.234-2.287 3-2.75m12-4.014a3 3 0 1 0-4-4.472M6 9.736a3 3 0 0 1 4-4.472m2 8.236a3 3 0 1 1 0-6a3 3 0 0 1 0 6"/></svg>
                                        <span><?= $pr["max_num_tenants"] ?></span>
                                    </div>
                                <?php }else if (!empty($pr["facade"])){ ?>
                                    <span>Fachada</span>
                                    <div class="ctn-icons-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M12 9v6m4 0v6m0-18v6M3 15h18M3 9h18M8 15v6M8 3v6"/></g></svg>
                                        <span><?= $pr["facade"][0]["name"] ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="ctn-icons-footer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M2 12c0-4.243 0-6.364 1.464-7.682C4.93 3 7.286 3 12 3s7.071 0 8.535 1.318S22 7.758 22 12s0 6.364-1.465 7.682C19.072 21 16.714 21 12 21s-7.071 0-8.536-1.318S2 16.242 2 12"/><path d="M8.4 8h-.8c-.754 0-1.131 0-1.366.234C6 8.47 6 8.846 6 9.6v.8c0 .754 0 1.131.234 1.366C6.47 12 6.846 12 7.6 12h.8c.754 0 1.131 0 1.366-.234C10 11.53 10 11.154 10 10.4v-.8c0-.754 0-1.131-.234-1.366C9.53 8 9.154 8 8.4 8M6 16h4m4-8h4m-4 4h4m-4 4h4"/></g></svg>
                                <span><?= $pr["user_name"] ?></span>
                            </div>
                            <div class="ctn-icons-footer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 36 36"><path fill="#666666" d="M32.25 6H29v2h3v22H4V8h3V6H3.75A1.78 1.78 0 0 0 2 7.81v22.38A1.78 1.78 0 0 0 3.75 32h28.5A1.78 1.78 0 0 0 34 30.19V7.81A1.78 1.78 0 0 0 32.25 6" class="clr-i-outline clr-i-outline-path-1"/><path fill="#666666" d="M8 14h2v2H8z" class="clr-i-outline clr-i-outline-path-2"/><path fill="#666666" d="M14 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-3"/><path fill="#666666" d="M20 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-4"/><path fill="#666666" d="M26 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-5"/><path fill="#666666" d="M8 19h2v2H8z" class="clr-i-outline clr-i-outline-path-6"/><path fill="#666666" d="M14 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-7"/><path fill="#666666" d="M20 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-8"/><path fill="#666666" d="M26 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-9"/><path fill="#666666" d="M8 24h2v2H8z" class="clr-i-outline clr-i-outline-path-10"/><path fill="#666666" d="M14 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-11"/><path fill="#666666" d="M20 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-12"/><path fill="#666666" d="M26 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-13"/><path fill="#666666" d="M10 10a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" class="clr-i-outline clr-i-outline-path-14"/><path fill="#666666" d="M26 10a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" class="clr-i-outline clr-i-outline-path-15"/><path fill="#666666" d="M13 6h10v2H13z" class="clr-i-outline clr-i-outline-path-16"/><path fill="none" d="M0 0h36v36H0z"/></svg>
                                <span><?= $pr["updated_at_text"] ?></span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="ctn-icons-footer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M1 12q11 16 22 0Q12-4 1 12"/></g></svg>
                                <span>Visitas: <?= $pr["post_visits"] ?></span>
                            </div>
                            <div class="ctn-icons-footer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" class="btn-share-facebook" data-url="https://kconecta.com/result/<?=$pr["reference"] ?>"><path class="btn-share-facebook" data-url="https://kconecta.com/result/<?=$pr["reference"] ?>" fill="#1877F2" d="M256 128C256 57.308 198.692 0 128 0S0 57.308 0 128c0 63.888 46.808 116.843 108 126.445V165H75.5v-37H108V99.8c0-32.08 19.11-49.8 48.348-49.8C170.352 50 185 52.5 185 52.5V84h-16.14C152.959 84 148 93.867 148 103.99V128h35.5l-5.675 37H148v89.445c61.192-9.602 108-62.556 108-126.445"/><path class="btn-share-facebook" data-url="https://kconecta.com/result/<?=$pr["reference"] ?>" fill="#FFF" d="m177.825 165l5.675-37H148v-24.01C148 93.866 152.959 84 168.86 84H185V52.5S170.352 50 156.347 50C127.11 50 108 67.72 108 99.8V128H75.5v37H108v89.445A129 129 0 0 0 128 256a129 129 0 0 0 20-1.555V165z"/></svg>
                                <span class="btn-share-facebook" data-url="https://kconecta.com/result/<?=$pr["reference"] ?>">Compartir</span>
                            </div>
                        </div>
                    </div>
                </div>
                </a>
                <?php } ?>
            </div>
        </div>
        <div class="button-explore-more-property">
            <a href="<?= base_url("result?ps=1&ca=&ty=&address=Barcelona&mode=1")?>" aria-label="Explorar más propiedades">
                <button>Explora más propiedades</button>
            </a>
        </div>
        <div class="container-video">
            <!-- <video autoplay muted loop playsinline class="video-element" src="<?= base_url()."video/vid-background.mp4" ?>">
                <track></track>
                Tu navegador no soporta videos.
            </video> -->
            <div class="container-text">
                <h2>El lugar de tus sueños está a solo un click de distancia. Encuéntralo ahora.</h2>
                <div class="container-col">
                    <div class="row-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24"><g fill="none" stroke="#ffffff" stroke-width="1.5"><path d="m18.18 8.04l.463-.464a1.966 1.966 0 1 1 2.781 2.78l-.463.464M18.18 8.04s.058.984.927 1.853s1.854.927 1.854.927M18.18 8.04l-4.26 4.26c-.29.288-.434.433-.558.592q-.22.282-.374.606c-.087.182-.151.375-.28.762l-.413 1.24l-.134.401m8.8-5.081l-4.26 4.26c-.29.29-.434.434-.593.558q-.282.22-.606.374c-.182.087-.375.151-.762.28l-1.24.413l-.401.134m0 0l-.401.134a.53.53 0 0 1-.67-.67l.133-.402m.938.938l-.938-.938"/><path stroke-linecap="round" d="M8 13h2.5M8 9h6.5M8 17h1.5M19.828 3.172C18.657 2 16.771 2 13 2h-2C7.229 2 5.343 2 4.172 3.172S3 6.229 3 10v4c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172c.944-.943 1.127-2.348 1.163-4.828"/></g></svg>
                        <span>Regístrate y anúnciate</span>
                    </div>
                    <div class="row-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 16 16"><path fill="#ffffff" fill-rule="evenodd" d="M12.5 12.618c.307-.275.5-.674.5-1.118V6.977a1.5 1.5 0 0 0-.585-1.189l-3.5-2.692a1.5 1.5 0 0 0-1.83 0l-3.5 2.692A1.5 1.5 0 0 0 3 6.978V11.5A1.496 1.496 0 0 0 4.493 13H5V9.5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2V13h.507c.381-.002.73-.146.993-.382m2-1.118a3 3 0 0 1-3 3h-7a3 3 0 0 1-3-3V6.977A3 3 0 0 1 2.67 4.6l3.5-2.692a3 3 0 0 1 3.66 0l3.5 2.692a3 3 0 0 1 1.17 2.378zm-5-2A.5.5 0 0 0 9 9H7a.5.5 0 0 0-.5.5V13h3z" clip-rule="evenodd"/></svg>
                        <span>Enviar anuncio</span>
                    </div>
                    <div class="row-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 100 100"><path fill="#ffffff" d="M37.18 0c-9.53 0-19.061 3.624-26.31 10.871c-14.495 14.495-14.495 38.122 0 52.617c13.57 13.569 35.1 14.367 49.687 2.528l3.576 3.576a3.956 5.958 45 0 0 .646 3.613l25.952 25.951a3.956 5.958 45 0 0 7.01-1.416a3.956 5.958 45 0 0 1.415-7.01l-25.95-25.95a3.956 5.958 45 0 0-3.616-.647l-3.576-3.576c11.84-14.588 11.043-36.118-2.526-49.686C56.241 3.624 46.71.001 37.18.001m0 8.217c7.397 0 14.795 2.834 20.463 8.502a28.875 28.875 0 0 1 0 40.924a28.875 28.875 0 0 1-40.924 0a28.875 28.875 0 0 1 0-40.924c5.668-5.668 13.064-8.502 20.46-8.502" color="#ffffff"/><path fill="#ffffff" d="M37.475 17.24c.295 0 .58.109.799.306l18.795 15.236c.817.731.298 1.146-.797 1.145h-4.854v19.26c0 .658-.534 1.192-1.193 1.192H24.728a1.193 1.193 0 0 1-1.193-1.193V33.927h-4.854c-1.095.001-1.613-.414-.797-1.145l18.795-15.236c.219-.196.502-.305.796-.305"/></svg>
                        <span>Explorar anuncios</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-message">
            <div class="swiper section-reviews-main-row">
                <h2>Lo que dicen nuestros clientes</h2>
                <div class="swiper-wrapper ctn-user-reviews-main-row">    
                    <div class="swiper-slide review">
                        <img src="<?= base_url("img/data_reviews/ivana-square.jpg") ?>" alt="Oscar Cardona Casals">
                        <div class="review-content">
                            <h3>Oscar Cardona Casals</h3>
                            <p>&#128205; Trav. de les Corts</p>
                            <p>"Gracias al equipo, pude vender mi casa sin complicaciones. Siempre estuvieron atentos a cada detalle."</p>
                            <div class="container-icon-p">
                                <img src="<?= base_url("img/data_reviews/facebook-icon.png") ?>" alt="José María Horcajada Barcenas">
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide review">
                        <img src="<?= base_url("img/data_reviews/kal-visuals-square.jpg") ?>" alt="José María Horcajada Barcenas">
                        <div class="review-content">
                            <h3>José María Horcajada Barcenas</h3>
                            <p>&#128205; Rambla Volart</p>
                            <p>"Un servicio excepcional. Me acompañaron en cada paso hasta cerrar la venta de mi piso de manera rápida y segura."</p>
                            <div class="container-icon-p">
                                <img src="<?= base_url("img/data_reviews/google-icon.png") ?>" alt="José María Horcajada Barcenas">
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide review">
                        <img src="<?= base_url("img/data_reviews/ivana-squares.jpg") ?>" alt="MARI BEL FERNANDEZ TORRENTE">
                        <div class="review-content">
                            <h3>MARI BEL FERNANDEZ TORRENTE</h3>
                            <p>&#128205; Numancia</p>
                            <p>"Encontré el hogar perfecto para mi familia gracias a su ayuda. Hicieron que todo el proceso fuera muy fácil y claro."</p>
                            <div class="container-icon-p">
                                <img src="<?= base_url("img/data_reviews/google-icon.png") ?>" alt="José María Horcajada Barcenas">
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide review">
                        <img src="<?= base_url("img/data_reviews/marie.jpg") ?>" alt="MARIA DOLORES RUIZ">
                        <div class="review-content">
                            <h3>MARIA DOLORES RUIZ</h3>
                            <p>&#128205; C/Ramon Berenguer III</p>
                            <p>"Vender mi casa con su asesoramiento fue una gran decisión. Son profesionales, cercanos y eficaces."</p>
                            <div class="container-icon-p">
                                <img src="<?= base_url("img/data_reviews/tiktok-icon.webp") ?>" alt="José María Horcajada Barcenas">
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide review">
                        <img src="<?= base_url("img/data_reviews/team-2.jpg") ?>" alt="Marc - Paul Ducrest">
                        <div class="review-content">
                            <h3>Marc - Paul Ducrest</h3>
                            <p>"Gracias a su dedicación, encontré una casa que realmente se ajustaba a lo que buscaba. Muy recomendados."</p>
                            <div class="container-icon-p">
                                <img src="<?= base_url("img/data_reviews/facebook-icon.png") ?>" alt="José María Horcajada Barcenas">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block-text">
                <h3>Confía en nosotros</h3>
                <p>Únete a nuestro vibrante grupo inmobiliario y forma parte de una red de empresas ganadoras que colaboran en nuestra plataforma online, donde cada paso es una oportunidad para crecer, innovar y alcanzar el éxito juntos en el emocionante mundo de los bienes raíces.</p>
            </div>
            <br>
            <br>
            <br>
        </div>
    </main>
@endsection

@section('js')   
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= config('services.google.maps_key') ?>&libraries=places"></script>
    <script src="<?= base_url()."js/index_func.js" ?>"></script>
    <script src="<?= base_url()."js/cadastral_calculator.js" ?>"></script>
    <script src="<?= base_url()."js/search_data.js" ?>"></script>
    <script src="<?= base_url("js/format_input.js") ?>"></script>
    <script src="<?= base_url("js/libraries/swiper-bundle.min.js") ?>"></script>
    <script>
        const btns_share_facebook = document.querySelectorAll(".btn-share-facebook");
        btns_share_facebook.forEach(btn_share_facebook => {
            btn_share_facebook.addEventListener("click", ()=>{
                const url = encodeURIComponent(btn_share_facebook.dataset.url);
                const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                window.open(facebookUrl, '_blank');
            })
        })
    </script>
    <script>
        const links = document.querySelectorAll(".redirect-to-details-link");
        links.forEach(link =>{
            link.addEventListener("click", (e)=>{
                if (e.target.classList.contains("btn-share-facebook")){
                    e.preventDefault();
                    document.getElementById('loader-page-change').style.display = "none";
                }
            })
        })
        search_property_function();
        search_service_function();

        const formatter_tags_1 = document.querySelectorAll(".meters-span");
        const formatter_tags_2 = document.querySelectorAll(".price-span");
        formatter_tags_1.forEach((tag)=>{
            format_1(tag, "element");
        })
        formatter_tags_2.forEach((tag)=>{
            format_1(tag, "element");
        })

        new Swiper('.swiper', {
            slidesPerView: 'auto',
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
            },
        });
    </script>
@endsection
