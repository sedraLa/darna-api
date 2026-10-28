<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('SYP');

            $table->string('purpose');
            $table->string('status')->default('pending');
            $table->string('type');

            $table->decimal('area', 10, 2);

            $table->foreignId('governorate_id')->constrained()->restrictOnDelete();
            $table->foreignId('office_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->constrained()->restrictOnDelete();

            $table->index('purpose');
            $table->index('type');
            $table->index('status');
            $table->index('price');
            $table->index('area');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
