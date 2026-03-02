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
        if (Schema::hasTable('alerts')) {
            return;
        }

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->text('text')->nullable();
            $table->integer('read')->nullable()->default(0);
            $table->integer('expire_at')->nullable()->default(0);
            $table->integer('markAsReadOnClose')->nullable()->default(1);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};