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
        if (Schema::hasTable('todolist')) {
            return;
        }

        Schema::create('todolist', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('created_by');
            $table->integer('assigned_to')->nullable();
            $table->string('status')->default('\'pending\'');
            $table->string('priority')->default('\'medium\'');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('completed_by')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index('due_date');
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('priority');
            $table->index('status');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('NO ACTION');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('NO ACTION');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('NO ACTION');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todolist');
    }
};