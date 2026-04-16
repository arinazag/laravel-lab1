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
        Schema::table('users', function (Blueprint $table) {
            // Добавляем колонку username после id, уникальный
            $table->string('username')->unique()->after('id');
            // Добавляем колонку birthday после email
            $table->date('birthday')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем добавленные колонки при откате миграции
            $table->dropColumn(['username', 'birthday']);
        });
    }
};