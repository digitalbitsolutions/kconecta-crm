@php
if (!function_exists('base_url')) {
    function base_url(string $path = '') {
        $base = url('/');
        if ($path == '') {
            return $base . '/';
        }
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('site_url')) {
    function site_url(string $path = '') {
        return url($path);
    }
}
@endphp
@extends('layouts.backoffice')

@section('title', 'Kconecta - Actualizar Local o nave')

@section('heading')
    Editar propiedad
@endsection

@section('subheading')
    Actualiza los datos del anuncio
@endsection

@section('header_actions')
    <a class="secondary" href="{{ url('/post/my_posts') }}">Ver propiedades</a>
@endsection
@section('styles')
<link rel="stylesheet" href="<?= base_url()."css/app/forms.css" ?>">
<link rel="stylesheet" href="<?= base_url()."css/app/map_address.css" ?>">
<link rel="stylesheet" href="<?= base_url()."css/ui/input_text.css" ?>">
<link rel="stylesheet" href="<?= base_url()."css/ui/input_radio.css" ?>">
<link rel="stylesheet" href="<?= base_url()."css/ui/input_checkbox.css" ?>">
<link rel="stylesheet" href="<?= base_url()."css/page/property-form.css" ?>">
@endsection

@section('content')

<form action="{{ url('/post/update') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="container-title-page">
        <h2>Actualizar &raquo; <span>Local o nave</span></h2>
    </div>
    <div class="container-main">
        <input type="hidden" name="property_id" value="<?= $property[0]["id"] ?>">

        <input type="hidden" name="city" value="<?= $propertyAddress[0]["city"] ?>" id="city">
        <input type="hidden" name="postal_code" value="<?= $propertyAddress[0]["postal_code"] ?>" id="postal_code">
        <input type="hidden" name="province" value="<?= $propertyAddress[0]["province"] ?>" id="province">
        <input type="hidden" name="country" value="<?= $propertyAddress[0]["country"] ?>" id="country">
        <input type="hidden" name="latitude" value="<?= $propertyAddress[0]["latitude"] ?>" id="latitude">
        <input type="hidden" name="longitude" value="<?= $propertyAddress[0]["longitude"] ?>" id="longitude">

        <h2 class="title-main-row-section">Localizaci&oacute;n del inmueble</h2>
        <div class="container-row-form box">

            <label for="" class="label-col-100">
                <span class="title-label">Localidad *</span>
                <input type="text" class="input" value="<?= $property[0]["locality"] ?>" name="locality" required>
            </label>

            <label for="" class="label-col-100">
                <span class="title-label">Nombre de la v&iacute;a *</span>
                <div class="container-google-maps-required-tags">
                    <input type="text" class="input" value="<?= $propertyAddress[0]["address"] ?>" id="address" name="address" required autocomplete="off">
                    <button class="button" type="button" id="button-open-map-google"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg></button>
                </div>
            </label>
            
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">Planta</span>
                    <div class="select">
                        <select name="plant" required>
                            <option>Seleccione</option>
                            <?php
                                $ctn = 0; 
                                foreach($plant as $plnt){ 
                                    if ($ctn > 4 ){
                            ?>
                            <option value="<?= $plnt["id"] ?>" <?= $plnt["id"] == $property[0]["plant_id"] ? "selected" : "" ?> ><?= $plnt["name"] ?></option>
                            <?php } $ctn++; } ?>
                        </select>
                    </div>
                </label>
                <!-- <?php $col_type_floor_id = array_column($typesFloors, "type_floor_id"); foreach($typeFloor as $tf){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="checkbox" <?= in_array($tf["id"], $col_type_floor_id) ? "checked" : "" ?> class="checkbox-input-ui" hidden="" name="type_floor[]" value="<?= $tf["id"] ?>" />
                    <span class="checkmark-checkbox-input-ui"></span>
                    <?= $tf["name"] ?>
                </label>
                <?php } ?> -->
            </div>
            <div class="div-col-2">
                <label for="" class="label-col-100">
                    <span class="title-label">Bloque / Esc.</span>
                    <input type="text" class="input" value="<?= $property[0]["esc_block"] ?>" name="esc_block">
                </label>
                <label for="" class="label-col-100">
                    <span class="title-label">Puerta</span>
                    <input type="text" class="input" value="<?= $property[0]["door"] ?>" name="door">
                </label>
            </div>
            <label for="" class="label-col-100">
                <span class="title-label">Nombre de la urbanizaci&oacute;n</span>
                <input type="text" class="input" value="<?= $property[0]["name_urbanization"] ?>" name="name_urbanization">
            </label>
            <!-- <div class="div-col-1">
                <span class="title-label">&iquest;C&oacute;mo prefieres que te contacten?</span>
                <?php foreach($contactOption as $cnt_option){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="contact_option" value="<?= $cnt_option["id"] ?>" /><?= $cnt_option["name"]?>
                </label>
                <?php } ?>
            </div> -->
            <div class="div-col-1">
                <span class="title-label">Visibilidad en portales</span>
                <?php foreach($visibilityInPortals as $vip){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= $vip["id"] == $property[0]["visibility_in_portals_id"] ? "checked" : "" ?> name="visibility_in_portals" value="<?= $vip["id"] ?>" /><div class="checkmark"></div></label>
                    <?= $vip["name"]?>    
                </label>
                <?php } ?>
            </div>
            
        </div>

        <h2 class="title-main-row-section">Operaci&oacute;n y precio *</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <span class="title-label">Operaci&oacute;n</span>
                <?php foreach($category as $cate){ 
                    if (intval($cate["id"]) === 2 || intval($cate["id"]) === 1){ 
                ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="category" <?= $cate["id"] == $property[0]["category_id"] ? "checked" : "" ?> value="<?= $cate["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $cate["name"] ?>
                </label>
                <?php }} ?>

                <span class="title-label">Ubicaci&oacute;n *</span>
                <div class="select">
                    <select name="location_premises" required>
                        <option value="" selected disabled>Seleccione</option>
                        <?php foreach($locationPremises as $lp){ ?>
                        <option value="<?= $lp["id"] ?>" <?= $lp["id"] == $property[0]["location_premises_id"] ? "selected" : "" ?> ><?= $lp["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <!-- <div class="div-col-1 container-reantal-type-div" style="display: none;">
                <span class="title-label">Tipo de arquiler</span>
                <?php foreach($rentalType as $r_type){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="rental_type" value="<?= $r_type["id"] ?>"/><?= $r_type["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            <div class="div-col-1">
                <label for="" class="label-col-100 sale_price_label">
                    <span class="title-label">Precio de venta *</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">&euro;</span>
                        <input placeholder="Precio" type="text" class="input-ui" value="<?= $property[0]["sale_price"] ?>" name="sale_price" id="sale_price">
                    </div>
                </label>
                <label for="" class="label-col-100 rental_price_label" style="display: none;">
                    <span class="title-label">Precio de arquiler</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">&euro;</span>
                        <input placeholder="Precio" type="text" class="input-ui" value="<?= $property[0]["rental_price"] ?>" name="rental_price" id="rental_price">
                    </div>
                </label>
                <label for="" class="label-col-100 container-for-rent-only" style="display: none;">
                    <span class="title-label">Fianza</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">Meses</span>
                        <input placeholder="" type="text" class="input-ui" value="<?= $property[0]["guarantee"] ?>" name="guarantee" id="guarantee">
                    </div>
                </label>
                <label for="" class="label-col-100 container-for-sale-only">
                    <span class="title-label">Gastos de comunidad</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">&euro;/mes</span>
                        <input placeholder="Precio" type="text" class="input-ui" value="<?= $property[0]["community_expenses"] ?>" name="community_expenses" id="community_expenses">
                    </div>
                </label>
                <label for="" class="label-col-100 container-for-sale-only">
                    <span class="title-label">IBI</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">&euro;/a&ntilde;o</span>
                        <input placeholder="Precio" type="text" class="input-ui" value="<?= $property[0]["ibi"] ?>" name="ibi" id="ibi">
                    </div>
                </label>
            </div>
            <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Hipoteca pendiente</span>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= !empty($property[0]["mortgage_rate"])? "checked" : "" ?> name="mortgage_state" value="1" /><div class="checkmark"></div></label>
                    SI
                </label>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= empty($property[0]["mortgage_rate"])? "checked" : "" ?> name="mortgage_state" value="0" /><div class="checkmark"></div></label>
                    NO
                </label>
                <label for="" class="label-col-100 mortgage_rate_label" style="<?= empty($property[0]["mortgage_rate"])? "display: none;" : "" ?>">
                    <span class="title-label">Importe *</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">&euro;</span>
                        <input placeholder="Precio" type="text" class="input-ui" value="<?= $property[0]["mortgage_rate"] ?>" name="mortgage_rate" id="mortgage_rate">
                    </div>
                </label>
            </div>
        </div>

        <h2 class="title-main-row-section container-for-sale-only">Estado de ocupaci&oacute;n</h2>
        <div class="container-row-form box container-for-sale-only">
            <div class="div-col-1">
                <span class="title-label">&iquest;Tiene inquilinos? *</span>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= $property[0]["has_tenants"] == 1 ? "checked" : "" ?> name="has_tenants" value="1" required /><div class="checkmark"></div></label>
                    SI
                </label>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= $property[0]["has_tenants"] == 2 ? "checked" : "" ?> name="has_tenants" value="2" required /><div class="checkmark"></div></label>
                    NO
                </label>
            </div>
        </div>
        <h2 class="title-main-row-section">Caracter&iacute;sticas del local o nave</h2>
        <div class="container-row-form box">
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Caracter&iacute;stica adicional *</span>
                <?php foreach($typeFloor as $tf){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="checkbox" name="type_floor[]" value="<?= $tf["id"] ?>" /><?= $tf["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Tipolog&iacute;a *</span>
                <?php foreach($typology as $typ){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="typology" value="<?= $typ["id"] ?>" required /><?= $typ["name"] ?>
                </label>
                <?php } ?>
            </div> -->

            <div class="div-col-1">
                <span class="title-label">Estado de conservaci&oacute;n *</span>
                <?php foreach($stateConservation as $stCons){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" <?= $stCons["id"] == $property[0]["state_conservation_id"] ? "checked" : "" ?> name="state_conservation" value="<?= $stCons["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $stCons["name"] ?>
                </label>
                <?php } ?>
            </div>
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">M<sup>2</sup> constru&iacute;dos *</span>
                    <input type="text" class="input" value="<?= $property[0]["meters_built"] ?>" name="meters_built" required id="meters_built">
                </label>
                <label for="">
                    <span class="title-label">M<sup>2</sup> &uacute;tiles</span>
                    <input type="text" class="input" value="<?= $property[0]["useful_meters"] ?>" name="useful_meters" id="useful_meters">
                </label>
            </div>
            <!-- <label for="">
                <span class="title-label">M<sup>2</sup> parcela</span>
                <input type="text" class="input" name="plot_meters">
            </label> -->
            <div class="div-col-1">
                <!-- <label for="">
                    <span class="title-label">N&uacute;mero de dormitorios *</span>
                    <input type="text" class="input" name="bedrooms" required>
                </label> -->
                <label for="">
                    <span class="title-label">N&uacute;mero de ba&ntilde;os*</span>
                    <div class="container-controls-cont">
                        <span class="icon is-left" id="bathrooms_rest">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-width="2" d="M3 8h10"/></svg>                      
                        </span>
                        <input class="input" readonly name="bathrooms" id="bathrooms" type="number" value="<?= $property[0]["bathrooms"] ?>" style="text-align: center;" required/>
                        <span class="icon is-right" id="bathrooms_sum">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"/></svg>
                        </span>
                    </div>
                </label>
            </div>
            <div class="div-col-1">
                <span class="title-label">Metros lineales de fachada</span>
                <label for="">
                    <input type="text" class="input" value="<?= $property[0]["linear_meters_of_facade"] ?>" name="linear_meters_of_facade" id="linear_meters_of_facade">
                </label>
            </div>
            <div class="div-col-1">
                <span class="title-label">Estancias *</span>
                <label for="">
                    <input type="text" class="input" value="<?= $property[0]["stays"] ?>" name="stays" required id="stays">
                </label>
            </div>
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">N&uacute;mero de escaparates *</span>
                    <div class="container-controls-cont">
                        <span class="icon is-left" id="number_of_shop_windows_rest">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-width="2" d="M3 8h10"/></svg>                      
                        </span>
                        <input class="input" readonly name="number_of_shop_windows" id="number_of_shop_windows" type="number" value="<?= $property[0]["number_of_shop_windows"] ?>" style="text-align: center;" required/>
                        <span class="icon is-right" id="number_of_shop_windows_sum">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"/></svg>
                        </span>
                    </div>
                </label>
            </div>
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">Plantas del local o nave *</span>
                    <div class="container-controls-cont">
                        <span class="icon is-left" id="number_of_plants_rest">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-width="2" d="M3 8h10"/></svg>                      
                        </span>
                        <input class="input" readonly name="number_of_plants" id="number_of_plants" type="number" value="<?= $property[0]["number_of_plants"] ?>" style="text-align: center;" required/>
                        <span class="icon is-right" id="number_of_plants_sum">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#ffffff" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"/></svg>
                        </span>
                    </div>
                </label>
            </div>
            <!-- <label for="">
                <span class="title-label">Plantas del chalet</span>
                <input type="text" class="input" name="number_of_plants">
            </label> -->
            <!-- <label for="" class="container-for-rent-only">
                <span class="title-label">N&uacute;mero de habitaciones *</span>
                <input type="text" class="input" name="rooms" required>
            </label> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Fachada del inmueble *</span>
                <?php foreach($facade as $fac){ ?>
                    <label class="radio label-radio-checkbox-col-100">
                        <input type="radio" name="facade" value="<?= $fac["id"] ?>" /> <?= $fac["name"] ?>
                    </label>
                <?php } ?>
            </div> -->
            <!-- <label for="" class="label-col-100">
                <span class="title-label">Clase energ&eacute;tica *</span>
                <div class="select">
                    <select name="energy_class" required>
                        <option>Seleccione</option>
                        <?php foreach($energyClass as $enclass){ ?>
                        <option value="<?= $enclass["id"] ?>"><?= $enclass["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </label> -->
            
            <!-- <div class="div-col-1">
                <span class="title-label">Orientaci&oacute;n</span>
                <?php foreach($orientation as $orient){ ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="orientation[]" value="<?= $orient["id"] ?>" /><?= $orient["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            
            <!-- <div class="div-col-1">
                <span class="title-label">Otras caracter&iacute;sticas del piso</span>
                <?php foreach($feature as $featur){ 
                    if ($featur["id_type"] == 1){
                ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="feature[]" value="<?= $featur["id"] ?>" /><?= $featur["name"] ?>
                </label>
                <?php }} ?>
            </div> -->
            <label for="">
                <span class="title-label">A&ntilde;o de construcci&oacute;n del edificio</span>
                <input type="text" class="input" value="<?= $property[0]["year_of_construction"] ?>" name="year_of_construction">
            </label>
            <div class="div-col-1">
                <span class="title-label">Categor&iacute;a</span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" class="checkbox-input-ui" <?= !empty($property[0]["bank_owned_property"]) ? "checked" : ""  ?> hidden="" name="bank_owned_property" value="1" />
                    <span class="checkmark-checkbox-input-ui"></span>
                    Inmueble de banco
                </label>
            </div>
            <!-- <div class="div-col-1">
                <span class="title-label">Adaptado a personas con movilidad reducida</span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="outdoor_wheelchair" value="1" />El acceso exterior a la vivienda esta adaptada para silla de ruedas
                </label>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="interior_wheelchair" value="1" />El interior de la vivienda esta adaptada para silla de ruedas
                </label> 
            </div>  -->
            <div class="div-col-1">
                <span class="title-label">Tipo de calefacci&oacute;n</span>
                <div class="select">
                    <select name="type_heating" id="type_heating">
                        <option>Seleccione</option>
                        <?php foreach($typeHeating as $th){ ?>
                        <option value="<?= $th["id"] ?>" <?= $th["id"] == $property[0]["type_heating_id"] ? "selected" : "" ?> ><?= $th["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="div-col-1" id="container_heating_fuel" style="<?= ($property[0]["type_heating_id"] == 1 || $property[0]["type_heating_id"] == 2) ? "" : "display:none" ?>">
                    <span class="title-label">Combustible</span>
                    <?php foreach($heatingFuel as $hf){ ?>
                        <label class="radio label-radio-checkbox-col-100">
                            <label class="container-input-radio-ui"><input type="radio" <?= $hf["id"] == $property[0]["heating_fuel_id"] ? "checked" : "" ?> name="heating_fuel" value="<?= $hf["id"] ?>" /><div class="checkmark"></div></label>
                            <?= $hf["name"] ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
            <div class="div-col-1">
                <span class="title-label">Equipamiento *</span>
                <?php $col_equipment_id = array_column($equipments, "equipment_id"); foreach($equipment as $equi){ ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" <?= in_array($equi["id"], $col_equipment_id) ? "checked" : "" ?> class="checkbox-input-ui" hidden="" name="equipment[]" value="<?= $equi["id"] ?>" />
                    <span class="checkmark-checkbox-input-ui"></span>
                    <?= $equi["name"] ?>
                </label>
                <?php } ?>
            </div>
        </div>
        <!-- <h2 class="title-main-row-section">Ascensor</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <span class="title-label">&iquest;Tiene ascensor?</span>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="elevator" value="1" /> SI
                </label>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="elevator" value="0" /> NO
                </label>
                <div class="box wheelchair_accessible_elevator_label div-col-1" style="display: none;">
                    <label class="radio label-radio-checkbox-col-100">
                        <input type="radio" name="wheelchair_accessible_elevator" value="1" />Apto para silla de ruedas
                    </label> 
                    <label class="checkbox label-radio-checkbox-col-100">
                        <input type="radio" name="wheelchair_accessible_elevator" value="0" />No apto para silla de ruedas
                    </label> 
                </div>
            </div>
        </div> -->
        <h2 class="title-main-row-section">Energ&iacute;a y emisiones</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <label for="" class="label-col-100">
                    <span class="title-label">Calificaci&oacute;n de consumo de energ&iacute;a *</span>
                    <div class="select">
                        <select name="power_consumption_rating" required>
                            <option>Seleccione</option>
                            <?php foreach($powerConsumptionRating as $pcr){ ?>
                            <option value="<?= $pcr["id"] ?>" <?= $pcr["id"] == $property[0]["power_consumption_rating_id"] ? "selected" : "" ?> ><?= $pcr["name"] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </label>
                <label for="">
                    <span class="title-label">Consumo de energ&iacute;a</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">kwh/m2 a&ntilde;o</span>
                        <input type="text" class="input-ui" value="<?= $property[0]["energy_consumption"] ?>" name="energy_consumption" id="energy_consumption">
                    </div>
                </label>
            </div>
            <div  class="div-col-1">
                <label for="" class="label-col-100">
                    <span class="title-label">Calificaci&oacute;n de emisiones</span>
                    <div class="select">
                        <select name="emissions_rating">
                            <option>Seleccione</option>
                            <?php foreach($emissionsRating as $er){ ?>
                            <option value="<?= $er["id"] ?>" <?= $er["id"] == $property[0]["emissions_rating_id"] ? "selected" : "" ?> ><?= $er["name"] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </label>
                <label for="">
                    <span class="title-label">Consumo de emisiones</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">kg CO/m2 a&ntilde;o</span>
                        <input type="text" class="input-ui" value="<?= $property[0]["emissions_consumption"] ?>" name="emissions_consumption" id="emissions_consumption">
                    </div>
                </label>
            </div>
        </div>
        <!-- <h2 class="title-main-row-section container-for-rent-only">&iquest;Qu&eacute; inquilinos buscas?</h2>
        <div class="container-row-form box container-for-rent-only">
            <label for="">
                <span class="title-label">N&uacute;mero m&aacute;ximo de inquilinos</span>
                <input type="text" class="input" name="max_num_tenants">
            </label>
            <div class="div-col-1">
                <span class="title-label">&iquest;Apropiado para ni&ntilde;os (0-12 a&ntilde;os)?</span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="appropriate_for_children" value="1" />La vivienda es apropiada para ni&ntilde;os
                </label>
            </div>
            <div class="div-col-1">
                <span class="title-label">&iquest;Admites mascotas? </span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="pet_friendly" value="1" />S&iacute;, admito mascotas
                </label>
            </div>
        </div> -->
        <!-- <h2 class="title-main-row-section container-for-sale-only">&iquest;La vivienda se vender&aacute; en alguna de estas situaciones? *</h2>
        <div class="container-row-form box container-for-sale-only">
            <div class="div-col-1">
                <?php foreach($reasonForSale as $rfs){ ?>
                    <label class="radio label-radio-checkbox-col-100">
                        <input type="radio" name="reason_for_sale" value="<?= $rfs["id"] ?>" /> <?= $rfs["name"] ?>
                    </label>
                <?php } ?>
            </div>
        </div> -->
        <h2 class="title-main-row-section">Descripci&oacute;n de la propiedad</h2>
        <div class="container-row-form box">
            <label for="" class="container-two-grid-col">
                <span class="title-label">T&iacute;tulo *</span>
                <input type="text" class="input" value="<?= $property[0]["title"] ?>" name="title" required>
            </label>
            <label for="">
                <span class="title-label">Sitio web</span>
                <input type="text" class="input" placeholder="https://" value="<?= $property[0]["page_url"] ?>" name="page_url">
            </label>
        </div>
        <div class="container-row-form-col-1 box">
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">Descripci&oacute;n *</span>
                    <textarea class="textarea" name="description" required><?= $property[0]["description"] ?></textarea>
                </label>
            </div>
        </div>


        <h2 class="title-main-row-section">Fotos y v&iacute;deos </h2>
        <div class="container-row-form-images box">
            <div class="container-main-template-input-simple">
                <div class="container-image">
                    <?php if(!empty($coverImage)){ ?>
                        <img src="<?= base_url("img/uploads/").$coverImage[0]["url"] ?>" alt="Placeholder image" id="preview_cover_image">
                    <?php }else{ ?>
                        <img src="<?= base_url("img/image-icon-1280x960.png") ?>" alt="Placeholder image" id="preview_cover_image">
                    <?php } ?>
                </div>
                <label for="cover_image">
                    <div class="btn-upload-image">
                        Subir imagen de portada *
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
                    </div>
                    <input type="file" name="cover_image" id="cover_image" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp">
                </label>
            </div>
            
            <div class="container-main-template-input-simple">
                <div class="container-images" id="container-images">
                    <?php foreach($moreImages as $img){ ?>
                        <div class="container-main-view-block-image">
                            <div class="container-image-view-more-image">
                                <img src='<?= base_url("img/uploads/"). $img["url"] ?>' alt='Placeholder image' />
                            </div>
                            <div class="container-button-actions">
                                <button class="button btn-delete-more-image" type="button" data-id="<?= $img["id"] ?>">Eliminar</button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <label for="more_images">
                    <div class="btn-upload-image">
                        Agregar imagen    
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
                    </div>
                    <input type="file" id="more_images" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp">
                </label>    
            </div>

            <div class="container-main-template-input-simple">
                <div class="container-video" id="container-video">
                    <?php if (!empty($video)){
                        echo "<video src='". base_url("video/uploads/".$video[0]["url"]) ."' id='preview_video' width='500' controls></video>";
                    }else{
                        echo "<img src='".base_url("img/play-button-circle-icon.webp")."' alt='video' />";    
                        echo "<video src='' id='preview_video' width='500' controls style='display: none;'></video>";
                    } 
                    ?>
                </div>
                <label for="video">
                    <div class="btn-upload-image">
                        Subir video (max. 150MB) (opcional)
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
                    </div>
                    <input type="file" name="video" id="video" class="input-simple-main-template" accept=".mp4,.mov,.avi,.mpeg,video/mp4,video/quicktime,video/x-msvideo,video/mpeg">
                </label>    
            </div>
        </div>
        
        
        <div class="box">
            <button class="button container-button-save" type="submit">Guardar y publicar anuncio</button>
        </div>
    </div>
</form>
<div class="modal" id="modal-view-map-select">
    <div class="modal-background"></div>
    <div class="modal-content box">
        <div class="message-title-map">
            <h3>Seleccione ubicaci&oacute;n</h3>
            <p>Arrastre el marcador a la ubicaci&oacute;n exacta de la propiedad.</p>
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
                <span class="name-attr">Pais: </span>
                <span class="value-attr" id="country-map"></span>
            </div>
        </div>
        <div class="container-controls-map">
            <button class="button" onclick="closeModal(document.getElementById('modal-view-map-select'))">Cerrar</button>
            <button class="button" id="my-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>  
                Mi ubicaci&oacute;n
            </button>
        </div>
    </div>
    <button class="button modal-close"></button>
</div>
@endsection

@section('scripts')
<script src="<?= base_url()."js/preview_image.js" ?>"></script>
<script>
    // preview_image_auto("more_images", "container-images");
    preview_image("cover_image", "preview_cover_image");
    preview_video("video", "preview_video");
    const categories = document.getElementsByName("category");
    const sale_price_label = document.querySelector(".sale_price_label");
    const rental_price_label = document.querySelector(".rental_price_label");
    const rental_type_container = document.querySelector(".container-reantal-type-div");
    const container_for_rent_only = document.querySelectorAll(".container-for-rent-only");
    const container_for_sale_only = document.querySelectorAll(".container-for-sale-only");
    container_for_rent_only.forEach(el =>{
        el.style.display = "none";
    })
    categories.forEach((category)=>{
        if(category.checked){
            if (parseInt(category.value) === 1){
                rental_price_label.removeAttribute("style");
                sale_price_label.style.display = "none";
                // rental_type_container.removeAttribute("style");
                document.getElementById("sale_price").value = "";
                container_for_sale_only.forEach(e =>{
                    e.style.display = "none";
                    e.querySelectorAll("input, select").forEach(e=>{
                        if (e.tagName === "SELECT") {
                            e.selectedIndex = 0;
                        } else if (e.type === "checkbox" || e.type === "radio") {
                            e.checked = false;
                        } else {
                            e.value = ""; 
                        }
                        e.removeAttribute("required");
                    })
                })
                container_for_rent_only.forEach(e =>{
                    e.removeAttribute("style");
                })
            }else if (parseInt(category.value) === 2){
                sale_price_label.removeAttribute("style");
                rental_price_label.style.display = "none";
                // rental_type_container.style.display = "none";
                document.getElementById("rental_price").value = "";
                container_for_sale_only.forEach(e =>{
                    e.removeAttribute("style");
                })
                container_for_rent_only.forEach(e =>{
                    e.style.display = "none";
                    e.querySelectorAll("input, select").forEach(e=>{
                        if (e.tagName === "SELECT") {
                            e.selectedIndex = 0;
                        } else if (e.type === "checkbox" || e.type === "radio") {
                            e.checked = false;
                        } else {
                            e.value = ""; 
                        }
                        e.removeAttribute("required");
                    })
                })
            }else if (parseInt(category.value) === 3){
                sale_price_label.removeAttribute("style");
                rental_price_label.removeAttribute("style");
            }
        }
        category.addEventListener("click", ()=>{
            if (parseInt(category.value) === 1){
                rental_price_label.removeAttribute("style");
                sale_price_label.style.display = "none";
                // rental_type_container.removeAttribute("style");
                document.getElementById("sale_price").value = "";
                container_for_sale_only.forEach(e =>{
                    e.style.display = "none";
                    e.querySelectorAll("input, select").forEach(e=>{
                        if (e.tagName === "SELECT") {
                            e.selectedIndex = 0;
                        } else if (e.type === "checkbox" || e.type === "radio") {
                            e.checked = false;
                        } else {
                            e.value = ""; 
                        }
                        e.removeAttribute("required");
                    })
                })
                container_for_rent_only.forEach(e =>{
                    e.removeAttribute("style");
                })
            }else if (parseInt(category.value) === 2){
                sale_price_label.removeAttribute("style");
                rental_price_label.style.display = "none";
                // rental_type_container.style.display = "none";
                document.getElementById("rental_price").value = "";
                container_for_sale_only.forEach(e =>{
                    e.removeAttribute("style");
                })
                container_for_rent_only.forEach(e =>{
                    e.style.display = "none";
                    e.querySelectorAll("input, select").forEach(e=>{
                        if (e.tagName === "SELECT") {
                            e.selectedIndex = 0;
                        } else if (e.type === "checkbox" || e.type === "radio") {
                            e.checked = false;
                        } else {
                            e.value = ""; 
                        }
                        e.removeAttribute("required");
                    })
                })
            }else if (parseInt(category.value) === 3){
                sale_price_label.removeAttribute("style");
                rental_price_label.removeAttribute("style");
            }
        })
    })
    const mortgage_state = document.getElementsByName("mortgage_state");
    const mortgage_rate_label = document.querySelector(".mortgage_rate_label");
    mortgage_state.forEach(el =>{
        el.addEventListener("click", ()=>{
            const i = mortgage_rate_label.querySelector("input");
            if (parseInt(el.value) === 1){
                mortgage_rate_label.removeAttribute("style");
                i.setAttribute("required", true);
            }else{
                mortgage_rate_label.style.display = "none";
                i.value = "";
                i.removeAttribute("required");
            }
        })
    })
    
    const type_heating = document.getElementById("type_heating");
    const container_heating_fuel = document.getElementById("container_heating_fuel");
    type_heating.addEventListener("change", ()=>{
        if (parseInt(type_heating.value) === 1 || parseInt(type_heating.value) === 2){
            container_heating_fuel.removeAttribute("style");
        }else{
            container_heating_fuel.querySelectorAll("input[type='radio']").forEach((el)=>{
                el.checked = false;
            })
            container_heating_fuel.style.display = "none";
        }
    })

    const add_rest = (id_rest, id_input, id_sum) =>{
        const rest_ = document.getElementById(id_rest);
        const sum_ = document.getElementById(id_sum);
        const number_of_plants = document.getElementById(id_input);
        rest_.addEventListener("click", ()=>{
            if (parseInt(number_of_plants.value) > 0){number_of_plants.value = parseInt(number_of_plants.value) - 1;}
        })
        sum_.addEventListener("click", ()=>{
            if (!number_of_plants.value){number_of_plants.value = 0;}
            if (parseInt(number_of_plants.value) < 135){number_of_plants.value = parseInt(number_of_plants.value) + 1;}
        })
    }
    add_rest("number_of_plants_rest", "number_of_plants", "number_of_plants_sum");
    add_rest("number_of_shop_windows_rest", "number_of_shop_windows", "number_of_shop_windows_sum");
    add_rest("bathrooms_rest", "bathrooms", "bathrooms_sum");
    
    const open_modal_addres = document.getElementById("button-open-map-google");
    open_modal_addres.addEventListener("click", ()=>{
        openModal(document.getElementById('modal-view-map-select'))
    });
</script>

<script src="<?= base_url("js/format_input.js") ?>"></script>
<script>
    format_1("sale_price");
    format_1("rental_price");
    format_1("guarantee");
    format_1("community_expenses");
    format_1("ibi");
    format_1("mortgage_rate");
    format_1("meters_built");
    format_1("useful_meters");
    format_1("linear_meters_of_facade");
    format_1("stays");
    format_1("energy_consumption");
    format_1("emissions_consumption");
</script>
<script src="<?= base_url("js/form_update_image.js") ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places"></script>
<script src="<?= base_url("js/google_maps.js") ?>"></script>

@endsection
