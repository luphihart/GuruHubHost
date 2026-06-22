<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Schedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }
}
