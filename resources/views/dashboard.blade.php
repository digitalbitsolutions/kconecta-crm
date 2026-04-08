@extends('layouts.backoffice')

@section('title', 'Kconecta - Dashboard')

@section('heading')
    Hola, {{ $user?->first_name ?: ($user?->user_name ?: 'Usuario') }}
@endsection

@section('subheading')
    {{ $userLevelName }} &middot; Panel de control
@endsection

@section('header_actions')
    @if ($canManageProperties ?? false)
        <a class="action-icon action-icon--accent" href="{{ url('/post/index') }}" data-tooltip="Agregar propiedad" aria-label="Agregar propiedad">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 11.5l9-7 9 7"/>
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 10.5V20a1 1 0 0 0 1 1h5v-6h2v6h5a1 1 0 0 0 1-1v-9.5"/>
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 4v4M16 6h4"/>
            </svg>
        </a>
    @endif
    @if ($canManageServices ?? false)
        <a class="action-icon action-icon--accent" href="{{ url('/post/services') }}" data-tooltip="Agregar proveedor de servicios" aria-label="Agregar proveedor de servicios">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="9" cy="8" r="3" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8v4M16 10h4"/>
            </svg>
        </a>
    @endif
@endsection

@section('content')
    @php
        $photoUrl = $user && $user->photo
            ? asset('img/photo_profile/' . $user->photo)
            : asset('img/default-avatar-profile-icon.webp');
    @endphp
    @if (! empty($alerts))
        <div class="alert-stack">
            @foreach ($alerts as $alert)
                <div class="alert-card">{{ $alert }}</div>
            @endforeach
        </div>
    @endif

    <section class="stats-grid">
        <div class="stat-card metric-card">
            <div class="stat-label">Clicks en alguna propiedad</div>
            <div class="stat-value">{{ number_format($viewsCount) }}</div>
            <div class="stat-sparkline stat-sparkline--soft">
                <svg viewBox="0 0 120 40" aria-hidden="true" focusable="false">
                    <path class="sparkline-fill" d="M2 28L18 24L32 30L48 14L60 26L76 18L92 22L108 12L118 20L118 40L2 40Z"/>
                    <path class="sparkline-line" d="M2 28L18 24L32 30L48 14L60 26L76 18L92 22L108 12L118 20"/>
                </svg>
            </div>
        </div>
        <div class="stat-card metric-card">
            <div class="stat-label">Usuarios que revisaron propiedades</div>
            <div class="stat-value">{{ number_format($uniqueViewersCount) }}</div>
            <div class="stat-sparkline stat-sparkline--muted">
                <svg viewBox="0 0 120 40" aria-hidden="true" focusable="false">
                    <path class="sparkline-fill" d="M2 14L16 20L30 16L44 22L58 12L72 18L86 10L100 16L118 8L118 40L2 40Z"/>
                    <path class="sparkline-line" d="M2 14L16 20L30 16L44 22L58 12L72 18L86 10L100 16L118 8"/>
                </svg>
            </div>
        </div>
        <div class="stat-card metric-card">
            <div class="stat-label">Clicks en contacto</div>
            <div class="stat-value">{{ number_format($contactClicks) }}</div>
            <div class="stat-sparkline stat-sparkline--accent">
                <svg viewBox="0 0 120 40" aria-hidden="true" focusable="false">
                    <path class="sparkline-fill" d="M2 26L16 22L30 28L44 18L58 24L72 14L86 20L100 12L118 18L118 40L2 40Z"/>
                    <path class="sparkline-line" d="M2 26L16 22L30 28L44 18L58 24L72 14L86 20L100 12L118 18"/>
                </svg>
            </div>
        </div>
    </section>

    <section class="insights-grid">
        <div class="insight-stack">
            <div class="card welcome-card">
                <div class="welcome-avatar">
                    <img src="{{ $photoUrl }}" alt="Usuario">
                </div>
                <div class="welcome-info">
                    <span class="welcome-label">Bienvenido</span>
                    <h3>{{ $user?->first_name ?: ($user?->user_name ?: 'Usuario') }}</h3>
                    <p>{{ $user?->email ?? '' }}</p>
                </div>
            </div>
            @if ($isAdmin)
                <div class="card user-metrics-card">
                    <div class="user-metrics-header">
                        <h3>Usuarios registrados</h3>
                        <span>Por tipo</span>
                    </div>
                    <div class="user-metrics-list">
                        @foreach ($userTypeMetrics as $metric)
                            <div class="user-metrics-item">
                                <div class="metric-label">
                                    <span class="metric-dot" style="background: {{ $metric['color'] }}"></span>
                                    <span>{{ $metric['label'] }}</span>
                                </div>
                                <div class="metric-count">{{ number_format($metric['count']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="card donut-card">
            <div class="donut-header">
                <h3>Tipo de inmueble visitado</h3>
                <span>Distribucion por visitas</span>
            </div>
            <div class="donut-body">
                <div class="donut-chart" style="--donut-fill: {{ $propertyTypeGradient }};"></div>
                <div class="donut-legend">
                    @forelse ($propertyTypeStats as $stat)
                        <div class="legend-item">
                            <div class="legend-label">
                                <span class="legend-dot" style="background: {{ $stat['color'] }}"></span>
                                <span>{{ $stat['label'] }}</span>
                            </div>
                            <strong>{{ number_format($stat['value']) }}</strong>
                        </div>
                    @empty
                        <div class="legend-empty">Sin datos de visitas</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="content-grid">
        @if ($canManageProperties ?? false)
            <div class="card">
                <h3>&Uacute;ltimos anuncios de propiedades</h3>
                <div class="list">
                    @forelse ($recentProperties as $property)
                        <div class="list-item">
                            <strong>{{ $property['title'] }}</strong>
                            <span>{{ $property['type'] }} &middot; {{ $property['category'] }}</span>
                            <span>
                                {{ $property['address'] }}{{ $property['city'] ? ', ' . $property['city'] : '' }}
                            </span>
                            <span class="pill">
                                {{ $property['price'] ? number_format($property['price']) . ' ' . html_entity_decode('&euro;', ENT_QUOTES, 'UTF-8') : 'Sin precio' }}
                            </span>
                        </div>
                    @empty
                        <div class="list-item">
                            <strong>Sin anuncios recientes</strong>
                            <span>Publica tu primer anuncio desde el panel.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="card">
            <h3>Actividad</h3>
            <div class="kpi-grid">
                <div class="kpi-item">
                    <span>Vistas en detalle</span>
                    <strong>{{ number_format($viewsCount) }}</strong>
                </div>
                <div class="kpi-item">
                    <span>Vistas en b&uacute;squeda</span>
                    <strong>{{ number_format($searchViewsCount) }}</strong>
                </div>
            </div>
            <h3 style="margin-top:1.4rem;">Acciones r&aacute;pidas</h3>
            <div class="quick-actions">
                @if ($canManageProperties ?? false)
                    <a href="{{ url('/post/index') }}">
                        <span>Crear anuncio</span>
                        <span>&rsaquo;</span>
                    </a>
                    <a href="{{ url('/post/my_posts') }}">
                        <span>Gestionar propiedades</span>
                        <span>&rsaquo;</span>
                    </a>
                @endif
                @if ($canManageServices ?? false)
                    <a href="{{ url('/post/services') }}">
                        <span>Gestionar proveedores</span>
                        <span>&rsaquo;</span>
                    </a>
                @endif
            </div>
        </div>

        @if ($canManageServices ?? false)
            <div class="card">
                <h3>&Uacute;ltimos proveedores de servicios</h3>
                <div class="list">
                    @forelse ($recentServices as $service)
                        <div class="list-item">
                            <strong>{{ $service['name'] }}</strong>
                            <span>{{ $service['address'] }}{{ $service['city'] ? ', ' . $service['city'] : '' }}</span>
                            @if ($service['phone'])
                                <span>{{ $service['phone'] }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="list-item">
                            <strong>Sin proveedores recientes</strong>
                            <span>Agrega tu primer proveedor de servicio desde el panel.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </section>
@endsection

