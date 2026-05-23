<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdoptionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pet_id',
        'requester_id',
        'pet_owner_id',
        'request_type',
        'status',
        'message',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the pet being requested.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the user who made the request.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the pet owner user.
     */
    public function petOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pet_owner_id');
    }

    /**
     * Get the contact exchange for this request (if accepted).
     */
    public function contactExchange(): HasOne
    {
        return $this->hasOne(ContactExchange::class);
    }

    /**
     * Scope to get only pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only accepted requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get only rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get requests sent by a specific user.
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('requester_id', $userId);
    }

    /**
     * Scope to get requests received by a specific user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('pet_owner_id', $userId);
    }

    /**
     * Scope to filter by request type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Check if request is for adoption.
     */
    public function isAdoptionRequest(): bool
    {
        return $this->request_type === 'adoption';
    }

    /**
     * Check if request is for breeding.
     */
    public function isBreedingRequest(): bool
    {
        return $this->request_type === 'breeding';
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Accept the request and create contact exchange.
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        // Create contact exchange record
        ContactExchange::create([
            'adoption_request_id' => $this->id,
            'requester_contact' => [
                'name' => $this->requester->name,
                'email' => $this->requester->email,
                'phone_number' => $this->requester->phone_number,
                'city' => $this->requester->city,
            ],
            'owner_contact' => [
                'name' => $this->petOwner->name,
                'email' => $this->petOwner->email,
                'phone_number' => $this->petOwner->phone_number,
                'city' => $this->petOwner->city,
            ],
        ]);
    }

    /**
     * Reject the request.
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }
}
