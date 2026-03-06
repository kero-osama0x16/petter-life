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
}
