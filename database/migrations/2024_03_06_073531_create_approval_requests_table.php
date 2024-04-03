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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id');
            $table->string('doc_code');
            $table->integer('sequence');
            $table->enum('approval_status', ['pending', 'approved', 'rejected']);
            $table->enum('doc_type', ['pr', 'po']);
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * R    everse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
