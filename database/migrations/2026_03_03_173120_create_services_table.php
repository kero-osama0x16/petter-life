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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., 'vet' or 'pharmacy'
            $table->decimal('lat', 10, 8)->nullable(); // Precision for map coordinates
            $table->decimal('long', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->decimal('rating', 2, 1)->nullable(); // e.g., 4.5
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
