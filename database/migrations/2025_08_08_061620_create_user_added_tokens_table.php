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
        Schema::create('user_added_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('account', 42)->index();
            $table->integer('chain_id')->index();
            $table->string('symbol', 20);
            $table->string('token_address', 42)->index();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_added_tokens');
    }
};
