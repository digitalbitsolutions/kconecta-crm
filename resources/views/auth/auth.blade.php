<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kconecta - {{ $mode === 'register' ? 'Crear cuenta' : 'Iniciar sesion' }}</title>
        <link rel="stylesheet" href="{{ asset('css/page/login.css') }}">
        <link rel="icon" href="{{ asset('img/ico.png') }}" type="image/png">
    </head>
    <body>
        @if ($errors->any() || session('status'))
            <div class="message">
                @if ($errors->any())
                    <div class="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('status'))
                    <div class="alert success">{{ session('status') }}</div>
                @endif
            </div>
        @endif

        <div class="container-main {{ $mode === 'register' ? 'right-panel-active' : '' }}" id="container-main">
            <div class="form-container sign-up-container">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="container-logo-image-dml-redirect-start">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('img/kconecta_icon.webp') }}" alt="Kconecta">
                        </a>
                    </div>
                    <h1 class="title-main-section">Crea tu Cuenta</h1>

                    <label>
                        <span>Tipo de usuario *</span>
                        <select name="user_level_id" required>
                            <option value="">Seleccione</option>
                            @foreach ($userLevels as $level)
                                <option value="{{ $level->id }}" {{ (string) old('user_level_id') === (string) $level->id ? 'selected' : '' }}>
                                    {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="container-two-col">
                        <label>
                            <span>Tipo de documento *</span>
                            <select name="document_type" required>
                                <option value="">Seleccione</option>
                                @foreach ($documentTypes as $documentType)
                                    <option value="{{ $documentType }}" {{ old('document_type') === $documentType ? 'selected' : '' }}>
                                        {{ $documentType }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>N&uacute;mero de documento *</span>
                            <input type="text" name="document_number" value="{{ old('document_number') }}" required>
                        </label>
                    </div>

                    <div class="container-two-col">
                        <label>
                            <span>Nombre *</span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                        </label>
                        <label>
                            <span>Apellido *</span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required>
                        </label>
                    </div>

                    <div class="container-two-col">
                        <label>
                            <span>M&oacute;vil (WhatsApp) *</span>
                            <input type="text" name="phone" value="{{ old('phone') }}" required>
                        </label>
                        <label>
                            <span>Tel&eacute;fono fijo</span>
                            <input type="text" name="landline_phone" value="{{ old('landline_phone') }}">
                        </label>
                    </div>

                    <label>
                        <span>Direcci&oacute;n *</span>
                        <input type="text" name="address" value="{{ old('address') }}" required>
                    </label>

                    <label>
                        <span>E-mail *</span>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </label>

                    <div class="container-two-col">
                        <label>
                            <span>Contrase&ntilde;a *</span>
                            <input type="password" name="password" required>
                        </label>
                        <label>
                            <span>Repita la contrase&ntilde;a *</span>
                            <input type="password" name="password_confirmation" required>
                        </label>
                    </div>

                    <button type="submit" id="lila">Registrar</button>
                    <span class="span-redirect-page" onclick="window.location='{{ route('login') }}'">
                        &iquest;Ya tienes cuenta? Inicia sesi&oacute;n
                    </span>
                </form>
            </div>

            <div class="form-container sign-in-container">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="container-logo-image-dml-redirect-start">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('img/kconecta_icon.webp') }}" alt="Kconecta">
                        </a>
                    </div>
                    <h1 class="title-main-section">Iniciar Sesi&oacute;n</h1>

                    <label>
                        <span>E-mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    </label>
                    <label>
                        <span>Contrase&ntilde;a</span>
                        <input type="password" name="password" required autocomplete="current-password">
                    </label>

                    <div class="container-checkbox-remember-session">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember"><span>Recordar sesi&oacute;n</span></label>
                    </div>

                    @if (isset($canResetPassword) && $canResetPassword)
                        <a href="{{ route('password.request') }}">Olvidaste tu contrase&ntilde;a?</a>
                    @endif
                    <button type="submit" id="lila">Iniciar Sesi&oacute;n</button>
                    <span class="span-redirect-page" onclick="window.location='{{ route('register') }}'">
                        &iquest;No tienes cuenta? Reg&iacute;strate
                    </span>
                </form>
            </div>

            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>&iexcl;Bienvenido!</h1>
                        <p>Inicia sesi&oacute;n con tu cuenta</p>
                        <button class="ghost" type="button" onclick="window.location='{{ route('login') }}'">
                            Inicia sesi&oacute;n
                        </button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>Hola!!!</h1>
                        <p>Crear tu cuenta</p>
                        <button class="ghost" type="button" onclick="window.location='{{ route('register') }}'">
                            Registrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
