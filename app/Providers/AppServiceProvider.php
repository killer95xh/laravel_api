<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('alpha_num_with_spaces', function ($attribute, $value) {
            // return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
            return preg_match('/^[a-zA-Z0-9\s\x{00C0}-\x{1EF9}]+$/u', $value);
        });
    }
}
