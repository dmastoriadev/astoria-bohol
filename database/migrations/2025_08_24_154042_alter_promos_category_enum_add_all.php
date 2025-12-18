<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE promos
            MODIFY COLUMN category
            ENUM('all','regular','premium')
            NOT NULL
            DEFAULT 'all'
        ");
        DB::statement("UPDATE promos SET category='all' WHERE category IS NULL OR category=''");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE promos
            MODIFY COLUMN category
            ENUM('regular','premium')
            NOT NULL
            DEFAULT 'regular'
        ");
    }
};
