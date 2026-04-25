<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Internal\OrchestratorController;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/properties', [ApiController::class, 'searchProperties']);
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

// Mobile app auth + agent endpoints
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test-now', fn () => response()->json(['message' => 'API is working']));

Route::middleware('orchestrator.key')->prefix('orchestrate')->group(function () {
    Route::post('/plan', [OrchestratorController::class, 'plan']);
    Route::post('/run', [OrchestratorController::class, 'run']);
    Route::post('/merge', [OrchestratorController::class, 'merge']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/agent/property-types', [PropertyApiController::class, 'propertyTypes']);
    Route::get('/agent/property-form-catalogs', [PropertyApiController::class, 'propertyFormCatalogs']);
    Route::delete('/agent/property-images/{imageId}', [PropertyApiController::class, 'destroyMoreImage']);
    Route::apiResource('/agent/properties', PropertyApiController::class);
});
