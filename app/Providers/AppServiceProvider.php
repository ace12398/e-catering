<?php

namespace App\Providers;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\KitchenTask;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use App\Models\WorkspacePreference;
use App\Observers\DeliveryObserver;
use App\Observers\InvoiceObserver;
use App\Observers\KitchenTaskObserver;
use App\Observers\OrderObserver;
use App\Observers\ProfileObserver;
use App\Observers\UserObserver;
use App\Observers\WorkspaceObserver;
use App\Repositories\AvatarRepository;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\AvatarRepositoryInterface;
use App\Repositories\Contracts\DeliveryRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\KitchenRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Repositories\Eloquent\AnalyticsRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\DeliveryRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\KitchenRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WorkspaceRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WorkspaceRepositoryInterface;
use App\Repositories\ProfileRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Core Contracts & Interfaces Bindings
        $this->app->bind(WorkspaceRepositoryInterface::class, WorkspaceRepository::class);
        $this->app->bind(\App\Repositories\Contracts\WorkspaceRepositoryInterface::class, WorkspaceRepository::class);

        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(\App\Repositories\Contracts\CartRepositoryInterface::class, CartRepository::class);

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(\App\Repositories\Contracts\UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ProfileRepositoryInterface::class, ProfileRepository::class);

        $this->app->bind(AvatarRepositoryInterface::class, AvatarRepository::class);
        
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\OrderRepositoryInterface::class, OrderRepository::class);

        $this->app->bind(KitchenRepositoryInterface::class, KitchenRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\KitchenRepositoryInterface::class, KitchenRepository::class);

        $this->app->bind(DeliveryRepositoryInterface::class, DeliveryRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\DeliveryRepositoryInterface::class, DeliveryRepository::class);

        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\InvoiceRepositoryInterface::class, InvoiceRepository::class);

        $this->app->bind(AnalyticsRepositoryInterface::class, AnalyticsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        // Register Model Observers
        User::observe(UserObserver::class);
        Profile::observe(ProfileObserver::class);
        WorkspacePreference::observe(WorkspaceObserver::class);
        Order::observe(OrderObserver::class);
        KitchenTask::observe(KitchenTaskObserver::class);
        Delivery::observe(DeliveryObserver::class);
        Invoice::observe(InvoiceObserver::class);

        // Adaptive UI View Composer — shares context to all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $engine = app(\App\Services\AdaptiveEngine::class);
                $ctx = $engine->getContext(auth()->user());
                $view->with('adaptiveCtx', $ctx);
                $view->with('sidebarSections', $engine->getSidebarSections($ctx));
            }
        });
    }
}
