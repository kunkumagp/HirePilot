<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table): void {
            $table->string('field')->nullable()->after('skills');
            $table->json('field_keywords')->nullable()->after('field');
            $table->string('experience_level')->default('any')->after('field_keywords');
            $table->unsignedInteger('min_years_experience')->nullable()->after('experience_level');
            $table->string('work_arrangement')->default('any')->after('min_years_experience');
            $table->json('preferred_countries')->nullable()->after('work_arrangement');
            $table->string('salary_currency')->default('USD')->after('preferred_countries');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'field',
                'field_keywords',
                'experience_level',
                'min_years_experience',
                'work_arrangement',
                'preferred_countries',
                'salary_currency',
            ]);
        });
    }
};
