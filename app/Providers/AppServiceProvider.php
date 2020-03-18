<?php

namespace App\Providers;

use App\Policies\TaskPolicy;
use App\Task;
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
        \Stripe\Stripe::setApiKey(config('services.stripe.key'));

        \Gate::policy(Task::class, TaskPolicy::class);
    }
}
