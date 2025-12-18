<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'article_category_id')) {
                $table->foreignId('article_category_id')
                      ->nullable()
                      ->after('id')
                      // IMPORTANT: correct table name is article_categories (plural)
                      ->constrained('article_categories')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'article_category_id')) {
                $table->dropForeign(['article_category_id']);
                $table->dropColumn('article_category_id');
            }
        });
    }
};
