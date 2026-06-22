<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MentoringNote extends Model
{
    use HasUuids;

    protected $table = 'mentoring_notes';

    protected $fillable = [
        'mentor_student_id',
        'category', // ACADEMIC, ATTENDANCE, DISCIPLINE, etc.
        'date',
        'content',
        'action_taken',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function mentorStudent()
    {
        return $this->belongsTo(MentorStudent::class, 'mentor_student_id');
    }
}
