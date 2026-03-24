<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_space_id')->constrained()->cascadeOnDelete();
            $table->string('slot_number');
            $table->enum('type', ['car', 'bike', 'suv', 'bus'])->default('car');
            $table->enum('status', ['available', 'booked', 'blocked', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_slots');
    }
};
