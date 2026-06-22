<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceDetail extends Model
{
    use HasUuids;

    protected $table = 'attendance_details';

    protected $fillable = [
        'attendance_id',
        'student_id',
        'status', // HADIR, IZIN, SAKIT, ALPA
        'notes',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
