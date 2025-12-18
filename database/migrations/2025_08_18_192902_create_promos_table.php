<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();

            // META
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('featured_image')->nullable();
            // Categories: All Members / Regular Members / Premium Members
            $table->enum('category', ['all','regular','premium'])->default('all')->index();

            // PUBLISHING + VALIDITY
            $table->string('status', 20)->default('draft'); // draft|published
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('scheduled_publish_date')->nullable();

            // AUDIT
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();

            $table->timestamps();

            $table->index(['status','published_at']);
            $table->index(['starts_at','ends_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('promos');
    }
};