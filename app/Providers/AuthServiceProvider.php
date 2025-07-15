<?php

namespace App\Providers;

use App\Policies\ListingPolicy;
use App\Models\ListingRelated\Listing;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider; // ✅ Switch to Laravel's base class

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Listing::class => ListingPolicy::class,
    ];

    /**
     * Register any application authentication/authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // ✅ This method now exists because we're extending the right class
    }
}
