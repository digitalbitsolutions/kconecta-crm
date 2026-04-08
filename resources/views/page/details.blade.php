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

    <!-- seo -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $property["title"] ?>">
    <meta property="og:description" content="<?= $property["description"] ?>">
    <meta property="og:image" content="<?= $coverImageUrl ?>">
    <meta property="og:url" content="<?= base_url()."result/".$property["reference"] ?>">
    <meta property="og:site_name" content="<?= base_url() ?>">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9259257545893744" crossorigin="anonymous"></script>
@endsection

@section('content')
<div class="container-main-body">
    <input type="hidden" id="app-ref-main" value="<?= $property["id"] ?>">
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
            <div class="container-main-share-options">
                <div class="container-block-share">
                    <button id="btn-share">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20"><path fill="#ffffff" d="M12 6V2l7 7l-7 7v-4c-5 0-8.5 1.5-11 5l.8-3l.2-.4A12 12 0 0 1 12 6"/></svg>
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M17 22q-1.25 0-2.125-.875T14 19q0-.15.075-.7L7.05 14.2q-.4.375-.925.588T5 15q-1.25 0-2.125-.875T2 12t.875-2.125T5 9q.6 0 1.125.213t.925.587l7.025-4.1q-.05-.175-.062-.337T14 5q0-1.25.875-2.125T17 2t2.125.875T20 5t-.875 2.125T17 8q-.6 0-1.125-.213T14.95 7.2l-7.025 4.1q.05.175.063.338T8 12t-.012.363t-.063.337l7.025 4.1q.4-.375.925-.587T17 16q1.25 0 2.125.875T20 19t-.875 2.125T17 22"/></svg> -->
                        Compartir</button>
                </div> 
            </div>
            <h1>
                <?= $property["title"] ?>
            </h1>
            <!-- <span><?= !empty($property["name_urbanization"])? $property["name_urbanization"]. ", ": "" ?> <?= $property["number"] ?>,<?= $property["address"] ?>, <?= $property["locality"] ?> <a href="#">Ver mapa</a></span> -->
            <span class="container-address">
                <span><?= $property["address"] ?> </span>
                <div class="container-main-map-video-btn">
                    <button class="button is-small btn-open-maps-view" id="btn-open-modal-view-map-coord" data-latitude="<?= !empty($property["property_address"]) ? $property["property_address"][0]["latitude"] : "" ?>" data-longitude="<?= !empty($property["property_address"]) ? $property["property_address"][0]["longitude"] : "" ?>">
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
            <div class="container-other-block-door">
                <?php
                    if (!empty($property["esc_block"])){
                        echo "<span class='tag is-white'>Bloque / Esc. &raquo; ". $property["esc_block"] ."</span>";
                    }
                    if (!empty($property["door"])){
                        echo "<span class='tag is-white'>Puerta &raquo; ". $property["door"] ."</span>";
                    }
                ?>
            </div>
        </div>
        <div class="container-details-2">
            <div class="btn-dec">
                <span class="">Tipo</span>
                <span><?= $property["type_name"] ?></span>
            </div>
            
            <?php if(!empty($property["plant"])){ ?>
            <div class="btn-dec">
                <span class="">Planta</span>
                <span><?= $property["plant"][0]["name"] ?></span>
            </div>
            <?php } ?>
            <?php if(!empty($property["types_floors"])){ ?>
            <div class="btn-dec">
                <span class="">Tipo de piso</span>
                <span><?php 
                $ctn = 1;
                foreach($property["types_floors"] as $tf){
                    if (count($property["types_floors"]) == $ctn){
                        if (count($property["types_floors"]) === 1){
                            echo $tf["name"];
                        }else{
                            echo " y ".$tf["name"];
                        }
                    }else{
                        if ($ctn < count($property["types_floors"]) -1 ){
                            echo $tf["name"] .", " ;
                        }else{
                            echo $tf["name"];
                        }
                    }
                    $ctn += 1;
                }
                ?></span>
            </div>
            <?php } ?>

            <div class="btn-dec">
                <span class="">Categoría</span>
                <span><?= $property["category_name"] ?></span>
            </div>
            <div class="btn-dec">
                <span class="">Precio</span>
                <span><?php
                    if (intval($property["category_id"]) == 1){
                        echo ($property["rental_price"]);
                    }else if (intval($property["category_id"]) == 2){
                        echo ($property["sale_price"]);
                    }else{
                        echo "";
                    }
                    ?> €
                </span>
            </div>
            <?php if (!empty($property["meters_built"])){ ?>
            <div class="btn-dec">
                <span class="">M<sup>2</sup> Construidos</span>
                <span><?= $property["meters_built"] ?> m<sup>2</sup></span>
            </div>
            <?php } ?>
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
                if (intval($property["type_id"]) != 9){
            ?>
            <article class="message">
                <div class="message-body">
                    <div class="container-row-free">
                        <?php if(!empty($property["state_conservation"])){ ?>
                        <div class="box-li">
                            <h3 class="text-title-h">Estado de conservación</h3>
                            <span class="text-span"><?= $property["state_conservation"][0]["name"] ?></span>
                        </div>
                        <?php } ?>
                        <?php if (!empty($property["facade"])){ ?>
                        <div class="box-li">
                            <h3 class="text-title-h">Fachada del inmueble</h3>
                            <span class="text-span"><?= $property["facade"][0]["name"] ?> </span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </article>
            <?php } ?>
            <?php if (intval($property["category_id"]) == 1 && intval($property["type_id"]) != 9){ ?>
                <?php if(!empty($property["rental_type"]) || !empty($property["rental_price"])){ ?>
                <article class="message">
                    <div class="message-body">
                        <div class="container-row-free">
                            <?php if(!empty($property["rental_type"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Tipo de arquiler</h3>
                                <span class="text-span"><?= $property["rental_type"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["rental_price"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Fianza</h3>
                                <span class="text-span"><?= $property["rental_price"] ?> €</span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
                <?php } ?>
                <?php if (intval($property["type_id"]) === 1 || intval($property["type_id"]) === 13){ ?>
                <article class="message">
                    <div class="message-body">
                        <div class="container-row-free">
                            <?php if(!empty($property["max_num_tenants"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Número máximo de inquilinos</h3>
                                <span class="text-span"><?= $property["max_num_tenants"] ?></span>
                            </div>
                            <?php } if(!empty($property["appropriate_for_children"])){ ?>
                            <div class="box-li">
                                <?php if($property["appropriate_for_children"]){
                                    echo "<h3 class='text-title-h'>Apropiado para niños</h3>";
                                 }else{
                                    echo "<h3 class='text-title-h'>No apropiado para niños</h3>";
                                 } ?>
                            </div>
                            <?php } ?>
                            <div class="box-li">
                                <?php if($property["pet_friendly"]){
                                    echo "<h3 class='text-title-h'>Se admiten mascotas</h3>";
                                 }else{
                                    echo "<h3 class='text-title-h'>No se admiten mascotas</h3>";
                                 } ?>
                            </div>
                        </div>
                    </div>
                </article>
                <?php } ?>
            <?php
                }
            ?>
            <?php 
                if (intval($property["category_id"]) == 2){
            ?>    
                <article class="message">
                    <div class="message-body">
                        <div class="container-row-free">
                            <?php if (!empty($property["type_of_terrain"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Tipo de terreno</h3>
                                <span class="text-span"><?= $property["type_of_terrain"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["wheeled_access"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Acceso rodado</h3>
                                <span class="text-span"><?= $property["wheeled_access"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["nearest_municipality_distance"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Distancia municipio más cercano</h3>
                                <span class="text-span"><?= $property["nearest_municipality_distance"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["land_size"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Superficie total</h3>
                                <span class="text-span"><?= $property["land_size"] ?> m<sup>2</sup></span>
                            </div>
                            <?php } ?>


                            <?php if (!empty($property["typology"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Tipología</h3>
                                <span class="text-span"><?= $property["typology"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["community_expenses"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Gastos de comunidad</h3>
                                <span class="text-span"><?= $property["community_expenses"] ?> €</span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["ibi"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">IBI</h3>
                                <span class="text-span"><?= $property["ibi"] ?> €</span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["mortgage_rate"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Hipoteca</h3>
                                <span class="text-span"><?= $property["mortgage_rate"] ?> €</span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["reason_for_sale"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Situación de venta</h3>
                                <span class="text-span"><?= $property["reason_for_sale"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php
                }
            ?>
            <?php if (!empty($property["community_expenses"] || !empty($property["useful_meters"]) || !empty($property["plot_meters"]) || !empty($property["number_of_plants"]) || !empty($property["bathrooms"]))){ ?>
                <article class="message">
                    <div class="message-body">
                        <div class="container-row-free">
                            <?php if (!empty($property["plaza_capacity"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Capacidad de la plaza</h3>
                                <span class="text-span"><?= $property["plaza_capacity"][0]["name"] ?></span>
                            </div>
                            <?php } ?>            
                            <?php if (!empty($property["has_tenants"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Inquilinos</h3>
                                <?php if (strval($property["has_tenants"]) === "1"){
                                    echo "<span class='text-span'>Si tiene</span>";
                                }else if (strval($property["has_tenants"]) === "2"){
                                    echo "<span class='text-span'>No tiene</span>";
                                }else{
                                    echo "<span class='text-span'>Preguntar</span>";
                                } ?>
                            </div>
                            <?php } ?>            


                            <?php if (!empty($property["bank_owned_property"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Es un inmueble del banco</h3>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["useful_meters"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">M<sup>2</sup> útiles</h3>
                                <span class="text-span"><?= $property["useful_meters"] ?> m<sup>2</sup></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["plot_meters"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">M<sup>2</sup> parcela</h3>
                                <span class="text-span"><?= $property["plot_meters"] ?> m<sup>2</sup></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["number_of_plants"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Plantas de la casa o chalet</h3>
                                <span class="text-span"><?= $property["number_of_plants"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["bathrooms"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Número de baños y aseo</h3>
                                <span class="text-span"><?= $property["bathrooms"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["bedrooms"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Número de dormitorios</h3>
                                <span class="text-span"><?= $property["bedrooms"] ?></span>
                            </div>
                            <?php } ?>


                            <?php if (!empty($property["linear_meters_of_facade"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Metros lineales de fachada</h3>
                                <span class="text-span"><?= $property["linear_meters_of_facade"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["stays"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Estancias</h3>
                                <span class="text-span"><?= $property["stays"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["number_of_shop_windows"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Número de escaparates</h3>
                                <span class="text-span"><?= $property["number_of_shop_windows"] ?></span>
                            </div>
                            <?php } ?>
                            



                            <?php if (!empty($property["year_of_construction"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Año de construcción</h3>
                                <span class="text-span"><?= $property["year_of_construction"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["type_heating"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Tipo de calefacción</h3>
                                <span class="text-span"><?= $property["type_heating"][0]["name"] ?></span>
                            </div>
                            <?php } ?>
                            <?php if (!empty($property["orientations"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Orientación</h3>
                                <span class="text-span">
                                <?php
                                    $crows = 1;
                                    foreach($property["orientations"] as $o){
                                        echo $o["name"];
                                        if (count($property["orientations"])>1){
                                            if (count($property["orientations"]) == $crows){
                                                echo ".";
                                            }else{
                                                echo ", ";
                                            }
                                        }
                                        $crows += 1;
                                    }
                                ?>
                                </span>
                            </div>
                            <?php } ?>

                            <?php if (!empty($property["elevator"])){ ?>
                            <div class="box-li">
                                <h3 class="text-title-h">Tiene ascensor</h3>
                                <span class="text-span">
                                    <?php if (!empty($property["wheelchair_accessible_elevator"])){
                                        echo "<span class='span-value'>Apto para silla de ruedas.</span>";
                                    }else{
                                        echo "<span class='span-value'>No apto para silla de ruedas.</span>";
                                    } ?>
                                </span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
            <?php if (!empty($property["features"])){ ?>
                <article class="message">
                    <div class="message-header">
                        <p>Características básicas</p>
                    </div>
                    <div class="message-body">
                        <div class="container-row">
                            <ul>
                                <?php foreach($property["features"] as $feature){ ?>
                                    <li><?= $feature["name"] ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </article>
            <?php } ?>
            <div class="container-two-col-flex">
            </div>
            <?php if (!empty($property["equipments"])){ ?>
                <article class="message">
                    <div class="message-header">
                        <p>Equipamientos</p>
                    </div>
                    <div class="message-body">
                        <div class="container-row">
                            <ul>
                                <?php foreach($property["equipments"] as $feature){ ?>
                                    <li><?= $feature["name"] ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </article>
            <?php } ?>
            <?php if (!empty($property["power_consumption_rating"])){ ?>
                <article class="message">
                    <div class="message-header">
                        <p>Certificado energético</p>
                    </div>
                    <div class="message-body">
                        <div class="container-row">
                            <?php if (!empty($property["power_consumption_rating_id"]) || !empty($property["energy_consumption"])){ ?>
                                <div class="container-row-icon-rating">
                                    <span>Consumo de energía</span>
                                    <div class="container-value">
                                        <?php 
                                        if (intval($property["power_consumption_rating_id"]) === 1 || intval($property["power_consumption_rating_id"]) === 9 || intval($property["power_consumption_rating_id"]) === 10 ){     
                                            echo  "<span class='tag is-link is-light'>".$property["power_consumption_rating"][0]["name"]."</span>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 2){
                                            echo "<img src='".base_url("img/icons/flecha_1.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 3){
                                            echo "<img src='".base_url("img/icons/flecha_2.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 4){
                                            echo "<img src='".base_url("img/icons/flecha_3.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 5){
                                            echo "<img src='".base_url("img/icons/flecha_4.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 6){
                                            echo "<img src='".base_url("img/icons/flecha_5.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 7){
                                            echo "<img src='".base_url("img/icons/flecha_6.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["power_consumption_rating_id"]) === 8){
                                            echo "<img src='".base_url("img/icons/flecha_7.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else{
                                            echo "";
                                        }
                                        ?>
                                        <?php if (!empty($property["energy_consumption"])){ ?>
                                            <span><?= $property["energy_consumption"] ?> kWh/m² año</span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($property["emissions_rating_id"]) || !empty($property["emissions_consumption"])){ ?>
                                <div class="container-row-icon-rating">
                                    <span>Consumo de emisiones</span>
                                    <div class="container-value">
                                        <?php 
                                        if (intval($property["emissions_rating_id"]) === 1){
                                            echo "<img src='".base_url("img/icons/flecha_1.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 2){
                                            echo "<img src='".base_url("img/icons/flecha_2.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 3){
                                            echo "<img src='".base_url("img/icons/flecha_3.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 4){
                                            echo "<img src='".base_url("img/icons/flecha_4.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 5){
                                            echo "<img src='".base_url("img/icons/flecha_5.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 6){
                                            echo "<img src='".base_url("img/icons/flecha_6.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else if (intval($property["emissions_rating_id"]) === 7){
                                            echo "<img src='".base_url("img/icons/flecha_7.png")."' alt='' class='img-icon-p-c-rating'>";
                                        }else{
                                            echo "";
                                        }
                                        ?>
                                        <?php if (!empty($property["emissions_consumption"])){ ?>
                                            <span><?= $property["emissions_consumption"] ?> kgCO/m² año</span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
            <?php if (!empty($property["interior_wheelchair"]) || !empty($property["outdoor_wheelchair"])){ ?>
                <article class="message">
                    <div class="message-header">
                        <p>Adaptado a personas con movilidad reducida</p>
                    </div>
                    <div class="message-body">
                        <div class="container-row">
                            <ul>
                                <?php if (!empty($property["interior_wheelchair"])){
                                    echo "<li>El acceso exterior a la vivienda está adaptada para silla de ruedas</li>";
                                }else{
                                    echo "<li>El acceso exterior a la vivienda <span class='tag is-danger'>NO</span> está adaptada para silla de ruedas</li>";
                                } if (!empty($property["outdoor_wheelchair"])){ 
                                    echo "<li>El interior de la vivienda está adaptada para silla de ruedas</li>";
                                } else{
                                    echo "<li>El interior de la vivienda <span class='tag is-danger'>NO</span> está adaptada para silla de ruedas</li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </div>
    </div>
    <div class="container-column-2">
        <div class="container-details-contact-owner">
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
                            <img src="<?= base_url("img/photo_profile/").$property["user"]["photo"] ?>" alt="Placeholder image" />
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
                        <div>
                            <?php if(!empty($property["user"]["landline_phone"])){ ?>
                                <a href="tel:<?= $property["user"]["landline_phone"] ?>" class="btn-contact-redirect A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="owner_calls">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24"><path fill="#ffffff" d="M3.833 4h4.49L9.77 7.618l-2.325 1.55A1 1 0 0 0 7 10c.003.094 0 .001 0 .001v.021a2 2 0 0 0 .006.134q.008.124.035.33c.039.27.114.642.26 1.08c.294.88.87 2.019 1.992 3.141s2.261 1.698 3.14 1.992c.439.146.81.22 1.082.26a4 4 0 0 0 .463.04l.013.001h.008s.112-.006.001 0a1 1 0 0 0 .894-.553l.67-1.34l4.436.74v4.32c-2.111.305-7.813.606-12.293-3.874S3.527 6.11 3.833 4m5.24 6.486l1.807-1.204a2 2 0 0 0 .747-2.407L10.18 3.257A2 2 0 0 0 8.323 2H3.781c-.909 0-1.764.631-1.913 1.617c-.34 2.242-.801 8.864 4.425 14.09s11.848 4.764 14.09 4.425c.986-.15 1.617-1.004 1.617-1.913v-4.372a2 2 0 0 0-1.671-1.973l-4.436-.739a2 2 0 0 0-2.118 1.078l-.346.693a5 5 0 0 1-.363-.105c-.62-.206-1.481-.63-2.359-1.508s-1.302-1.739-1.508-2.36a5 5 0 0 1-.125-.447z"/></svg>
                                    Llamar
                                </a>
                            <?php } ?>
                        </div>
    
                        <button id="btn-openLoginModal" data-email="<?= $property["user"]["email"] ?>" data-link="https://kconecta.com/result/<?=$property["reference"]?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24"><path fill="#666666" d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5l-8-5zm0 12H4V8l8 5l8-5z"/></svg>
                            Enviar mensaje por correo
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="banners_google_ads">
            <!-- banner_in_details_1 -->
            <ins class="adsbygoogle"
                style="display:block"
                data-ad-client="ca-pub-9259257545893744"
                data-ad-slot="1002920395"
                data-ad-format="auto"
                data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>

            <!-- banner_in_details_2 -->
            <ins class="adsbygoogle"
                style="display:block"
                data-ad-client="ca-pub-9259257545893744"
                data-ad-slot="5043944044"
                data-ad-format="auto"
                data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal" id="googleLoginModal">
    <div class="modal-background"></div>
    <div class="modal-content box" style="width: 100%; max-width: 26rem">
        <div class="ctn-sendMessageEmail container is-fullwidth" style="display:none;">
            <div class="columns is-centered is-fullwidth">
                <div class="column is-fullwidth">
                    <h1 class="title is-4 has-text-centered" style="margin-bottom: 32px;">Enviar Mensaje</h1>
                    <form action="#" id="formSendMessageEmail" class="is-fullwidth">
                        <input type="hidden" name="provider_email">
                        <input type="hidden" name="property_link">
                        <input type="hidden" name="user_email">
                        <input type="hidden" name="user_name">
                        <input type="hidden" name="property_id" value="<?= $property["id"] ?>">
                        <div class="field">
                            <div class="control">
                                <textarea name="message" id="messageEmailToProvider" class="textarea is-medium" placeholder="Escribe tu mensaje aquí..."></textarea>
                            </div>
                        </div>
                        <div class="control">
                            <button type="button" id="btnSubitSendMessageToProvider" class="button is-primary is-fullwidth has-text-white A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="email_owner">Enviar Mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="ctn-googleSignInButton is-fullwidth" style="display:none;">
            <h2 style="margin-bottom: 1rem;">Inicia sesión con Google</h2>
            <div id="googleSignInButton"></div>
            <div id="loginStatus" style="margin-top:10px;"></div>
        </div>
    </div>
    <button class="button modal-close"></button>
</div>
<div class="modal" id="modal-share">
    <div class="modal-background"></div>
    <div class="modal-content">
        <div class="box p-5"> <!-- Aumentado el padding con p-5 -->
            <!-- Sección de cabecera con título y botón de cierre -->
            <div class="is-clearfix"> <!-- Aumentado el margen inferior -->
                <h3 class="title is-4 is-pulled-left has-text-grey-darker">Compartir anuncio</h3> <!-- Color de texto más oscuro -->
                <!-- El botón de eliminar es típicamente manejado por el componente padre del modal,
                    pero se incluye aquí para la completitud visual como se ve en la imagen. -->
                <button class="delete is-large is-pulled-right" onclick="closeModal(document.getElementById('modal-share'))" aria-label="close"></button>
            </div>
            <p class="subtitle is-6 mb-2 has-text-grey"><?= $property["title"] ?></p> <!-- Color de texto gris -->
            <p class="subtitle is-6 has-text-weight-bold has-text-primary"><?php
                    if (intval($property["category_id"]) == 1){
                        echo ($property["rental_price"]);
                    }else if (intval($property["category_id"]) == 2){
                        echo ($property["sale_price"]);
                    }else{
                        echo "";
                    }
                    ?> €
            </p> <!-- Precio en negrita y color primario -->

            <hr class="has-background-light"> <!-- Línea divisoria más clara -->

            <!-- Sección de Compartir por Mensajería -->
            <div class="field mb-5">
                <label class="label has-text-grey-dark">Compartir por redes sociales</label> <!-- Color de texto más oscuro -->
                <div class="control">
                    <a style="display:flex;column-gap:.4rem;" class="button is-success is-outlined is-fullwidth is-rounded A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="whatsapp_clicks" href="https://wa.me/?text=Estimado/a, comparto con usted los detalles de una propiedad que podría resultar de interés. https://kconecta.com/result/<?=$property["reference"] ?>" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="#65a30d" fill-rule="evenodd" d="M12 1.25c5.937 0 10.75 4.813 10.75 10.75S17.937 22.75 12 22.75c-1.86 0-3.61-.473-5.137-1.305l-4.74.795a.75.75 0 0 1-.865-.852l.8-5.29A10.7 10.7 0 0 1 1.25 12C1.25 6.063 6.063 1.25 12 1.25M7.943 6.7c-.735 0-1.344.62-1.23 1.386c.216 1.436.854 4.082 2.752 5.994c1.984 1.999 4.823 2.854 6.36 3.191c.796.175 1.475-.455 1.475-1.232v-1.824a.3.3 0 0 0-.192-.28l-1.96-.753a.3.3 0 0 0-.166-.014l-1.977.386c-1.275-.66-2.047-1.4-2.51-2.515l.372-2.015a.3.3 0 0 0-.014-.16l-.735-1.969a.3.3 0 0 0-.28-.195z" clip-rule="evenodd"/></svg>
                        Enviar por Whatsapp
                    </a>
                </div>
                <!-- <label class="label has-text-grey-dark">Compartir por mensajería</label> -->
                <div class="control mt-3">
                    <button style="display:flex;column-gap:.4rem;" class="button is-success is-outlined is-fullwidth is-rounded A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="shared_facebook" id="btn-share-facebook" data-url="https://kconecta.com/result/<?=$property["reference"] ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 256 256"><path fill="#1877F2" d="M256 128C256 57.308 198.692 0 128 0S0 57.308 0 128c0 63.888 46.808 116.843 108 126.445V165H75.5v-37H108V99.8c0-32.08 19.11-49.8 48.348-49.8C170.352 50 185 52.5 185 52.5V84h-16.14C152.959 84 148 93.867 148 103.99V128h35.5l-5.675 37H148v89.445c61.192-9.602 108-62.556 108-126.445"/><path fill="#FFF" d="m177.825 165l5.675-37H148v-24.01C148 93.866 152.959 84 168.86 84H185V52.5S170.352 50 156.347 50C127.11 50 108 67.72 108 99.8V128H75.5v37H108v89.445A129 129 0 0 0 128 256a129 129 0 0 0 20-1.555V165z"/></svg>
                        Publicar en facebook
                    </button>
                </div>
            </div>

            <hr class="has-background-light">

            <!-- Sección de Copiar Enlace -->
            <div class="field mb-5">
                <label class="label has-text-grey-dark">Copiar enlace</label>
                <div class="field has-addons">
                    <div class="control is-expanded">
                        <input class="input is-rounded" id="link-reference" type="text" value="<?= base_url("result/").$property["reference"] ?>" readonly> <!-- Input redondeado -->
                    </div>
                    <div class="control">
                        <button class="button is-info is-rounded A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="link_copied" id="copyLinkButton">Copiar</button> <!-- Botón redondeado -->
                    </div>
                </div>
            </div>

            <hr class="has-background-light">

            <!-- Sección de Compartir por Email -->
            <div class="field mb-5">
                <label class="label has-text-grey-dark">Compartir por email</label>
                <div class="control">
                    <input class="input is-rounded" type="email" placeholder="Email de tus amigos" id="input-email-share"> <!-- Input redondeado -->
                </div>
                <p class="help has-text-grey-light">Si son varios sepáralos con una coma (,)</p> <!-- Texto de ayuda más claro -->
            </div>

            <!-- Botón de Enviar -->
            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <button class="button is-primary is-rounded A7x9Vb2QmL-psu" data-i="<?= $property["id"] ?>" data-col="shared_friends" id="send-share-to-emails">Enviar</button> <!-- Botón redondeado -->
                </div>
            </div>
        </div>
    </div>
    <button class="button modal-close"></button>
</div>

<!-- Cargar API de Google -->
<script>
    const btn_share = document.getElementById("btn-share");
    btn_share.addEventListener("click", ()=>{
        openModal(document.getElementById("modal-share"));
    })
    const sendShareToEmails = document.getElementById("send-share-to-emails");
    const inputEmailShare = document.getElementById("input-email-share");
    const propertyLink = document.getElementById("link-reference");
    sendShareToEmails.addEventListener("click", async()=>{
        if (inputEmailShare.value.trim() !== ""){
            sendShareToEmails.textContent = "Enviando...";
            sendShareToEmails.setAttribute("disabled", true);
            await fetch("/api/send/message/email_share?user_emails="+inputEmailShare.value+"&property_link="+propertyLink.value).then(res => res.json()).then(data => {
                inputEmailShare.value = "";
                closeModal(document.getElementById("modal-share"));
                sendShareToEmails.textContent = "Enviar";
                sendShareToEmails.removeAttribute("disabled");
            })
        }
    });


    const btn_share_facebook = document.getElementById("btn-share-facebook");
    btn_share_facebook.addEventListener("click", ()=>{
        const url = btn_share_facebook.dataset.url;
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        window.open(facebookUrl, '_blank');
    })
    document.getElementById('copyLinkButton').addEventListener('click', function() {
        const linkInput = this.closest('.field').querySelector('input');
        linkInput.select();
        linkInput.setSelectionRange(0, 99999); // Para dispositivos móviles
        document.execCommand("copy");

        // Proporcionar feedback visual al usuario (solo cambio de texto)
        const originalText = this.textContent;
        this.textContent = '¡Copiado!';
        setTimeout(() => {
            this.textContent = originalText;
        }, 2000);
    });
</script>
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
    const formSendMessageEmail = document.getElementById("formSendMessageEmail");
    const ctn_sendMessageEmail = document.querySelector(".ctn-sendMessageEmail");
    const ctn_googleSignInButton = document.querySelector(".ctn-googleSignInButton");

    let googleAuthInstance;

    function openLoginModal() {
        const details_userfree = localStorage.getItem("userfree");
        if (details_userfree){
            const json_userfree = JSON.parse(details_userfree);
            ctn_googleSignInButton.style.display = "none";
            ctn_sendMessageEmail.removeAttribute("style");

            document.getElementsByName("user_email")[0].value = json_userfree.user.email;
            document.getElementsByName("user_name")[0].value = json_userfree.user.name;
        }else{
            ctn_sendMessageEmail.style.display = "none";
            ctn_googleSignInButton.removeAttribute("style");
            initializeGoogleAuth();
        }
        openModal(document.getElementById("googleLoginModal"));
    }

    function initializeGoogleAuth() {
        google.accounts.id.initialize({
            client_id: "916285583768-enj3t3n6c9esrggsn8giik6j541kbcjg.apps.googleusercontent.com", //'TU_CLIENT_ID_DE_GOOGLE.apps.googleusercontent.com',
            callback: handleCredentialResponse,
            ux_mode: 'popup', // Importante para que funcione en el modal
            context: 'use'
        });
        
        // Renderizar el botón
        google.accounts.id.renderButton(
            document.getElementById("googleSignInButton"),{ 
                theme: "outline", 
                size: "large",
                // width: "350" 
            }
        );
        
        // Opcional: Mostrar el One Tap (aparece automáticamente)
        google.accounts.id.prompt();
    }

    function handleCredentialResponse(response) {
        const loginStatus = document.getElementById('loginStatus');
        loginStatus.innerHTML = 'Verificando credenciales...';
        const form_data = new FormData();
        form_data.append("credential", response.credential);
        // Enviar el token al backend para verificación
        fetch('/api/google/user/verify_token_google', {
            method: 'POST',
            body: form_data,
        })
        .then(response => response.json())
        .then(data => {
            localStorage.setItem("userfree", JSON.stringify(data));
            const ctn_profile = document.querySelector(".container-profile-userfree-app");
            ctn_profile.classList.add("container-profile-userfree-app-active");
            const img_tag = document.createElement("img");
            img_tag.src = data.user.picture;
            img_tag.alt = "profile user";
            ctn_profile.insertAdjacentElement("beforeend", img_tag);

            ctn_googleSignInButton.style.display = "none";
            ctn_sendMessageEmail.removeAttribute("style");
            document.querySelector(".a-loggin-redirect").style.display = "none";

            document.getElementsByName("user_email")[0].value = data.user.email;
            document.getElementsByName("user_name")[0].value = data.user.name;
        })
        .catch(error => {
            loginStatus.innerHTML = 'Error en la conexión';
            console.error('Error:', error);
        });
    }
    
    const btn_open_login_modal = document.getElementById("btn-openLoginModal");
    btn_open_login_modal.addEventListener("click", ()=>{
        document.getElementsByName("provider_email")[0].value = btn_open_login_modal.dataset.email;
        document.getElementsByName("property_link")[0].value = btn_open_login_modal.dataset.link;
        openLoginModal();
    })
    const btn_send_message_email = document.getElementById("btnSubitSendMessageToProvider");
    const messageEmailToProvider = document.getElementById("messageEmailToProvider")
    btn_send_message_email.addEventListener("click", async()=>{
        btn_send_message_email.textContent = "Enviando...";
        btn_send_message_email.setAttribute("disabled", true);
        if (messageEmailToProvider){
            const form_data = new FormData(formSendMessageEmail);
            await fetch("/api/send/message/email_to_provider", {
                method: "POST",
                body: form_data,
            }).then(res => res.text()).then(data =>{
                btn_send_message_email.textContent = "Enviar Mensaje";
                btn_send_message_email.removeAttribute("disabled");
                closeModal(document.getElementById("googleLoginModal"));
                document.getElementsByName("message")[0].value = "";
            });
        }else{
            alert("El mensaje no debe estar vacio.");
        }

    })
</script>










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
<script src="<?= base_url()."js/visits_control.js" ?>"></script>

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
    video_app?.addEventListener("contextmenu", function(e) {
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
