<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'designation',
        'designation_other',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayDesignation(): string
    {
        if ($this->designation === 'Other' && $this->designation_other) {
            return 'Other — '.$this->designation_other;
        }

        return $this->designation;
    }
}
