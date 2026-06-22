<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\SchoolProfile;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\LearningObjective;
use App\Models\Score;
use App\Models\Agenda;
use App\Models\MentorStudent;
use App\Models\MentoringNote;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Teacher::truncate();
        SchoolProfile::truncate();
        SchoolYear::truncate();
        Semester::truncate();
        SchoolClass::truncate();
        Student::truncate();
        Subject::truncate();
        Schedule::truncate();
        LearningObjective::truncate();
        Score::truncate();
        Agenda::truncate();
        MentorStudent::truncate();
        MentoringNote::truncate();
        Notification::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Hash passwords
        $adminPasswordHash = Hash::make('admin123');
        $teacherPasswordHash = Hash::make('guru123');

        // 2. Create Users
        $adminUser = User::create([
            'email' => 'admin@guruhub.com',
            'password_hash' => $adminPasswordHash,
            'role' => 'ADMIN',
        ]);

        $teacherUser = User::create([
            'email' => 'guru@guruhub.com',
            'password_hash' => $teacherPasswordHash,
            'role' => 'TEACHER',
        ]);

        // 3. Create Teacher
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198804152015031002',
            'name' => 'Budi Santoso, S.Pd.',
            'phone' => '081234567890',
        ]);

        // 4. Create SchoolProfile
        SchoolProfile::create([
            'id' => 'singleton',
            'yayasan_name' => 'YAYASAN PENDIDIKAN GURUHUB INDONESIA',
            'school_name' => 'SMA GURUHUB UTAMA',
            'address' => 'Jl. Antigravity No. 101, Kota Bandung, Jawa Barat',
            'phone' => '(022) 1234567',
            'email' => 'info@guruhub.sch.id',
            'website' => 'www.guruhub.sch.id',
            'headmaster' => 'Dr. H. Mulyadi, M.Pd.',
            'headmaster_nip' => '197208201998031003',
        ]);

        // 5. Create School Year & Semester
        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'name' => 'GENAP',
            'is_active' => true,
        ]);

        // 6. Create Classes
        $classA = SchoolClass::create([
            'name' => 'X MIPA 1',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        $classB = SchoolClass::create([
            'name' => 'XI MIPA 2',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        // 7. Create Students
        $student1 = Student::create([
            'nis' => '10001',
            'nisn' => '0012345678',
            'name' => 'Ahmad Fauzi',
            'gender' => 'MALE',
            'class_id' => $classA->id,
            'parent_name' => 'H. Budi',
            'parent_phone' => '081223344556',
        ]);

        $student2 = Student::create([
            'nis' => '10002',
            'nisn' => '0012345679',
            'name' => 'Citra Lestari',
            'gender' => 'FEMALE',
            'class_id' => $classA->id,
            'parent_name' => 'Agus Setiawan',
            'parent_phone' => '081223344557',
        ]);

        $student3 = Student::create([
            'nis' => '10003',
            'nisn' => '0012345680',
            'name' => 'Dani Wijaya',
            'gender' => 'MALE',
            'class_id' => $classA->id,
            'parent_name' => 'Hendra Gunawan',
            'parent_phone' => '081223344558',
        ]);

        $student4 = Student::create([
            'nis' => '10004',
            'nisn' => '0012345681',
            'name' => 'Eka Putri',
            'gender' => 'FEMALE',
            'class_id' => $classB->id,
            'parent_name' => 'Rudi Hermawan',
            'parent_phone' => '081223344559',
        ]);

        // 8. Create Subjects
        $subjectMath = Subject::create([
            'code' => 'MAT-SMA',
            'name' => 'Matematika Peminatan',
        ]);

        $subjectEng = Subject::create([
            'code' => 'ING-SMA',
            'name' => 'Bahasa Inggris',
        ]);

        // 9. Create Schedules
        $scheduleMath = Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $classA->id,
            'subject_id' => $subjectMath->id,
            'day' => 'MONDAY',
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);

        $scheduleEng = Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $classA->id,
            'subject_id' => $subjectEng->id,
            'day' => 'TUESDAY',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        // 10. Create Learning Objectives (TP)
        $tpMath1 = LearningObjective::create([
            'subject_id' => $subjectMath->id,
            'class_id' => $classA->id,
            'teacher_id' => $teacher->id,
            'code' => 'TP-01',
            'description' => 'Menjelaskan konsep eksponen dan logaritma serta menyelesaikan masalah terkait.',
        ]);

        $tpMath2 = LearningObjective::create([
            'subject_id' => $subjectMath->id,
            'class_id' => $classA->id,
            'teacher_id' => $teacher->id,
            'code' => 'TP-02',
            'description' => 'Menganalisis sifat-sifat fungsi kuadrat dan menggambar grafiknya.',
        ]);

        // 11. Create Scores
        Score::create([
            'student_id' => $student1->id,
            'learning_objective_id' => $tpMath1->id,
            'score' => 85,
        ]);

        Score::create([
            'student_id' => $student2->id,
            'learning_objective_id' => $tpMath1->id,
            'score' => 90,
        ]);

        Score::create([
            'student_id' => $student3->id,
            'learning_objective_id' => $tpMath1->id,
            'score' => 75,
        ]);

        Score::create([
            'student_id' => $student1->id,
            'learning_objective_id' => $tpMath2->id,
            'score' => 80,
        ]);

        Score::create([
            'student_id' => $student2->id,
            'learning_objective_id' => $tpMath2->id,
            'score' => 95,
        ]);

        // 12. Create Agendas
        Agenda::create([
            'teacher_id' => $teacher->id,
            'title' => 'Rapat Koordinasi MGMP',
            'description' => 'Membahas penyusunan TP semester genap tingkat wilayah.',
            'date' => '2026-06-05 00:00:00',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        Agenda::create([
            'teacher_id' => $teacher->id,
            'title' => 'Koreksi Lembar Kerja Siswa',
            'description' => 'Melakukan koreksi hasil latihan logaritma kelas X.',
            'date' => '2026-06-06 13:00:00',
            'start_time' => '13:00',
            'end_time' => '15:00',
        ]);

        // 13. Create MentorStudent & MentoringNote
        $mentor = MentorStudent::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student1->id,
        ]);

        MentoringNote::create([
            'mentor_student_id' => $mentor->id,
            'category' => 'ACADEMIC',
            'date' => now(),
            'content' => 'Ahmad Fauzi menunjukkan peningkatan konsentrasi pada materi matematika, namun perlu bimbingan mandiri untuk latihan logika lanjut.',
            'action_taken' => 'Memberikan latihan soal tambahan terstruktur dan sesi bimbingan 15 menit setelah jam kelas selesai.',
        ]);

        // 14. Create Notifications
        Notification::create([
            'user_id' => $teacherUser->id,
            'title' => 'Presensi Belum Diisi',
            'message' => 'Jadwal Matematika hari Senin belum diisi presensinya.',
            'type' => 'ATTENDANCE_OVERDUE',
        ]);

        Notification::create([
            'user_id' => $teacherUser->id,
            'title' => 'Nilai TP Belum Lengkap',
            'message' => 'Ada 1 murid di kelas X MIPA 1 yang belum memiliki nilai pada TP-02 Matematika.',
            'type' => 'SCORE_INCOMPLETE',
        ]);
    }
}
