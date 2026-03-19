<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\PropertyController as MobilePropertyController;
use App\Http\Controllers\Mobile\ProviderController as MobileProviderController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::middleware('mobile.auth')->get('/auth/me', [AuthController::class, 'me']);

Route::get('/properties', [MobilePropertyController::class, 'index']);
Route::get('/services', [ApiController::class, 'searchServices']);
Route::get('/properties_for_map', [ApiController::class, 'dataPropertiesForMap']);
Route::get('/services_for_map', [ApiController::class, 'dataServicesForMap']);
Route::get('/delete_more_image', [ApiController::class, 'deleteMoreImage']);
Route::post('/visitor/save', [ApiController::class, 'visitorRegister']);
Route::post('/visitor/contacted', [ApiController::class, 'visitorContactedUpdate']);
Route::post('/google/user/verify_token_google', [ApiController::class, 'verifyTokenGoogleFloat']);
Route::post('/send/message/email_to_provider', [ApiController::class, 'sendEmailContactUser']);
Route::get('/send/message/email_share', [ApiController::class, 'sendEmailShare']);
Route::post('/property_stats/register', [ApiController::class, 'propertyStatsConfig']);

Route::middleware('mobile.auth')->group(function () {
    Route::post('/properties', [MobilePropertyController::class, 'create']);
    Route::get('/properties/summary', [MobilePropertyController::class, 'summary']);
    Route::get('/properties/priorities/queue', [MobilePropertyController::class, 'priorityQueue']);
    Route::post('/properties/priorities/queue/{queueItemId}/complete', [MobilePropertyController::class, 'completePriorityQueueItem']);
    Route::get('/properties/{id}', [MobilePropertyController::class, 'show']);
    Route::patch('/properties/{id}', [MobilePropertyController::class, 'update']);
    Route::post('/properties/{id}/reserve', [MobilePropertyController::class, 'reserve']);
    Route::post('/properties/{id}/release', [MobilePropertyController::class, 'release']);
    Route::get('/properties/{id}/provider-candidates', [MobilePropertyController::class, 'providerCandidates']);
    Route::post('/properties/{id}/assign-provider', [MobilePropertyController::class, 'assignProvider']);
    Route::get('/properties/{id}/assignment-context', [MobilePropertyController::class, 'assignmentContext']);

    Route::get('/providers', [MobileProviderController::class, 'index']);
    Route::get('/providers/{id}', [MobileProviderController::class, 'show']);
    Route::get('/providers/{id}/availability', [MobileProviderController::class, 'availability']);
    Route::patch('/providers/{id}/availability', [MobileProviderController::class, 'updateAvailability']);
});
