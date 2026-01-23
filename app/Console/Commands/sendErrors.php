<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class sendErrors extends Command
{

    protected $developerMail = 'yann@cymdev.com';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendErrors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send errors log file to developer.';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function send($file)
    {
        $this->info('Envoi du message vers '.$this->developerMail);

        $data = [];
        $data['to'] = $this->developerMail;
        $data['subject'] = 'LOG ERREURS '.env('APP_NAME');
        $data['content'] = 'Nouveau fichier de log contenant des erreurs pour '.env('APP_NAME');
        $data['fileData'] = file_get_contents($file);
        $data['filename'] = 'laravel-'.date('Y-m-d').'.log';

        Mail::raw($data['content'], function($message) use ($data) {

            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
            $message->attachData($data['fileData'], $data['filename'], [
                    'mime' => 'application/txt',
                ]);

        });

        if (count(Mail::failures()) > 0) {
            $this->info("Error! Email did not send.");
        }

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $folder = str_replace('/public', '', getcwd()).'/storage/logs/';
        $dayFile = 'laravel-'.date('Y-m-d').'.log';
        if (is_file($folder.$dayFile)) {
            $this->info('1 fichier a envoyé a '.$this->developerMail.' : '.$folder.$dayFile);
            $this->send($folder.$dayFile);
        } else {
            $this->info('Aucun fichier a envoyé');
        }
        
    }
}
