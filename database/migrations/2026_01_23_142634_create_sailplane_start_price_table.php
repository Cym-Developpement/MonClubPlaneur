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
        Schema::create('sailplaneStartPrice', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->integer('basePrice')->nullable();
            $table->integer('public')->nullable()->default(1);
            $table->integer('byMinutes')->nullable()->default(0);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sailplaneStartPrice');
    }
};