<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promos')) {
            return;
        }

        if (!Schema::hasColumn('promos', 'scheduled_publish_date')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->dateTime('scheduled_publish_date')->nullable()->after('published_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promos') && Schema::hasColumn('promos', 'scheduled_publish_date')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->dropColumn('scheduled_publish_date');
            });
        }
    }
};
