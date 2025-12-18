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
    Schema::create('articles', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->text('excerpt')->nullable();
        $table->longText('body');                        // HTML allowed
        $table->string('featured_image')->nullable();    // storage path
        $table->string('tags')->nullable();              // comma-separated
        $table->string('category_name')->nullable();     // simple single category for now
        $table->timestamp('published_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamp('scheduled_publish_date')->nullable();
        $table->string('status')->default('draft'); 
        $table->timestamps();
        $table->softDeletes();

        $table->index(['status','published_at','expires_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
