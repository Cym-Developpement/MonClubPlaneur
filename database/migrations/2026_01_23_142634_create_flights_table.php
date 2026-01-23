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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->integer('idUser')->nullable();
            $table->text('pilotState')->nullable();
            $table->integer('totalTime')->nullable();
            $table->text('takeOffTime')->nullable();
            $table->text('landingTime')->nullable();
            $table->integer('flightTimestamp')->nullable();
            $table->float('value')->nullable();
            $table->integer('landing')->nullable();
            $table->integer('aircraftId')->nullable();
            $table->integer('motorStartTime')->nullable();
            $table->integer('motorEndTime')->nullable();
            $table->text('airportStartCode')->nullable();
            $table->text('airportEndCode')->nullable();
            $table->integer('startType')->nullable();
            $table->integer('transactionID')->nullable();
            $table->integer('towingFlightId')->nullable();
            $table->integer('userPayId')->nullable();
            $table->integer('idInstructor')->nullable()->default(0);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};