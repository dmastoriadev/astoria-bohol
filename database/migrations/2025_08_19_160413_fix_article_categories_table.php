<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If singular exists, rename to plural
        if (!Schema::hasTable('article_categories') && Schema::hasTable('article_category')) {
            Schema::rename('article_category', 'article_categories');
        }

        // If still missing, create fresh
        if (!Schema::hasTable('article_categories')) {
            Schema::create('article_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();

                $table->unique('name', 'article_categories_name_unique');
                $table->unique('slug', 'article_categories_slug_unique');
            });
            return;
        }

        // Ensure slug column exists
        if (!Schema::hasColumn('article_categories', 'slug')) {
            Schema::table('article_categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Add uniques if missing (errors ignored if already exist)
        try { Schema::table('article_categories', fn (Blueprint $t) => $t->unique('name', 'article_categories_name_unique')); } catch (\Throwable $e) {}
        try { Schema::table('article_categories', fn (Blueprint $t) => $t->unique('slug', 'article_categories_slug_unique')); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Keep data safe: only drop indexes if present
        try { Schema::table('article_categories', fn (Blueprint $t) => $t->dropUnique('article_categories_name_unique')); } catch (\Throwable $e) {}
        try { Schema::table('article_categories', fn (Blueprint $t) => $t->dropUnique('article_categories_slug_unique')); } catch (\Throwable $e) {}
        // (No automatic table drop)
    }
};
