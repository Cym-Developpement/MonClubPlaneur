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
        if (Schema::hasTable('refund')) {
            return;
        }

        Schema::create('refund', function (Blueprint $table) {
            $table->id();
            $table->integer('idUser')->nullable();
            $table->integer('time')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('refundType')->nullable();
            $table->integer('category')->nullable();
            $table->text('file')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund');
    }
};