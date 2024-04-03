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
        Schema::create('user_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id');
            $table->string('company_code')->nullable();
            $table->string('divisi_code')->nullable();
            $table->string('departemen_code')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->foreign('company_code')->references('code')->on('companies');
            $table->foreign('divisi_code')->references('code')->on('divisis');
            $table->foreign('departemen_code')->references('code')->on('departemens');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('user_companies');
    }

};
