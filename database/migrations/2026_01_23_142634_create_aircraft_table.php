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
        if (Schema::hasTable('aircraft')) {
            return;
        }

        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->integer('minPrice')->nullable()->default(0);
            $table->integer('basePrice')->nullable();
            $table->integer('motorPrice')->nullable();
            $table->integer('motorPriceType')->nullable()->default(1);
            $table->integer('type')->nullable();
            $table->text('register')->nullable();
            $table->integer('public')->nullable()->default(1);
            $table->integer('exportFFVP')->nullable()->default(1);
            $table->integer('actif')->nullable()->default(1);
            $table->integer('isTower')->nullable()->default(0);
            $table->text('ognAddress')->nullable()->default(null);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};