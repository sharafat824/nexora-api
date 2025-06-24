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
            Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('type'); // deposit, withdrawal, daily_income, referral_commission, etc.
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('level')->nullable(); // For referral commissions
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
