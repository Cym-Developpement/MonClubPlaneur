<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vol_initiations')) {
            return;
        }

        Schema::create('vol_initiations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('source', 20)->default('admin');
            $table->string('type', 100)->nullable();
            $table->integer('prix_cts')->nullable();
            $table->boolean('actif')->default(false);
            $table->boolean('realise')->default(false);
            $table->date('date_realisation')->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('adresse')->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('ville')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->string('helloasso_order_id')->nullable();
            $table->string('helloasso_payment_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_initiations');
    }
};
