<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('amount_cts');          // montant en centimes
            $table->boolean('active')->default(true);
            $table->boolean('show_on_site')->default(false);
            $table->string('image_path')->nullable();
            $table->text('email_extra')->nullable(); // texte ajouté dans l'email de confirmation
            $table->timestamps();

            $table->index('active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
