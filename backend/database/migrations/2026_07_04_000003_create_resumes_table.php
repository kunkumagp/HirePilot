<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'deleted_at']);
        });

        Schema::create('resume_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->boolean('is_current')->default(false);
            $table->string('original_filename', 255);
            $table->string('file_path', 255);
            $table->string('file_type', 10);
            $table->unsignedInteger('file_size');
            $table->json('parsed_content')->nullable();
            $table->longText('raw_text')->nullable();
            $table->string('parse_status', 20)->default('pending');
            $table->unsignedTinyInteger('parse_attempts')->default(0);
            $table->text('parse_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['resume_id', 'is_current']);
            $table->index(['resume_id', 'version_number']);
            $table->index('parse_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_versions');
        Schema::dropIfExists('resumes');
    }
};
