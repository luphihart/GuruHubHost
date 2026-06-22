<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Journal extends Model
{
    use HasUuids;

    protected $fillable = [
        'schedule_id',
        'date',
        'material',
        'activity',
        'notes',
        'status', // DRAFT, COMPLETED
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
