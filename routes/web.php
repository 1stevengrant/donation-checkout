<?php

use App\Http\Middleware\VerifyCsrfToken;
use Ghijk\DonationCheckout\Http\Controllers\StartDonationController;
use Illuminate\Support\Facades\Route;

Route::prefix('donation-checkout')->group(function () {
    Route::post('start', StartDonationController::class)
        ->withoutMiddleware([VerifyCsrfToken::class]);
});
