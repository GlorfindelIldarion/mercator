<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_document', function (Blueprint $table) {
            $table->unsignedInteger('application_id')->index('application_id_fk_7812341');
            $table->unsignedInteger('document_id')->index('document_id_fk_7812342');

            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->foreign('application_id', 'application_id_fk_7812341')
                    ->references('id')->on('applications')
                    ->onUpdate('NO ACTION')->onDelete('CASCADE');
                $table->foreign('document_id', 'document_id_fk_7812342')
                    ->references('id')->on('documents')
                    ->onUpdate('NO ACTION')->onDelete('CASCADE');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_document', function (Blueprint $table) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->dropForeign('application_id_fk_7812341');
                $table->dropForeign('document_id_fk_7812342');
            }
        });
        Schema::dropIfExists('application_document');
    }
};
