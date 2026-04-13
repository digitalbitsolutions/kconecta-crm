@extends('layouts.backoffice')

@section('title', 'Kconecta - Actualizar proveedor de servicio')

@section('heading')
    Editar proveedor de servicio
@endsection

@section('subheading')
    Actualiza los datos del proveedor de servicio
@endsection

@section('header_actions')
    <a class="secondary" href="{{ url('/post/services') }}">Ver proveedores</a>
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

<form action="{{ url('/post/services/update/save') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="container-title-page">
        <h2>Actualizar &raquo; <span>Servicio</span></h2>
    </div>
    <div class="container-main">

        <input type="hidden" name="service_id" value="<?= $service[0]["id"] ?>">
        <h2 class="title-main-row-section">Datos del usuario</h2>
        <div class="container-row-form box">
            <label for="" class="label-col-100">
                <span class="title-label">Disponibilidad *</span>
                <input type="text" class="input" name="availability" value="<?= $service[0]["availability"] ?>" required>
            </label>
            <label for="">
                <span class="title-label">Sitio web</span>
                <input type="text" class="input" placeholder="" value="<?= $service[0]["page_url"] ?>" name="page_url">
            </label>
        </div>
        <div class="container-row-form-col-1 box">
            <div class="div-col-1">
                <label for="">
                    <span class="title-label">Descripci&oacute;n *</span>
                    <textarea class="textarea" name="description" required><?= $service[0]["description"] ?></textarea>
                </label>
            </div>
        </div>
        <h2 class="title-main-row-section">Tipo de servicio</h2>
        <div class="container-row-form-col-1 box">
            <div class="div-col-3">
                <!-- <span class="title-label">Tipo de servicio *</span> -->
                <?php $col_type_service_id = array_column($serviceTypes, "service_type_id"); foreach($serviceType as $st){ ?>
                <label class="radio label-radio-checkbox-col-100">
                    <input type="checkbox" <?= in_array($st["id"], $col_type_service_id) ? "checked" : "" ?> class="checkbox-input-ui" hidden="" name="service_type[]" value="<?= $st["id"] ?>" />
                    <span class="checkmark-checkbox-input-ui"></span>
                    <?= $st["name"] ?>
                </label>
                <?php } ?>
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
                        Subir video (max. 200MB) (opcional)
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><g fill="none"><path fill="#ffffff" d="M44 24a2 2 0 1 0-4 0zM24 8a2 2 0 1 0 0-4zm15 32H9v4h30zM8 39V9H4v30zm32-15v15h4V24zM9 8h15V4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zM8 9a1 1 0 0 1 1-1V4a5 5 0 0 0-5 5z"/><path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31m-5-13V6m-5 5l5-5l5 5"/></g></svg>
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
</script>
<script src="<?= base_url("js/form_update_image.js") ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places"></script>
<script src="<?= base_url("js/google_maps.js") ?>"></script>

@endsection
