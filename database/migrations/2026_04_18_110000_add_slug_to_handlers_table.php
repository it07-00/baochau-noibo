<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handlers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Tạo slug cho các bản ghi hiện có
        DB::table('handlers')->orderBy('id')->each(function ($handler) {
            $base = Str::slug($handler->name);
            $slug = $base;
            $i = 1;
            while (DB::table('handlers')->where('slug', $slug)->where('id', '!=', $handler->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            DB::table('handlers')->where('id', $handler->id)->update(['slug' => $slug]);
        });

        Schema::table('handlers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('handlers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
