<?php

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type');
            $table->enum('type', ['larapay'])->default(PaymentType::Larapay->value);
            $table->enum(
                'status',
                ['pending', 'processing', 'completed', 'canceled']
            )->default(PaymentStatus::Pending->value);
            $table->double('amount');
            $table->jsonb('result')->nullable();
            $table->string('pay_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
