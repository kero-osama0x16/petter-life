<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public const TYPE_VET = 'vet';
    public const TYPE_PHARMACY = 'pharmacy';
    public const TYPE_GROOMER = 'groomer';
    public const TYPE_BOARDING = 'boarding';
    public const TYPE_DOG_PARK = 'dog_park';
    public const TYPE_PET_SHOP = 'pet_shop';

    public const TYPES = [
        self::TYPE_VET,
        self::TYPE_PHARMACY,
        self::TYPE_GROOMER,
        self::TYPE_BOARDING,
        self::TYPE_DOG_PARK,
        self::TYPE_PET_SHOP,
    ];

    protected $fillable = ['name', 'type', 'lat', 'long', 'address', 'rating'];

    public static function isValidType(?string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
