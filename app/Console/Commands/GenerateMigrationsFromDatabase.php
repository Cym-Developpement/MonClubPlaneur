<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GenerateMigrationsFromDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:generate 
                            {--tables= : Tables spécifiques à générer (séparées par des virgules)}
                            {--ignore= : Tables à ignorer (séparées par des virgules)}
                            {--path= : Chemin où sauvegarder les migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les migrations à partir de la base de données existante';

    /**
     * Tables à ignorer par défaut
     */
    protected $defaultIgnoreTables = ['migrations', 'sqlite_sequence'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Génération des migrations à partir de la base de données...');

        // Récupérer toutes les tables
        $tables = $this->getTables();
        
        if (empty($tables)) {
            $this->error('Aucune table trouvée dans la base de données.');
            return 1;
        }

        $this->info('Tables trouvées: ' . implode(', ', $tables));

        // Filtrer les tables selon les options
        $tablesToGenerate = $this->filterTables($tables);

        if (empty($tablesToGenerate)) {
            $this->warn('Aucune table à générer après filtrage.');
            return 0;
        }

        $path = $this->option('path') ?: database_path('migrations');
        
        $generated = 0;
        foreach ($tablesToGenerate as $table) {
            if ($this->generateMigrationForTable($table, $path)) {
                $generated++;
            }
        }

        $this->info("✓ {$generated} migration(s) générée(s) avec succès!");
        return 0;
    }

    /**
     * Récupère toutes les tables de la base de données
     */
    protected function getTables()
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            return array_map(function($table) {
                return $table->name;
            }, $tables);
        } elseif ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $tables = DB::select("SELECT TABLE_NAME as name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$database]);
            return array_map(function($table) {
                return $table->name;
            }, $tables);
        } elseif ($driver === 'pgsql') {
            $tables = DB::select("SELECT tablename as name FROM pg_tables WHERE schemaname = 'public'");
            return array_map(function($table) {
                return $table->name;
            }, $tables);
        }

        return [];
    }

    /**
     * Filtre les tables selon les options
     */
    protected function filterTables(array $tables)
    {
        $ignore = array_merge(
            $this->defaultIgnoreTables,
            $this->option('ignore') ? explode(',', $this->option('ignore')) : []
        );

        $ignore = array_map('trim', $ignore);

        // Si des tables spécifiques sont demandées
        if ($this->option('tables')) {
            $requested = array_map('trim', explode(',', $this->option('tables')));
            return array_intersect($tables, $requested);
        }

        // Sinon, retourner toutes les tables sauf celles à ignorer
        return array_filter($tables, function($table) use ($ignore) {
            return !in_array($table, $ignore);
        });
    }

    /**
     * Génère une migration pour une table
     */
    protected function generateMigrationForTable($tableName, $path)
    {
        try {
            $columns = $this->getTableColumns($tableName);
            $indexes = $this->getTableIndexes($tableName);
            $foreignKeys = $this->getTableForeignKeys($tableName);

            $className = 'Create' . Str::studly(Str::singular($tableName)) . 'Table';
            $fileName = date('Y_m_d_His') . '_create_' . Str::snake($tableName) . '_table.php';
            $filePath = $path . '/' . $fileName;

            // Vérifier si le fichier existe déjà
            if (file_exists($filePath)) {
                $this->warn("  ⚠ Migration existe déjà pour {$tableName}, ignorée.");
                return false;
            }

            $content = $this->generateMigrationContent($tableName, $className, $columns, $indexes, $foreignKeys);
            
            // Créer le répertoire s'il n'existe pas
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($filePath, $content);
            $this->line("  ✓ Migration générée: {$fileName}");
            
            return true;
        } catch (\Exception $e) {
            $this->error("  ✗ Erreur pour {$tableName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les colonnes d'une table
     */
    protected function getTableColumns($tableName)
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $columns = DB::select("PRAGMA table_info({$tableName})");
            $result = [];
            foreach ($columns as $column) {
                $isPrimary = $column->pk == 1;
                $isAutoIncrement = $isPrimary && strtoupper($column->type) === 'INTEGER';
                
                $result[] = [
                    'name' => $column->name,
                    'type' => $this->mapSqliteType($column->type),
                    'nullable' => !$column->notnull,
                    'default' => $column->dflt_value,
                    'primary' => $isPrimary,
                    'autoIncrement' => $isAutoIncrement,
                    'rawType' => $column->type,
                ];
            }
            return $result;
        }

        return [];
    }

    /**
     * Mappe les types SQLite vers les types Laravel
     */
    protected function mapSqliteType($type)
    {
        $type = strtoupper(trim($type));
        
        if (preg_match('/INT(EGER)?/i', $type)) {
            return 'integer';
        } elseif (preg_match('/TEXT/i', $type)) {
            return 'text';
        } elseif (preg_match('/VARCHAR\((\d+)\)/i', $type, $matches)) {
            return 'string';
        } elseif (preg_match('/CHAR\((\d+)\)/i', $type, $matches)) {
            return 'string';
        } elseif (preg_match('/REAL|FLOAT|DOUBLE|DECIMAL|NUMERIC/i', $type)) {
            return 'float';
        } elseif (preg_match('/BLOB/i', $type)) {
            return 'binary';
        } elseif (preg_match('/BOOLEAN/i', $type)) {
            return 'boolean';
        } elseif (preg_match('/DATETIME|TIMESTAMP/i', $type)) {
            return 'timestamp';
        } elseif (preg_match('/DATE/i', $type)) {
            return 'date';
        } elseif (preg_match('/TIME/i', $type)) {
            return 'time';
        }
        
        return 'string';
    }

    /**
     * Récupère les index d'une table
     */
    protected function getTableIndexes($tableName)
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$tableName})");
            $result = [];
            foreach ($indexes as $index) {
                // Ignorer les index automatiques de SQLite
                if (strpos($index->name, 'sqlite_autoindex_') === 0) {
                    continue;
                }
                
                $indexInfo = DB::select("PRAGMA index_info({$index->name})");
                $columns = array_map(function($info) {
                    return $info->name;
                }, $indexInfo);
                $result[] = [
                    'name' => $index->name,
                    'columns' => $columns,
                    'unique' => $index->unique == 1,
                ];
            }
            return $result;
        }

        return [];
    }

    /**
     * Récupère les clés étrangères d'une table
     */
    protected function getTableForeignKeys($tableName)
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list({$tableName})");
            $result = [];
            foreach ($foreignKeys as $fk) {
                $result[] = [
                    'column' => $fk->from,
                    'references' => $fk->table,
                    'on' => $fk->to,
                    'onDelete' => $fk->on_delete ?? 'restrict',
                    'onUpdate' => $fk->on_update ?? 'restrict',
                ];
            }
            return $result;
        }

        return [];
    }

    /**
     * Génère le contenu de la migration
     */
    protected function generateMigrationContent($tableName, $className, $columns, $indexes, $foreignKeys)
    {
        $stub = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{TABLE_NAME}', function (Blueprint $table) {
{COLUMNS}
{INDEXES}
{FOREIGN_KEYS}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{TABLE_NAME}');
    }
};
PHP;

        $columnsCode = $this->generateColumnsCode($columns);
        $indexesCode = $this->generateIndexesCode($indexes);
        $foreignKeysCode = $this->generateForeignKeysCode($foreignKeys);

        // Construire le contenu en évitant les lignes vides inutiles
        $body = $columnsCode;
        if (!empty($indexesCode)) {
            $body .= $indexesCode;
        }
        if (!empty($foreignKeysCode)) {
            $body .= $foreignKeysCode;
        }

        $content = str_replace('{TABLE_NAME}', $tableName, $stub);
        $content = str_replace('{COLUMNS}', $body, $content);
        $content = str_replace('{INDEXES}', '', $content);
        $content = str_replace('{FOREIGN_KEYS}', '', $content);

        return $content;
    }

    /**
     * Génère le code pour les colonnes
     */
    protected function generateColumnsCode($columns)
    {
        $code = [];
        
        $hasTimestamps = false;
        $hasId = false;
        
        foreach ($columns as $column) {
            $line = "            ";
            
            // Gérer les colonnes primaires auto-incrémentées
            if ($column['primary'] && isset($column['autoIncrement']) && $column['autoIncrement']) {
                if ($column['name'] === 'id') {
                    $line .= "\$table->id();";
                    $code[] = $line;
                    $hasId = true;
                    continue;
                } else {
                    $line .= "\$table->bigIncrements('{$column['name']}');";
                    $code[] = $line;
                    continue;
                }
            }
            
            // Gérer les timestamps
            if ($column['name'] === 'created_at' || $column['name'] === 'updated_at') {
                $hasTimestamps = true;
                continue; // On les ajoutera à la fin
            }
            
            // Construire la ligne de colonne
            $method = $this->getColumnMethod($column);
            $line .= "\$table->{$method}";
            
            $params = [];
            if ($column['name'] !== 'id') {
                $params[] = "'{$column['name']}'";
            }
            
            if (isset($column['length']) && $column['length']) {
                $params[] = $column['length'];
            }
            
            if (count($params) > 0) {
                $line .= "(" . implode(", ", $params) . ")";
            } else {
                $line .= "()";
            }
            
            // Ajouter nullable
            if ($column['nullable']) {
                $line .= "->nullable()";
            }
            
            // Ajouter default
            if ($column['default'] !== null) {
                $default = $this->formatDefaultValue($column['default']);
                $line .= "->default({$default})";
            }
            
            $line .= ";";
            $code[] = $line;
        }
        
        // Ajouter timestamps si nécessaire
        if ($hasTimestamps) {
            $code[] = "            \$table->timestamps();";
        }
        
        return implode("\n", $code);
    }

    /**
     * Détermine la méthode de colonne à utiliser
     */
    protected function getColumnMethod($column)
    {
        $name = strtolower($column['name']);
        $type = $column['type'];
        
        // Cas spéciaux
        if ($name === 'remember_token') {
            return 'rememberToken';
        }
        
        if ($name === 'email' && strpos($name, 'email') !== false) {
            return 'string';
        }
        
        // Mapping des types
        switch ($type) {
            case 'integer':
                return 'integer';
            case 'string':
                return 'string';
            case 'text':
                return 'text';
            case 'float':
            case 'double':
                return 'float';
            case 'boolean':
                return 'boolean';
            case 'datetime':
            case 'timestamp':
                return 'timestamp';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'binary':
                return 'binary';
            default:
                return 'string';
        }
    }

    /**
     * Formate une valeur par défaut
     */
    protected function formatDefaultValue($value)
    {
        if ($value === null) {
            return 'null';
        }
        
        // Gérer les valeurs NULL en string
        if (strtoupper(trim($value)) === 'NULL') {
            return 'null';
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        if (strtoupper($value) === 'CURRENT_TIMESTAMP') {
            return 'now()';
        }
        
        return "'" . addslashes($value) . "'";
    }

    /**
     * Génère le code pour les index
     */
    protected function generateIndexesCode($indexes)
    {
        if (empty($indexes)) {
            return "";
        }
        
        $code = [];
        foreach ($indexes as $index) {
            $method = $index['unique'] ? 'unique' : 'index';
            
            if (count($index['columns']) === 1) {
                $code[] = "            \$table->{$method}('{$index['columns'][0]}');";
            } else {
                $columns = "'" . implode("', '", $index['columns']) . "'";
                $code[] = "            \$table->{$method}([{$columns}]);";
            }
        }
        
        return !empty($code) ? "\n" . implode("\n", $code) : "";
    }

    /**
     * Génère le code pour les clés étrangères
     */
    protected function generateForeignKeysCode($foreignKeys)
    {
        if (empty($foreignKeys)) {
            return "";
        }
        
        $code = [];
        foreach ($foreignKeys as $fk) {
            $line = "            \$table->foreign('{$fk['column']}')";
            $line .= "->references('{$fk['on']}')";
            $line .= "->on('{$fk['references']}')";
            
            if ($fk['onDelete'] !== 'restrict') {
                $line .= "->onDelete('{$fk['onDelete']}')";
            }
            
            if ($fk['onUpdate'] !== 'restrict') {
                $line .= "->onUpdate('{$fk['onUpdate']}')";
            }
            
            $line .= ";";
            $code[] = $line;
        }
        
        return !empty($code) ? "\n" . implode("\n", $code) : "";
    }
}
