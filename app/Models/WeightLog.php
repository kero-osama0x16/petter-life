<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightLog extends Model
{
    /** @use HasFactory<\Database\Factories\WeightLogFactory> */
    use HasFactory;
    protected $fillable = ['pet_id', 'weight', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
        ];
    }

    //ouldnt this be added ? and for the rest of the models as well ?
   
    // Relationship to Pet
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
