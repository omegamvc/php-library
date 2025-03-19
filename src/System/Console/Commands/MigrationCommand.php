<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use DirectoryIterator;
use Exception;
use System\Collection\Collection;
use System\Console\Command;
use System\Console\Prompt;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Database\MyQuery;
use System\Database\MySchema\Table\Create;
use System\Support\Facades\DB;
use System\Support\Facades\PDO;
use System\Support\Facades\Schema;
use Throwable;

use function System\Application\app;
use function System\Application\migration_path;
use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * Handles migration commands, including migration, rollback, fresh migration,
 * and database operations like creation, dropping, and showing.
 *
 * This class provides various methods to manage database migrations, including
 * running migrations, rolling them back, refreshing the database, and managing
 * the database lifecycle. Additionally, it integrates seeder functionality
 * during migrations and offers command-line options for better control and
 * flexibility during database operations.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @property ?int        $take
 * @property ?int        $batch
 * @property bool        $force
 * @property string|bool $seed
 */
class MigrationCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Paths to vendor migrations.
     *
     * This static array holds the paths to migration directories provided by
     * vendor packages.
     *
     * @var string[]
     */
    public static array $vendorPaths = [];

    /**
     * Command registration details.
     *
     * This array defines the commands available for managing the application's
     * maintenance mode. Each command is associated with a pattern and a function
     * that handles the command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'migrate',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'migrate:fresh',
            'fn'      => [self::class, 'fresh'],
        ], [
            'pattern' => 'migrate:reset',
            'fn'      => [self::class, 'reset'],
        ], [
            'pattern' => 'migrate:refresh',
            'fn'      => [self::class, 'refresh'],
        ], [
            'pattern' => 'migrate:rollback',
            'fn'      => [self::class, 'rollback'],
        ], [
            'pattern' => ['database:create', 'db:create'],
            'fn'      => [self::class, 'databaseCreate'],
        ], [
            'pattern' => ['database:drop', 'db:drop'],
            'fn'      => [self::class, 'databaseDrop'],
        ], [
            'pattern' => ['database:show', 'db:show'],
            'fn'      => [self::class, 'databaseShow'],
        ], [
            'pattern' => 'migrate:status',
            'fn'      => [self::class, 'status'],
        ], [
            'pattern' => 'migrate:init',
            'fn'      => [self::class, 'initializeMigration'],
        ],
    ];

    /**
     * Provides help documentation for the command.
     *
     * This method returns an array with information about available commands
     * and options. It describes the two main commands (`down` and `up`) for
     * managing maintenance mode.
     *
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
                '--dry-run'           => 'Execute migration but only get query output.',
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
     * Get the database name from configuration.
     *
     * This method retrieves the database name from the SQL connection configuration.
     *
     * @return string The database name.
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function DbName(): string
    {
        return app()->get('dsn.sql')['database_name'];
    }

    /**
     * Prompts for confirmation to run commands in production.
     *
     * This method prompts the user to confirm if they want to execute a
     * migration/database command in a production environment.
     *
     * @return bool Returns true if the user confirms, false otherwise.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
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
     * Confirms an action with the user.
     *
     * This method asks for user confirmation before proceeding with a given
     * action, with an optional custom message.
     *
     * @param string|Style $message The confirmation message or style.
     * @return bool Returns true if the user confirms, false otherwise.
     * @throws Exception
     */
    private function confirmation(string|Style $message): bool
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
     * Run migrations in a specific batch.
     *
     * This method retrieves and executes the migration scripts in the specified
     * batch, ensuring that migrations are run in order.
     *
     * @param int|false $batch The batch number for migrations to be executed.
     * @return Collection<string, array<string, string>> The migrations to run.
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function baseMigrate(int|false &$batch = false): Collection
    {
        $migrationBatch = $this->getMigrationTable();
        $height         = $migrationBatch->length() > 0
            ? $migrationBatch->max() + 1
            : 0;
        $batch = false === $batch ? $height : $batch;

        $paths   = [migration_path(), ...static::$vendorPaths];
        $migrate = new Collection([]);
        foreach ($paths as $dir) {
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() | $file->isDir()) {
                    continue;
                }

                $migrationName = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                $hasMigration  = $migrationBatch->has($migrationName);

                if (!$batch && $hasMigration) {
                    if ($migrationBatch->get($migrationName) <= $height - 1) {
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
                        'batch'     => $height,
                    ]);
                    $this->insertMigrationTable([
                        'migration' => $migrationName,
                        'batch'     => $height,
                    ]);
                    continue;
                }

                if ($migrationBatch->get($migrationName) <= $batch) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $migrationBatch->get($migrationName),
                    ]);
                    //continue;
                }
            }
        }

        return $migrate;
    }

    /**
     * Main method to run migrations.
     *
     * This method is called to start running migrations based on the command-line
     * options and parameters.
     *
     * @return int The exit status code (0 for success, non-zero for failure).
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function main(): int
    {
        return $this->migration();
    }

    /**
     * Run migrations with optional dry-run.
     *
     * This method runs the migrations, allowing for a "dry-run" mode where
     * only the SQL queries are output without actually being executed.
     *
     * @param bool $silent If set to true, prevents interaction prompts.
     * @return int The exit status code (0 for success, non-zero for failure).
     * @throws DependencyException
     * @throws NotFoundException
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
     * @throws NotFoundException
     * @throws DependencyException
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
     * Rollback all migrations.
     *
     * This method rolls back all migrations and resets the database to its
     * previous state.
     *
     * @param bool $silent If set to true, prevents interaction prompts.
     * @return int The exit status code (0 for success, non-zero for failure).
     * @throws DependencyException
     * @throws NotFoundException
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
     * Refreshes the database by rolling back and running migrations again.
     *
     * This method resets the database and reruns the migrations, typically used
     * when a fresh migration is needed after changes to schema or data.
     *
     * @return int The exit status code (0 for success, non-zero for failure).
     * @throws DependencyException
     * @throws NotFoundException
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
     * Rolls back a specified number of migrations.
     *
     * This method rolls back migrations based on the given batch number and the
     * number of migrations to roll back. If the batch option is not provided or
     * is invalid, the method will return an error. If the number of migrations to
     * roll back is less than 0, the method defaults to rolling back all migrations.
     *
     * @return int Returns 0 if the rollback is successful, or 1 if there is an error (e.g., missing batch).
     * @throws DependencyException
     * @throws NotFoundException
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
     * Rollback migrations in a specific batch.
     *
     * This method rolls back migrations in the specified batch. If a batch
     * number is provided, it only rolls back those migrations.
     *
     * @param int|false $batch The batch number to rollback.
     * @param int $take The number of migrations to rollback.
     * @return int The exit status code (0 for success, non-zero for failure).
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function rollbacks(int|false $batch, int $take): int
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
     * Creates a new database.
     *
     * This method attempts to create a new database with the name fetched from
     * the configuration. If the creation process is successful, it initializes
     * the migration process.
     *
     * @param bool $silent If set to true, suppresses any confirmation prompts.
     * @return int Returns 0 if the database is created successfully, 1 if the creation fails, or 2 if the user cancels the action.
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     * @throws NotFoundException If the required configuration or resources are not found.
     * @throws Exception If an error occurs during the database creation process.
     */
    public function databaseCreate(bool $silent = false): int
    {
        $db_name = $this->DbName();
        $message = style("Do you want to create database `{$db_name}`?")->textBlue();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("creating database `{$db_name}`")->out(false);

        $success = Schema::create()->database($db_name)->ifNotExists()->execute();

        if ($success) {
            ok("success create database `{$db_name}`")->out(false);

            $this->initializeMigration();

            return 0;
        }

        fail("cant created database `{$db_name}`")->out(false);

        return 1;
    }

    /**
     * Drops the existing database.
     *
     * This method attempts to drop the database with the name fetched from the
     * configuration. If the process is successful, it returns 0. If the operation
     * is canceled or fails, it returns a different status code.
     *
     * @param bool $silent If set to true, suppresses any confirmation prompts.
     * @return int Returns 0 if the database is dropped successfully, 1 if the drop fails, or 2 if the user cancels the action.
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     * @throws NotFoundException If the required configuration or resources are not found.
     * @throws Exception If an error occurs during the database drop process.
     */
    public function databaseDrop(bool $silent = false): int
    {
        $db_name = $this->DbName();
        $message = style("Do you want to drop database `{$db_name}`?")->textRed();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("try to drop database `{$db_name}`")->out(false);

        //$success = Schema::drop()->database($db_name)->ifExists(true)->execute();
        $success = Schema::drop()->database($db_name)->ifExists()->execute();

        if ($success) {
            ok("success drop database `{$db_name}`")->out(false);

            return 0;
        }

        fail("cant drop database `{$db_name}`")->out(false);

        return 1;
    }

    /**
     * Displays information about the current database's tables.
     *
     * This method shows the list of tables in the current database, along with
     * details such as table size and creation time. If a specific table is specified
     * through an option, detailed information for that table is shown.
     *
     * @return int Returns 0 if the database and its tables are shown successfully, or 2 if the tables are empty or no tables exist.
     * @throws NotFoundException If the required configuration or resources are not found.
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     */
    public function databaseShow(): int
    {
        if ($this->option('table-name')) {
            //return $this->tableShow($this->option('table-name', null));
            return $this->tableShow($this->option('table-name'));
        }

        $db_name = $this->DbName();
        $width   = $this->getWidth(40, 60);
        info('showing database')->out(false);

        $tables = PDO::instance()
            ->query('SHOW DATABASES')
            ->query('
                SELECT table_name, create_time, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size`
                FROM information_schema.tables
                WHERE table_schema = :db_name')
            ->bind(':db_name', $db_name)
            ->resultset();

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
     * Displays information about a specific table.
     *
     * This method shows detailed information about the specified table, including
     * column names, data types, and additional properties like nullability and
     * primary key status.
     *
     * @param string $table The name of the table to display information for.
     * @return int Returns 0 after showing the table's details.
     */
    public function tableShow(string $table): int
    {
        $table = (new MyQuery(PDO::instance()))->table($table)->info();
        $print = new Style("\n");
        $width = $this->getWidth(40, 60);

        $print->push('column')->textYellow()->bold()->resetDecorate()->newLines();
        foreach ($table as $column) {
            $will_print = [];

            if ($column['IS_NULLABLE'] === 'YES') {
                $will_print[] = 'nullable';
            }
            if ($column['COLUMN_KEY'] === 'PRI') {
                $will_print[] = 'primary';
            }

            $info   = implode(', ', $will_print);
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
     * Displays the migration status.
     *
     * This method lists the migrations currently applied to the database, showing
     * the migration names along with their respective batch numbers.
     *
     * @return int Returns 0 after successfully showing the migration status.
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
     * Runs the seeder during the migration process.
     *
     * This method integrates the seeding process during migration, optionally running
     * a specific seeder based on the provided options. If a dry-run is requested,
     * it skips the seeding step.
     *
     * @return int Returns 0 after successfully running the seeder (if applicable).
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     * @throws NotFoundException If the required configuration or resources are not found.
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
     * Checks if the migration table exists in the current database.
     *
     * This method verifies whether the migration table is present in the current
     * database by querying the database's information schema.
     *
     * @return bool Returns true if the migration table exists, false otherwise.
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     * @throws NotFoundException If the required configuration or resources are not found.
     */
    private function hasMigrationTable(): bool
    {
        $result = PDO::instance()->query(
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
     * Creates the migration table schema.
     *
     * This method creates the schema for the migration table, which tracks the
     * applied migrations and their respective batch numbers.
     *
     * @return bool Returns true if the migration table is created successfully, false otherwise.
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
     * Retrieves the migration table entries.
     *
     * This method fetches the migration table from the database, returning the
     * migration names along with their respective batch numbers.
     *
     * @return Collection<string, int> A collection of migration names and their batch numbers.
     */
    private function getMigrationTable(): Collection
    {
        /** @var Collection<string, int> $pair */
        $pair = DB::table('migration')
            ->select()
            ->get()
            ->assocBy(static fn ($item) => [$item['migration'] => (int) $item['batch']]);

        return $pair;
    }

    /**
     * Inserts a new migration entry into the migration table.
     *
     * This method adds a new record to the migration table with the specified
     * migration name and batch number.
     *
     * @param array<string, string|int> $migration The migration name and batch number to insert.
     * @return bool Returns true if the migration entry is inserted successfully, false otherwise.
     */
    private function insertMigrationTable(array $migration): bool
    {
        return DB::table('migration')
            ->insert()
            ->values($migration)
            ->execute()
        ;
    }

    /**
     * Initializes the migration process by creating the migration table.
     *
     * This method checks if the migration table exists and, if not, attempts to
     * create it. It ensures that the migration table is in place before any
     * migrations can be run.
     *
     * @return int Returns 0 if the migration table exists or is created successfully, or 1 if an error occurs.
     * @throws DependencyException If any dependencies are missing or cannot be resolved.
     * @throws NotFoundException If the required configuration or resources are not found.
     */
    public function initializeMigration(): int
    {
        $has_migration_table = $this->hasMigrationTable();

        if ($has_migration_table) {
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
     * Adds a vendor migration path.
     *
     * This static method allows for the addition of a migration path from a vendor,
     * enabling the system to recognize and include vendor-specific migrations.
     *
     * @param string $path The path to the vendor's migration directory.
     * @return void
     */
    public static function addVendorMigrationPath(string $path): void
    {
        static::$vendorPaths[] = $path;
    }

    /**
     * Flushes the vendor migration paths.
     *
     * This static method clears all previously added vendor migration paths, effectively
     * resetting the system's awareness of those paths.
     *
     * @return void
     */
    public static function flushVendorMigrationPaths(): void
    {
        static::$vendorPaths = [];
    }
}
