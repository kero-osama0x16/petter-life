<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia; 
use Spatie\MediaLibrary\InteractsWithMedia; 

class MedicalRecord extends Model
{
    /** @use HasFactory<\Database\Factories\MedicalRecordFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['pet_id', 'type', 'title', 'description', 'date'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
