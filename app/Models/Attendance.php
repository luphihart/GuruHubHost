<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'schedule_id',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function details()
    {
        return $this->hasMany(AttendanceDetail::class);
    }
}
