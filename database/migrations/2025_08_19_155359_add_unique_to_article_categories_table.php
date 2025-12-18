<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne("
            SELECT COUNT(1) AS c
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND index_name = ?
        ", [$table, $index]);

        return (int) ($row->c ?? 0) > 0;
    }

    public function up(): void
    {
        if (!Schema::hasTable('article_categories')) {
            return;
        }

        // If the index already exists, skip.
        if ($this->indexExists('article_categories', 'article_categories_name_unique')) {
            return;
        }

        // Try to add it; swallow the duplicate error just in case
        try {
            Schema::table('article_categories', function (Blueprint $table) {
                $table->unique('name', 'article_categories_name_unique');
            });
        } catch (\Throwable $e) {
            // no-op
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('article_categories')) {
            return;
        }

        // Drop only if it exists
        if ($this->indexExists('article_categories', 'article_categories_name_unique')) {
            Schema::table('article_categories', function (Blueprint $table) {
                $table->dropUnique('article_categories_name_unique');
            });
        }
    }
};
