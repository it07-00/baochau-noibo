<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        DB::table('customers')->orderBy('id')->each(function ($customer) {
            $base = Str::slug($customer->name);
            if (empty($base)) {
                $base = 'khach-hang-' . $customer->id;
            }
            $slug = $base;
            $i = 1;
            while (DB::table('customers')->where('slug', $slug)->where('id', '!=', $customer->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            DB::table('customers')->where('id', $customer->id)->update(['slug' => $slug]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
