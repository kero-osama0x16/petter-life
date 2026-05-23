<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityListing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pet_id',
        'user_id',
        'listing_type',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the pet that owns the listing.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the user who created the listing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active listings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by listing type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('listing_type', $type);
    }

    /**
     * Check if listing is for adoption.
     */
    public function isAdoption(): bool
    {
        return $this->listing_type === 'adoption';
    }

    /**
     * Check if listing is for breeding.
     */
    public function isBreeding(): bool
    {
        return $this->listing_type === 'breeding';
    }
}
