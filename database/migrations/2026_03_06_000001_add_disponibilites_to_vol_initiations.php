<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vol_initiations', function (Blueprint $table) {
            $table->text('disponibilites')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('vol_initiations', function (Blueprint $table) {
            $table->dropColumn('disponibilites');
        });
    }
};
