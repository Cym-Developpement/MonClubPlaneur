<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->unsignedBigInteger('invoiceId')->nullable()->after('refundId');
            $table->index('invoiceId');
        });
    }

    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropIndex(['invoiceId']);
            $table->dropColumn('invoiceId');
        });
    }
};
