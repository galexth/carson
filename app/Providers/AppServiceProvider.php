<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        \Stripe\Stripe::setLogger(\Log::channel('stripe'));
    }
}
