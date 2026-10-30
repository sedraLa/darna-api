<?php

namespace App\Providers;

use App\Models\Property;
use App\Models\Scopes\AvailableScope;
use App\Policies\PropertyPolicy;
use App\Services\PropertyCacheService;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PropertyCacheService::class);
        $this->app->singleton(PropertyService::class);
    }

    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);
        Route::bind('property', function (string $value) {
            return Property::withoutGlobalScope(AvailableScope::class)
                ->findOrFail($value);
        });
    }
}
