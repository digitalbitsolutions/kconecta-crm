<?php

namespace App\Http\Controllers;

use App\Models\MoreImage;
use App\Models\PostVisit;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\PsEmailOwner;
use App\Models\PsLinkCopied;
use App\Models\PsMessagesReceived;
use App\Models\PsOwnerCalls;
use App\Models\PsSavedFavorite;
use App\Models\PsSharedFacebook;
use App\Models\PsSharedFriends;
use App\Models\PsViewsDetail;
use App\Models\PsViewsSearch;
use App\Models\PsWhatsappClicks;
use App\Models\Service;
use App\Models\ServiceTypeLink;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFree;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function searchProperties(Request $request)
    {
        $text = trim((string) $request->query('text', ''));
        $typeId = $request->query('type');
        $categoryId = $request->query('category');

        $provinceCounter = [];

        if (mb_strlen($text) >= 3) {
            $provinceRows = PropertyAddress::query()
                ->where('province', 'like', '%' . $text . '%')
                ->get(['province']);

            foreach ($provinceRows as $row) {
                $name = $row->province;
                if (! isset($provinceCounter[$name])) {
                    $provinceCounter[$name] = 0;
                }
                $provinceCounter[$name]++;
            }
        }

        if (count($provinceCounter) > 2) {
            $provinceCounter = array_slice($provinceCounter, 0, 2, true);
        }

        $addressRows = PropertyAddress::query()
            ->where('address', 'like', '%' . $text . '%')
            ->get();

        $addressCounter = [];

        foreach ($addressRows as $row) {
            $propertyQuery = Property::query()
                ->where('id', $row->property_id)
                ->where('state_id', 4);

            if (! empty($typeId)) {
                $propertyQuery->where('type_id', $typeId);
            }

            if (! empty($categoryId)) {
                $propertyQuery->where('category_id', $categoryId);
            }

            if (! $propertyQuery->exists()) {
                continue;
            }

            $address = $row->address;
            if (! isset($addressCounter[$address])) {
                $addressCounter[$address] = 0;
            }
            $addressCounter[$address]++;
        }

        $addressCounter = array_slice($addressCounter, 0, 7, true);

        return response()->json([
            'status' => 200,
            'data' => $addressCounter,
            'province' => $provinceCounter,
        ]);
    }

    public function searchServices(Request $request)
    {
        $text = trim((string) $request->query('text', ''));
        $serviceTypeId = $request->query('service_type');

        $provinceCounter = [];

        if (mb_strlen($text) >= 3) {
            $provinceRows = UserAddress::query()
                ->where('province', 'like', '%' . $text . '%')
                ->get(['province']);

            foreach ($provinceRows as $row) {
                $name = $row->province;
                if (! isset($provinceCounter[$name])) {
                    $provinceCounter[$name] = 0;
                }
                $provinceCounter[$name]++;
            }
        }

        if (count($provinceCounter) > 2) {
            $provinceCounter = array_slice($provinceCounter, 0, 2, true);
        }

        $addressRows = UserAddress::query()
            ->where('address', 'like', '%' . $text . '%')
            ->get();

        $addressCounter = [];

        foreach ($addressRows as $row) {
            $userId = $row->user_id;
            $stateServiceType = true;

            if (! empty($serviceTypeId)) {
                $service = Service::query()
                    ->where('user_id', $userId)
                    ->first();

                if (! $service) {
                    continue;
                }

                $stateServiceType = ServiceTypeLink::query()
                    ->where('service_type_id', $serviceTypeId)
                    ->where('service_id', $service->id)
                    ->exists();
            }

            if (! $stateServiceType) {
                continue;
            }

            $address = $row->address;
            if (! isset($addressCounter[$address])) {
                $addressCounter[$address] = 0;
            }
            $addressCounter[$address]++;
        }

        $addressCounter = array_slice($addressCounter, 0, 7, true);

        return response()->json([
            'status' => 200,
            'data' => $addressCounter,
            'province' => $provinceCounter,
        ]);
    }

    public function dataPropertiesForMap(Request $request)
    {
        $address = (string) $request->query('address');
        $categoryId = $request->query('ca');
        $typeId = $request->query('ty');
        $pMin = $request->query('p_min');
        $pMax = $request->query('p_max');
        $builtMin = $request->query('built_min');
        $builtMax = $request->query('built_max');
        $numMinBathrooms = $request->query('n_bar');
        $numMinBedrooms = $request->query('n_ber');
        $city = $request->query('city');
        $province = $request->query('province');

        $addressQuery = PropertyAddress::query();
        if (! empty($city) || ! empty($province)) {
            if (! empty($province) && empty($city)) {
                $addressQuery->where('province', trim($province));
            } elseif (! empty($city)) {
                $addressQuery->where('city', trim($city));
            }
            $addresses = $addressQuery->get();
        } elseif (! empty($address)) {
            $addressParts = explode(',', $address);
            $addressSeed = trim($addressParts[0]);
            $addressQuery->where('address', 'like', '%' . trim($address) . '%')
                ->orWhere('address', 'like', '%' . $addressSeed . '%')
                ->orWhere('province', 'like', '%' . $addressSeed . '%')
                ->orWhere('city', 'like', '%' . $addressSeed . '%');
            $addresses = $addressQuery->get();
        } else {
            $addresses = collect();
        }

        $dataProperties = [];

        foreach ($addresses as $row) {
            $propertyQuery = Property::query()
                ->where('id', $row->property_id)
                ->where('state_id', 4);

            if (! empty($typeId)) {
                $propertyQuery->where('type_id', $typeId);
            }
            if (! empty($categoryId)) {
                $propertyQuery->where('category_id', $categoryId);
            }
            if (! empty($categoryId)) {
                $priceField = ((int) $categoryId === 1) ? 'rental_price' : 'sale_price';
                if (! empty($pMin)) {
                    $propertyQuery->where($priceField, '>=', $pMin);
                }
                if (! empty($pMax)) {
                    $propertyQuery->where($priceField, '<=', $pMax);
                }
            } else {
                if (! empty($pMin)) {
                    $propertyQuery->where(function ($query) use ($pMin) {
                        $query->where('sale_price', '>=', $pMin)
                            ->orWhere('rental_price', '>=', $pMin);
                    });
                }
                if (! empty($pMax)) {
                    $propertyQuery->where(function ($query) use ($pMax) {
                        $query->where('sale_price', '<=', $pMax)
                            ->orWhere('rental_price', '<=', $pMax);
                    });
                }
            }
            if (! empty($builtMin)) {
                $propertyQuery->where('meters_built', '>=', $builtMin);
            }
            if (! empty($builtMax)) {
                $propertyQuery->where('meters_built', '<=', $builtMax);
            }
            if (! empty($numMinBathrooms)) {
                $propertyQuery->where('bathrooms', '>=', $numMinBathrooms);
            }
            if (! empty($numMinBedrooms)) {
                $propertyQuery->where('bedrooms', '>=', $numMinBedrooms);
            }

            $property = $propertyQuery->first();
            if ($property) {
                $dataProperties[] = [
                    'id' => $property->reference,
                    'title' => $property->title,
                    'price' => $property->sale_price ?: $property->rental_price,
                    'lat' => $row->latitude,
                    'lng' => $row->longitude,
                ];
            }
        }

        return response()->json(['status' => 200, 'data' => $dataProperties]);
    }

    public function dataServicesForMap(Request $request)
    {
        $sti = $request->query('sti');
        $address = (string) $request->query('address');
        $city = $request->query('city');
        $province = $request->query('province');

        $serviceTypeIds = [];
        if (! empty($sti)) {
            if (is_array($sti)) {
                $sti = array_map('intval', $sti);
            } else {
                $sti = [(int) $sti];
            }

            $serviceTypeIds = ServiceTypeLink::query()
                ->whereIn('service_type_id', $sti)
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $addressQuery = UserAddress::query();
        if (! empty($city) || ! empty($province)) {
            if (! empty($province) && empty($city)) {
                $addressQuery->where('province', trim($province));
            } elseif (! empty($city)) {
                $addressQuery->where('city', trim($city));
            }
            $addresses = $addressQuery->get();
        } elseif (! empty($address)) {
            $addressParts = explode(',', $address);
            $addressSeed = trim($addressParts[0]);
            $addressQuery->where('address', 'like', '%' . trim($address) . '%')
                ->orWhere('address', 'like', '%' . $addressSeed . '%')
                ->orWhere('province', 'like', '%' . $addressSeed . '%')
                ->orWhere('city', 'like', '%' . $addressSeed . '%');
            $addresses = $addressQuery->get();
        } else {
            $addresses = collect();
        }

        $dataProperties = [];
        foreach ($addresses as $row) {
            $userId = $row->user_id;
            $userName = '';
            $userLogoUrl = '';
            $user = User::find($userId);
            if ($user) {
                $userName = $user->first_name;
                if (! empty($user->last_name)) {
                    $userName .= ', ' . $user->last_name;
                }
                if (! empty($user->photo)) {
                    $userLogoUrl = '/img/photo_profile/' . ltrim($user->photo, '/');
                }
            }

            $serviceQuery = Service::query();
            if (! empty($serviceTypeIds)) {
                $serviceQuery->whereIn('id', $serviceTypeIds);
            }

            $service = $serviceQuery->where('user_id', $userId)->first();
            if ($service) {
                $dataProperties[] = [
                    'id' => $service->id,
                    'title' => $userName,
                    'logo_url' => $userLogoUrl,
                    'lat' => $row->latitude,
                    'lng' => $row->longitude,
                ];
            }
        }

        return response()->json(['status' => 200, 'data' => $dataProperties]);
    }

    public function deleteMoreImage(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 403], 403);
        }

        $id = $request->query('id');
        if (! empty($id)) {
            MoreImage::where('id', $id)->delete();
        }

        return response()->json(['status' => 200]);
    }

    public function visitorRegister(Request $request)
    {
        $postId = $request->post('post_id');
        $ipAddress = $request->ip();
        $referer = $request->headers->get('referer');

        $postVisit = PostVisit::create([
            'post_id' => $postId,
            'ip_address' => $ipAddress,
            'referer' => $referer,
            'contacted' => null,
        ]);

        return response()->json(['status' => 200, 'id' => $postVisit->id]);
    }

    public function visitorContactedUpdate(Request $request)
    {
        $rowId = $request->post('row_id');
        if (! empty($rowId)) {
            PostVisit::where('id', $rowId)->update(['contacted' => 1]);
            return response()->json(['status' => 200, 'post_id' => $rowId]);
        }

        return response()->json(['status' => 503, 'post_id' => $rowId]);
    }

    public function verifyTokenGoogleFloat(Request $request)
    {
        $token = $request->post('credential');
        if (empty($token)) {
            return response()->json(['success' => false, 'error' => 'Token missing'], 400);
        }

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $token,
        ]);

        if (! $response->ok()) {
            return response()->json(['success' => false, 'error' => 'Token invalido'], 400);
        }

        $payload = $response->json();
        $clientId = config('services.google.client_id');
        if (! empty($clientId) && isset($payload['aud']) && $payload['aud'] !== $clientId) {
            return response()->json(['success' => false, 'error' => 'Token invalido'], 400);
        }

        $userData = [
            'id' => $payload['sub'] ?? null,
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? 'Usuario Google',
            'picture' => $payload['picture'] ?? '',
            'logged_in' => true,
        ];

        if (! empty($userData['email'])) {
            UserFree::firstOrCreate(
                ['email' => $userData['email']],
                ['name' => $userData['name'], 'photo' => $userData['picture']]
            );
        }

        return response()->json(['success' => true, 'user' => $userData]);
    }

    public function sendEmailContactUser(Request $request)
    {
        $userEmail = $request->post('user_email');
        $userName = $request->post('user_name');
        $providerEmail = $request->post('provider_email');
        $message = $request->post('message');
        $propertyLink = $request->post('property_link');
        $propertyId = $request->post('property_id');

        $safeUserName = htmlspecialchars((string) $userName, ENT_QUOTES, 'UTF-8');
        $safeUserEmail = htmlspecialchars((string) $userEmail, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars((string) $propertyLink, ENT_QUOTES, 'UTF-8');

        $template = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Correo Electronico de Consulta de Propiedad</title>'
            . '<style>a{color:blue;}body{font-family: Arial, sans-serif;margin:0;padding:0;background-color:#f4f4f4;color:#333;line-height:1.6;}'
            . '.container{max-width:600px;margin:20px auto;padding:20px;background-color:#fff;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1);}p{margin-bottom:16px;}'
            . '.datos-contacto{margin-bottom:20px;padding:15px;border:1px solid #ddd;border-radius:8px;background-color:#f9f9f9;}'
            . '.datos-contacto strong{color:#0078d7;}</style></head><body><div class="container"><div class="datos-contacto">'
            . '<p><strong>Usuario:</strong> ' . $safeUserName . '</p>'
            . '<p><strong>Correo:</strong> ' . $safeUserEmail . '</p>'
            . '<p><strong>Mensaje:</strong> ' . $safeMessage . '</p>'
            . '<a href="' . $safeLink . '" target="_blank">' . $safeLink . '</a>'
            . '</div></div></body></html>';

        $emailService = app(EmailService::class);
        $sent = $emailService->send((string) $providerEmail, 'Un usuario se ha contactado contigo', $template);

        if (! empty($userEmail)) {
            $userFree = UserFree::where('email', $userEmail)->first();
            if ($userFree) {
                PsMessagesReceived::create([
                    'property_id' => $propertyId,
                    'user_free_id' => $userFree->id,
                    'message' => $message,
                ]);
            }
        }

        return response()->json(['status' => $sent ? 200 : 500]);
    }

    public function sendEmailShare(Request $request)
    {
        $userEmails = (string) $request->query('user_emails');
        $propertyLink = (string) $request->query('property_link');

        $safeLink = htmlspecialchars($propertyLink, ENT_QUOTES, 'UTF-8');
        $template = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Mira este inmueble</title>'
            . '<style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;background-color:#f4f4f4;margin:0;padding:0;}'
            . '.container{max-width:600px;margin:20px auto;padding:20px;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}'
            . '.header{text-align:center;padding-bottom:20px;border-bottom:1px solid #eee;}'
            . '.header h1{color:#0056b3;font-size:24px;margin:0;}.content{padding:20px 0;}'
            . '.content p{margin-bottom:15px;}.button-container{text-align:center;padding:20px 0;}'
            . '.button{display:inline-block;padding:12px 25px;background-color:#63c4ca;color:#ffffff;text-decoration:none;border-radius:5px;font-size:16px;}'
            . '.footer{text-align:center;padding-top:20px;border-top:1px solid #eee;font-size:12px;color:#777;}</style></head><body>'
            . '<div class="container"><div class="header"><h1>Mira este inmueble</h1></div><div class="content">'
            . '<p>Hola,</p><p>Queria compartir contigo un inmueble que podria interesarte.</p>'
            . '<p>Puedes ver todos los detalles en el siguiente enlace:</p>'
            . '<div class="button-container"><a href="' . $safeLink . '" class="button" style="color:white;">Ver Inmueble</a></div>'
            . '<p>Saludos,</p></div><div class="footer"><p>Este correo ha sido enviado porque un usuario compartio un inmueble.</p>'
            . '<p>&copy; ' . date('Y') . ' Kconecta.</p></div></div></body></html>';

        $emailService = app(EmailService::class);
        $emails = array_filter(array_map('trim', explode(',', $userEmails)));
        foreach ($emails as $email) {
            $emailService->send($email, 'Mira este inmueble', $template);
        }

        return response()->json(['status' => 'success', 'message' => 'Emails sent successfully']);
    }

    public function propertyStatsConfig(Request $request)
    {
        $propertyId = $request->post('_i');
        $ipAddress = $request->ip();

        $fieldModelMap = [
            'views_detail' => PsViewsDetail::class,
            'whatsapp_clicks' => PsWhatsappClicks::class,
            'views_search' => PsViewsSearch::class,
            'owner_calls' => PsOwnerCalls::class,
            'shared_facebook' => PsSharedFacebook::class,
            'link_copied' => PsLinkCopied::class,
            'shared_friends' => PsSharedFriends::class,
            'email_owner' => PsEmailOwner::class,
            'saved_favorite' => PsSavedFavorite::class,
        ];

        foreach ($fieldModelMap as $field => $modelClass) {
            if ($request->post($field)) {
                $existing = $modelClass::query()
                    ->where('property_id', $propertyId)
                    ->where('ip_address', $ipAddress)
                    ->first();

                if (! $existing) {
                    $modelClass::create([
                        'property_id' => $propertyId,
                        'ip_address' => $ipAddress,
                        'counter' => 1,
                    ]);
                }

                break;
            }
        }

        return response()->json(['status' => 200]);
    }
}
