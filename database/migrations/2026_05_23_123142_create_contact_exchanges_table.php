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
        Schema::create('contact_exchanges', function (Blueprint $table) {
            $table->id();
            
            // Link back to the accepted request
            $table->foreignId('adoption_request_id')->constrained('adoption_requests')->cascadeOnDelete();
            
            // JSON columns are great here so we can snapshot the contact info 
            // at the time of exchange, even if the user changes their profile later.
            $table->json('requester_contact');
            $table->json('owner_contact');
            
            $table->timestamp('exchanged_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_exchanges');
    }
};
