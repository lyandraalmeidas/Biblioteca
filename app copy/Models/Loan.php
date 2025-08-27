<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'copy_id',
        'member_id',
        'loaned_at',
        'due_at',
        'returned_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
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

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
