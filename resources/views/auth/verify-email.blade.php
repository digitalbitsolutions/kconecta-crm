<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kconecta - Verifica tu correo</title>
        <link rel="icon" href="{{ asset('img/ico.png') }}" type="image/png">
        <style>
            body {
                margin: 0;
                font-family: "Poppins", sans-serif;
                background: #f8fafc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
            .verify-card {
                width: min(100%, 34rem);
                margin: 0 auto;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: .8rem;
                padding: 1.2rem;
                box-shadow: 0 10px 24px rgba(2, 8, 23, .08);
                text-align: center;
            }
            .verify-card h1 {
                margin: 0 0 .6rem;
                color: #172958;
                font-size: 1.4rem;
            }
            .verify-card p {
                color: #475569;
                margin: 0 0 1.1rem;
            }
            .verify-btn {
                background: #59c4cc;
                color: #fff;
                border: 0;
                border-radius: .5rem;
                padding: .65rem 1rem;
                text-decoration: none;
                font-size: .9rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-weight: 600;
                min-width: 140px;
            }
        </style>
    </head>
    <body>
        <div class="verify-card">
            <h1>Verifica tu correo electr&oacute;nico</h1>
            <p>Revisa tu bandeja y confirma tu cuenta desde el enlace enviado.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="verify-btn">Aceptar</button>
            </form>
        </div>
    </body>
</html>
