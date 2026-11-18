<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->json('skills')->nullable();
            $table->json('title_include')->nullable();
            $table->json('exclude_words')->nullable();
            $table->json('location_accept')->nullable();
            $table->json('location_reject')->nullable();
            $table->unsignedInteger('max_age_days')->default(14);
            $table->unsignedInteger('min_salary_usd_yearly')->default(24000);
            $table->boolean('allow_unknown_salary')->default(true);
            $table->integer('min_score')->default(5);
            $table->json('weights')->nullable();
            $table->unsignedInteger('max_notifications_per_run')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
