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
        if (Schema::hasTable('parametres')) {
            return;
        }

        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->text('nom')->nullable();
            $table->text('type')->nullable();
            $table->text('value')->nullable();
            $table->integer('public')->nullable()->default(0);
            $table->integer('monetary')->nullable()->default(0);
            $table->text('description')->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};