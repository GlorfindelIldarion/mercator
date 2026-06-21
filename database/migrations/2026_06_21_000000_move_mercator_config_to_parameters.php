<?php

use App\Models\Parameter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed the `parameters` table with whatever this install currently has
     * in config/mercator.php, so admin-edited settings survive the move
     * away from rewriting that file on disk.
     */
    public function up(): void
    {
        $path = config_path('mercator.php');

        if (! file_exists($path)) {
            return;
        }

        $cfg = require $path;

        if (! is_array($cfg) || $cfg === []) {
            return;
        }

        Parameter::updateOrCreate(
            ['name' => 'mercator_config'],
            ['value' => json_encode($cfg)]
        );
    }

    public function down(): void
    {
        Parameter::where('name', 'mercator_config')->delete();
    }
};
