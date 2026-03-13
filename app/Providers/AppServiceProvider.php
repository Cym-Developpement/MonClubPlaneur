<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Events\MessageSending;
use App\Models\parametre;
use App\Models\refundCategory;
use App\Models\User;
use App\Models\transaction;
use App\Models\flight;
use App\Models\usersAttributes;
use App\Observers\TransactionObserver;
use App\Observers\UserObserver;
use App\Observers\FlightObserver;
use App\Observers\UsersAttributesObserver;

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
        transaction::observe(TransactionObserver::class);
        User::observe(UserObserver::class);
        flight::observe(FlightObserver::class);
        usersAttributes::observe(UsersAttributesObserver::class);

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

        
        Event::listen(MessageSending::class, function (MessageSending $event) {
            try {
                $testMode = parametre::getValue('mail-mode_test', false);
            } catch (\Exception $e) {
                return true;
            }

            if ($testMode) {
                $message = $event->message;
                $to = implode(', ', array_map(fn ($a) => $a->getAddress(), $message->getTo()));
                $subject = $message->getSubject();
                $body = $message->getHtmlBody() ?? $message->getTextBody() ?? '';

                Log::channel('stack')->info('[EMAIL MODE TEST] Email intercepté', [
                    'to'      => $to,
                    'subject' => $subject,
                    'body'    => $body,
                ]);

                return false;
            }

            return true;
        });

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
