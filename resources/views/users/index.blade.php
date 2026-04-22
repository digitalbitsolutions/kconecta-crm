@extends('layouts.backoffice')

@section('title', 'Kconecta - Usuarios')

@section('heading')
    Usuarios
@endsection

@section('subheading')
    Gestiona los usuarios registrados
@endsection

@section('content')
    <div class="page-card">
        <div class="section-header">
            <div>
                <h2>Listado</h2>
                <p>{{ $users->total() }} usuarios en total</p>
            </div>
        </div>

        <form class="filter-bar" method="GET" action="{{ url('/users') }}">
            <label class="filter-group">
                <span>Buscar</span>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, correo o usuario">
            </label>
            <label class="filter-group">
                <span>Nivel</span>
                <select name="level">
                    <option value="all">Todos</option>
                    @foreach ($levelOptions as $level)
                        <option value="{{ $level->value }}" {{ ($filters['level'] ?? '') === (string) $level->value ? 'selected' : '' }}>
                            {{ $level->name }}
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
                <a class="secondary" href="{{ url('/users') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="catalog-grid users-grid">
        @forelse ($users as $userItem)
            @php
                $photoUrl = $userItem['photo'] ? asset('img/photo_profile/' . $userItem['photo']) : asset('img/default-avatar-profile-icon.webp');
                $addressText = trim($userItem['address'] . ($userItem['city'] ? ', ' . $userItem['city'] : '') . ($userItem['province'] ? ', ' . $userItem['province'] : ''));
                $isActive = (int) $userItem['is_active'] === 1;
                $toggleLabel = $isActive ? 'Desactivar' : 'Activar';
            @endphp
            <article class="entity-card user-card {{ $isActive ? '' : 'is-inactive' }}" data-card-id="user-{{ $userItem['id'] }}">
                <div class="user-card-header">
                    <div class="user-avatar">
                        <img src="{{ $photoUrl }}" alt="Usuario">
                    </div>
                    <div class="user-heading">
                        <h3>{{ $userItem['name'] }}</h3>
                        <div class="user-subline">
                            <span class="user-role">{{ $userItem['level'] }}</span>
                            <span class="user-status {{ $isActive ? '' : 'inactive' }}">{{ $userItem['status_label'] }}</span>
                        </div>
                    </div>
                    <span class="user-badge">#{{ $userItem['id'] }}</span>
                </div>
                <div class="user-card-body">
                    <div class="user-line">
                        <strong>Correo</strong>
                        <span>{{ $userItem['email'] ?: 'Sin correo' }}</span>
                    </div>
                    <div class="user-line">
                        <strong>Telefono</strong>
                        <span>{{ $userItem['phone'] ?: 'Sin telefono' }}</span>
                    </div>
                    <div class="user-line">
                        <strong>Usuario</strong>
                        <span>{{ $userItem['user_name'] ?: 'Sin usuario' }}</span>
                    </div>
                    <div class="user-line">
                        <strong>Direccion</strong>
                        <span>{{ $addressText !== '' ? $addressText : 'Sin direccion' }}</span>
                    </div>
                    <div class="user-dates">
                        @if ($userItem['created_at'])
                            <span>Registrado {{ $userItem['created_at'] }}</span>
                        @endif
                        @if ($userItem['updated_at'])
                            <span>Actualizado {{ $userItem['updated_at'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="user-actions">
                    <a class="icon-action neutral" href="{{ url('/users/' . $userItem['id']) }}" title="Ver usuario" aria-label="Ver usuario">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3h7v7"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14L21 3"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14v7a2 2 0 0 1-2 2h-7"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10v11a2 2 0 0 0 2 2h11"/>
                        </svg>
                    </a>
                    <a class="icon-action accent" href="{{ url('/users/edit/' . $userItem['id']) }}" title="Editar usuario" aria-label="Editar usuario">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20h9"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                        </svg>
                    </a>
                    <button type="button" class="icon-action warning" data-toggle-user="{{ $userItem['id'] }}" data-active="{{ $isActive ? '1' : '0' }}" title="{{ $toggleLabel }}" aria-label="{{ $toggleLabel }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2v10"/>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.3 6.3a7 7 0 1 0 11.4 0"/>
                        </svg>
                    </button>
                    @if (($isAdmin ?? false) && (int) ($userItem['user_level_id'] ?? 0) === \App\Models\User::LEVEL_SERVICE_PROVIDER)
                        <button type="button" class="icon-action danger" data-delete-user="{{ $userItem['id'] }}" title="Eliminar usuario" aria-label="Eliminar usuario">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 11v6M14 11v6"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-state">
                <h3>Sin usuarios</h3>
                <p>No hay usuarios registrados actualmente.</p>
            </div>
        @endforelse
    </div>

    @if ($users->lastPage() > 1)
        <div class="pager">
            <a class="pager-link {{ $users->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $users->previousPageUrl() ?? '#' }}" aria-disabled="{{ $users->onFirstPage() ? 'true' : 'false' }}">
                Anterior
            </a>
            <span class="pager-meta">Pagina {{ $users->currentPage() }} de {{ $users->lastPage() }}</span>
            <a class="pager-link {{ $users->currentPage() === $users->lastPage() ? 'is-disabled' : '' }}" href="{{ $users->nextPageUrl() ?? '#' }}" aria-disabled="{{ $users->currentPage() === $users->lastPage() ? 'true' : 'false' }}">
                Siguiente
            </a>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (() => {
            const buttons = document.querySelectorAll('[data-toggle-user]');
            const deleteButtons = document.querySelectorAll('[data-delete-user]');
            buttons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const userId = button.dataset.toggleUser;
                    const isActive = button.dataset.active === '1';
                    const actionLabel = isActive ? 'desactivar' : 'activar';
                    if (!confirm(`Deseas ${actionLabel} este usuario?`)) {
                        return;
                    }

                    button.disabled = true;
                    try {
                        const response = await fetch(`/users/toggle?id=${userId}`);
                        const data = await response.json();
                        if (data.status !== 200) {
                            alert(data.message || 'No se pudo actualizar el usuario.');
                            return;
                        }

                        const card = document.querySelector(`[data-card-id="user-${userId}"]`);
                        const statusBadge = card ? card.querySelector('.user-status') : null;
                        const isNowActive = Number(data.is_active) === 1;
                        button.dataset.active = isNowActive ? '1' : '0';
                        button.title = isNowActive ? 'Desactivar' : 'Activar';
                        button.setAttribute('aria-label', button.title);
                        if (card) {
                            card.classList.toggle('is-inactive', !isNowActive);
                        }
                        if (statusBadge) {
                            statusBadge.textContent = isNowActive ? 'Activo' : 'Desactivado';
                            statusBadge.classList.toggle('inactive', !isNowActive);
                        }
                    } catch (error) {
                        alert('Error al actualizar el usuario.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });

            deleteButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const userId = button.dataset.deleteUser;
                    if (!confirm('Se eliminara este proveedor y todas sus publicaciones. Deseas continuar?')) {
                        return;
                    }

                    button.disabled = true;
                    try {
                        const response = await fetch(`/user/delete?id=${userId}`);
                        const data = await response.json();
                        if (data.status !== 200) {
                            alert(data.message || 'No se pudo eliminar el proveedor.');
                            return;
                        }

                        const card = document.querySelector(`[data-card-id="user-${userId}"]`);
                        if (card) {
                            card.remove();
                        }
                    } catch (error) {
                        alert('Error al eliminar el proveedor.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        })();
    </script>
@endsection
