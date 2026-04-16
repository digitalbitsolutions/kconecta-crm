@extends('layouts.backoffice')

@section('title', 'Kconecta - Terreno')

@section('heading')
    Agregar propiedad
@endsection

@section('subheading')
    Completa los datos para registrar Terreno
@endsection

@section('header_actions')
    <a class="secondary" href="{{ url('/post/my_posts') }}">Ver propiedades</a>
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

<form action="{{ url('/post/create') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="container-title-page">
        <h2>Complete los datos para registrar &raquo; <span>Terreno</span></h2>
    </div>
    <div class="container-main">
        
        <input type="hidden" name="type" value="9">
        <input type="hidden" name="city" id="city">
        <input type="hidden" name="postal_code" id="postal_code">
        <input type="hidden" name="province" id="province">
        <input type="hidden" name="country" id="country">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <h2 class="title-main-row-section">Localización del inmueble</h2>
        <div class="container-row-form box">

            <label for="" class="label-col-100">
                <span class="title-label">Localidad *</span>
                <input type="text" class="input" name="locality" required>
            </label>

            <label for="" class="label-col-100">
                <span class="title-label">Nombre de la vía *</span>
                <div class="container-google-maps-required-tags">
                    <input type="text" class="input" id="address" name="address" required autocomplete="off">
                    <button class="button" type="button" id="button-open-map-google"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><g fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#666666"><path d="M15.129 13.747a.906.906 0 0 1-1.258 0c-1.544-1.497-3.613-3.168-2.604-5.595A3.53 3.53 0 0 1 14.5 6c1.378 0 2.688.84 3.233 2.152c1.008 2.424-1.056 4.104-2.604 5.595M14.5 9.5h.009"/><path d="M2.5 12c0-4.478 0-6.718 1.391-8.109S7.521 2.5 12 2.5c4.478 0 6.718 0 8.109 1.391S21.5 7.521 21.5 12c0 4.478 0 6.718-1.391 8.109S16.479 21.5 12 21.5c-4.478 0-6.718 0-8.109-1.391S2.5 16.479 2.5 12M17 21L3 7m7 7l-6 6"/></g></svg></button>
                </div>
            </label>
            
            <div class="div-col-2">
                <label for="" class="label-col-100">
                    <span class="title-label">Bloque / Esc.</span>
                    <input type="text" class="input" name="esc_block">
                </label>
                <label for="" class="label-col-100">
                    <span class="title-label">Puerta</span>
                    <input type="text" class="input" name="door">
                </label>
            </div>
            <label for="" class="label-col-100">
                <span class="title-label">Nombre de la urbanización</span>
                <input type="text" class="input" name="name_urbanization">
            </label>
            <!-- <div class="div-col-1">
                <span class="title-label">¿Cómo prefieres que te contacten?</span>
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
                    <label class="container-input-radio-ui"><input type="radio" name="visibility_in_portals" value="<?= $vip["id"] ?>" /><div class="checkmark"></div></label>
                    <?= $vip["name"]?>    
                </label>
                <?php } ?>
            </div>
            
        </div>

        <h2 class="title-main-row-section">Operación y precio *</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <span class="title-label">Operación</span>
                <?php foreach($category as $cate){ 
                    if (intval($cate["id"]) === 2 || intval($cate["id"]) === 1){ 
                ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="category" checked value="<?= $cate["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $cate["name"] ?>
                </label>
                <?php }} ?>
            </div>
            <!-- <div class="div-col-1 container-reantal-type-div" style="display: none;">
                <span class="title-label">Tipo de alquiler</span>
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
                        <span class="icon-ui">€</span>
                        <input placeholder="Precio" type="text" class="input-ui" name="sale_price" id="sale_price">
                    </div>
                </label>
                <label for="" class="label-col-100 rental_price_label" style="display: none;">
                    <span class="title-label">Precio de alquiler</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">€</span>
                        <input placeholder="Precio" type="text" class="input-ui" name="rental_price" id="rental_price">
                    </div>
                </label>
                <label for="" class="label-col-100 container-for-rent-only" style="display: none;">
                    <span class="title-label">Fianza</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">Meses</span>
                        <input placeholder="" type="text" class="input-ui" name="guarantee" id="guarantee">
                    </div>
                </label>
                <!-- <label for="" class="label-col-100 container-for-sale-only">
                    <span class="title-label">Gastos de comunidad</span>
                    <div class="group-icon-input-ui-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#666666" d="M15 21q-2.95 0-5.25-1.675T6.5 15H4q-.425 0-.712-.288T3 14t.288-.712T4 13h2.05Q6 12.725 6 12.5v-1q0-.225.05-.5H4q-.425 0-.712-.288T3 10t.288-.712T4 9h2.5q.95-2.65 3.25-4.325T15 3q1.425 0 2.675.413t2.35 1.137q.425.275.438.775t-.338.85t-.875.413t-.975-.213q-.725-.425-1.562-.65T15 5.5q-1.875 0-3.413.963T9.25 9H14q.425 0 .713.288T15 10t-.288.713T14 11H8.6q-.05.275-.075.5T8.5 12t.025.5t.075.5H14q.425 0 .713.288T15 14t-.288.713T14 15H9.25q.8 1.575 2.338 2.538T15 18.5q.875 0 1.713-.213t1.562-.637q.45-.275.975-.225t.875.4t.338.85t-.438.775q-1.1.725-2.35 1.138T15 21"/></svg>
                        <input placeholder="Precio" type="text" class="input-ui" name="community_expenses">
                    </div>
                </label> -->
                <!-- <label for="" class="label-col-100 container-for-sale-only">
                    <span class="title-label">IBI</span>
                    <div class="group-icon-input-ui-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#666666" d="M15 21q-2.95 0-5.25-1.675T6.5 15H4q-.425 0-.712-.288T3 14t.288-.712T4 13h2.05Q6 12.725 6 12.5v-1q0-.225.05-.5H4q-.425 0-.712-.288T3 10t.288-.712T4 9h2.5q.95-2.65 3.25-4.325T15 3q1.425 0 2.675.413t2.35 1.137q.425.275.438.775t-.338.85t-.875.413t-.975-.213q-.725-.425-1.562-.65T15 5.5q-1.875 0-3.413.963T9.25 9H14q.425 0 .713.288T15 10t-.288.713T14 11H8.6q-.05.275-.075.5T8.5 12t.025.5t.075.5H14q.425 0 .713.288T15 14t-.288.713T14 15H9.25q.8 1.575 2.338 2.538T15 18.5q.875 0 1.713-.213t1.562-.637q.45-.275.975-.225t.875.4t.338.85t-.438.775q-1.1.725-2.35 1.138T15 21"/></svg>
                        <input placeholder="Precio" type="text" class="input-ui" name="ibi">
                    </div>
                </label> -->
            </div>
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Hipoteca pendiente</span>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="mortgage_state" value="1" /><div class="checkmark"></div></label>
                    SI
                </label>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="mortgage_state" value="0" /><div class="checkmark"></div></label>
                    NO
                </label>
                <label for="" class="label-col-100 mortgage_rate_label" style="display: none;">
                    <span class="title-label">Importe *</span>
                    <div class="group-icon-input-ui-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#666666" d="M15 21q-2.95 0-5.25-1.675T6.5 15H4q-.425 0-.712-.288T3 14t.288-.712T4 13h2.05Q6 12.725 6 12.5v-1q0-.225.05-.5H4q-.425 0-.712-.288T3 10t.288-.712T4 9h2.5q.95-2.65 3.25-4.325T15 3q1.425 0 2.675.413t2.35 1.137q.425.275.438.775t-.338.85t-.875.413t-.975-.213q-.725-.425-1.562-.65T15 5.5q-1.875 0-3.413.963T9.25 9H14q.425 0 .713.288T15 10t-.288.713T14 11H8.6q-.05.275-.075.5T8.5 12t.025.5t.075.5H14q.425 0 .713.288T15 14t-.288.713T14 15H9.25q.8 1.575 2.338 2.538T15 18.5q.875 0 1.713-.213t1.562-.637q.45-.275.975-.225t.875.4t.338.85t-.438.775q-1.1.725-2.35 1.138T15 21"/></svg>
                        <input placeholder="Precio" type="text" class="input-ui" name="mortgage_rate">
                    </div>
                </label>
            </div> -->
        </div>

        <!-- <h2 class="title-main-row-section container-for-sale-only">Estado de ocupación</h2>
        <div class="container-row-form box container-for-sale-only">
            <div class="div-col-1">
                <span class="title-label">¿Tiene inquilinos? *</span>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="has_tenants" value="1" required /><div class="checkmark"></div></label>
                    SI
                </label>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="has_tenants" value="0" required /><div class="checkmark"></div></label>
                    NO
                </label>
            </div>
        </div> -->
        <h2 class="title-main-row-section">Características del terreno</h2>
        <div class="container-row-form box">
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Característica adicional *</span>
                <?php foreach($typeFloor as $tf){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="checkbox" name="type_floor[]" value="<?= $tf["id"] ?>" /><?= $tf["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Tipología *</span>
                <?php foreach($typology as $typ){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="radio" name="typology" value="<?= $typ["id"] ?>" required /><?= $typ["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            <!-- <div class="div-col-1 container-for-sale-only">
                <span class="title-label">Capacidad de la plaza *</span>
                <div class="select">
                    <select name="plaza_capacity" required>
                        <option value="" selected disabled>Seleccione</option>
                        <?php foreach($plazaCapacity as $plc){ ?>
                            <option value="<?= $plc["id"] ?>"><?= $plc["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div> -->
            <div class="div-col-1">
                <span class="title-label">Tipo de terreno *</span>
                <?php foreach($typeOfTerrain as $tot){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="type_of_terrain" value="<?= $tot["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $tot["name"] ?>
                </label>
                <?php } ?>
            </div>
            <div class="div-col-1">
                <span class="title-label">Acceso rodado *</span>
                <?php foreach($wheeledAccess as $wa){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="wheeled_access" value="<?= $wa["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $wa["name"] ?>
                </label>
                <?php } ?>
            </div>
            <div class="div-col-1">
                <span class="title-label">Distancia municipio más cercano *</span>
                <?php foreach($nearestMunicipalityDistance as $nmd){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <label class="container-input-radio-ui"><input type="radio" name="nearest_municipality_distance" value="<?= $nmd["id"] ?>" required /><div class="checkmark"></div></label>
                    <?= $nmd["name"] ?>
                </label>
                <?php } ?>
            </div>
            <div class="div-col-1">
                <label for="" class="label-col-100">
                    <span class="title-label">Superficie total *</span>
                    <div class="group-icon-input-ui-2">
                        <span class="icon-ui">m<sup>2</sup></span>
                        <input placeholder="" type="text" class="input-ui" name="land_size" id="land_size">
                    </div>
                </label>
                <!-- <label for="">
                    <span class="title-label">M<sup>2</sup> Superficie total *</span>
                    <input type="text" class="input" name="meters_built">
                </label> -->
                <!-- <label for="">
                    <span class="title-label">M<sup>2</sup> útiles</span>
                    <input type="text" class="input" name="useful_meters">
                </label> -->
            </div>
            <!-- <label for="">
                <span class="title-label">M<sup>2</sup> parcela</span>
                <input type="text" class="input" name="plot_meters">
            </label> -->
            <!-- <div class="div-col-1">
                <label for="">
                    <span class="title-label">Número de dormitorios *</span>
                    <input type="text" class="input" name="bedrooms" required>
                </label>
                <label for="">
                    <span class="title-label">Número de baños*</span>
                    <input type="text" class="input" name="bathrooms" required>
                </label>
            </div> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Metros lineales de fachada *</span>
                <label for="">
                    <input type="text" class="input" name="linear_meters_of_facade" required>
                </label>
            </div> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Estancias *</span>
                <label for="">
                    <input type="text" class="input" name="stays" required>
                </label>
            </div> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Número de escaparates *</span>
                <label for="">
                    <input type="text" class="input" name="number_of_shop_windows" required>
                </label>
            </div> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Plantas del garaje *</span>
                <label for="">
                    <input type="number" class="input" name="number_of_plants" required>
                </label>
            </div> -->
            <!-- <label for="">
                <span class="title-label">Plantas del chalet</span>
                <input type="text" class="input" name="number_of_plants">
            </label> -->
            <!-- <label for="" class="container-for-rent-only">
                <span class="title-label">Número de habitaciones *</span>
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
                <span class="title-label">Clase energética *</span>
                <div class="select">
                    <select name="energy_class" required>
                        <option value="" selected disabled>Seleccione</option>
                        <?php foreach($energyClass as $enclass){ ?>
                        <option value="<?= $enclass["id"] ?>"><?= $enclass["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </label> -->
            
            <!-- <div class="div-col-1">
                <span class="title-label">Orientación</span>
                <?php foreach($orientation as $orient){ ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="orientation[]" value="<?= $orient["id"] ?>" /><?= $orient["name"] ?>
                </label>
                <?php } ?>
            </div> -->
            
            <!-- <div class="div-col-1">
                <span class="title-label">Otras características del piso</span>
                <?php foreach($feature as $featur){ 
                    if ($featur["id_type"] == 1){
                ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="feature[]" value="<?= $featur["id"] ?>" /><?= $featur["name"] ?>
                </label>
                <?php }} ?>
            </div> -->
            <!-- <label for="">
                <span class="title-label">Año de construcción del edificio</span>
                <input type="text" class="input" name="year_of_construction">
            </label> -->
            <div class="div-col-1">
                <span class="title-label">Categoría</span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" class="checkbox-input-ui" hidden="" name="bank_owned_property" value="1" />
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
            <!-- <div class="div-col-1">
                <span class="title-label">Tipo de calefacción</span>
                <div class="select">
                    <select name="type_heating">
                        <option value="" selected disabled>Seleccione</option>
                        <?php foreach($typeHeating as $th){ ?>
                        <option value="<?= $th["id"] ?>"><?= $th["name"] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div> -->
            <!-- <div class="div-col-1">
                <span class="title-label">Equipamiento *</span>
                <?php foreach($equipment as $equi){ ?>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" class="checkbox-input-ui" hidden="" name="equipment[]" value="<?= $equi["id"] ?>" />
                    <span class="checkmark-checkbox-input-ui"></span>
                    <?= $equi["name"] ?>
                </label>
                <?php } ?>
            </div> -->
        </div>
        <!-- <h2 class="title-main-row-section">Ascensor</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <span class="title-label">¿Tiene ascensor?</span>
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
        <!-- <h2 class="title-main-row-section">Energía y emisiones</h2>
        <div class="container-row-form box">
            <div class="div-col-1">
                <label for="" class="label-col-100">
                    <span class="title-label">Calificación de consumo de energía *</span>
                    <div class="select">
                        <select name="power_consumption_rating" required>
                            <option value="" selected disabled>Seleccione</option>
                            <?php foreach($powerConsumptionRating as $pcr){ ?>
                            <option value="<?= $pcr["id"] ?>"><?= $pcr["name"] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </label>
                <label for="">
                    <span class="title-label">Consumo de energía</span>
                    <input type="text" class="input" name="energy_consumption">
                </label>
            </div>
            <div  class="div-col-1">
                <label for="" class="label-col-100">
                    <span class="title-label">Calificación de emisiones</span>
                    <div class="select">
                        <select name="emissions_rating">
                            <option value="" selected disabled>Seleccione</option>
                            <?php foreach($emissionsRating as $er){ ?>
                            <option value="<?= $er["id"] ?>"><?= $er["name"] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </label>
                <label for="">
                    <span class="title-label">Consumo de emisiones</span>
                    <input type="text" class="input" name="emissions_consumption">
                </label>
            </div>
        </div> -->
        <!-- <h2 class="title-main-row-section container-for-rent-only">¿Qué inquilinos buscas?</h2>
        <div class="container-row-form box container-for-rent-only">
            <label for="">
                <span class="title-label">Número máximo de inquilinos</span>
                <input type="text" class="input" name="max_num_tenants">
            </label>
            <div class="div-col-1">
                <span class="title-label">¿Apropiado para niños (0-12 años)?</span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="appropriate_for_children" value="1" />La vivienda es apropiada para niños
                </label>
            </div>
            <div class="div-col-1">
                <span class="title-label">¿Admites mascotas? </span>
                <label class="checkbox label-radio-checkbox-col-100">
                    <input type="checkbox" name="pet_friendly" value="1" />Sí, admito mascotas
                </label>
            </div>
        </div> -->
        <!-- <h2 class="title-main-row-section container-for-sale-only">¿La vivienda se venderá en alguna de estas situaciones? *</h2>
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
        <div class="container-row-form box property-description-row">
            <label for="" class="property-description-title">
                <span class="title-label">T&iacute;tulo *</span>
                <input type="text" class="input" name="title" required>
            </label>
            <label for="" class="property-description-website">
                <span class="title-label">Sitio web</span>
                <input type="text" class="input" placeholder="https://" name="page_url">
            </label>
        </div>
        <div class="container-row-form-col-1 box property-description-box">
            <div class="div-col-1">
                <label for="" class="property-description-textarea">
                    <span class="title-label">Descripci&oacute;n *</span>
                    <textarea class="textarea" name="description" required></textarea>
                </label>
            </div>
        </div>


        <h2 class="title-main-row-section">Fotos y vídeos </h2>
        <div class="container-row-form-images box">
            <div class="container-main-template-input-simple">
                <div class="container-image">
                    <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" id="preview_cover_image">
                </div>
                <label for="cover_image">
                    <div class="btn-upload-image">
                        Subir imagen de portada *
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
                    </div>
                    <input type="file" name="cover_image" id="cover_image" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                </label>
            </div>
            
            <div class="container-main-template-input-simple">
                <div class="container-images" id="container-images">
                    <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" />
                    <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" />
                    <img src="{{ asset('img/image-icon-1280x960.png') }}" alt="Placeholder image" />
                </div>
                <label for="more_images">
                    <div class="btn-upload-image">
                        Subir imágenes *
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
                    </div>
                    <input type="file" name="more_images[]" id="more_images" class="input-simple-main-template" accept="image/png, image/jpeg, image/jpg, image/webp" multiple required>
                </label>    
            </div>

            <div class="container-main-template-input-simple">
                <div class="container-video" id="container-video">
                    <img src="{{ asset('img/play-button-circle-icon.webp') }}" alt="video" />
                    <video id="preview_video" width="500" controls style="display: none;"></video>
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
            <h3>Seleccione ubicación</h3>
            <p>Arrastre el marcador a la ubicación exacta de la propiedad.</p>
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
                <span class="name-attr">País: </span>
                <span class="value-attr" id="country-map"></span>
            </div>
        </div>
        <div class="container-controls-map">
            <button class="button" onclick="closeModal(document.getElementById('modal-view-map-select'))">Cerrar</button>
            <button class="button" id="my-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="#666666" d="M12 2c-4.4 0-8 3.6-8 8c0 5.4 7 11.5 7.3 11.8c.2.1.5.2.7.2s.5-.1.7-.2C13 21.5 20 15.4 20 10c0-4.4-3.6-8-8-8m0 17.7c-2.1-2-6-6.3-6-9.7c0-3.3 2.7-6 6-6s6 2.7 6 6s-3.9 7.7-6 9.7M12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>  
                Mi ubicación
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
    
    const open_modal_addres = document.getElementById("button-open-map-google");
    open_modal_addres.addEventListener("click", ()=>{
        openModal(document.getElementById('modal-view-map-select'))
    });    
</script>

<script src="{{ asset('js/format_input.js') }}"></script>
<script>
    format_1("sale_price");
    format_1("rental_price");
    format_1("guarantee");
    format_1("land_size");

</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places"></script>
<script src="{{ asset('js/google_maps.js') }}"></script>

@endsection
