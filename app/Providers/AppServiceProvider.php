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
        try {
            $refundCategory = refundCategory::all();
            View::share('refundCategory', $refundCategory);
        } catch (\Exception $e) {
            View::share('refundCategory', collect());
        }

        $gitMessage = '';
        $gitDate    = '';
        try {
            if (function_exists('exec')) {
                exec('git log -1 --pretty="%s" 2>/dev/null', $msgOut, $rc);
                exec('git log -1 --pretty="%ci" 2>/dev/null', $dateOut);
                $gitMessage = trim($msgOut[0]  ?? '');
                $gitDate    = trim(substr($dateOut[0] ?? '', 0, 10));
            }
        } catch (\Throwable $e) {}
        View::share('gitCommitMessage', $gitMessage);
        View::share('gitCommitDate',    $gitDate);

        
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
