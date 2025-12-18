<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If you originally created the singular table, rename it to the standard plural
        if (!Schema::hasTable('article_categories') && Schema::hasTable('article_category')) {
            Schema::rename('article_category', 'article_categories');
        }

        if (Schema::hasTable('article_categories')) {
            // Ensure columns exist
            if (!Schema::hasColumn('article_categories', 'slug')) {
                Schema::table('article_categories', function (Blueprint $table) {
                    $table->string('slug')->nullable()->after('name');
                });
            }

            // Try to add unique indexes, but don't crash if duplicates exist yet
            try {
                Schema::table('article_categories', function (Blueprint $table) {
                    $table->unique('name', 'article_categories_name_unique');
                });
            } catch (\Throwable $e) { /* ignore for now */ }

            try {
                Schema::table('article_categories', function (Blueprint $table) {
                    $table->unique('slug', 'article_categories_slug_unique');
                });
            } catch (\Throwable $e) { /* ignore for now */ }
        }
    }

    public function down(): void
    {
        // Optional: drop uniques if present
        if (Schema::hasTable('article_categories')) {
            try {
                Schema::table('article_categories', function (Blueprint $table) {
                    $table->dropUnique('article_categories_name_unique');
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('article_categories', function (Blueprint $table) {
                    $table->dropUnique('article_categories_slug_unique');
                });
            } catch (\Throwable $e) {}
        }
        // We do not rename back to singular.
    }
};
