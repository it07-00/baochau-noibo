<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const RENAMES = [
        'regulatory-facilities.view' => 'customer-lists.view',
        'regulatory-facilities.edit' => 'customer-lists.edit',
    ];

    public function up(): void
    {
        $this->renamePermissions(self::RENAMES);
    }

    public function down(): void
    {
        $this->renamePermissions(array_flip(self::RENAMES));
    }

    /**
     * @param  array<string, string>  $renames
     */
    private function renamePermissions(array $renames): void
    {
        $table = config('permission.table_names.permissions');

        foreach ($renames as $from => $to) {
            DB::table($table)
                ->where('name', $from)
                ->where('guard_name', 'web')
                ->update(['name' => $to]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
