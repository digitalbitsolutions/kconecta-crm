@extends('layouts.backoffice')

@section('title', 'Kconecta - Usuario')

@section('heading')
    Usuario
@endsection

@section('subheading')
    Detalles del perfil
@endsection

@section('content')
    @php
        $photoUrl = $profileUser->photo ? asset('img/photo_profile/' . $profileUser->photo) : asset('img/default-avatar-profile-icon.webp');
        $fullName = trim(($profileUser->first_name ?? '') . ' ' . ($profileUser->last_name ?? ''));
        $fullName = $fullName !== '' ? $fullName : ($profileUser->user_name ?: $profileUser->email);
        $addressText = trim(($address?->address ?? $profileUser->address ?? ''));
        $locationParts = array_filter([
            $address?->city ?? '',
            $address?->province ?? '',
            $address?->country ?? '',
        ]);
        $locationText = $locationParts ? implode(', ', $locationParts) : '';
    @endphp

    <div class="page-card user-detail">
        <div class="user-detail-header">
            <div class="user-avatar">
                <img src="{{ $photoUrl }}" alt="Usuario">
            </div>
            <div class="user-title">
                <h2>{{ $fullName }}</h2>
                <p>{{ $levelName }} &middot; {{ $isActive ? 'Activo' : 'Desactivado' }} &middot; Registrado {{ $profileUser->created_at ? $profileUser->created_at->format('d/m/Y') : '-' }}</p>
            </div>
            <div class="user-detail-actions">
                <a class="secondary" href="{{ url('/users') }}">Volver</a>
                <a class="primary" href="{{ url('/users/edit/' . $profileUser->id) }}">Editar</a>
            </div>
        </div>

        <div class="user-detail-grid">
            <div class="user-detail-block">
                <h3>Contacto</h3>
                <div class="user-detail-row">
                    <span>Correo</span>
                    <strong>{{ $profileUser->email ?: 'Sin correo' }}</strong>
                </div>
                <div class="user-detail-row">
                    <span>Telefono</span>
                    <strong>{{ $profileUser->phone ?: 'Sin telefono' }}</strong>
                </div>
                <div class="user-detail-row">
                    <span>Telefono fijo</span>
                    <strong>{{ $profileUser->landline_phone ?: 'Sin telefono fijo' }}</strong>
                </div>
                <div class="user-detail-row">
                    <span>Usuario</span>
                    <strong>{{ $profileUser->user_name ?: 'Sin usuario' }}</strong>
                </div>
            </div>

            <div class="user-detail-block">
                <h3>Documento</h3>
                <div class="user-detail-row">
                    <span>Tipo</span>
                    <strong>{{ $profileUser->document_type ?: 'Sin tipo' }}</strong>
                </div>
                <div class="user-detail-row">
                    <span>Numero</span>
                    <strong>{{ $profileUser->document_number ?: 'Sin numero' }}</strong>
                </div>
                <div class="user-detail-row">
                    <span>Ultima actualizacion</span>
                    <strong>{{ $profileUser->updated_at ? $profileUser->updated_at->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>

            <div class="user-detail-block">
                <h3>Direccion</h3>
                <p>{{ $addressText !== '' ? $addressText : 'Sin direccion registrada' }}</p>
                @if ($locationText !== '')
                    <p>{{ $locationText }}</p>
                @endif
            </div>
        </div>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Propiedades</div>
            <div class="stat-value">{{ number_format($propertyCount) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Publicaciones de servicio</div>
            <div class="stat-value">{{ number_format($serviceCount) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Tipos de servicio</div>
            <div class="stat-value">{{ number_format($serviceTypeCount ?? 0) }}</div>
        </div>
    </section>

    @if (!empty($serviceTypeNames))
        <div class="page-card user-service-types-card">
            <h3>Servicios que ofrece</h3>
            <div class="user-service-types-tags">
                @foreach ($serviceTypeNames as $serviceTypeName)
                    <span>{{ $serviceTypeName }}</span>
                @endforeach
            </div>
        </div>
    @endif
@endsection
