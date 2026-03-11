<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUser');
            $table->enum('type', ['facture', 'avoir'])->default('facture');
            $table->string('invoiceNumber', 30)->unique();
            $table->unsignedInteger('sequence')->unique();
            $table->unsignedBigInteger('relatedInvoiceId')->nullable();
            $table->integer('periodStart');
            $table->integer('periodEnd');
            $table->integer('totalAmount');
            $table->integer('emittedAt');
            $table->string('pdfPath', 255)->nullable();
            $table->string('pdfHash', 64)->nullable();
            $table->timestamps();

            $table->index(['idUser', 'type']);
            $table->foreign('idUser')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
