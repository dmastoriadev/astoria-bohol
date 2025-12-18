<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();

            // Basic content
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // image URL or storage path

            // CTAs
            $table->string('cta1_label')->nullable();
            $table->string('cta1_url')->nullable();
            $table->string('cta2_label')->nullable();
            $table->string('cta2_url')->nullable();
            $table->string('cta3_label')->nullable();
            $table->string('cta3_url')->nullable();

            // Trigger controls
            $table->boolean('trigger_on_click')->default(false);
            $table->string('trigger_click_class')->nullable(); // optional custom class

            $table->boolean('trigger_on_load')->default(false);
            $table->unsignedSmallInteger('trigger_load_delay_seconds')->nullable(); // seconds

            $table->boolean('trigger_on_scroll')->default(false);
            $table->enum('trigger_scroll_direction', ['up', 'down'])->nullable();
            $table->unsignedTinyInteger('trigger_scroll_percent')->nullable(); // 25, 50, 75

            // Page targeting
            // all      = all pages
            // include  = only the paths listed
            // exclude  = all except the paths listed
            $table->enum('target_scope', ['all', 'include', 'exclude'])->default('all');
            $table->text('target_paths')->nullable(); // one path pattern per line, e.g. "blog/*" "/about"

            // Status flags
            $table->boolean('is_active')->default(true);   // live on site
            $table->boolean('is_draft')->default(false);   // draft flag (never shown on site)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
