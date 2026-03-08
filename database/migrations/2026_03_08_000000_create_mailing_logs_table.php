<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mailing_logs')) {
            return;
        }

        Schema::create('mailing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_by');
            $table->string('subject');
            $table->text('body');
            $table->string('filter');
            $table->integer('recipient_count')->default(0);
            $table->string('test_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailing_logs');
    }
};
