<?php

declare(strict_types=1);

namespace System\Console\Commands;

use DirectoryIterator;
use System\Collection\Collection;
use System\Console\Command;
use System\Console\Prompt;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Database\Schema\Table\Create;
use System\Support\Facades\Query;
use System\Support\Facades\Database;
use System\Support\Facades\Schema;
use Throwable;

use function array_change_key_case;
use function get_database_path;
use function implode;
use function pathinfo;
use function strlen;
use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

use const PATHINFO_FILENAME;

/**
 * @property ?int        $take
 * @property ?int        $batch
 * @property bool        $force
 * @property string|bool $seed
 */
class MigrationCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register vendor migration path.
     *
     * @var string[]
     */
    public static array $vendorPaths = [];

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'migrate',
            'fn' => [MigrationCommand::class, 'main'],
        ], [
            'pattern' => 'migrate:fresh',
            'fn' => [MigrationCommand::class, 'fresh'],
        ], [
            'pattern' => 'migrate:reset',
            'fn' => [MigrationCommand::class, 'reset'],
        ], [
            'pattern' => 'migrate:refresh',
            'fn' => [MigrationCommand::class, 'refresh'],
        ], [
            'pattern' => 'migrate:rollback',
            'fn' => [MigrationCommand::class, 'rollback'],
        ], [
            'pattern' => ['database:create', 'db:create'],
            'fn' => [MigrationCommand::class, 'databaseCreate'],
        ], [
            'pattern' => ['database:drop', 'db:drop'],
            'fn' => [MigrationCommand::class, 'databaseDrop'],
        ], [
            'pattern' => ['database:show', 'db:show'],
            'fn' => [MigrationCommand::class, 'databaseShow'],
        ], [
            'pattern' => 'migrate:status',
            'fn' => [MigrationCommand::class, 'status'],
        ], [
            'pattern' => 'migrate:init',
            'fn' => [MigrationCommand::class, 'initializeMigration'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'migrate'                  => 'Run migration (up)',
                'migrate:fresh'            => 'Drop database and run migrations',
                'migrate:reset'            => 'Rolling back all migrations (down)',
                'migrate:refresh'          => 'Rolling back and run migration all',
                'migrate:rollback'         => 'Rolling back last migrations (down)',
                'migrate:init'             => 'Initialize migration table',
                'migrate:status'           => 'Show migration status.',
                'database:create'          => 'Create database',
                'database:drop'            => 'Drop database',
                'database:show'            => 'Show database table',
            ],
            'options'   => [
                '--take'              => 'Limit of migrations to be run.',
                '--batch'             => 'Batch migration execution.',
                '--dry-run'           => 'Execute migration but olny get query output.',
                '--force'             => 'Force running migration/database query in production.',
                '--seed'              => 'Run seeder after migration.',
                '--seed-namespace'    => 'Run seeder after migration using class namespace.',
                '--yes'               => 'Accept it without having it ask any questions',
            ],
            'relation'  => [
                'migrate'                   => ['--seed', '--dry-run', '--force'],
                'migrate:fresh'             => ['--seed', '--dry-run', '--force'],
                'migrate:reset'             => ['--dry-run', '--force'],
                'migrate:refresh'           => ['--seed', '--dry-run', '--force'],
                'migrate:rollback'          => ['--batch', '--take', '--dry-run', '--force'],
                'database:create'           => ['--force'],
                'database:drop'             => ['--force'], ],
        ];
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    private function DbName(): string
    {
        return app()->get('dsn.sql')['database_name'];
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Running migration/database in production?')->textRed(), [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    /**
     * @param string|Style $message
     * @return bool
     */
    private function confirmation(Style|string $message): bool
    {
        if ($this->option('yes', false)) {
            return true;
        }

        /* @var bool */
        return (new Prompt($message, [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
        ->selection([
            style('yes')->textDim(),
            ' no',
        ])
        ->option();
    }

    /**
     * Get migration list.
     *
     * @param false|int $batch
     * @return Collection<string, array<string, string>>
     */
    public function baseMigrate(false|int &$batch = false): Collection
    {
        $migrationBatch = $this->getMigrationTable();
        $heights        = $migrationBatch->length() > 0
            ? $migrationBatch->max() + 1
            : 0;
        $batch = false === $batch ? $heights : $batch;

        $paths   = [get_database_path('migration/'), ...static::$vendorPaths];
        $migrate = new Collection([]);
        foreach ($paths as $dir) {
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() | $file->isDir()) {
                    continue;
                }

                $migrationName = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                $hasMigration   = $migrationBatch->has($migrationName);

                if (false == $batch && $hasMigration) {
                    if ($migrationBatch->get($migrationName) <= $heights - 1) {
                        $migrate->set($migrationName, [
                            'file_name' => $dir . $file->getFilename(),
                            'batch'     => $migrationBatch->get($migrationName),
                        ]);
                        continue;
                    }
                }

                if (false === $hasMigration) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $heights,
                    ]);
                    $this->insertMigrationTable([
                        'migration' => $migrationName,
                        'batch'     => $heights,
                    ]);
                    continue;
                }

                if ($migrationBatch->get($migrationName) <= $batch) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $migrationBatch->get($migrationName),
                    ]);
                    continue;
                }
            }
        }

        return $migrate;
    }

    /**
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function main(): int
    {
        return $this->migration();
    }

    /**
     * @param bool $silent
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function migration(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }

        $print   = new Style();
        $width   = $this->getWidth(40, 60);
        $batch   = false;
        $migrate = $this->baseMigrate($batch);
        $migrate
            ->filter(static fn ($value): bool => $value['batch'] == $batch)
            ->sort();

        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
    }

    /**
     * @param bool $silent
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function fresh(bool $silent = false): int
    {
        // drop and recreate database
        if (($drop = $this->databaseDrop($silent)) > 0) {
            return $drop;
        }
        if (($create = $this->databaseCreate(true)) > 0) {
            return $create;
        }

        // run migration

        $print   = new Style();
        $migrate = $this->baseMigrate()->sort();
        $width   = $this->getWidth(40, 60);

        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
    }

    /**
     * @param bool $silent
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function reset(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }
        info('Rolling back all migrations')->out(false);

        return $this->rollbacks(false, 0);
    }

    /**
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function refresh(): int
    {
        if (false === $this->runInDev()) {
            return 2;
        }

        if (($reset = $this->reset(true)) > 0) {
            return $reset;
        }
        if (($migration = $this->migration(true)) > 0) {
            return $migration;
        }

        return 0;
    }

    /**
     * @return int
     */
    public function rollback(): int
    {
        if (false === ($batch = $this->option('batch', false))) {
            fail('batch is required.')->out();

            return 1;
        }
        $take    = $this->take;
        $message = "Rolling {$take} back migrations.";
        if ($take < 0) {
            $take    = 0;
            $message = 'Rolling back migrations.';
        }
        info($message)->out(false);

        return $this->rollbacks((int) $batch, (int) $take);
    }

    /**
     * Rolling backs migration.
     *
     * @param false|int $batch
     * @param int $take
     * @return int
     */
    public function rollbacks(false|int $batch, int $take): int
    {
        $print   = new Style();
        $width   = $this->getWidth(40, 60);

        $migrate = false === $batch
            ? $this->baseMigrate($batch)
            : $this->baseMigrate($batch)->filter(static fn ($value): bool => $value['batch'] >= $batch - $take);

        foreach ($migrate->sortDesc() as $key => $val) {
            $schema = require_once $val['file_name'];
            $down   = new Collection($schema['down'] ?? []);

            if ($this->option('dry-run')) {
                $down->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $down->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * @param bool $silent
     * @return int
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    public function databaseCreate(bool $silent = false): int
    {
        $dbName  = $this->DbName();
        $message = style("Do you want to create database `{$dbName}`?")->textBlue();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("creating database `{$dbName}`")->out(false);

        $success = Schema::create()->database($dbName)->ifNotExists()->execute();

        if ($success) {
            ok("success create database `{$dbName}`")->out(false);

            $this->initializeMigration();

            return 0;
        }

        fail("cant created database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * @param bool $silent
     * @return int
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    public function databaseDrop(bool $silent = false): int
    {
        $dbName  = $this->DbName();
        $message = style("Do you want to drop database `{$dbName}`?")->textRed();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("try to drop database `{$dbName}`")->out(false);

        $success = Schema::drop()->database($dbName)->ifExists(true)->execute();

        if ($success) {
            ok("success drop database `{$dbName}`")->out(false);

            return 0;
        }

        fail("cant drop database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function databaseShow(): int
    {
        if ($this->option('table-name')) {
            return $this->tableShow($this->option('table-name', null));
        }

        $dbName = $this->DbName();
        $width  = $this->getWidth(40, 60);
        info('showing database')->out(false);

        $tables = Database::instance()
            ->query('SHOW DATABASES')
            ->query('
                SELECT table_name, create_time, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size`
                FROM information_schema.tables
                WHERE table_schema = :db_name')
            ->bind(':db_name', $dbName)
            ->resultSet();

        if (0 === count($tables)) {
            warn('table is empty try to run migration')->out();

            return 2;
        }

        foreach ($tables as $table) {
            $table  = array_change_key_case($table);
            $name   = $table['table_name'];
            $time   = $table['create_time'];
            $size   = $table['size'];
            $length = strlen($name) + strlen($time) + strlen($size);

            style($name)
                ->push(' ' . $size . ' Mb ')->textDim()
                ->repeat('.', $width - $length)->textDim()
                ->push(' ' . $time)
                ->out();
        }

        return 0;
    }

    /**
     * @param string $table
     * @return int
     */
    public function tableShow(string $table): int
    {
        $table = (new Query(Database::instance()))->table($table)->info();
        $print = new Style("\n");
        $width = $this->getWidth(40, 60);

        $print->push('column')->textYellow()->bold()->resetDecorate()->newLines();
        foreach ($table as $column) {
            $willPrint = [];

            if ($column['IS_NULLABLE'] === 'YES') {
                $willPrint[] = 'nullable';
            }
            if ($column['COLUMN_KEY'] === 'PRI') {
                $willPrint[] = 'primary';
            }

            $info   = implode(', ', $willPrint);
            $length = strlen($column['COLUMN_NAME']) + strlen($column['COLUMN_TYPE']) + strlen($info);

            $print->push($column['COLUMN_NAME'])->bold()->resetDecorate();
            $print->push(' ' . $info . ' ')->textDim();
            $print->repeat('.', $width - $length)->textDim();
            $print->push(' ' . $column['COLUMN_TYPE']);
            $print->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * @return int
     */
    public function status(): int
    {
        $print = new Style();
        $print->tap(info('show migration status'));
        $width = $this->getWidth(40, 60);
        foreach ($this->getMigrationTable() as $migration_name => $batch) {
            $length = strlen($migration_name) + strlen((string) $batch);
            $print
                ->push($migration_name)
                ->push(' ')
                ->repeat('.', $width - $length)->textDim()
                ->push(' ')
                ->push($batch)
                ->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * Integrate seeder during run migration.
     *
     * @return int
     */
    private function seed(): int
    {
        if ($this->option('dry-run', false)) {
            return 0;
        }
        if ($this->seed) {
            $seed = true === $this->seed ? null : $this->seed;

            return (new SeedCommand([], ['class' => $seed]))->main();
        }

        $namespace = $this->option('seed-namespace', false);
        if ($namespace) {
            $namespace = true === $namespace ? null : $namespace;

            return (new SeedCommand([], ['name-space' => $namespace]))->main();
        }

        return 0;
    }

    /**
     * Check for migration table exist or not in this current database.
     *
     * @return bool
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    private function hasMigrationTable(): bool
    {
        $result = Database::instance()->query(
            "SELECT COUNT(table_name) as total
            FROM information_schema.tables
            WHERE table_schema = :dbname
            AND table_name = 'migration'"
        )->bind(':dbname', $this->DbName())
        ->single();

        if ($result) {
            return $result['total'] > 0;
        }

        return false;
    }

    /**
     * Create migration table schema.
     *
     * @return bool
     */
    private function createMigrationTable(): bool
    {
        return Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
    }

    /**
     * Get migration batch file in migration table.
     *
     * @return Collection<string, int>
     */
    private function getMigrationTable(): Collection
    {
        /** @var Collection<string, int> $item */
        return Query::table('migration')
            ->select()
            ->get()
            ->assocBy(static fn ($item) => [$item['migration'] => (int) $item['batch']]);
    }

    /**
     * Save insert migration file with batch to migration table.
     *
     * @param array<string, string|int> $migration
     * @return bool
     */
    private function insertMigrationTable(array $migration): bool
    {
        return Query::table('migration')
            ->insert()
            ->values($migration)
            ->execute()
        ;
    }

    /**
     * @return int
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function initializeMigration(): int
    {
        $hasMigrationTable = $this->hasMigrationTable();

        if ($hasMigrationTable) {
            info('Migration table already exist on your database table.')->out(false);

            return 0;
        }

        if ($this->createMigrationTable()) {
            ok('Success create migration table.')->out(false);

            return 0;
        }

        fail('Migration table cant be create.')->out(false);

        return 1;
    }

    /**
     * Add migration from vendor path.
     *
     * @param string $path
     * @return void
     */
    public static function addVendorMigrationPath(string $path): void
    {
        static::$vendorPaths[] = $path;
    }

    /**
     * Flush migration vendor paths.
     *
     * @return void
     */
    public static function flushVendorMigrationPaths(): void
    {
        static::$vendorPaths = [];
    }
}
