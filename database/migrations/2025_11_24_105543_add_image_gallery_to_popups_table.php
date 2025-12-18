<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            if (! Schema::hasColumn('popups', 'image_gallery')) {
                $table->text('image_gallery')->nullable()->after('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            if (Schema::hasColumn('popups', 'image_gallery')) {
                $table->dropColumn('image_gallery');
            }
        });
    }
};
