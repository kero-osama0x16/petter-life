<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    /** @use HasFactory<\Database\Factories\ReminderFactory> */
    use HasFactory;
    protected $fillable = ['pet_id', 'title', 'remind_at', 'is_completed'];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }
    
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
