@extends('layouts.backoffice')

@section('title', ($isProviderView ?? false) ? 'Kconecta - Servicios' : 'Kconecta - Proveedores de servicios')

@section('heading')
    {{ ($isProviderView ?? false) ? 'Servicios' : 'Proveedores de servicios' }}
@endsection

@section('subheading')
    {{ ($isProviderView ?? false) ? 'Gestiona tu perfil y los servicios que ofreces' : 'Gestiona los proveedores de servicios publicados y sus detalles' }}
@endsection

@section('header_actions')
    @if (! ($isProviderView ?? false))
        <a class="action-primary" href="{{ url('/post/create_form/service') }}" aria-label="Agregar servicio">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </svg>
            <span>Agregar proveedor de servicio</span>
        </a>
        <a class="secondary" href="{{ url('/post/my_posts') }}">Ver propiedades</a>
    @endif
@endsection

@section('content')
    @if ($isProviderView ?? false)
        @php
            $landing = $providerLanding ?? [];
            $providerProfile = $providerProfile ?? [];
            $heroImage = $landing['hero_image'] ?? asset('img/image-icon-1280x960.png');
            $landingAddress = $landing['address'] ?? '';
            $landingDescription = $landing['description'] ?? '';
            $landingAvailability = $landing['availability'] ?? '';
            $landingUpdated = $landing['updated_at'] ?? '';
            $landingPageUrl = $landing['page_url'] ?? '';
            $mapLink = $landing['map_link'] ?? '';
            $mapEmbed = $landing['map_embed'] ?? '';
            $whatsappLink = $landing['whatsapp_link'] ?? '';
            $videoUrl = $landing['video_url'] ?? '';
            $landingImages = $landing['images'] ?? [$heroImage];
        @endphp
        <section class="service-landing">
            <div class="service-landing-grid">
                <div class="service-main">
                    <div class="card service-media">
                        <div class="service-media-frame" data-service-slider>
                            @foreach ($landingImages as $index => $imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="Servicio destacado {{ $index + 1 }}"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                    class="service-slide {{ $index === 0 ? 'is-active' : '' }}"
                                    style="{{ $index === 0 ? 'display:block;' : 'display:none;' }}"
                                    data-service-slide
                                >
                            @endforeach
                            @if (count($landingImages) > 1)
                                <button type="button" class="service-media-control is-prev" data-service-prev aria-label="Imagen anterior">&#10094;</button>
                                <button type="button" class="service-media-control is-next" data-service-next aria-label="Imagen siguiente">&#10095;</button>
                            @endif
                        </div>
                        @if (count($landingImages) > 1)
                            <div class="service-media-dots" data-service-dots>
                                @foreach ($landingImages as $index => $imageUrl)
                                    <button
                                        type="button"
                                        class="service-media-dot {{ $index === 0 ? 'is-active' : '' }}"
                                        data-service-dot="{{ $index }}"
                                        aria-label="Ir a imagen {{ $index + 1 }}"
                                    ></button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="service-location">
                        <div class="service-location-text">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/>
                                <circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                            </svg>
                            <span>{{ $landingAddress !== '' ? $landingAddress : 'Dirección por confirmar' }}</span>
                        </div>
                        <div class="service-location-actions">
                            @if ($mapLink !== '')
                                <a class="service-map-link" href="{{ $mapLink }}" target="_blank" rel="noopener">Ver en el mapa</a>
                            @endif
                            @if ($videoUrl !== '')
                                <a class="service-video-link" href="{{ $videoUrl }}" target="_blank" rel="noopener">Ver vídeo</a>
                            @endif
                        </div>
                    </div>

                    <div class="card service-description">
                        <h3>Descripción</h3>
                        <p>{{ $landingDescription !== '' ? $landingDescription : 'Sin descripción disponible.' }}</p>
                        @if ($landingPageUrl !== '')
                            <a class="service-website" href="{{ $landingPageUrl }}" target="_blank" rel="noopener">Visita nuestra pagina web</a>
                        @endif
                    </div>

                    <div class="card service-list">
                        <div class="service-list-header">
                            <div>
                                <h3>Servicios publicados</h3>
                                <p>{{ $services->total() }} servicios en total</p>
                            </div>
                        </div>
                        <div class="service-items">
                            @forelse ($services as $service)
                                <article class="service-item" data-card-id="service-{{ $service['id'] }}">
                                    <div class="service-item-header">
                                        <div>
                                            <h4>{{ $service['title'] }}</h4>
                                            <p>{{ $service['description'] ?: 'Sin descripción.' }}</p>
                                        </div>
                                        <div class="entity-actions icon-actions">
                                            <a class="icon-action accent" href="{{ url('/post/services/update_form/' . $service['id']) }}" title="Editar" aria-label="Editar">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20h9"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                </svg>
                                            </a>
                                            <button type="button" class="icon-action danger" data-delete-service="{{ $service['id'] }}" title="Eliminar" aria-label="Eliminar">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6V4h8v2"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 6l-1 14H6L5 6"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 11v6M14 11v6"/>
                                                </svg>
                                            </button>
                                            <a class="icon-action neutral" href="{{ url('/result_service/' . $service['id']) }}" title="Ver servicio" aria-label="Ver servicio" target="_blank" rel="noopener">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3h7v7"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14L21 3"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14v7a2 2 0 0 1-2 2h-7"/>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10v11a2 2 0 0 0 2 2h11"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="service-item-meta">
                                        <div class="service-item-tags">
                                            @if (! empty($service['types']))
                                                @foreach ($service['types'] as $type)
                                                    <span>{{ $type }}</span>
                                                @endforeach
                                            @else
                                                <span>Sin categoría</span>
                                            @endif
                                        </div>
                                        <div class="service-item-details">
                                            <span>{{ $service['availability'] ? 'Disponibilidad: ' . $service['availability'] : 'Disponibilidad por confirmar' }}</span>
                                            @if ($service['updated_at'])
                                                <span>Actualizado {{ $service['updated_at'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="empty-state">
                                <h3>Sin servicios registrados</h3>
                                    <p>Agrega tu primer servicio para mostrarlo en tu landing.</p>
                                    <a class="primary" href="{{ url('/post/create_form/service') }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                                        </svg>
                                        <span>Agregar servicio</span>
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <aside class="service-aside">
                    <div class="card service-contact-card">
                        <h3>Pregunta al anunciante</h3>
                        <div class="service-contact-list">
                            <div class="service-contact-item">
                                <span class="service-contact-label">Publicado por</span>
                                <strong class="service-contact-value">{{ $providerProfile['name'] ?? 'Proveedor' }}</strong>
                            </div>
                            <div class="service-contact-item">
                            <span class="service-contact-label">Última actualización</span>
                                <strong class="service-contact-value">{{ $landingUpdated !== '' ? $landingUpdated : 'Sin datos' }}</strong>
                            </div>
                            <div class="service-contact-item">
                                <span class="service-contact-label">Disponibilidad</span>
                                <strong class="service-contact-value">{{ $landingAvailability !== '' ? $landingAvailability : 'Por confirmar' }}</strong>
                            </div>
                            <div class="service-contact-item">
                                <span class="service-contact-label">Contacto</span>
                                <strong class="service-contact-value">{{ $providerProfile['phone'] ?? 'Sin teléfono' }}</strong>
                            </div>
                        </div>
                        @if ($whatsappLink !== '')
                            <a class="service-whatsapp" href="{{ $whatsappLink }}" target="_blank" rel="noopener">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M20.5 3.5A10 10 0 0 0 4.1 17.2L3 21l3.9-1A10 10 0 1 0 20.5 3.5Zm-8.5 17a8.4 8.4 0 0 1-4.3-1.2l-.3-.2-2.3.6.6-2.2-.2-.3a8.4 8.4 0 1 1 6.5 3.3Zm4.6-6.3c-.3-.2-1.7-.9-2-.9s-.4-.2-.6.2-.7.9-.8 1-.3.2-.6.1a6.9 6.9 0 0 1-2-1.2 7.5 7.5 0 0 1-1.4-1.8c-.1-.2 0-.4.1-.5l.4-.5c.1-.1.2-.3.3-.5a.5.5 0 0 0 0-.5c0-.2-.6-1.5-.8-2s-.4-.4-.6-.4h-.5c-.1 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.4a8.7 8.7 0 0 0 3.3 3.2c1.2.5 1.7.5 2.3.4.4-.1 1.3-.5 1.5-1s.2-1 .1-1.1-.2-.2-.5-.3Z"/>
                                </svg>
                                <span>Contactar por WhatsApp</span>
                            </a>
                        @else
                            <span class="service-whatsapp is-disabled">WhatsApp no disponible</span>
                        @endif
                    </div>

                    <div class="card service-offered">
                        <h3>Servicios ofrecidos</h3>
                        @if (! empty($providerServiceTypes))
                            <ul class="service-offered-list">
                                @foreach ($providerServiceTypes as $type)
                                    <li>{{ $type }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="service-offered-empty">Sin categorías registradas.</p>
                        @endif
                    </div>

                    <div class="card service-map-card">
                        <h3>Ubicación</h3>
                        <div class="service-map-frame">
                            @if ($mapEmbed !== '')
                                <iframe src="{{ $mapEmbed }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            @else
                                <div class="service-map-placeholder">Mapa no disponible</div>
                            @endif
                        </div>
                    </div>
                </aside>

                <div class="card service-video-wide" id="service-video-section">
                    <div class="service-video-header">
                        <h3>Vídeo de presentación</h3>
                    </div>
                    <div class="service-video-frame">
                        @if ($videoUrl !== '')
                            <video src="{{ $videoUrl }}" controls></video>
                        @else
                            <div class="service-video-placeholder">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h16v16H4z"/>
                                    <path fill="currentColor" d="M10 8.5l6 3.5-6 3.5V8.5z"/>
                                </svg>
                                <p>Aún no hay vídeo disponible.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @else
        <div class="page-card">
        <div class="section-header">
            <div>
                <h2>Listado</h2>
                <p>{{ $services->total() }} servicios en total</p>
            </div>
        </div>

        <form class="filter-bar" method="GET" action="{{ url('/post/services') }}">
            <label class="filter-group">
                <span>Buscar</span>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Título o descripción">
            </label>
            <label class="filter-group">
                <span>Tipo de servicio</span>
                <select name="type">
                    <option value="all">Todos</option>
                    @foreach ($serviceTypeOptions as $serviceType)
                        <option value="{{ $serviceType->id }}" {{ ($filters['type'] ?? '') === (string) $serviceType->id ? 'selected' : '' }}>
                            {{ $serviceType->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="filter-group">
                <span>Desde</span>
                <input type="date" name="ds" value="{{ $filters['ds'] ?? '' }}">
            </label>
            <label class="filter-group">
                <span>Hasta</span>
                <input type="date" name="de" value="{{ $filters['de'] ?? '' }}">
            </label>
            <div class="filter-actions">
                <button type="submit">Filtrar</button>
                <a class="secondary" href="{{ url('/post/services') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="catalog-grid services-grid">
        @forelse ($services as $service)
            @php
                $imageUrl = $service['image'] ? asset('img/uploads/' . $service['image']) : asset('img/image-icon-1280x960.png');
            @endphp
            <article class="entity-card" data-card-id="service-{{ $service['id'] }}">
                <div class="entity-media">
                    <img src="{{ $imageUrl }}" alt="Servicio" loading="lazy">
                </div>
                <div class="entity-body">
                    <div class="entity-tags">
                        @if (! empty($service['types']))
                            @foreach ($service['types'] as $type)
                                <span>{{ $type }}</span>
                            @endforeach
                        @else
                            <span>Sin categoría</span>
                        @endif
                    </div>
                    <h3>{{ $service['title'] }}</h3>
                    <p class="entity-description">{{ $service['description'] ?: 'Sin descripción.' }}</p>
                    <div class="entity-stats">
                        <span>{{ $service['availability'] ? 'Disponibilidad: ' . $service['availability'] : 'Disponibilidad por confirmar' }}</span>
                        @if ($service['phone'])
                            <span>{{ $service['phone'] }}</span>
                        @endif
                    </div>
                    <p class="entity-location">
                        {{ $service['address'] }}{{ $service['city'] ? ', ' . $service['city'] : '' }}
                    </p>
                    @if ($isAdmin && $service['owner'])
                        <div class="entity-owner">Proveedor: {{ $service['owner'] }}</div>
                    @endif
                    <div class="entity-meta">
                        @if ($service['updated_at'])
                            <span>Actualizado {{ $service['updated_at'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="entity-actions icon-actions">
                    <a class="icon-action accent" href="{{ url('/post/services/update_form/' . $service['id']) }}" title="Editar" aria-label="Editar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20h9"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                        </svg>
                    </a>
                    <button type="button" class="icon-action danger" data-delete-service="{{ $service['id'] }}" title="Eliminar" aria-label="Eliminar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6V4h8v2"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 6l-1 14H6L5 6"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 11v6M14 11v6"/>
                        </svg>
                    </button>
                    <a class="icon-action neutral" href="{{ url('/result_service/' . $service['id']) }}" title="Ver servicio" aria-label="Ver servicio" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3h7v7"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14L21 3"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14v7a2 2 0 0 1-2 2h-7"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10v11a2 2 0 0 0 2 2h11"/>
                        </svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <h3>Sin servicios aún</h3>
                <p>Agrega tu primer servicio desde el panel.</p>
                <a class="primary" href="{{ url('/post/create_form/service') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                    </svg>
                    <span>Agregar servicio</span>
                </a>
            </div>
        @endforelse
    </div>
    @endif

    @if ($services->lastPage() > 1)
        <div class="pager">
            <a class="pager-link {{ $services->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $services->previousPageUrl() ?? '#' }}" aria-disabled="{{ $services->onFirstPage() ? 'true' : 'false' }}">
                Anterior
            </a>
            <span class="pager-meta">Página {{ $services->currentPage() }} de {{ $services->lastPage() }}</span>
            <a class="pager-link {{ $services->currentPage() === $services->lastPage() ? 'is-disabled' : '' }}" href="{{ $services->nextPageUrl() ?? '#' }}" aria-disabled="{{ $services->currentPage() === $services->lastPage() ? 'true' : 'false' }}">
                Siguiente
            </a>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (() => {
            const sliderRoot = document.querySelector('[data-service-slider]');
            if (sliderRoot) {
                const slides = Array.from(sliderRoot.querySelectorAll('[data-service-slide]'));
                const dotsContainer = document.querySelector('[data-service-dots]');
                const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('[data-service-dot]')) : [];
                const prevBtn = sliderRoot.querySelector('[data-service-prev]');
                const nextBtn = sliderRoot.querySelector('[data-service-next]');
                let current = 0;

                const renderSlide = (index) => {
                    current = index;
                    slides.forEach((slide, idx) => {
                        slide.classList.toggle('is-active', idx === current);
                        slide.style.display = idx === current ? 'block' : 'none';
                    });
                    dots.forEach((dot, idx) => {
                        dot.classList.toggle('is-active', idx === current);
                    });
                };

                if (slides.length > 1) {
                    prevBtn?.addEventListener('click', () => {
                        const nextIndex = current === 0 ? slides.length - 1 : current - 1;
                        renderSlide(nextIndex);
                    });

                    nextBtn?.addEventListener('click', () => {
                        const nextIndex = current === slides.length - 1 ? 0 : current + 1;
                        renderSlide(nextIndex);
                    });

                    dots.forEach((dot) => {
                        dot.addEventListener('click', () => {
                            const index = Number(dot.dataset.serviceDot);
                            if (!Number.isNaN(index)) {
                                renderSlide(index);
                            }
                        });
                    });
                }
            }

            const deleteButtons = document.querySelectorAll('[data-delete-service]');

            deleteButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!confirm('Eliminar servicio?')) {
                        return;
                    }

                    button.disabled = true;
                    const id = button.dataset.deleteService;

                    try {
                        const response = await fetch(`/post/services/delete?id=${id}`);
                        const data = await response.json();
                        if (data.status === 200) {
                            const card = document.querySelector(`[data-card-id="service-${id}"]`);
                            if (card) {
                                card.remove();
                            }
                        } else {
                            alert('No se pudo eliminar el servicio.');
                        }
                    } catch (error) {
                        alert('Error al eliminar el servicio.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        })();
    </script>
@endsection
