<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserLevel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.auth', [
            'mode' => 'register',
            'userLevels' => UserLevel::query()
                ->whereIn('id', [User::LEVEL_SERVICE_PROVIDER, User::LEVEL_AGENT])
                ->orderBy('id')
                ->get(),
            'documentTypes' => $this->documentTypes(),
            'mapsKey' => (string) config('services.google.maps_key'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'user_level_id' => 'required|integer|in:' . User::LEVEL_SERVICE_PROVIDER . ',' . User::LEVEL_AGENT,
            'document_type' => 'nullable|string|max:25',
            'document_number' => 'nullable|string|max:25',
            'first_name' => 'nullable|required_without:company_name|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'company_name' => 'nullable|required_without:first_name|string|max:100',
            'phone' => 'required|string|max:20',
            'landline_phone' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'address_floor' => 'nullable|string|max:50',
            'address_door' => 'nullable|string|max:50',
            'address_place_id' => 'nullable|string|max:255',
            'address_street_name' => 'nullable|string|max:255',
            'address_street_number' => 'nullable|string|max:50',
            'address_neighborhood' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country' => 'nullable|string|max:255',
            'address_lat' => 'nullable|numeric',
            'address_lng' => 'nullable|numeric',
            'email' => 'required|string|lowercase|email|max:50',
            'password' => ['required', 'confirmed', 'min:6'],
        ];

        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'required_without' => 'Debes completar al menos uno de estos campos: :attribute.',
            'numeric' => 'El campo :attribute debe ser numerico.',
            'email' => 'El campo :attribute debe ser un correo electronico valido.',
            'confirmed' => 'La confirmacion de :attribute no coincide.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
        ];

        $attributes = [
            'user_level_id' => 'tipo de usuario',
            'document_type' => 'tipo de documento',
            'document_number' => 'numero de documento',
            'first_name' => 'nombre',
            'last_name' => 'apellidos',
            'company_name' => 'razon social',
            'phone' => 'movil (WhatsApp)',
            'landline_phone' => 'telefono fijo',
            'address' => 'direccion',
            'address_place_id' => 'direccion validada',
            'address_lat' => 'coordenada de latitud',
            'address_lng' => 'coordenada de longitud',
            'email' => 'e-mail',
            'password' => 'contrasena',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        $validator->after(function ($validator) use ($request) {
            $email = mb_strtolower(trim((string) $request->input('email')));
            $companyName = $this->normalizeCompanyName((string) $request->input('company_name'));
            $phone = $this->normalizePhone((string) $request->input('phone'));
            $landlinePhone = $this->normalizePhone((string) $request->input('landline_phone'));

            $duplicates = [];

            if ($companyName !== '' && User::whereRaw('LOWER(TRIM(user_name)) = ?', [mb_strtolower($companyName)])->exists()) {
                $duplicates['company_name'] = [
                    'label' => 'Razon social',
                    'value' => $companyName,
                ];
                $validator->errors()->add('company_name', 'Ya existe un registro con esta razon social.');
            }

            if ($email !== '' && User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                $duplicates['email'] = [
                    'label' => 'E-mail',
                    'value' => $email,
                ];
                $validator->errors()->add('email', 'Ya existe un registro con este e-mail.');
            }

            if ($phone !== '' && User::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'.',''),'+','') = ?", [$phone])->exists()) {
                $duplicates['phone'] = [
                    'label' => 'WhatsApp',
                    'value' => trim((string) $request->input('phone')),
                ];
                $validator->errors()->add('phone', 'Ya existe un registro con este WhatsApp.');
            }

            if ($landlinePhone !== '' && User::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(landline_phone,' ',''),'-',''),'.',''),'+','') = ?", [$landlinePhone])->exists()) {
                $duplicates['landline_phone'] = [
                    'label' => 'Telefono',
                    'value' => trim((string) $request->input('landline_phone')),
                ];
                $validator->errors()->add('landline_phone', 'Ya existe un registro con este telefono.');
            }

            if (! empty($duplicates)) {
                $details = collect($duplicates)
                    ->map(fn ($row) => $row['label'] . ': ' . $row['value'])
                    ->implode(' | ');
                $validator->errors()->add(
                    'duplicate_identity',
                    'Ya existe un registro con estos valores: ' . $details
                );
            }

            $firstName = trim((string) $request->input('first_name'));
            $companyNameValue = trim((string) $request->input('company_name'));
            if ($firstName === '' && $companyNameValue === '') {
                $validator->errors()->add('first_name', 'Completa Nombre o Razon social.');
                $validator->errors()->add('company_name', 'Completa Nombre o Razon social.');
            }
        });
        $validator->validate();

        $email = (string) $request->email;
        $userName = $this->normalizeCompanyName((string) $request->input('company_name'));
        $documentType = strtoupper(trim((string) $request->input('document_type')));
        $documentNumber = $this->normalizeDocumentNumber((string) $request->input('document_number'));
        $firstNameInput = trim((string) $request->input('first_name'));
        $firstName = $firstNameInput !== '' ? $firstNameInput : $userName;
        $lastName = trim((string) $request->input('last_name'));
        $phone = trim((string) $request->input('phone'));
        $landlinePhone = trim((string) $request->input('landline_phone'));

        $addressPlaceId = trim((string) $request->input('address_place_id'));
        $addressValue = null;
        if ($addressPlaceId !== '') {
            $addressValue = $this->composeRegistrationAddress(
                (string) $request->input('address'),
                (string) $request->input('address_floor'),
                (string) $request->input('address_door')
            );
            $addressValue = trim((string) $addressValue) !== '' ? $addressValue : null;
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName !== '' ? $lastName : null,
            'user_name' => $userName !== '' ? $userName : $firstName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'landline_phone' => $landlinePhone !== '' ? $landlinePhone : null,
            'document_type' => $documentType !== '' ? $documentType : null,
            'document_number' => $documentNumber !== '' ? $documentNumber : null,
            'address' => $addressValue,
            'user_level_id' => (int) $request->user_level_id,
            'password' => Hash::make($request->password),
        ]);

        UserAddress::create([
            'user_id' => $user->id,
            'address' => $addressValue,
            'street_name' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_street_name', '')) : null,
            'street_number' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_street_number', '')) : null,
            'neighborhood' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_neighborhood', '')) : null,
            'city' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_city', '')) : null,
            'province' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_province', '')) : null,
            'postal_code' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_postal_code', '')) : null,
            'state' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_state', '')) : null,
            'country' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_country', '')) : null,
            'latitude' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_lat', '')) : null,
            'longitude' => $addressPlaceId !== '' ? $this->nullableTrim((string) $request->input('address_lng', '')) : null,
            'additional_info' => trim(implode(', ', array_filter([
                trim((string) $request->input('address_floor', '')) !== '' ? 'Piso: ' . trim((string) $request->input('address_floor', '')) : null,
                trim((string) $request->input('address_door', '')) !== '' ? 'Puerta: ' . trim((string) $request->input('address_door', '')) : null,
            ]))) ?: null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect($this->redirectPathForUser($user));
    }

    private function documentTypes(): array
    {
        return [
            'DNI',
            'NIE',
            'CIF',
            'Pasaporte',
            'Otro',
        ];
    }

    private function normalizeDocumentNumber(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');
    }

    private function normalizeCompanyName(string $value): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        return $clean;
    }

    private function normalizePhone(string $value): string
    {
        $clean = trim($value);
        return preg_replace('/[\s\-\.\+]/', '', $clean) ?? '';
    }

    private function composeRegistrationAddress(string $address, string $floor, string $door): string
    {
        $baseAddress = trim($address);
        $parts = [];
        $floorValue = trim($floor);
        $doorValue = trim($door);

        if ($floorValue !== '') {
            $parts[] = 'Piso: ' . $floorValue;
        }
        if ($doorValue !== '') {
            $parts[] = 'Puerta: ' . $doorValue;
        }

        if (empty($parts)) {
            return $baseAddress;
        }

        return $baseAddress . ' (' . implode(', ', $parts) . ')';
    }

    private function nullableTrim(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function isValidSpanishDocument(string $documentType, string $documentNumber): bool
    {
        if ($documentNumber === '') {
            return false;
        }

        if ($documentType === 'DNI') {
            return $this->isValidSpanishDni($documentNumber);
        }

        if ($documentType === 'NIE') {
            return $this->isValidSpanishNie($documentNumber);
        }

        if ($documentType === 'CIF') {
            return $this->isValidSpanishCif($documentNumber);
        }

        return true;
    }

    private function isValidSpanishDni(string $value): bool
    {
        if (! preg_match('/^\d{8}[A-Z]$/', $value)) {
            return false;
        }

        $number = (int) substr($value, 0, 8);
        $letter = substr($value, -1);
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $letter === $letters[$number % 23];
    }

    private function isValidSpanishNie(string $value): bool
    {
        if (! preg_match('/^[XYZ]\d{7}[A-Z]$/', $value)) {
            return false;
        }

        $prefixMap = ['X' => '0', 'Y' => '1', 'Z' => '2'];
        $prefix = substr($value, 0, 1);
        $converted = $prefixMap[$prefix] . substr($value, 1);

        return $this->isValidSpanishDni($converted);
    }

    private function isValidSpanishCif(string $value): bool
    {
        if (! preg_match('/^[ABCDEFGHJKLMNPQRSUVW]\d{7}[0-9A-J]$/', $value)) {
            return false;
        }

        $letter = substr($value, 0, 1);
        $digits = substr($value, 1, 7);
        $control = substr($value, -1);

        $sumEven = 0;
        $sumOdd = 0;
        for ($index = 0; $index < 7; $index++) {
            $digit = (int) $digits[$index];
            if (($index + 1) % 2 === 0) {
                $sumEven += $digit;
                continue;
            }

            $doubled = $digit * 2;
            $sumOdd += intdiv($doubled, 10) + ($doubled % 10);
        }

        $sum = $sumEven + $sumOdd;
        $controlDigit = (10 - ($sum % 10)) % 10;
        $controlLetter = 'JABCDEFGHI'[$controlDigit];

        if (in_array($letter, ['A', 'B', 'E', 'H'], true)) {
            return $control === (string) $controlDigit;
        }

        if (in_array($letter, ['K', 'P', 'Q', 'S', 'W'], true)) {
            return $control === $controlLetter;
        }

        return $control === (string) $controlDigit || $control === $controlLetter;
    }

    private function redirectPathForUser(User $user): string
    {
        if ($this->requiresEmailVerification($user) && ! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        if ($user->isAdmin()) {
            return route('dashboard', absolute: false);
        }

        if ($user->canManageServices() && ! $user->canManageProperties()) {
            return url('/post/services');
        }

        if ($user->canManageProperties()) {
            return url('/post/my_posts');
        }

        return route('dashboard', absolute: false);
    }

    private function requiresEmailVerification(User $user): bool
    {
        return in_array((int) $user->user_level_id, [
            User::LEVEL_SERVICE_PROVIDER,
            User::LEVEL_AGENT,
        ], true);
    }
}
