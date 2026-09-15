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
        // Owned by the profile, not the page, so the same upload can be
        // reused across pages once multi-page support exists.
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profile_id')->constrained()->cascadeOnDelete();
            $table->text('storage_path')->unique();
            $table->text('mime_type');
            $table->bigInteger('size_bytes');
            $table->integer('width');
            $table->integer('height');
            $table->timestampTz('created_at')->useCurrent();

            $table->index('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
