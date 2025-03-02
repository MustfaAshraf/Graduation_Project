<?php

namespace App\Providers;

use Kreait\Firebase\Factory;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase', function () {
            $serviceAccount = env('FIREBASE_CREDENTIALS');

            return (new Factory)
                ->withServiceAccount(base_path($serviceAccount));
        });
    }

    public function boot()
    {
        //
    }
}