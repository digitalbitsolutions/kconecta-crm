@extends('layouts.page')

@section('nav_option')
<a href="<?= site_url() ?>">
    <span>Ir al inicio</span>
</a>
@endsection

@section('css')
    <?php $coverImageUrl = !empty($property["cover_image"]["url"]) ? base_url()."img/uploads/".$property["cover_image"]["url"] : base_url()."img/image-icon-1280x960.png"; ?>
    <link rel="stylesheet" href="<?= base_url()."css/libraries/swiper-bundle.min.css" ?>">
    <script src="<?= base_url()."js/libraries/swiper-bundle.min.js" ?>"></script>
    <script src="<?= base_url()."js/libraries/bulma.modal.min.js" ?>"></script>
    <link rel="stylesheet" href="<?= base_url()."css/page/details.css" ?>">
@endsection

@section('content')
<div class="container-main-body">
    <div class="container-column-1">
        <div class="container-image">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="<?= $coverImageUrl ?>" alt="Placeholder image" />
                    </div>
                    <?php foreach($property["more_images"] as $im){ ?>
                    <div class="swiper-slide">
                        <img src="<?= base_url()."img/uploads/".$im["url"] ?>" class="carousel-img-app" alt="Placeholder image" />
                    </div>
                    <?php } ?>
                </div>

                <!-- Botones de navegación -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>

                <!-- Paginación -->
                <div class="swiper-pagination"></div>
            </div>            
        </div>
        
        <div class="container-details-1">
            <h1 class="h1-service">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>  
                <?= !empty($property["address"]) ? $property["address"][0]["address"] : "" ?> 
            </h1>
            <span class="container-address">
                <span>
                    
                </span>
                <div class="container-main-map-video-btn">
                    <button class="button is-small btn-open-maps-view" id="btn-open-modal-view-map-coord" data-latitude="<?= !empty($property["address"]) ? $property["address"][0]["latitude"] : "" ?>" data-longitude="<?= !empty($property["address"]) ? $property["address"][0]["longitude"] : "" ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="#c026d3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#c026d3"><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg>
                        Ver mapa
                    </button>
                    <?php if (!empty($property["videos"])){ ?>
                        <div class="container-options-view">
                            <button class="button is-small" onclick="openModal(document.getElementById('modal-view-video'))">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 15 15"><path fill="#c026d3" fill-rule="evenodd" d="M4.764 3.122A33 33 0 0 1 7.5 3c.94 0 1.868.049 2.736.122c1.044.088 1.72.148 2.236.27c.47.111.733.258.959.489c.024.025.06.063.082.09c.2.23.33.518.405 1.062c.08.583.082 1.343.082 2.492c0 1.135-.002 1.885-.082 2.46c-.074.536-.204.821-.405 1.054l-.083.09c-.23.234-.49.379-.948.487c-.507.12-1.168.178-2.194.264c-.869.072-1.812.12-2.788.12s-1.92-.048-2.788-.12c-1.026-.086-1.687-.144-2.194-.264c-.459-.108-.719-.253-.948-.487l-.083-.09c-.2-.233-.33-.518-.405-1.054C1.002 9.41 1 8.66 1 7.525c0-1.149.002-1.91.082-2.492c.075-.544.205-.832.405-1.062c.023-.027.058-.065.082-.09c.226-.231.489-.378.959-.489c.517-.122 1.192-.182 2.236-.27M0 7.525c0-2.242 0-3.363.73-4.208c.036-.042.085-.095.124-.135c.78-.799 1.796-.885 3.826-1.056C5.57 2.05 6.527 2 7.5 2s1.93.05 2.82.126c2.03.171 3.046.257 3.826 1.056c.039.04.087.093.124.135c.73.845.73 1.966.73 4.208c0 2.215 0 3.323-.731 4.168a3 3 0 0 1-.125.135c-.781.799-1.778.882-3.773 1.048C9.48 12.951 8.508 13 7.5 13s-1.98-.05-2.87-.124c-1.996-.166-2.993-.25-3.774-1.048a3 3 0 0 1-.125-.135C0 10.848 0 9.74 0 7.525m5.25-2.142a.25.25 0 0 1 .35-.23l4.828 2.118c.2.088.2.37 0 .458L5.6 9.846a.25.25 0 0 1-.35-.229z" clip-rule="evenodd"/></svg>
                                Video
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </span>
            
        </div>
        
        <div class="container-description">
            <p><?php 
                // echo $property["description"];
                $text_with_breaks = str_replace('. ', ".\n", $property["description"]);
                $text_with_html_breaks = nl2br($text_with_breaks);
                echo $text_with_html_breaks;
            ?></p>
            <?php if (!empty($property["page_url"])){ ?>
            <a href="<?= $property["page_url"] ?>" class="tag is-link" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16"><path fill="#ffffff" d="M6.01 10.49a.47.47 0 0 1-.35-.15c-.2-.2-.2-.51 0-.71l8.49-8.48c.2-.2.51-.2.71 0s.2.51 0 .71l-8.5 8.48c-.1.1-.23.15-.35.15"/><path fill="#ffffff" d="M14.5 7c-.28 0-.5-.22-.5-.5V2H9.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5h5c.28 0 .5.22.5.5v5c0 .28-.22.5-.5.5m-3 8H2.49C1.67 15 1 14.33 1 13.51V4.49C1 3.67 1.67 3 2.49 3H7.5c.28 0 .5.22.5.5s-.22.5-.5.5H2.49a.49.49 0 0 0-.49.49v9.02c0 .27.22.49.49.49h9.01c.27 0 .49-.22.49-.49V8.5c0-.28.22-.5.5-.5s.5.22.5.5v5.01c0 .82-.67 1.49-1.49 1.49"/></svg>
                Visita nuestra página web
            </a>
            <?php } ?>
        </div>
        <div class="container-more-data">
            <?php 
                if (!empty($property["service_types"])){
            ?>    
                <article class="message">
                    <div class="message-body">
                        <div class="container-row-free-s">
                            <?php foreach($property["service_types"] as $st){ ?>  
                                <div class="box-li-s">
                                    &raquo; <span class="text-span"><?= $st["name"] ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php
                }
            ?>
            
        </div>
    </div>
    <div class="container-column-2">
        <div class="title-block">
            <h3>Pregunta al anunciante</h3>
        </div>
        <div class="container-form-message">
            <!-- <label for="">
                <span>Mensaje: </span>
                <textarea name="" id="" rows="3" class="textarea"></textarea>
            </label>
            <label for="">
                <span>Email:</span>
                <input type="text" class="input">
            </label>
            <label for="">
                <span>Teléfono:</span>
                <input type="text" class="input">
            </label>
            <label for="">
                <span>Nombre: </span>
                <input type="text" class="input">
            </label>
            <label for="">
                <input type="checkbox" name="" id="" class="checkbox"> Aceptar política de privacidad
            </label>
            <button class="button is-link">Contactar por chat</button> -->
            
            <div class="container-contact">
                <div class="container-profile">
                    <div class="container-image">
                        <img src="<?= base_url("img/photo_profile/").$property["user"]["photo"] ?>" alt="" />
                    </div>
                    <span><?= $property["user"]["user_name"] ?></span>
                </div>
                <div class="details-user-post">
                    <div class="data-row-in">
                        <span class="span-title">Publicado por: </span>
                        <span class="span-value"><?= $property["user"]["first_name"] ?><?= !empty($property["user"]["last_name"])? ", ".$property["user"]["last_name"] : ""?></span>
                    </div>
                    <div class="data-row-in">
                        <span class="span-title">Última actualización</span>
                        <span class="span-value">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 36 36"><path fill="#666666" d="M32.25 6H29v2h3v22H4V8h3V6H3.75A1.78 1.78 0 0 0 2 7.81v22.38A1.78 1.78 0 0 0 3.75 32h28.5A1.78 1.78 0 0 0 34 30.19V7.81A1.78 1.78 0 0 0 32.25 6" class="clr-i-outline clr-i-outline-path-1"></path><path fill="#666666" d="M8 14h2v2H8z" class="clr-i-outline clr-i-outline-path-2"></path><path fill="#666666" d="M14 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-3"></path><path fill="#666666" d="M20 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-4"></path><path fill="#666666" d="M26 14h2v2h-2z" class="clr-i-outline clr-i-outline-path-5"></path><path fill="#666666" d="M8 19h2v2H8z" class="clr-i-outline clr-i-outline-path-6"></path><path fill="#666666" d="M14 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-7"></path><path fill="#666666" d="M20 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-8"></path><path fill="#666666" d="M26 19h2v2h-2z" class="clr-i-outline clr-i-outline-path-9"></path><path fill="#666666" d="M8 24h2v2H8z" class="clr-i-outline clr-i-outline-path-10"></path><path fill="#666666" d="M14 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-11"></path><path fill="#666666" d="M20 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-12"></path><path fill="#666666" d="M26 24h2v2h-2z" class="clr-i-outline clr-i-outline-path-13"></path><path fill="#666666" d="M10 10a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" class="clr-i-outline clr-i-outline-path-14"></path><path fill="#666666" d="M26 10a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" class="clr-i-outline clr-i-outline-path-15"></path><path fill="#666666" d="M13 6h10v2H13z" class="clr-i-outline clr-i-outline-path-16"></path><path fill="none" d="M0 0h36v36H0z"></path></svg>
                            <?= $property["updated_at_text"] ?>
                        </span>
                    </div>
                </div>
                <div class="container-contact-header-main">
                    <?php if(!empty($property["user"]["phone"])){ ?>
                        <a href="https://wa.me/<?= $property["user"]["phone"] ?>?text=Hola,%20me%20interesa%20tu%20propiedad" class="btn-contact-redirect" target="_blank" style="width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32"><path fill="#ffffff" d="M23.328 19.177c-.401-.203-2.354-1.156-2.719-1.292c-.365-.13-.63-.198-.896.203c-.26.391-1.026 1.286-1.26 1.547s-.464.281-.859.104c-.401-.203-1.682-.62-3.203-1.984c-1.188-1.057-1.979-2.359-2.214-2.76c-.234-.396-.026-.62.172-.818c.182-.182.401-.458.604-.698c.193-.24.255-.401.396-.661c.13-.281.063-.5-.036-.698s-.896-2.161-1.229-2.943c-.318-.776-.651-.677-.896-.677c-.229-.021-.495-.021-.76-.021s-.698.099-1.063.479c-.365.401-1.396 1.359-1.396 3.297c0 1.943 1.427 3.823 1.625 4.104c.203.26 2.807 4.26 6.802 5.979c.953.401 1.693.641 2.271.839c.953.302 1.823.26 2.51.161c.76-.125 2.354-.964 2.688-1.901c.339-.943.339-1.724.24-1.901c-.099-.182-.359-.281-.76-.458zM16.083 29h-.021c-2.365 0-4.703-.641-6.745-1.839l-.479-.286l-5 1.302l1.344-4.865l-.323-.5a13.17 13.17 0 0 1-2.021-7.01c0-7.26 5.943-13.182 13.255-13.182c3.542 0 6.865 1.38 9.365 3.88a13.06 13.06 0 0 1 3.88 9.323C29.328 23.078 23.39 29 16.088 29zM27.359 4.599C24.317 1.661 20.317 0 16.062 0C7.286 0 .14 7.115.135 15.859c0 2.792.729 5.516 2.125 7.927L0 32l8.448-2.203a16.1 16.1 0 0 0 7.615 1.932h.005c8.781 0 15.927-7.115 15.932-15.865c0-4.234-1.651-8.219-4.661-11.214z"/></svg>
                            <span>Contactar por whatsApp</span>
                        </a>
                    <?php } ?>
                </div>
                
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modal-view-map-coord">
    <div class="modal-background"></div>
    <div class="modal-content box">
        <div id="map"></div>
    </div>
    <button class="button modal-close"></button>
</div>
<div class="modal" id="modal-view-video">
    <div class="modal-background modal-background-video"></div>
    <div class="modal-content box">
        <div class="container-video">
            <?php if (!empty($property["videos"])){ ?>
                <video id="video-app" src="<?= base_url("video/uploads/".$property["videos"][0]["url"]) ?>" controlsList="nodownload nofullscreen" disablePictureInPicture></video>
            <?php } ?>
        </div>
        <div class="container-actions">
            <button class="btn-close-modal-video"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="none" stroke="#0284c7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6L6 18M6 6l12 12"/></svg></button>
            <button id="control-play-pause">
                <svg class="svg-pause" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><path d="M5 5v14a2 2 0 0 0 2.75 1.84L20 13.74a2 2 0 0 0 0-3.5L7.75 3.14A2 2 0 0 0 5 4.89" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg class="svg-play" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><path fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7c0-1.414 0-2.121.44-2.56C4.878 4 5.585 4 7 4s2.121 0 2.56.44C10 4.878 10 5.585 10 7v10c0 1.414 0 2.121-.44 2.56C9.122 20 8.415 20 7 20s-2.121 0-2.56-.44C4 19.122 4 18.415 4 17zm10 0c0-1.414 0-2.121.44-2.56C14.878 4 15.585 4 17 4s2.121 0 2.56.44C20 4.878 20 5.585 20 7v10c0 1.414 0 2.121-.44 2.56c-.439.44-1.146.44-2.56.44s-2.121 0-2.56-.44C14 19.122 14 18.415 14 17z" color="#ffffff"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@section('js')   
<script src="<?= base_url()."js/index_func.js" ?>"></script>
<script>

    const swiper = new Swiper('.swiper', {
        // Configuración básica
        loop: true, // Permite bucle infinito
        autoplay: {
        delay: 3000, // Cambia automáticamente cada 3 segundos
        },
        navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
        },
        pagination: {
        el: '.swiper-pagination',
        clickable: true,
        },
    });
</script>
<script>
    const video_app = document.getElementById("video-app");
    const btn_control_play_pause = document.getElementById("control-play-pause");
    const btn_close_modal = document.querySelector(".btn-close-modal-video");
    const btn_open_modal_map = document.getElementById("btn-open-modal-view-map-coord");
    btn_control_play_pause.addEventListener("click", ()=>{
        if (video_app.dataset.state === "play"){
            video_app.pause();
            video_app.dataset.state = "pause";
            btn_control_play_pause.querySelector(".svg-play").style.display = "none";
            btn_control_play_pause.querySelector(".svg-pause").removeAttribute("style");
        }else{
            video_app.play();
            video_app.dataset.state = "play";
            btn_control_play_pause.querySelector(".svg-play").removeAttribute("style");
            btn_control_play_pause.querySelector(".svg-pause").style.display = "none";
        }
    })
    document.querySelector(".modal-background-video").addEventListener("click", ()=>{
        video_app.pause();
        video_app.dataset.state = "pause";
        btn_control_play_pause.querySelector(".svg-play").style.display = "none";
        btn_control_play_pause.querySelector(".svg-pause").removeAttribute("style");
        closeModal(document.getElementById('modal-view-video'))
    })
    btn_close_modal.addEventListener("click", ()=>{
        video_app.pause();
        video_app.dataset.state = "pause";
        btn_control_play_pause.querySelector(".svg-play").style.display = "none";
        btn_control_play_pause.querySelector(".svg-pause").removeAttribute("style");
        closeModal(document.getElementById('modal-view-video'))
    })
    btn_open_modal_map.addEventListener("click", ()=>{
        openModal(document.getElementById("modal-view-map-coord"));
    })
    video_app.addEventListener("contextmenu", function(e) {
        e.preventDefault(); // Bloquea el clic derecho en el video
    });

    document.addEventListener("keydown", function(e) {
        if (e.ctrlKey && (e.key === "s" || e.key === "S" || e.key === "u" || e.key === "U")) {
            e.preventDefault(); // Bloquea Ctrl + S (Guardar) y Ctrl + U (Ver código fuente)
        }
    });
    
    document.addEventListener("DOMContentLoaded", function () {
    const images = document.querySelectorAll(".carousel-img-app");

    const loadImage = (img) => {
        img.src = img.getAttribute("src");
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                loadImage(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });

    images.forEach(img => observer.observe(img));
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= config('services.google.maps_key') ?>&libraries=places"></script>
<script>
    function initMap(initial_position) {
        // Coordenadas iniciales (puedes cambiarlo)
        let initialPosition = initial_position; 

        // Crear el mapa
        map = new google.maps.Map(document.getElementById("map"), {
            center: initialPosition,
            zoom: 14,
            streetViewControl: false,
            styles: [
                {
                    featureType: "transit", // Oculta las estaciones de transporte público
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        // Crear el marcador movible
        marker = new google.maps.Marker({
            position: initialPosition,
            map: map,
            icon: {
                url: "/img/icon-location-main-app.webp",
                scaledSize: new google.maps.Size(30, 42)
            },
        });
    }
    const lat = document.getElementById("btn-open-modal-view-map-coord").dataset.latitude;
    const lng = document.getElementById("btn-open-modal-view-map-coord").dataset.longitude;
    if (lat && lng){
        window.onload = initMap({ lat: parseFloat(lat), lng: parseFloat(lng) });
    }
</script>
@endsection

