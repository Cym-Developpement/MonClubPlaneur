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
        if (Schema::hasTable('flightDay')) {
            return;
        }

        Schema::create('flightDay', function (Blueprint $table) {
            $table->id();
            $table->text('date')->nullable();
            $table->integer('userId')->nullable();
            $table->text('state')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flightDay');
    }
};