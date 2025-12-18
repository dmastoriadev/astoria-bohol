<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Add only if it doesn't exist yet (safe re-runs)
            if (!Schema::hasColumn('articles', 'deleted_at')) {
                $table->softDeletes()->after('updated_at'); // adds nullable timestamp `deleted_at`
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Drop if present
            if (Schema::hasColumn('articles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
