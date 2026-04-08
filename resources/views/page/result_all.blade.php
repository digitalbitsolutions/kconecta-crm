@extends('layouts.page')

@section('nav_option')
<a href="<?= site_url() ?>">
    <span>Ir al inicio</span>
</a>
@endsection

@section('css')
    <link rel="stylesheet" href="<?= base_url()."css/page/details.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/page/result_all.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/ui/input_number_cont.css" ?>">
    <link rel="stylesheet" href="<?= base_url()."css/ui/input_radio.css" ?>">

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
                        <label for="">
                            <span>Categoría</span>
                            <div class="select">
                                <select name="ca" id="">
                                    <option value="">Indiferente</option>
                                    <option value="1" <?= $category_id == 1? "selected" : "" ?>>Alquiler</option>
                                    <option value="2" <?= $category_id == 2? "selected" : "" ?>>Venta</option>
                                </select>
                            </div>            
                        </label>
                        <label for="">
                            <span>Tipo de inmueble</span>
                            <div class="select">
                                <select name="ty" id="">
                                    <option value="">Indiferente</option>
                                    <option value="1" <?= $type_id == 1 ? "selected" : "" ?>>Casa o chalet</option>
                                    <option value="15" <?= $type_id == 15 ? "selected" : "" ?>>Casa rústica</option>
                                    <option value="13" <?= $type_id == 13 ? "selected" : "" ?>>Piso</option>
                                    <option value="4" <?= $type_id == 4 ? "selected" : "" ?>>Local o nave</option>
                                    <option value="14" <?= $type_id == 14 ? "selected" : "" ?>>Garaje</option>
                                    <option value="9" <?= $type_id == 9 ? "selected" : "" ?>>Terreno</option>
                                </select>
                            </div>                
                        </label>
                        <label for="">
                            <span>Precio</span>
                            <div class="div-two-col">
                                <input type="number" name="p_min" value="<?= $p_min ?>" class="input" placeholder="Mínimo">
                                <input type="number" name="p_max" value="<?= $p_max ?>" class="input" placeholder="Máximo">
                            </div>
                        </label>
                        <label for="">
                            <span>m<sup>2</sup> construidos</span>
                            <div class="div-two-col">
                                <input type="number" name="built_min" value="<?= $built_min?>" class="input" placeholder="Mínimo">
                                <input type="number" name="built_max" value="<?= $built_max ?>" class="input" placeholder="Máximo">
                            </div>
                        </label>
                        <label for="">
                            <span>N° de baños (min)</span>
                            <div class="container-controls-cont-ui">
                                <span class="icon-ui is-left" id="n_bar_rest">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-width="2" d="M3 8h10"/></svg>                      
                                </span>
                                <input class="input" readonly name="n_bar" id="n_bar" type="number" value="<?= $n_bar ?>" style="text-align: center;"/>
                                <span class="icon-ui is-right" id="n_bar_sum">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"/></svg>
                                </span>
                            </div>
                        </label>
                        <label for="">
                            <span>N° de dormitorios (min)</span>
                            <div class="container-controls-cont-ui">
                                <span class="icon-ui is-left" id="n_ber_rest">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-width="2" d="M3 8h10"/></svg>                      
                                </span>
                                <input class="input" readonly name="n_ber" id="n_ber" type="number" value="<?= $n_ber ?>" style="text-align: center;"/>
                                <span class="icon-ui is-right" id="n_ber_sum">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"/></svg>
                                </span>
                            </div>
                        </label>
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
                            <span>Propiedades</span>
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
                    <a href="<?= site_url("result/".$pr["reference"]) ?>">
                    <div class="card">
                        <div class="card-image">
                            <img src="<?= !empty($pr["cover_image"]["url"]) ? base_url()."img/uploads/".$pr["cover_image"]["url"] : base_url()."img/image-icon-1280x960.png" ?>" alt="">
                        </div>
                        <div class="badge-container">
                            <span class="badge"><?= $pr["type_name"] ?></span>
                            <span class="badge"><?= $pr["category_name"] ?></span>
                        </div>
                        <div class="ctn-detils-p-m">
                            <span><span class="meters-span"><?= !empty($pr["meters_built"])? $pr["meters_built"] : $pr["land_size"] ?> </span> m<sup>2</sup> - <span class="price-span"><?= !empty($pr["sale_price"]) ? $pr["sale_price"] :  (!empty($pr["rental_price"]) ? $pr["rental_price"] : "") ?></span> ?</span>
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
                                    <?php }?>
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
                        </div>
                    </div>
                    </a>
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
                            url: "/img/icon-location-main-app.webp",
                            scaledSize: new google.maps.Size(30, 42)
                        },
                        title: `Cantidad: ${location.quantity}`
                    });
        
                    // InfoWindow con detalles adicionales
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="container-map-details-info-app-main">
                                <h3>${location.title}</h3>
                                <div class="div-row-data">
                                    <span>Precio:</span>
                                    <span>${location.price} &euro;</span>
                                </div>
                                <a href="/result/${location.id}" class="a-redirect-view">
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
                iconUrl: "/img/icon-location-main-app.webp",
                iconSize: [30, 42],
                iconAnchor: [15, 42],
                popupAnchor: [0, -42],
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
                            <div class="div-row-data">
                                <span>Precio:</span>
                                <span>${location.price} &euro;</span>
                            </div>
                            <a href="/result/${location.id}" class="a-redirect-view">
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
                const response = await fetch("/api/properties_for_map?" + query_string, {
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
<script>
    const formatter_tags_1 = document.querySelectorAll(".meters-span");
    const formatter_tags_2 = document.querySelectorAll(".price-span");
    formatter_tags_1.forEach((tag)=>{
        format_1(tag, "element");
    })
    formatter_tags_2.forEach((tag)=>{
        format_1(tag, "element");
    })

    add_rest("n_bar_rest", "n_bar", "n_bar_sum");
    add_rest("n_ber_rest", "n_ber", "n_ber_sum");
</script>
<script src="<?= base_url("js/autocomplet.js") ?>"></script>
@endsection


