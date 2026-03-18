<?php

use Illuminate\Support\Facades\Route;
use Ghijk\DonationCheckout\Http\Controllers\CP\RefundsController;
use Ghijk\DonationCheckout\Http\Controllers\CP\DonationsController;
use Ghijk\DonationCheckout\Http\Controllers\CP\DonorProfileController;
use Ghijk\DonationCheckout\Http\Controllers\CP\PauseSubscriptionController;
use Ghijk\DonationCheckout\Http\Controllers\CP\CancelSubscriptionController;
use Ghijk\DonationCheckout\Http\Controllers\CP\ResumeSubscriptionController;

Route::prefix('donation-checkout')->group(function () {
    Route::get('/', [DonationsController::class, 'index'])->name('donation-checkout.index');
    Route::get('/donor/{email}', [DonorProfileController::class, 'show'])->name('donation-checkout.donor');

    Route::delete('/subscriptions/{id}', CancelSubscriptionController::class)
        ->name('donation-checkout.subscriptions.cancel');
    Route::put('/subscriptions/{id}/pause', PauseSubscriptionController::class)
        ->name('donation-checkout.subscriptions.pause');
    Route::put('/subscriptions/{id}/resume', ResumeSubscriptionController::class)
        ->name('donation-checkout.subscriptions.resume');

    Route::post('/payments/{id}/refund', [RefundsController::class, 'store'])
        ->name('donation-checkout.payments.refund');
});
