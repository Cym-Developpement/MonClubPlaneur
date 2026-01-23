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
    protected $signature = 'sendAccountAlert';

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
        $users = User::all();
        foreach ($users as $user) {
            $amount = $user->real_amount_account;
            if ($amount < 0) {
                $this->error($user->name.' : '.$amount);
            }
        }
        return 0;
    }
}
