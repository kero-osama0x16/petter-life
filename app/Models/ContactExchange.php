<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactExchange extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adoption_request_id',
        'requester_contact',
        'owner_contact',
        'exchanged_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requester_contact' => 'array',
        'owner_contact' => 'array',
        'exchanged_at' => 'datetime',
    ];

    /**
     * Get the adoption request this exchange belongs to.
     */
    public function adoptionRequest(): BelongsTo
    {
        return $this->belongsTo(AdoptionRequest::class);
    }

    /**
     * Get the requester's contact info.
     */
    public function getRequesterContact(): array
    {
        return $this->requester_contact ?? [];
    }

    /**
     * Get the owner's contact info.
     */
    public function getOwnerContact(): array
    {
        return $this->owner_contact ?? [];
    }
}
