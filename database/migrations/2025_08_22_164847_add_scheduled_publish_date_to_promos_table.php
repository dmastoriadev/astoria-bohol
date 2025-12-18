<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            if (! Schema::hasColumn('promos', 'scheduled_publish_date')) {
                // Use plain dateTime for widest DB compatibility (treat as UTC in app layer)
                $table->dateTime('scheduled_publish_date')
                      ->nullable()
                      ->index()
                      ->comment('UTC; when set in the future, promo is considered Scheduled (dashboard logic).');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            if (Schema::hasColumn('promos', 'scheduled_publish_date')) {
                $table->dropIndex(['scheduled_publish_date']); // safe even if not present
                $table->dropColumn('scheduled_publish_date');
            }
        });
    }
};
