<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MentorStudent extends Model
{
    use HasUuids;

    protected $table = 'mentor_students';

    protected $fillable = [
        'teacher_id',
        'student_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function notes()
    {
        return $this->hasMany(MentoringNote::class, 'mentor_student_id');
    }
}
