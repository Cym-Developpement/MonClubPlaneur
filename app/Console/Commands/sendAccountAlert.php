<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class sendAccountAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendAccountAlert {--force : Envoyer sans demander de confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des alertes au utilisateurs avec un compte a découvert';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all()->filter(fn($u) => $u->real_amount_account < 0 && $u->state == 1 && !$u->isAttr('user:technique'));

        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur avec un compte débiteur.');
            return 0;
        }

        $this->info($users->count() . ' utilisateur(s) avec un compte débiteur :');
        foreach ($users as $user) {
            $this->line('  - ' . $user->name . ' : ' . number_format($user->real_amount_account / 100, 2) . ' €');
        }

        if (!$this->option('force') && !$this->confirm('Envoyer les emails ?')) {
            $this->info('Annulé.');
            return 0;
        }

        User::sendAccountAlertNotification();
        $this->info('Emails envoyés à ' . $users->count() . ' utilisateur(s).');
        return 0;
    }
}
