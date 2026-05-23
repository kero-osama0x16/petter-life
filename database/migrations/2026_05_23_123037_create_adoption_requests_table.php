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
        Schema::create('adoption_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Pet::class)->constrained()->cascadeOnDelete();
            
            // Custom foreign keys referencing the users table
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pet_owner_id')->constrained('users')->cascadeOnDelete();
            
            $table->enum('request_type', ['adoption', 'breeding']);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoption_requests');
    }
};
