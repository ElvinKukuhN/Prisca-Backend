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
        Schema::create('commercial_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('currency_id');
            $table->foreignUuid('etalase_id');
            $table->foreignUuid('product_id');
            $table->double('price');
            // $table->string('satuan');
            $table->double('berat');
            $table->string('payment_terms');
            $table->integer('discount')->default(0);
            $table->date('price_exp');
            $table->integer('stock');
            $table->integer('pre_order')->default(0);
            $table->enum('contract', ['yes', 'no'])->default('no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_infos');
    }
};
