<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('role', 20)->default('user')->index()->after('email_verified_at');
            $table->boolean('is_active')->default(true)->index()->after('role');
            $table->string('timezone', 64)->default('UTC')->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('timezone');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'role', 'is_active', 'timezone', 'last_login_at', 'last_login_ip']);
        });
    }
};
