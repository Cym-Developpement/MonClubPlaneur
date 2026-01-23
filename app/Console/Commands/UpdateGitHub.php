<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UpdateGitHub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:update 
                            {action=pull : Action à effectuer (pull, push, sync, status)}
                            {--branch= : Branche spécifique à utiliser}
                            {--commit : Effectuer un commit avant le push}
                            {--message= : Message de commit (requis si --commit est utilisé)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour le dépôt GitHub (pull, push, sync ou status)';

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
        $action = $this->argument('action');
        $branch = $this->option('branch') ?: $this->getCurrentBranch();

        $this->info("Action: {$action}");
        $this->info("Branche: {$branch}");

        switch ($action) {
            case 'pull':
                return $this->pull($branch);
            case 'push':
                return $this->push($branch);
            case 'sync':
                return $this->sync($branch);
            case 'status':
                return $this->status();
            default:
                $this->error("Action inconnue: {$action}");
                $this->info("Actions disponibles: pull, push, sync, status");
                return 1;
        }
    }

    /**
     * Récupère la branche actuelle
     *
     * @return string
     */
    private function getCurrentBranch()
    {
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->setWorkingDirectory(base_path());
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Impossible de déterminer la branche actuelle');
            return 'main';
        }

        return trim($process->getOutput());
    }

    /**
     * Affiche le statut du dépôt
     *
     * @return int
     */
    private function status()
    {
        $this->info('=== Statut du dépôt ===');
        
        // Statut git
        $this->executeGitCommand(['git', 'status'], 'Statut Git');
        
        // Branche actuelle
        $branch = $this->getCurrentBranch();
        $this->info("Branche actuelle: {$branch}");
        
        // Derniers commits
        $this->info("\n=== Derniers commits ===");
        $this->executeGitCommand(['git', 'log', '--oneline', '-5'], 'Historique');
        
        return 0;
    }

    /**
     * Effectue un pull depuis GitHub
     *
     * @param string $branch
     * @return int
     */
    private function pull($branch)
    {
        $this->info("Récupération des modifications depuis GitHub (branche: {$branch})...");
        
        try {
            $this->executeGitCommand(['git', 'fetch', 'origin'], 'Fetch');
            $this->executeGitCommand(['git', 'pull', 'origin', $branch], 'Pull');
            
            $this->info('✓ Pull réussi');
            return 0;
        } catch (ProcessFailedException $e) {
            $this->error('✗ Erreur lors du pull: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Effectue un push vers GitHub
     *
     * @param string $branch
     * @return int
     */
    private function push($branch)
    {
        // Vérifier s'il y a des modifications non commitées
        $statusProcess = new Process(['git', 'status', '--porcelain']);
        $statusProcess->setWorkingDirectory(base_path());
        $statusProcess->run();
        $hasChanges = !empty(trim($statusProcess->getOutput()));

        if ($hasChanges && $this->option('commit')) {
            $message = $this->option('message');
            
            if (!$message) {
                $this->error('Le message de commit est requis avec l\'option --commit');
                return 1;
            }

            $this->info('Ajout des modifications...');
            $this->executeGitCommand(['git', 'add', '.'], 'Add');
            
            $this->info('Création du commit...');
            $this->executeGitCommand(['git', 'commit', '-m', $message], 'Commit');
        } elseif ($hasChanges && !$this->option('commit')) {
            $this->warn('Des modifications non commitées sont présentes.');
            $this->warn('Utilisez --commit --message="votre message" pour les commiter avant le push.');
            
            if (!$this->confirm('Voulez-vous continuer le push sans commit ?', false)) {
                return 1;
            }
        }

        $this->info("Envoi des modifications vers GitHub (branche: {$branch})...");
        
        try {
            $this->executeGitCommand(['git', 'push', 'origin', $branch], 'Push');
            
            $this->info('✓ Push réussi');
            return 0;
        } catch (ProcessFailedException $e) {
            $this->error('✗ Erreur lors du push: ' . $e->getMessage());
            $this->warn('Vérifiez que vous avez les droits d\'écriture et que la branche distante existe.');
            return 1;
        }
    }

    /**
     * Synchronise le dépôt (pull puis push)
     *
     * @param string $branch
     * @return int
     */
    private function sync($branch)
    {
        $this->info("Synchronisation du dépôt (branche: {$branch})...");
        
        // D'abord pull
        $pullResult = $this->pull($branch);
        if ($pullResult !== 0) {
            $this->warn('Le pull a échoué, mais on continue avec le push...');
        }
        
        // Ensuite push
        $pushResult = $this->push($branch);
        
        if ($pullResult === 0 && $pushResult === 0) {
            $this->info('✓ Synchronisation réussie');
            return 0;
        } else {
            $this->warn('⚠ Synchronisation partielle (vérifiez les erreurs ci-dessus)');
            return 1;
        }
    }

    /**
     * Exécute une commande git et affiche le résultat
     *
     * @param array $command
     * @param string $label
     * @return void
     * @throws ProcessFailedException
     */
    private function executeGitCommand(array $command, $label = '')
    {
        $process = new Process($command);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300); // 5 minutes timeout
        
        $process->run(function ($type, $buffer) {
            $this->line($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
