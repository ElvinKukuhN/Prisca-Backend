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
        Schema::create('others', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignUuid("product_id");
            $table->text("incomterm");
            $table->integer("warranty");
            $table->integer("maintenance")->default(12);
            $table->string("sku")->nullable();
            $table->string("tags");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('others');
    }
};
