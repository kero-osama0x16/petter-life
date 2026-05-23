<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        // 'governorate',
        'city',
        'birthday',
        // 'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    // Community feature relationships
    public function communityListings()
    {
        return $this->hasMany(CommunityListing::class);
    }

    /**
     * Get adoption/breeding requests sent BY this user (requester).
     */
    public function sentAdoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class, 'requester_id');
    }

    /**
     * Get adoption/breeding requests received BY this user (pet owner).
     */
    public function receivedAdoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class, 'pet_owner_id');
    }

    /**
     * Get all pending requests received by this user.
     */
    public function getPendingReceivedRequests()
    {
        return $this->receivedAdoptionRequests()->where('status', 'pending')->get();
    }

    /**
     * Get all accepted exchanges for this user (sent + received).
     */
    public function getAcceptedExchanges()
    {
        $sent = $this->sentAdoptionRequests()
            ->where('status', 'accepted')
            ->with('contactExchange')
            ->get();

        $received = $this->receivedAdoptionRequests()
            ->where('status', 'accepted')
            ->with('contactExchange')
            ->get();

        return $sent->merge($received);
    }
}
