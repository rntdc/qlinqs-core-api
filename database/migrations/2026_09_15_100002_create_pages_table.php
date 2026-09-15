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
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profile_id')->unique()->constrained()->cascadeOnDelete();

            // The internal JSONB shape (content: header/socialIcons/blocks;
            // theme: page/blockDefaults/fonts/palette) is validated at the
            // application boundary, per qlinqs-estrutura-de-dados.md (not yet
            // in this repo). `content` defaults to an empty object so a page
            // row can exist before the editor writes real content; `theme`
            // has no default since a page is always created with one
            // (either the default theme or a copy from a template).
            $table->jsonb('content')->default('{}');
            $table->jsonb('theme');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
