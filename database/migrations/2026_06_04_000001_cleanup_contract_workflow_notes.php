<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'contract_wastes'           => 'note',
        'contract_consultings'      => 'notes',
        'contract_projects'         => 'notes',
        'contract_commercials'      => 'notes',
        'contract_sustainabilities' => 'notes',
        'contract_energies'         => 'notes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)->whereNotNull($column)->chunkById(100, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    $note = $row->{$column};
                    if (empty($note)) {
                        continue;
                    }

                    $cleaned = $this->cleanNote($note);
                    if ($cleaned !== $note) {
                        DB::table($table)->where('id', $row->id)->update([$column => $cleaned]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Cleanup is one-way as raw workflow URLs/paths cannot be easily reconstructed
    }

    private function cleanNote(?string $note): ?string
    {
        if (empty($note)) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $note);
        $filteredLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $lower = strtolower($trimmed);
            if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
                continue;
            }
            if (str_starts_with($lower, 'downloads/') || str_starts_with($lower, 'downloads\\')) {
                continue;
            }

            $filteredLines[] = $line;
        }

        if (empty($filteredLines)) {
            return null;
        }

        return implode("\n", $filteredLines);
    }
};
