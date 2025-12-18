<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles','author')) {
                $table->string('author')->default('AVLCI')->after('slug');
            }
            if (!Schema::hasColumn('articles','created_by')) {
                $table->foreignId('created_by')->nullable()->after('author')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('articles','created_by_name')) {
                // convenience snapshot of the creator’s name/email at time of create
                $table->string('created_by_name')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles','created_by_name')) {
                $table->dropColumn('created_by_name');
            }
            if (Schema::hasColumn('articles','created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('articles','author')) {
                $table->dropColumn('author');
            }
        });
    }
};
