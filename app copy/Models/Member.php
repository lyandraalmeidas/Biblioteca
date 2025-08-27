<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'document',
        'phone',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function fines(): HasManyThrough
    {
        return $this->hasManyThrough(Fine::class, Loan::class);
    }
}
