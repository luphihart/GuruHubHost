<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Agenda extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
