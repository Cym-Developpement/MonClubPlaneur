<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelloassoPendingPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('helloasso_pending_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique();
            $table->string('order_id');
            $table->integer('amount');
            $table->string('payer_email');
            $table->string('payer_name');
            $table->integer('installment_number')->default(1);
            $table->json('webhook_data');
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('helloasso_pending_payments');
    }
}
