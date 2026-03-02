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
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('recordPasswordAdminAccess')->nullable();
            $table->rememberToken('remember_token')->nullable();
            $table->integer('sexe')->nullable();
            $table->integer('isAdmin')->nullable()->default(0);
            $table->text('licenceNumber')->nullable();
            $table->integer('isSupervisor')->nullable()->default(0);
            $table->integer('FFVP')->nullable()->default(1);
            $table->integer('FFPLUM')->nullable()->default(1);
            $table->integer('state')->nullable()->default(1);
            $table->timestamps();
            $table->unique('email');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};