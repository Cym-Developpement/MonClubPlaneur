<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_title');        // snapshot au moment de l'achat
            $table->integer('amount_cts');          // snapshot du montant en centimes

            $table->string('payer_firstname');
            $table->string('payer_lastname');
            $table->string('payer_email');
            $table->text('message')->nullable();

            $table->string('helloasso_checkout_intent_id')->nullable();
            $table->string('helloasso_order_id')->nullable();
            $table->string('helloasso_payment_id')->nullable()->unique();

            $table->string('status')->default('pending'); // pending | paid | failed
            $table->timestamp('paid_at')->nullable();
            $table->json('webhook_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_purchases');
    }
}
