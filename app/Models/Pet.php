<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia; 
use Spatie\MediaLibrary\InteractsWithMedia; 

class Pet extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PetFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id', 'name', 'type', 'breed', 'gender', 
        'birthday', 'personality', 'color'
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'personality' => 'array',
        ];
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function weightLogs()
    {
        return $this->hasMany(WeightLog::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    // Community feature relationships
    public function communityListings()
    {
        return $this->hasMany(CommunityListing::class);
    }

    public function adoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    /**
     * Get all active community listings for this pet.
     */
    public function getActiveListings()
    {
        return $this->communityListings()->where('is_active', true)->get();
    }

    /**
     * Check if pet is listed for adoption.
     */
    public function isListedForAdoption(): bool
    {
        return $this->communityListings()
            ->where('listing_type', 'adoption')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if pet is listed for breeding.
     */
    public function isListedForBreeding(): bool
    {
        return $this->communityListings()
            ->where('listing_type', 'breeding')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get pending adoption/breeding requests for this pet.
     */
    public function getPendingRequests()
    {
        return $this->adoptionRequests()->where('status', 'pending')->get();
    }
}
