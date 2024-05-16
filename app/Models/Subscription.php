<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Subscription extends Model
{
    use HasFactory,Notifiable;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    // Abdur Rahman //
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
