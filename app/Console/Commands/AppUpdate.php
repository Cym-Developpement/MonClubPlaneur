<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AppUpdate extends Command
{
    protected $signature = 'app:update
                            {--no-migrate : Ne pas exécuter les migrations}
                            {--no-cache : Ne pas reconstruire les caches}';

    protected $description = 'Met à jour l\'application depuis GitHub (git pull + migrate + cache)';

    public function handle()
    {
        $this->info('=== Mise à jour de l\'application ===');
        $this->newLine();

        $stashed = $this->gitStash();

        if (! $this->step('git pull', ['git', 'pull'])) {
            if ($stashed) {
                $this->gitStashPop();
            }
            return 1;
        }

        if ($stashed) {
            $this->gitStashPop();
        }

        $composer = base_path('composer.phar');

        if (! $this->step('composer install', ['php', $composer, 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'])) {
            return 1;
        }

        if (! $this->step('composer update', ['php', $composer, 'update', '--no-dev', '--optimize-autoloader', '--no-interaction'])) {
            return 1;
        }

        if (! $this->option('no-migrate')) {
            if (! $this->step('migrations', ['php', 'artisan', 'migrate', '--force'])) {
                return 1;
            }
        }

        if (! $this->option('no-cache')) {
            if (! $this->step('config:cache', ['php', 'artisan', 'config:cache'])) {
                return 1;
            }
            if (! $this->step('route:cache', ['php', 'artisan', 'route:cache'])) {
                return 1;
            }
            if (! $this->step('view:cache', ['php', 'artisan', 'view:cache'])) {
                return 1;
            }
        }

        $this->newLine();
        $this->info('✓ Application mise à jour avec succès.');
        return 0;
    }

    private function gitStash(): bool
    {
        $process = new Process(['git', 'stash'], base_path());
        $process->setTimeout(30);
        $process->run();

        $stashed = str_contains($process->getOutput(), 'Saved working directory');
        if ($stashed) {
            $this->line('  → git stash : modifications locales mises de côté.');
        }

        return $stashed;
    }

    private function gitStashPop(): void
    {
        $process = new Process(['git', 'stash', 'pop'], base_path());
        $process->setTimeout(30);
        $process->run();

        if ($process->isSuccessful()) {
            $this->line('  ✓ git stash pop : modifications locales restaurées.');
        } else {
            $this->warn('  ⚠ git stash pop a échoué — vérifiez manuellement.');
        }
    }

    private function step(string $label, array $command): bool
    {
        $this->line("  → {$label}...");

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->line('    ' . trim($buffer));
        });

        if (! $process->isSuccessful()) {
            $this->error("  ✗ Échec : {$label}");
            $this->line($process->getErrorOutput());
            return false;
        }

        $this->line("  ✓ {$label}");
        return true;
    }
}
