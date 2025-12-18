<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            if (!Schema::hasColumn('promos', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            if (Schema::hasColumn('promos', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
