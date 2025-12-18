<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('article_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();   // uniqueness at DB level
        $table->string('slug')->unique();   // uniqueness at DB level
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('article_category');
    }
};
