@extends('layouts.backoffice')

@section('title', 'Kconecta - Mis propiedades')

@section('heading')
    Mis propiedades
@endsection

@section('subheading')
    Gestiona tus anuncios y su estado de publicaci&oacute;n
@endsection

@section('header_actions')
    <a class="action-primary" href="{{ url('/post/index') }}" aria-label="Agregar propiedad">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Agregar propiedad</span>
    </a>
@endsection

@section('content')
    <div class="page-card">
        <div class="section-header">
            <div>
                <h2>Listado</h2>
                <p>{{ $properties->total() }} anuncios en total</p>
            </div>
        </div>

        <form class="filter-bar" method="GET" action="{{ url('/post/my_posts') }}">
            <label class="filter-group">
                <span>Buscar</span>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Titulo o referencia">
            </label>
            <label class="filter-group">
                <span>Estado</span>
                <select name="status">
                    <option value="all">Todos</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['status'] ?? '') === (string) $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="filter-group">
                <span>Categoria</span>
                <select name="category">
                    <option value="all">Todas</option>
                    @foreach ($categoryOptions as $category)
                        <option value="{{ $category->id }}" {{ ($filters['category'] ?? '') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="filter-group">
                <span>Tipo</span>
                <select name="type">
                    <option value="all">Todos</option>
                    @foreach ($typeOptions as $type)
                        <option value="{{ $type->id }}" {{ ($filters['type'] ?? '') === (string) $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
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
                <a class="secondary" href="{{ url('/post/my_posts') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="catalog-grid{{ !empty($isCompanyUser) ? ' properties-grid' : '' }}">
        @forelse ($properties as $property)
            @php
                $imageUrl = $property['image'] ? asset('img/uploads/' . $property['image']) : asset('img/image-icon-1280x960.png');
                $statusClass = 'neutral';
                if ($property['state_id'] === 4) {
                    $statusClass = 'success';
                } elseif ($property['state_id'] === 5) {
                    $statusClass = 'warning';
                } elseif ($property['state_id'] === 3) {
                    $statusClass = 'danger';
                }
            @endphp
            <article class="entity-card" data-card-id="{{ $property['id'] }}">
                <div class="entity-media">
                    <img src="{{ $imageUrl }}" alt="Propiedad" loading="lazy">
                    <span class="status-pill {{ $statusClass }}">{{ $property['state_label'] }}</span>
                </div>
                <div class="entity-body">
                    <div class="entity-tags">
                        <span>{{ $property['type'] }}</span>
                        <span>{{ $property['category'] }}</span>
                    </div>
                    <h3>{{ $property['title'] }}</h3>
                    <p class="entity-location">
                        {{ $property['address'] }}{{ $property['city'] ? ', ' . $property['city'] : '' }}
                    </p>
                    <div class="entity-stats">
                        <span>{{ $property['meters'] ? $property['meters'] . ' m2' : 'Sin metraje' }}</span>
                        <span>{{ $property['price'] ? number_format($property['price']) . ' ' . html_entity_decode('&euro;', ENT_QUOTES, 'UTF-8') : 'Sin precio' }}</span>
                    </div>
                    @if ($isAdmin && $property['owner'])
                        <div class="entity-owner">Usuario: {{ $property['owner'] }}</div>
                    @endif
                    <div class="entity-meta">
                        @if ($property['updated_at'])
                            <span>Actualizado {{ $property['updated_at'] }}</span>
                        @endif
                        @if ($property['reference'])
                            <span>Ref. {{ $property['reference'] }}</span>
                        @endif
                    </div>
                </div>
                @php
                    $toggleLabel = $property['state_id'] === 5 ? 'Habilitar' : 'Deshabilitar';
                @endphp
                <div class="entity-actions icon-actions">
                    <a class="icon-action accent" href="{{ url('/post/update_form/' . $property['id']) }}" title="Editar" aria-label="Editar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20h9"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                        </svg>
                    </a>
                    <button type="button" class="icon-action warning" data-toggle-status="{{ $property['id'] }}" title="{{ $toggleLabel }}" aria-label="{{ $toggleLabel }}">
                        @if ($property['state_id'] === 5)
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7a21.75 21.75 0 0 1 5.11-5.78"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 2l20 20"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.12 6.12A10.94 10.94 0 0 1 22 12s-1.64 2.87-4.5 5"/>
                            </svg>
                        @endif
                    </button>
                    <button type="button" class="icon-action danger" data-delete-property="{{ $property['id'] }}" title="Eliminar" aria-label="Eliminar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6V4h8v2"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 6l-1 14H6L5 6"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 11v6M14 11v6"/>
                        </svg>
                    </button>
                    <a class="icon-action neutral" href="{{ url('/result/' . $property['reference']) }}" title="Ver anuncio" aria-label="Ver anuncio">
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
                <h3>Sin anuncios aun</h3>
                <p>Publica tu primera propiedad desde el panel.</p>
                <a class="primary" href="{{ url('/post/index') }}">Crear anuncio</a>
            </div>
        @endforelse
    </div>

    @if ($properties->lastPage() > 1)
        <div class="pager">
            <a class="pager-link {{ $properties->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $properties->previousPageUrl() ?? '#' }}" aria-disabled="{{ $properties->onFirstPage() ? 'true' : 'false' }}">
                Anterior
            </a>
            <span class="pager-meta">Pagina {{ $properties->currentPage() }} de {{ $properties->lastPage() }}</span>
            <a class="pager-link {{ $properties->currentPage() === $properties->lastPage() ? 'is-disabled' : '' }}" href="{{ $properties->nextPageUrl() ?? '#' }}" aria-disabled="{{ $properties->currentPage() === $properties->lastPage() ? 'true' : 'false' }}">
                Siguiente
            </a>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (() => {
            const deleteButtons = document.querySelectorAll('[data-delete-property]');
            const toggleButtons = document.querySelectorAll('[data-toggle-status]');

            deleteButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!confirm('Eliminar propiedad?')) {
                        return;
                    }

                    button.disabled = true;
                    const id = button.dataset.deleteProperty;

                    try {
                        const response = await fetch(`/post/delete?id=${id}`);
                        const data = await response.json();
                        if (data.status === 200) {
                            const card = document.querySelector(`[data-card-id="${id}"]`);
                            if (card) {
                                card.remove();
                            }
                        } else {
                            alert('No se pudo eliminar la propiedad.');
                        }
                    } catch (error) {
                        alert('Error al eliminar la propiedad.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });

            toggleButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!confirm('Cambiar estado de la propiedad?')) {
                        return;
                    }

                    button.disabled = true;
                    const id = button.dataset.toggleStatus;

                    try {
                        const response = await fetch(`/post/disabledenabled?id=${id}`);
                        const data = await response.json();
                        if (data.status === 200) {
                            window.location.reload();
                        } else {
                            alert('No se pudo actualizar el estado.');
                        }
                    } catch (error) {
                        alert('Error al actualizar el estado.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        })();
    </script>
@endsection
