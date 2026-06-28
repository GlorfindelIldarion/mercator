<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = collect(DB::select('DESCRIBE backups'))->pluck('Field');
        $indexes = collect(DB::select("SHOW INDEX FROM backups"))->pluck('Key_name');

        Schema::table('backups', function (Blueprint $table) use ($columns, $indexes) {
            if ($indexes->contains('backups_name_unique')) {
                $table->dropUnique('backups_name_unique');
            }
            if ($columns->contains('logical_server_id')) {
                $table->dropForeign(['logical_server_id']);
                $table->dropColumn('logical_server_id');
            }
            if ($columns->contains('storage_device_id')) {
                $table->dropForeign(['storage_device_id']);
                $table->dropColumn('storage_device_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->unique('name');
            $table->unsignedInteger('logical_server_id')->nullable();
            $table->unsignedInteger('storage_device_id')->nullable();
        });
    }
};
