<?php

namespace App\Providers;

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
        Relation::morphMap([
            'Retail Office' => \App\Models\ListingRelated\RetailOfficeListing::class,
            'Industrial Warehouse' => \App\Models\ListingRelated\WarehouseListing::class,
            'Industrial Lot' => \App\Models\ListingRelated\IndLotListing::class,
            'Commercial Lot'=> \App\Models\ListingRelated\CommLotListing::class,
            'Office Space'=> \App\Models\ListingRelated\OfficeSpaceListing::class,
        ]);
    }
}
