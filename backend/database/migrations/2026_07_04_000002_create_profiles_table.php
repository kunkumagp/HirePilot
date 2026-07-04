<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('avatar', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('headline', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
