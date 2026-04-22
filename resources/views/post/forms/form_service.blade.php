@extends('layouts.backoffice')

@section('title', 'Kconecta - Servicio')

@section('heading')
    Agregar servicio
@endsection

@section('subheading')
    Completa los datos para registrar un servicio
@endsection

@section('header_actions')
    <a class="secondary" href="{{ url('/post/services') }}">Ver proveedores</a>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/libraries/bulma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_text.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_radio.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui/input_checkbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page/property-form.css') }}">
@endsection

@section('content')
    <form action="{{ url('/post/create_service') }}" method="post" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="container-title-page">
            <h2>Complete los datos para registrar &raquo; <span>Servicio</span></h2>
        </div>
        <div class="container-main">
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
                            Subir video (max. 150MB) (opcional)
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
@endsection

@section('scripts')
    <script src="{{ asset('js/preview_image.js') }}"></script>
    <script>
        preview_image_auto("more_images", "container-images");
        preview_image("cover_image", "preview_cover_image");
        preview_video("video", "preview_video");
    </script>
@endsection
