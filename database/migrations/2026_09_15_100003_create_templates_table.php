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
        // No FK to `pages` on purpose: applying a template copies its theme
        // into pages.theme, it doesn't link to it. Editing or removing a
        // template must never affect existing pages.
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name')->unique();
            $table->text('preview');
            $table->jsonb('theme');
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
