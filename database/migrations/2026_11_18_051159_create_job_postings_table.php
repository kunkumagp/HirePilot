<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table): void {
            $table->id();
            $table->string('source');
            $table->string('external_id');
            $table->string('title');
            $table->string('company');
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('location_text')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->string('content_hash', 64);
            $table->timestamps();

            // One posting per external id from a given source.
            $table->unique(['source', 'external_id']);
            // Used to collapse the same posting arriving from different sources.
            $table->index('content_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
