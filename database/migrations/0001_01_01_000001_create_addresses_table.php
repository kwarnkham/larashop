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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('country');
            $table->string('province');
            $table->string('township');
            $table->string('city');
            $table->string('street_one');
            $table->string('street_two')->nullable();
            $table->string('street_three')->nullable();
            $table->string('street_four')->nullable();
            $table->string('phone');
            $table->string('zip_code')->nullable();
            $table->boolean('default')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
