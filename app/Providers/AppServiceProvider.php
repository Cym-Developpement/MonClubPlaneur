<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\refundCategory;
use App\Models\User;

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
        //
        $refundCategory = refundCategory::all();
        View::share('refundCategory', $refundCategory);

        
        \Response::macro('csv', function ($content, $name) {

            $headers = [
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$name.'.csv"',
            ];

            return \Response::make($content, 200, $headers);

        });

        \Response::macro('txt', function ($content, $name) {

            $headers = [
                'Content-type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="'.$name.'.txt"',
            ];

            return \Response::make($content, 200, $headers);

        });
    }
}
