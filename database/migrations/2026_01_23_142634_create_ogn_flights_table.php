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
        if (Schema::hasTable('ogn_flights')) {
            return;
        }

        Schema::create('ogn_flights', function (Blueprint $table) {
            $table->id();
            $table->text('date')->nullable();
            $table->text('data')->nullable();
            $table->integer('imported')->nullable()->default(0);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ogn_flights');
    }
};