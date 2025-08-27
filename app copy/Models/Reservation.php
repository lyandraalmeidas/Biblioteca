<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'copy_id',
        'member_id',
        'reserved_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
        ];
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
