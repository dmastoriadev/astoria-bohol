<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            // Author (admin) who created the pop-up
            if (! Schema::hasColumn('popups', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('is_draft')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            if (Schema::hasColumn('popups', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
