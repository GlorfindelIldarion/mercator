<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bays', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('room_fk_1483441');
            }
        });

        Schema::table('bays', function (Blueprint $table) {
            $table->renameColumn('room_id', 'building_id');
        });

        Schema::table('bays', function (Blueprint $table) {
            $table->foreign('building_id', 'building_fk_1483441')
                ->references('id')->on('buildings')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');

            $table->unsignedInteger('site_id')
                ->nullable()
                ->after('building_id')
                ->index('site_id_fk_1483442');
            $table->foreign('site_id', 'site_id_fk_1483442')
                ->references('id')->on('sites')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bays', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('building_fk_1483441');
                $table->dropForeign('site_id_fk_1483442');
            }
            $table->dropColumn('site_id');
        });

        Schema::table('bays', function (Blueprint $table) {
            $table->renameColumn('building_id', 'room_id');
        });

        Schema::table('bays', function (Blueprint $table) {
            $table->foreign('room_id', 'room_fk_1483441')->references('id')->on('buildings')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }
};
