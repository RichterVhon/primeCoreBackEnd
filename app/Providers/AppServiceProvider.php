<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Relation::morphMap([
            'Industrial Lot' => \App\Models\ListingRelated\IndLotListing::class,
            'Industrial Warehouse' => \App\Models\ListingRelated\WarehouseListing::class,
            'Commercial Lot' => \App\Models\ListingRelated\CommLotListing::class,
            'Retail Office' => \App\Models\ListingRelated\RetailOfficeListing::class,
            'Office Space' => \App\Models\ListingRelated\OfficeSpaceListing::class,
        ]);
    }
}
