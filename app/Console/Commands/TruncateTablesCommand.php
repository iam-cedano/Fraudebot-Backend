<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Attribute\AsCommand;

use function in_array;

#[AsCommand(name: 'db:truncate')]
class TruncateTablesCommand extends Command
{
    use ConfirmableTrait, Prohibitable;

    protected $signature = 'db:truncate
                            {--database= : The database connection to use}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Delete the content of all tables (keeps schema and migrations)';

    public function handle(): int
    {
        if ($this->isProhibited() || ! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $connection = $this->option('database') ?: null;
        $schema = Schema::connection($connection);
        $excluded = ['migrations'];

        $tables = collect($schema->getTableListing(schemaQualified: false))
            ->reject(fn (string $table) => in_array($table, $excluded, true))
            ->values();

        if ($tables->isEmpty()) {
            $this->components->warn('No tables to truncate.');

            return self::SUCCESS;
        }

        $schema->disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::connection($connection)->table($table)->truncate();
            $this->components->twoColumnDetail($table, '<fg=yellow;options=bold>TRUNCATED</>');
        }

        $schema->enableForeignKeyConstraints();

        $this->components->info('All tables truncated successfully.');

        return self::SUCCESS;
    }
}
