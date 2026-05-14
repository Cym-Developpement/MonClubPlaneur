<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckoutIntentIdToHelloassoPendingPayments extends Migration
{
    public function up()
    {
        Schema::table('helloasso_pending_payments', function (Blueprint $table) {
            $table->string('checkout_intent_id')->nullable()->after('order_id');
            $table->index('checkout_intent_id');
        });
    }

    public function down()
    {
        Schema::table('helloasso_pending_payments', function (Blueprint $table) {
            $table->dropIndex(['checkout_intent_id']);
            $table->dropColumn('checkout_intent_id');
        });
    }
}
