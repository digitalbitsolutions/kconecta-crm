<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', $title ?? 'Kconecta')</title>
        <link rel="stylesheet" href="{{ asset('css/page/dashboard.css') }}">
        <link rel="icon" href="{{ asset('img/ico.png') }}" type="image/png">
        @yield('styles')
    </head>
    <body>
        <div class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="brand">
                    <img src="{{ asset('img/kconecta_icon.webp') }}" alt="Kconecta">
                    <span>Kconecta</span>
                </div>
                <nav class="sidebar-nav">
                    @php
                        $authUser = auth()->user();
                        $navIsAdmin = $authUser?->isAdmin() ?? false;
                        $navCanManageProperties = $authUser?->canManageProperties() ?? false;
                        $navCanManageServices = $authUser?->canManageServices() ?? false;
                    @endphp
                    <a class="{{ ($activeNav ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    @if ($navCanManageProperties)
                        <a class="{{ ($activeNav ?? '') === 'properties' ? 'active' : '' }}" href="{{ url('/post/my_posts') }}">Mis propiedades</a>
                    @endif
                    @if ($navCanManageServices)
                        <a class="{{ ($activeNav ?? '') === 'services' ? 'active' : '' }}" href="{{ url('/post/services') }}">
                            {{ $navIsAdmin ? 'Proveedores de servicios' : 'Servicios' }}
                        </a>
                    @endif
                    @if ($navIsAdmin)
                        <a class="{{ ($activeNav ?? '') === 'blog' ? 'active' : '' }}" href="{{ url('/post/blogs') }}">Blog</a>
                        <a class="{{ ($activeNav ?? '') === 'users' ? 'active' : '' }}" href="{{ url('/users') }}">Usuarios</a>
                    @endif
                    <a class="{{ ($activeNav ?? '') === 'profile' ? 'active' : '' }}" href="{{ url('/user/update') }}">Mi perfil</a>
                </nav>
                <div class="sidebar-footer">
                    <a href="{{ url('/') }}">Ir al sitio</a>
                    <form class="sidebar-logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Cerrar sesi&oacute;n</button>
                    </form>
                </div>
            </aside>

            <main class="dashboard-main">
                <header class="dashboard-header">
                    <div>
                        <h1>@yield('heading')</h1>
                        <p>@yield('subheading')</p>
                    </div>
                    @hasSection('header_actions')
                        <div class="header-actions">
                            @yield('header_actions')
                        </div>
                    @endif
                </header>

                @yield('content')
            </main>
        </div>

        <script src="{{ asset('js/libraries/bulma.modal.min.js') }}"></script>
        @yield('scripts')
    </body>
</html>
