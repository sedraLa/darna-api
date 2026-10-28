<?php

namespace App\Providers;

use App\Models\Property;
use App\Models\Scopes\AvailableScope;
use App\Policies\PropertyPolicy;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PropertyService::class);
    }

    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);

        // Resolve properties without the global AvailableScope so that
        // authenticated users (office, admin) can act on non-available properties.
        // Authorization (policy) is still enforced on the resolved model.
        Route::bind('property', function (string $value) {
            return Property::withoutGlobalScope(AvailableScope::class)
                ->findOrFail($value);
        });
    }
}
