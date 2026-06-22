<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Student extends Model
{
    use HasUuids;

    protected $fillable = [
        'nis',
        'nisn',
        'name',
        'gender',
        'class_id',
        'parent_name',
        'parent_phone',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendanceDetails()
    {
        return $this->hasMany(AttendanceDetail::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function mentorStudent()
    {
        return $this->hasOne(MentorStudent::class);
    }
}
