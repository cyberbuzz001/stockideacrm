<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ExternalLeadController;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook', [WhatsAppWebhookController::class, 'handle']);
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/leads', ExternalLeadController::class);
});
