@extends('layouts.page')

@section('nav_option')
<a href="<?= site_url() ?>">
    <span>Ir al inicio</span>
</a>
@endsection

@section('css')
    <link rel="stylesheet" href="<?= base_url()."css/page/details.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/page/result_all.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/page/result_all_services.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/ui/input_number_cont.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/ui/input_radio.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/ui/input_checkbox.css" ?>">

    <script>
        window.__gmAuthFailed = false;
        window.__gmAuthFailureCallback = null;
        window.gm_authFailure = function () {
            window.__gmAuthFailed = true;
            if (typeof window.__gmAuthFailureCallback === "function") {
                window.__gmAuthFailureCallback();
            }
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= config('services.google.maps_key') ?>&libraries=drawing,places"></script>
@endsection

@section('content')

<div class="container-result-all">
    <div class="container-properties">
        <div class="container-title-section">
            <button class="btn-close"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6L6 18M6 6l12 12"/></svg></button>
            <div class="container-details-header-get">
                <?php 
                    if ($quantity === 0){
                        echo "<span>Sin resultados</span>";
                    }else if ($quantity === 1){
                        echo "<span>Se encontró 1 resultado</span>";
                    }else{
                        echo "<span>Se encontraron ".$quantity." resultados</span>";
                    }
                ?>
                <!-- <div class="address-view">
                </div> -->
            </div>
            <div class="container-filter-result">
                <form action="" method="get" id="form-filter-result">
                    <input type="hidden" name="page" value="1" id="input-page-nav">
                    <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">                
                    <input type="hidden" name="city" id="city" value="<?= $city ?>">
                    <input type="hidden" name="province" id="province" value="<?= $province ?>">
                    <input type="hidden" name="latitude" id="latitude" value="<?= $latitude ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?= $longitude ?>">
                    <input type="hidden" name="zoom" id="zoom" value="<?= $zoom ?>">
                    <div class="field">
                        <p class="control has-icons-left"> 
                            <input type="text" name="address" id="address" value="<?= !empty($address) ? $address : "Barcelona" ?>" class="input" required>
                            <span class="icon is-small is-left">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>  
                            </span>
                        </p>
                    </div>
                    <div class="container-others">
                        <button class="button save-search-main" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16"><path fill="#ffffff" d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0a5.5 5.5 0 0 1 11 0"/></svg>
                            Buscar propiedades
                        </button>
                    </div>
                    <div class="container-form-inputs">
                        
                        <span>Tipos</span>
                        <div class="container-label-radio">
                            <?php foreach($service_type as $st){ ?>
                                <label class="radio label-radio-checkbox-col-100">
                                    <input type="checkbox" class="checkbox-input-ui" hidden="" name="sti[]" value="<?= $st["id"]  ?>" <?php if (!empty($sti)){if (in_array($st["id"], $sti)){ echo "checked";}} ?>>
                                    <span class="checkmark-checkbox-input-ui"></span>
                                    <?= $st["name"] ?>
                                </label>
                            <?php } ?>
                        </div>
                    
                    </div>
                </form>
            </div>
        </div>
        <div class="container-body-data-result" id="container-body-data-result">
            <div class="container-nav-search-body tabs is-right">
                <ul>
                    <li class="<?= $mode == 1 ? "is-active" : "" ?>">
                        <button class="button-change-page-result" data-mode="1">
                            <span class="icon is-small"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="none" stroke="" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12.001 2.5c4.478 0 6.717 0 8.108 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.717 0-8.109-1.391c-1.39-1.392-1.39-3.63-1.39-8.109M2.5 8h19M11 17h6M7 17h1m3-4h6M7 13h1" color="#666666"/></svg></span>
                            <span>Servicios</span>
                        </button>
                    </li>
                    <li class="<?= $mode != 1 ? "is-active" : "" ?>">
                        <button class="button-change-page-result" data-mode="2">
                            <span class="icon is-small"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color=""><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg></span>
                            <span>Mapa</span>
                        </button>
                    </li>
                </ul>
            </div>
            <?php if ($mode == 1){ ?>
                <div class="card-grid">
                    <button class="button is-small btn-open">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 512 512"><path fill="#ffffff" d="M238.627 496H192V253.828l-168-200V16h456v37.612l-160 200v161.015ZM224 464h1.373L288 401.373V242.388L443.51 48H60.9L224 242.172Z"/></svg>
                        <span>Filtrar</span>
                    </button>
                    <?php 
                    $contador = 1;
                    foreach($properties as $index => $pr){ 
                    ?>
                        <div class="container-service-main-block">
                            <div class="container-image">
                                <img src="<?= !empty($pr["cover_image"]["url"]) ? base_url("img/uploads/".$pr["cover_image"]["url"]) : base_url("img/image-icon-1280x960.png") ?>" alt="">
                            </div>
                            <div class="container-details">
                                <div class="container-row-data">
                                    <?php if(!empty($pr["user"])){ ?>
                                    <span class="title-name-main">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"><path fill="#666666" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19M12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4"/></svg>
                                        <?= $pr["user"][0]["first_name"] ?><?= !empty($pr["user"][0]["last_name"]) ? ", ".$pr["user"][0]["last_name"] : "" ?>
                                    </span>
                                        <?php if(!empty($pr["user"][0]["user_name"])){ ?>
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M2 12c0-4.243 0-6.364 1.464-7.682C4.93 3 7.286 3 12 3s7.071 0 8.535 1.318S22 7.758 22 12s0 6.364-1.465 7.682C19.072 21 16.714 21 12 21s-7.071 0-8.536-1.318S2 16.242 2 12"></path><path d="M8.4 8h-.8c-.754 0-1.131 0-1.366.234C6 8.47 6 8.846 6 9.6v.8c0 .754 0 1.131.234 1.366C6.47 12 6.846 12 7.6 12h.8c.754 0 1.131 0 1.366-.234C10 11.53 10 11.154 10 10.4v-.8c0-.754 0-1.131-.234-1.366C9.53 8 9.154 8 8.4 8M6 16h4m4-8h4m-4 4h4m-4 4h4"></path></g></svg>
                                            <?= $pr["user"][0]["user_name"] ?>
                                        </span>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if(!empty($pr["user_address"])){ ?>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>  
                                        <?= $pr["user_address"][0]["address"] ?>
                                    </span>
                                    <?php } ?>
                                </div>
                                <!-- <div class="container-row-data">
                                    <h4>Servicios</h4>
                                    <?php 
                                        if (!empty($pr["service_types"])){
                                            foreach($pr["service_types"] as $st){?>          
                                                <span>
                                                    <?= $st["name"] ?>
                                                </span>
                                            <?php }
                                        }
                                    ?>
                                </div> -->
                                <div class="container-btns-redirect">
                                    <?php if(!empty($pr["user"])){ ?>
                                    <a href="https://wa.me/<?= $pr["user"][0]["phone"] ?>?text=Hola,%20me%20necesito%20tu%20servicio" class="whatsapp-btn-contact-link" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="#ffffff" d="M11.42 9.49c-.19-.09-1.1-.54-1.27-.61s-.29-.09-.42.1s-.48.6-.59.73s-.21.14-.4 0a5.1 5.1 0 0 1-1.49-.92a5.3 5.3 0 0 1-1-1.29c-.11-.18 0-.28.08-.38s.18-.21.28-.32a1.4 1.4 0 0 0 .18-.31a.38.38 0 0 0 0-.33c0-.09-.42-1-.58-1.37s-.3-.32-.41-.32h-.4a.72.72 0 0 0-.5.23a2.1 2.1 0 0 0-.65 1.55A3.6 3.6 0 0 0 5 8.2A8.3 8.3 0 0 0 8.19 11c.44.19.78.3 1.05.39a2.5 2.5 0 0 0 1.17.07a1.93 1.93 0 0 0 1.26-.88a1.67 1.67 0 0 0 .11-.88c-.05-.07-.17-.12-.36-.21"/><path fill="#ffffff" d="M13.29 2.68A7.36 7.36 0 0 0 8 .5a7.44 7.44 0 0 0-6.41 11.15l-1 3.85l3.94-1a7.4 7.4 0 0 0 3.55.9H8a7.44 7.44 0 0 0 5.29-12.72M8 14.12a6.1 6.1 0 0 1-3.15-.87l-.22-.13l-2.34.61l.62-2.28l-.14-.23a6.18 6.18 0 0 1 9.6-7.65a6.12 6.12 0 0 1 1.81 4.37A6.19 6.19 0 0 1 8 14.12"/></svg>
                                        WhatsApp
                                    </a>
                                    <?php } ?>
                                    <a href="<?= site_url("result_service/".$pr["id"]) ?>" class="redirect-view">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32"><path fill="#ffffff" d="M30.94 15.66A16.69 16.69 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68A16.69 16.69 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M16 25c-5.3 0-10.9-3.93-12.93-9C5.1 10.93 10.7 7 16 7s10.9 3.93 12.93 9C26.9 21.07 21.3 25 16 25"/><path fill="#ffffff" d="M16 10a6 6 0 1 0 6 6a6 6 0 0 0-6-6m0 10a4 4 0 1 1 4-4a4 4 0 0 1-4 4"/></svg>
                                        Ver más
                                    </a>
                                </div>
                            </div>
                        </div>    
                    <?php } ?>
                </div>
                <nav class="pagination" role="navigation" aria-label="pagination">
                    <ul class="pagination-list">
                        <?php for ($i = 0; $i < $quantity_block_nav; $i++){ ?>
                            <li>
                                <a class="pagination-link <?= $number_position == $i+1 ? "is-current" : "" ?>" data-value="<?= $i + 1 ?>" aria-label="Page 1" aria-current="page"><?= $i + 1 ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </nav>
            <?php }else{ ?>
                <div class="container-search-map-data">
                    <button class="button is-small btn-open">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 512 512"><path fill="#ffffff" d="M238.627 496H192V253.828l-168-200V16h456v37.612l-160 200v161.015ZM224 464h1.373L288 401.373V242.388L443.51 48H60.9L224 242.172Z"/></svg>
                        <span>Filtrar</span>
                    </button>
                    <div class="container-map-main-search">
                        <div id="map"></div>
                    </div>
                    <div class="container-column-view-data-map-macro">
                        <?php foreach($provinceCities as $key => $pc){ ?>
                            <div class="container-column-view-data-map">
                                <h4 class="link-h4-filter-addres" data-province="<?= $key ?>"><?= $key ?></h4>
                                <ul>
                                    <?php foreach($pc as $cit){ ?>
                                    <li class="link-li-filter-addres" data-city="<?= $cit["city"] ?>" data-province="<?= $key ?>">
                                        <span><?= $cit["city"] ?></span> &raquo;<span><?= $cit["total"] ?></span>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>    
                        <?php } ?>
                    </div>    
                </div>
            <?php } ?>
        </div>
    </div>
</div>

@endsection

@section('js')
<?php if ($mode != 1){ ?>
<script>
    const mapContainer = document.getElementById("map");
    if (mapContainer) {
        let map;
        let usingLeaflet = false;
        let leafletMap;
        let leafletMarkers = [];
        let zoom = 6;
        let center_map = { lat: 40.4168, lng: -3.7038 };
        let data_temp = [];

        const showMapError = (message) => {
            mapContainer.innerHTML = `<div class="map-error">${message}</div>`;
        };

        const useGoogleMaps = () => window.google && window.google.maps && !window.__gmAuthFailed;

        const loadLeaflet = () => {
            if (window.L) {
                return Promise.resolve();
            }

            if (!document.querySelector("link[data-leaflet]")) {
                const leafletCss = document.createElement("link");
                leafletCss.rel = "stylesheet";
                leafletCss.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
                leafletCss.dataset.leaflet = "true";
                document.head.appendChild(leafletCss);
            }

            return new Promise((resolve, reject) => {
                const existingScript = document.querySelector("script[data-leaflet]");
                if (existingScript) {
                    existingScript.addEventListener("load", resolve);
                    existingScript.addEventListener("error", () => reject(new Error("Leaflet failed to load")));
                    return;
                }

                const script = document.createElement("script");
                script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
                script.async = true;
                script.dataset.leaflet = "true";
                script.onload = resolve;
                script.onerror = () => reject(new Error("Leaflet failed to load"));
                document.head.appendChild(script);
            });
        };

        function actualizarParametro(parametro, valor) {
            let url = new URL(window.location.href);
            url.searchParams.set(parametro, valor); // Agrega o actualiza el parametro
            window.history.pushState({}, "", url); // Modifica la URL sin recargar
        }

        function initGoogleMap() {
            map = new google.maps.Map(mapContainer, {
                center: center_map, // Madrid
                zoom: zoom,
                streetViewControl: false,
                styles: [
                        {
                            featureType: "all",
                            elementType: "labels.text.fill",
                            stylers: [{ color: "#5a5a5c" }] // Color del texto de las ciudades
                        },
                        {
                            featureType: "administrative", // Para mostrar nombres de ciudades/provincias/estados
                            elementType: "geometry",
                            stylers: [{ visibility: "on" }]
                        },
                        {
                            featureType: "administrative.locality", // Ciudades
                            elementType: "labels",
                            stylers: [{ visibility: "on" }]
                        },
                        {
                            featureType: "administrative.province", // Provincias
                            elementType: "labels",
                            stylers: [{ visibility: "on" }]
                        },
                        {
                            featureType: "administrative.country", // Paises
                            elementType: "labels",
                            stylers: [{ visibility: "on" }]
                        },
                        {
                            featureType: "poi", // Ocultar puntos de interes (negocios, parques, etc.)
                            stylers: [{ visibility: "off" }]
                        },
                        {
                            featureType: "road", // Ocultar calles
                            stylers: [{ visibility: "on" }]
                        },
                        {
                            featureType: "transit", // Ocultar transporte publico
                            stylers: [{ visibility: "off" }]
                        },
                        {
                            featureType: "water", // Mantener los cuerpos de agua visibles
                            elementType: "geometry",
                            stylers: [{ color: "#b3b4f9" }]
                        }
                    ],
                restriction: {
                    latLngBounds: {
                        north: 44.0,
                        south: 35.0,
                        west: -10.0,
                        east: 5.0
                    },
                    strictBounds: true
                }
            });

            // map center get
            map.addListener("center_changed", function () {
                document.getElementById("latitude").value = map.getCenter().lat()
                document.getElementById("longitude").value = map.getCenter().lng()
                
                actualizarParametro("latitude", map.getCenter().lat());
                actualizarParametro("longitude", map.getCenter().lng());
            });

            // Evento cuando el usuario cambia el zoom
            map.addListener("zoom_changed", function () {
                document.getElementById("zoom").value = map.getZoom();
                actualizarParametro("zoom", map.getZoom());
            });

            let activeInfoWindow = null;
            // Agregar marcadores al mapa
            data_temp.forEach((location) => {
                if (location.lat && location.lng){
                    let lat = parseFloat(location.lat);
                    let lng = parseFloat(location.lng);
                    
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                        icon: {
                            url: "/img/kconecta_icon.webp",
                            scaledSize: new google.maps.Size(34, 34)
                        },
                        title: `Cantidad: ${location.quantity}`
                    });
        
                    // InfoWindow con detalles adicionales
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="container-map-details-info-app-main">
                                <h3>${location.title}</h3>
                                <a href="/result_service/${location.id}" class="a-redirect-view">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32"><path fill="#ffffff" d="M30.94 15.66A16.69 16.69 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68A16.69 16.69 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M16 25c-5.3 0-10.9-3.93-12.93-9C5.1 10.93 10.7 7 16 7s10.9 3.93 12.93 9C26.9 21.07 21.3 25 16 25"/><path fill="#ffffff" d="M16 10a6 6 0 1 0 6 6a6 6 0 0 0-6-6m0 10a4 4 0 1 1 4-4a4 4 0 0 1-4 4"/></svg>
                                    Ver detalle
                                </a>
                            </div>
                        `
                    });
        
                    // Mostrar InfoWindow al hacer clic en el marcador
                    marker.addListener("click", () => {
                        if (activeInfoWindow) {
                            activeInfoWindow.close();
                        }
        
                        // Abrir la nueva InfoWindow y actualizar la variable
                        infoWindow.open(map, marker);
                        activeInfoWindow = infoWindow;
                    });
                }
            });
        }

        const initLeafletMap = async () => {
            try {
                await loadLeaflet();
            } catch (error) {
                showMapError("No se pudo cargar el mapa.");
                return;
            }

            if (!leafletMap) {
                mapContainer.innerHTML = "";
                leafletMap = L.map(mapContainer, {
                    zoomControl: true,
                });

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 19,
                    attribution: "(c) OpenStreetMap",
                }).addTo(leafletMap);

                leafletMap.on("moveend", () => {
                    const center = leafletMap.getCenter();
                    document.getElementById("latitude").value = center.lat;
                    document.getElementById("longitude").value = center.lng;

                    actualizarParametro("latitude", center.lat);
                    actualizarParametro("longitude", center.lng);
                });

                leafletMap.on("zoomend", () => {
                    const currentZoom = leafletMap.getZoom();
                    document.getElementById("zoom").value = currentZoom;
                    actualizarParametro("zoom", currentZoom);
                });
            }

            leafletMap.setView([center_map.lat, center_map.lng], zoom);

            leafletMarkers.forEach((marker) => {
                leafletMap.removeLayer(marker);
            });
            leafletMarkers = [];

            const icon = L.icon({
                iconUrl: "/img/kconecta_icon.webp",
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -18],
            });

            data_temp.forEach((location) => {
                if (location.lat && location.lng) {
                    let lat = parseFloat(location.lat);
                    let lng = parseFloat(location.lng);

                    const marker = L.marker([lat, lng], { icon: icon });
                    marker.addTo(leafletMap);

                    marker.bindPopup(`
                        <div class="container-map-details-info-app-main">
                            <h3>${location.title}</h3>
                            <a href="/result_service/${location.id}" class="a-redirect-view">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32"><path fill="#ffffff" d="M30.94 15.66A16.69 16.69 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68A16.69 16.69 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M16 25c-5.3 0-10.9-3.93-12.93-9C5.1 10.93 10.7 7 16 7s10.9 3.93 12.93 9C26.9 21.07 21.3 25 16 25"/><path fill="#ffffff" d="M16 10a6 6 0 1 0 6 6a6 6 0 0 0-6-6m0 10a4 4 0 1 1 4-4a4 4 0 0 1-4 4"/></svg>
                                Ver detalle
                            </a>
                        </div>
                    `);

                    leafletMarkers.push(marker);
                }
            });
        };

        const address_input = document.getElementById("address");
        const city_input = document.getElementById("city");
        const province_input = document.getElementById("province");

        const latitude_input = document.getElementById("latitude").value;
        const longitude_input = document.getElementById("longitude").value;
        const zoom_input = document.getElementById("zoom").value;

        if (latitude_input && latitude_input != null && latitude_input != undefined && longitude_input && longitude_input != null && longitude_input != undefined){center_map.lat = parseFloat(latitude_input); center_map.lng = parseFloat(longitude_input);}
        if (zoom_input && zoom_input != null && zoom_input != undefined){zoom = parseInt(zoom_input)}

        const data_for_map = async (useGoogle) => {
            try {
                if (!useGoogle) {
                    usingLeaflet = true;
                }
                const form_data = new FormData(document.getElementById("form-filter-result"));
                const query_string = new URLSearchParams(form_data).toString();
                const response = await fetch("/api/services_for_map?" + query_string, {
                    method: "GET",
                });
                const data = await response.json();
                const applyData = async () => {
                    data_temp = Array.isArray(data.data) ? data.data : [];
                    if (useGoogle) {
                        initGoogleMap();
                    } else {
                        await initLeafletMap();
                    }
                };

                if (useGoogle && address_input.value && (!city_input.value && !province_input.value)){
                    const geocoder = new google.maps.Geocoder();
                    
                    geocoder.geocode({ address: address_input.value }, function(results, status) {
                        if (status === "OK") {
                            const location = results[0].geometry.location;
                            document.getElementById("latitude").value = location.lat();
                            document.getElementById("longitude").value = location.lng();
                            document.getElementById("zoom").value = 13;
                            zoom = 13;
                            center_map.lat = location.lat(); 
                            center_map.lng = location.lng();
                        }

                        applyData();
                    });
                }else{
                    await applyData();
                }
            } catch (error) {
                showMapError("No se pudo cargar los datos del mapa.");
            }
        }

        window.__gmAuthFailureCallback = () => {
            if (!usingLeaflet) {
                data_for_map(false);
            }
        };

        const waitForGoogleMaps = (attempt = 0) => {
            if (window.__gmAuthFailed) {
                data_for_map(false);
                return;
            }
            if (useGoogleMaps()) {
                data_for_map(true);
                return;
            }
            if (attempt >= 20) {
                data_for_map(false);
                return;
            }
            setTimeout(() => waitForGoogleMaps(attempt + 1), 200);
        };

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => waitForGoogleMaps());
        } else {
            waitForGoogleMaps();
        }
    }
</script>   
<?php } ?>
<script>
    const form_filter = document.getElementById("form-filter-result");
    const controll_inputs_filters = ()=>{
        const inputs = form_filter.querySelectorAll("input");
        inputs.forEach((input) =>{
            if (input.value === "" || input.value === "0"){
                input.setAttribute("disabled", true);
            }else{
                input.removeAttribute("disabled");
            }
        });
        form_filter.submit();
    }
    form_filter.addEventListener("submit", (e)=>{
        e.preventDefault();
        controll_inputs_filters();
    })

    function obtenerCoordenadas(ciudad, provincia, pais) {
        if (!window.google || !window.google.maps) {
            controll_inputs_filters();
            return;
        }
        const geocoder = new google.maps.Geocoder();
        let direccion = `${provincia}, ${pais}`;
        if (ciudad){
            direccion = ciudad+", "+ direccion;
        }
        geocoder.geocode({ address: direccion }, function(results, status) {
            if (status === "OK") {
                const location = results[0].geometry.location;
                document.getElementById("latitude").value = location.lat();
                document.getElementById("longitude").value = location.lng();
                if (ciudad){
                    document.getElementById("zoom").value = 11;
                }else{
                    document.getElementById("zoom").value = 9;
                }
                controll_inputs_filters();
            }
        });
    }

    const card_grid = document.querySelector(".container-body-data-result");
    const container_nav_section = document.querySelector(".container-title-section");
    const btn_open = document.querySelector(".btn-open");
    const btn_close = document.querySelector(".btn-close");
    btn_close.addEventListener("click", ()=>{
        container_nav_section.style.display = "none";
        card_grid.style.width = "100%";
        btn_open.style.display = "flex";
    })
    btn_open.addEventListener("click", ()=>{
        container_nav_section.style.display = "flex";
        card_grid.style.width = "calc(100% - 18rem)";
        btn_open.style.display = "none";
    })
    
    const navs = document.querySelectorAll(".pagination-link");
    navs.forEach(nav =>{
        nav.addEventListener("click", ()=>{
            document.getElementById("input-page-nav").value = nav.dataset.value;
            controll_inputs_filters();
        })
    });

    const btns_change_mode = document.querySelectorAll(".button-change-page-result");
    btns_change_mode.forEach(btn =>{
        btn.addEventListener("click", ()=>{
            document.getElementById("mode").value = btn.dataset.mode;
            controll_inputs_filters();
        })
    });
    const link_li_filter_addres = document.querySelectorAll(".link-li-filter-addres");
    link_li_filter_addres.forEach(li =>{
        li.addEventListener("click", ()=>{
            const city = li.dataset.city;
            const province = li.dataset.province;
            document.getElementById("address").value = city + ", " + province;
            document.getElementById("city").value = city;
            document.getElementById("province").value = province;
            obtenerCoordenadas(city, province, "España");
        })
    })

    const link_h4_filter_addres = document.querySelectorAll(".link-h4-filter-addres");
    link_h4_filter_addres.forEach(h4 =>{
        h4.addEventListener("click", ()=>{
            const province = h4.dataset.province;
            document.getElementById("address").value = province;
            document.getElementById("city").value = "";
            document.getElementById("province").value = province;
            obtenerCoordenadas("", province, "España");
        });
    })

</script>
<script src="<?= base_url("js/helpers.js") ?>"></script>
<script src="<?= base_url("js/format_input.js") ?>"></script>

<script src="<?= base_url("js/autocomplet.js") ?>"></script>
@endsection

