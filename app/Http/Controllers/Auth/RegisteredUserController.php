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
        $documentType = strtoupper(trim((string) $request->input('document_type')));
        $isCif = $documentType === 'CIF';

        $rules = [
            'user_level_id' => 'required|integer|in:' . User::LEVEL_SERVICE_PROVIDER . ',' . User::LEVEL_AGENT,
            'document_type' => 'required|string|max:25',
            'document_number' => 'required|string|max:25',
            'first_name' => ($isCif ? 'nullable' : 'required') . '|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'company_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'landline_phone' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'address_floor' => 'nullable|string|max:50',
            'address_door' => 'nullable|string|max:50',
            'address_place_id' => 'required|string|max:255',
            'address_street_name' => 'nullable|string|max:255',
            'address_street_number' => 'nullable|string|max:50',
            'address_neighborhood' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country' => 'nullable|string|max:255',
            'address_lat' => 'required|numeric',
            'address_lng' => 'required|numeric',
            'email' => 'required|string|lowercase|email|max:50',
            'password' => ['required', 'confirmed', 'min:6'],
        ];

        if (! $isCif) {
            $rules['last_name'] = 'required|string|max:50';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($documentType, $request) {
            $documentNumber = $this->normalizeDocumentNumber((string) $request->input('document_number'));
            if (! $this->isValidSpanishDocument($documentType, $documentNumber)) {
                $validator->errors()->add(
                    'document_number',
                    'El n\u00famero de documento no es v\u00e1lido para el tipo seleccionado.'
                );
            }

            $email = mb_strtolower(trim((string) $request->input('email')));
            $companyName = $this->normalizeCompanyName((string) $request->input('company_name'));
            $phone = $this->normalizePhone((string) $request->input('phone'));
            $landlinePhone = $this->normalizePhone((string) $request->input('landline_phone'));

            $duplicates = [];

            if ($documentNumber !== '' && User::where('document_number', $documentNumber)->exists()) {
                $duplicates['document_number'] = [
                    'label' => 'Documento',
                    'value' => $documentNumber,
                ];
                $validator->errors()->add('document_number', 'Ya existe un registro con este n\u00famero de documento.');
            }

            if ($companyName !== '' && User::whereRaw('LOWER(TRIM(user_name)) = ?', [mb_strtolower($companyName)])->exists()) {
                $duplicates['company_name'] = [
                    'label' => 'Raz\u00f3n social',
                    'value' => $companyName,
                ];
                $validator->errors()->add('company_name', 'Ya existe un registro con esta raz\u00f3n social.');
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
                    'label' => 'Tel\u00e9fono',
                    'value' => trim((string) $request->input('landline_phone')),
                ];
                $validator->errors()->add('landline_phone', 'Ya existe un registro con este tel\u00e9fono.');
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
        });
        $validator->validate();

        $email = (string) $request->email;
        $userName = $this->normalizeCompanyName((string) $request->input('company_name'));
        $documentNumber = $this->normalizeDocumentNumber((string) $request->input('document_number'));
        $firstName = $isCif ? $userName : trim((string) $request->first_name);
        $lastName = $isCif ? null : trim((string) $request->last_name);
        $addressValue = $this->composeRegistrationAddress(
            (string) $request->input('address'),
            (string) $request->input('address_floor'),
            (string) $request->input('address_door')
        );

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName !== '' ? $lastName : null,
            'user_name' => $userName,
            'email' => $email,
            'phone' => $request->phone,
            'landline_phone' => $request->landline_phone,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'address' => $addressValue,
            'user_level_id' => (int) $request->user_level_id,
            'password' => Hash::make($request->password),
        ]);

        UserAddress::create([
            'user_id' => $user->id,
            'address' => $addressValue,
            'street_name' => (string) $request->input('address_street_name', ''),
            'street_number' => (string) $request->input('address_street_number', ''),
            'neighborhood' => (string) $request->input('address_neighborhood', ''),
            'city' => (string) $request->input('address_city', ''),
            'province' => (string) $request->input('address_province', ''),
            'postal_code' => (string) $request->input('address_postal_code', ''),
            'state' => (string) $request->input('address_state', ''),
            'country' => (string) $request->input('address_country', ''),
            'latitude' => (string) $request->input('address_lat', ''),
            'longitude' => (string) $request->input('address_lng', ''),
            'additional_info' => trim(implode(', ', array_filter([
                trim((string) $request->input('address_floor', '')) !== '' ? 'Piso: ' . trim((string) $request->input('address_floor', '')) : null,
                trim((string) $request->input('address_door', '')) !== '' ? 'Puerta: ' . trim((string) $request->input('address_door', '')) : null,
            ]))),
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
