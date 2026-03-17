<?php

use Illuminate\Support\Facades\Route;
use Ghijk\DonationCheckout\Http\Controllers\StartDonationController;

Route::prefix('donation-checkout')->group(function () {
    Route::post('start', StartDonationController::class)
        ->middleware(['throttle:10,1']);
});
