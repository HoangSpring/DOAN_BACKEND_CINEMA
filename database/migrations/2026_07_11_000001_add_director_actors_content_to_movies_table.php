<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('director')->nullable()->after('description');
            $table->text('actors')->nullable()->after('director');
            $table->text('content')->nullable()->after('actors');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['director', 'actors', 'content']);
        });
    }
};
