<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// Route::prefix('billing')->middleware('auth:sanctum')->group(function () {
Route::prefix('billing')->group(function () {
    Route::get('/status', [BillingController::class, 'checkStatus']);
    Route::get('/last-voucher/{ptoVta}/{cbteTipo}', [BillingController::class, 'getLastVoucher']);
    Route::post('/invoice', [BillingController::class, 'createInvoice']);
    Route::get('/invoice/{id}/pdf', [BillingController::class, 'downloadPdf']);
});
