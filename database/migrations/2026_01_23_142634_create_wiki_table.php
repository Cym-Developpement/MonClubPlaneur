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
        if (Schema::hasTable('wiki')) {
            return;
        }

        Schema::create('wiki', function (Blueprint $table) {
            $table->id();
            $table->text('pageName')->nullable();
            $table->integer('parent')->nullable();
            $table->text('content')->nullable();
            $table->integer('levelRead')->nullable();
            $table->integer('levelWrite')->nullable();
            $table->text('userName')->nullable();
            $table->text('UUID')->nullable()->default(null);
            $table->text('deleted_at')->nullable()->default(null);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wiki');
    }
};