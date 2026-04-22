<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Service;
use App\Models\ServiceAddress;
use App\Models\ServiceTypeLink;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserLevel;
use App\Models\CoverImage;
use App\Models\MoreImage;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function userTypeLabels(): array
    {
        return [
            1 => 'Administrador',
            2 => 'Usuario Libre',
            3 => 'Usuario Premium',
            4 => 'Proveedor de servicio',
            5 => 'Agente inmobiliario',
        ];
    }

    private function userTypeGroups(): array
    {
        return [
            'admin' => [1],
            'service' => [4],
            'owner' => [2, 3, 5],
        ];
    }

    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin) {
            return redirect()->route('dashboard');
        }

        $userLevelName = UserLevel::find($user->user_level_id)?->name ?? 'Usuario';
        $request = request();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'level' => (string) $request->query('level', ''),
            'ds' => (string) $request->query('ds', ''),
            'de' => (string) $request->query('de', ''),
        ];

        $query = User::query();
        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('user_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($filters['level'] !== '' && $filters['level'] !== 'all') {
            $groups = $this->userTypeGroups();
            if (isset($groups[$filters['level']])) {
                $query->whereIn('user_level_id', $groups[$filters['level']]);
            } elseif (is_numeric($filters['level'])) {
                $query->where('user_level_id', (int) $filters['level']);
            }
        }

        if ($filters['ds'] !== '') {
            $query->whereDate('created_at', '>=', $filters['ds']);
        }

        if ($filters['de'] !== '') {
            $query->whereDate('created_at', '<=', $filters['de']);
        }

        $users = $query->orderByDesc('id')->paginate(12)->withQueryString();
        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
        $addressRows = empty($userIds)
            ? collect()
            : UserAddress::whereIn('user_id', $userIds)->get()->groupBy('user_id');
        $levelMap = UserLevel::pluck('name', 'id')->all();
        $roleLabels = $this->userTypeLabels();
        foreach ($roleLabels as $id => $label) {
            $levelMap[$id] = $label;
        }

        $users->getCollection()->transform(function (User $row) use ($addressRows, $levelMap) {
            $address = $addressRows->get($row->id)?->first();
            $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
            if ($name === '') {
                $name = $row->user_name ?: ($row->email ?: 'Usuario');
            }

            return [
                'id' => $row->id,
                'name' => $name,
                'user_level_id' => (int) $row->user_level_id,
                'user_name' => $row->user_name ?? '',
                'email' => $row->email ?? '',
                'phone' => $row->phone ?? '',
                'landline_phone' => $row->landline_phone ?? '',
                'level' => $levelMap[$row->user_level_id] ?? 'Usuario',
                'is_active' => (int) ($row->is_active ?? 1),
                'status_label' => (int) ($row->is_active ?? 1) === 1 ? 'Activo' : 'Desactivado',
                'address' => $address?->address ?? ($row->address ?? ''),
                'city' => $address?->city ?? '',
                'province' => $address?->province ?? '',
                'photo' => $row->photo ?? '',
                'created_at' => $row->created_at ? $row->created_at->format('d/m/Y') : '',
                'updated_at' => $row->updated_at ? $row->updated_at->format('d/m/Y') : '',
            ];
        });

        $levelOptions = UserLevel::orderBy('id')
            ->get(['id', 'name'])
            ->map(function (UserLevel $level) {
                return (object) [
                    'value' => $level->id,
                    'name' => $level->name,
                ];
            });

        return view('users.index', [
            'user' => $user,
            'userLevelName' => $userLevelName,
            'isAdmin' => $isAdmin,
            'activeNav' => 'users',
            'users' => $users,
            'filters' => $filters,
            'levelOptions' => $levelOptions,
        ]);
    }

    public function userView(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin) {
            return redirect()->route('dashboard');
        }

        $profileUser = User::findOrFail($id);
        $address = UserAddress::where('user_id', $profileUser->id)->first();
        $roleLabels = $this->userTypeLabels();
        $levelName = $roleLabels[$profileUser->user_level_id]
            ?? (UserLevel::find($profileUser->user_level_id)?->name ?? 'Usuario');
        $propertyCount = Property::where('user_id', $profileUser->id)->count();
        $serviceIds = Service::where('user_id', $profileUser->id)->pluck('id')->all();
        $serviceCount = count($serviceIds);
        $serviceTypeCount = empty($serviceIds)
            ? 0
            : ServiceTypeLink::whereIn('service_id', $serviceIds)
                ->distinct()
                ->count('service_type_id');

        return view('users.view', [
            'user' => $user,
            'userLevelName' => $roleLabels[$user->user_level_id]
                ?? (UserLevel::find($user->user_level_id)?->name ?? 'Usuario'),
            'isAdmin' => $isAdmin,
            'activeNav' => 'users',
            'profileUser' => $profileUser,
            'address' => $address,
            'levelName' => $levelName,
            'propertyCount' => $propertyCount,
            'serviceCount' => $serviceCount,
            'serviceTypeCount' => $serviceTypeCount,
            'isActive' => (int) ($profileUser->is_active ?? 1) === 1,
        ]);
    }

    public function update()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $address = UserAddress::where('user_id', $user->id)->first();
        $documentTypes = ['DNI', 'NIE', 'CIF', 'Pasaporte'];
        $addressValidated = $address && $address->latitude && $address->longitude;

        return view('user.update', [
            'user' => $user,
            'address' => $address,
            'documentTypes' => $documentTypes,
            'activeNav' => 'profile',
            'isAdmin' => (int) $user->user_level_id === 1,
            'mapsKey' => config('services.google.maps_key'),
            'addressValidated' => $addressValidated,
            'pageTitle' => 'Kconecta - Mi perfil',
            'pageHeading' => 'Mi perfil',
            'pageSubheading' => 'Actualiza tus datos personales',
        ]);
    }

    public function editAdmin(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin) {
            return redirect()->route('dashboard');
        }

        $profileUser = User::findOrFail($id);
        $address = UserAddress::where('user_id', $profileUser->id)->first();
        $documentTypes = ['DNI', 'NIE', 'CIF', 'Pasaporte'];
        $addressValidated = $address && $address->latitude && $address->longitude;

        return view('user.update', [
            'user' => $profileUser,
            'address' => $address,
            'documentTypes' => $documentTypes,
            'activeNav' => 'users',
            'isAdmin' => $isAdmin,
            'mapsKey' => config('services.google.maps_key'),
            'addressValidated' => $addressValidated,
            'pageTitle' => 'Kconecta - Editar usuario',
            'pageHeading' => 'Editar usuario',
            'pageSubheading' => 'Actualiza la informacion del usuario',
            'targetUserId' => $profileUser->id,
        ]);
    }

    public function updateSave(Request $request)
    {
        $authUser = $request->user();
        if (! $authUser) {
            return redirect()->route('login');
        }

        $isAdmin = (int) $authUser->user_level_id === 1;
        $targetUserId = (int) $request->input('target_user_id', 0);
        if ($targetUserId && ! $isAdmin) {
            abort(403);
        }

        $user = $authUser;
        if ($targetUserId && $targetUserId !== (int) $authUser->id) {
            $user = User::findOrFail($targetUserId);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'landline_phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['nullable', 'string', 'max:25'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'address_place_id' => ['nullable', 'string', 'max:255'],
            'address_street_name' => ['nullable', 'string', 'max:255'],
            'address_street_number' => ['nullable', 'string', 'max:50'],
            'address_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:255'],
            'address_postal_code' => ['nullable', 'string', 'max:20'],
            'address_country' => ['nullable', 'string', 'max:255'],
            'address_lat' => ['nullable', 'numeric'],
            'address_lng' => ['nullable', 'numeric'],
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $addressRecord = UserAddress::firstOrNew(['user_id' => $user->id]);
        $addressInput = trim((string) ($validated['address'] ?? ''));
        $placeId = trim((string) ($validated['address_place_id'] ?? ''));
        $currentAddress = trim((string) ($addressRecord->address ?? $user->address ?? ''));

        if ($addressInput !== '' && $addressInput !== $currentAddress && $placeId === '') {
            return back()
                ->withErrors(['address' => 'Selecciona una direccion sugerida por Google.'])
                ->withInput();
        }

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'] ?? '';
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? '';
        $user->landline_phone = $validated['landline_phone'] ?? '';
        $user->document_type = $validated['document_type'] ?? '';
        $user->document_number = $validated['document_number'] ?? '';

        if ($addressInput !== '') {
            $user->address = $addressInput;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $photoUpdated = false;
        if ($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                $filename = $this->processProfilePhoto($file, (int) $user->id);
                $user->photo = $filename;
                $photoUpdated = true;
            } catch (\Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'photo' => 'No se pudo procesar el logo en este momento. Verifica que sea una imagen JPG, PNG o WEBP e intentalo de nuevo.',
                ]);
            }
        }

        $user->save();

        if ($addressInput !== '') {
            $addressRecord->address = $addressInput;
        }

        if ($placeId !== '') {
            $addressRecord->street_name = $validated['address_street_name'] ?? '';
            $addressRecord->street_number = $validated['address_street_number'] ?? '';
            $addressRecord->neighborhood = $validated['address_neighborhood'] ?? '';
            $addressRecord->city = $validated['address_city'] ?? '';
            $addressRecord->province = $validated['address_province'] ?? '';
            $addressRecord->state = $validated['address_state'] ?? '';
            $addressRecord->postal_code = $validated['address_postal_code'] ?? '';
            $addressRecord->country = $validated['address_country'] ?? '';
            $addressRecord->latitude = $validated['address_lat'] ?? null;
            $addressRecord->longitude = $validated['address_lng'] ?? null;
        }

        if ($addressRecord->address || $placeId !== '') {
            $addressRecord->user_id = $user->id;
            $addressRecord->save();
        }

        $statusMessage = 'Perfil actualizado correctamente.';
        if ($photoUpdated) {
            $statusMessage .= ' Foto actualizada en formato WebP (350x350).';
        }

        return redirect()
            ->back()
            ->with('status', $statusMessage);
    }

    public function toggleStatus(Request $request)
    {
        $authUser = Auth::user();
        if (! $authUser) {
            return response()->json(['status' => 401], 401);
        }

        $isAdmin = (int) $authUser->user_level_id === 1;
        if (! $isAdmin) {
            return response()->json(['status' => 403], 403);
        }

        $id = (int) $request->query('id');
        if (! $id) {
            return response()->json(['status' => 400], 400);
        }

        if ($id === (int) $authUser->id) {
            return response()->json(['status' => 409, 'message' => 'No puedes desactivar tu usuario.'], 409);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json(['status' => 404], 404);
        }

        $user->is_active = (int) ($user->is_active ?? 1) === 1 ? 0 : 1;
        $user->save();

        return response()->json([
            'status' => 200,
            'is_active' => (int) $user->is_active,
        ]);
    }

    public function userDelete(Request $request)
    {
        $authUser = Auth::user();
        if (! $authUser) {
            return response()->json(['status' => 401], 401);
        }

        $isAdmin = (int) $authUser->user_level_id === 1;
        if (! $isAdmin) {
            return response()->json(['status' => 403], 403);
        }

        $id = (int) $request->query('id');
        if (! $id) {
            return response()->json(['status' => 400], 400);
        }

        if ($id === (int) $authUser->id) {
            return response()->json(['status' => 409, 'message' => 'No puedes eliminar tu usuario.'], 409);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json(['status' => 404], 404);
        }

        if ((int) $user->user_level_id !== 4) {
            return response()->json(['status' => 403, 'message' => 'Solo puedes eliminar proveedores de servicio.'], 403);
        }

        $serviceIds = Service::where('user_id', $user->id)->pluck('id')->all();
        if (! empty($serviceIds)) {
            CoverImage::whereIn('service_id', $serviceIds)->delete();
            MoreImage::whereIn('service_id', $serviceIds)->delete();
            Video::whereIn('service_id', $serviceIds)->delete();
            ServiceAddress::whereIn('service_id', $serviceIds)->delete();
            ServiceTypeLink::whereIn('service_id', $serviceIds)->delete();
            Service::whereIn('id', $serviceIds)->delete();
        }

        UserAddress::where('user_id', $user->id)->delete();
        $user->delete();

        return response()->json(['status' => 200]);
    }

    private function processProfilePhoto(\Illuminate\Http\UploadedFile $file, int $userId): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new \RuntimeException('GD/WebP no disponible en el servidor.');
        }

        $source = $this->createImageResourceFromUpload($file);
        if (! $source) {
            throw new \RuntimeException('No se pudo procesar la imagen subida.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $squareSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) floor(($sourceWidth - $squareSize) / 2);
        $sourceY = (int) floor(($sourceHeight - $squareSize) / 2);

        $canvas = imagecreatetruecolor(350, 350);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            350,
            350,
            $squareSize,
            $squareSize
        );

        $directory = public_path('img/photo_profile');
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('No se pudo crear el directorio de logos de perfil.');
        }

        if (! is_writable($directory)) {
            throw new \RuntimeException('El directorio de logos de perfil no tiene permisos de escritura.');
        }

        $filename = 'user_' . $userId . '_' . Str::random(12) . '.webp';
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;
        $saved = imagewebp($canvas, $destination, 82);

        imagedestroy($canvas);
        imagedestroy($source);

        if (! $saved) {
            throw new \RuntimeException('No se pudo guardar la imagen en formato WebP.');
        }

        return $filename;
    }

    private function createImageResourceFromUpload(\Illuminate\Http\UploadedFile $file): \GdImage|false
    {
        $mime = (string) $file->getMimeType();
        $path = $file->getRealPath();

        if (! $path) {
            return false;
        }

        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }
}
