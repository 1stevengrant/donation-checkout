<?php

use Illuminate\Support\Facades\Route;
use Ghijk\DonationCheckout\Http\Controllers\ThankYouController;
use Ghijk\DonationCheckout\Http\Controllers\MagicLinkController;
use Ghijk\DonationCheckout\Http\Controllers\DonorPortalController;
use Ghijk\DonationCheckout\Http\Controllers\StartDonationController;
use Ghijk\DonationCheckout\Http\Controllers\DonorCancelSubscriptionController;

Route::prefix('donation-checkout')->group(function () {
    Route::post('start', StartDonationController::class)
        ->middleware(['throttle:10,1']);

    Route::get('thank-you', ThankYouController::class)
        ->name('donation-checkout.thank-you');

    Route::post('magic-link', [MagicLinkController::class, 'store'])
        ->middleware(['throttle:5,1'])
        ->name('donation-checkout.magic-link.store');

    Route::get('magic-link/verify', [MagicLinkController::class, 'verify'])
        ->middleware(['signed'])
        ->name('donation-checkout.magic-link.verify');

    Route::get('portal', [DonorPortalController::class, 'index'])
        ->middleware(['auth'])
        ->name('donation-checkout.portal');

    Route::post('portal/subscriptions/{id}/cancel', DonorCancelSubscriptionController::class)
        ->middleware(['auth'])
        ->name('donation-checkout.portal.subscriptions.cancel');
});
