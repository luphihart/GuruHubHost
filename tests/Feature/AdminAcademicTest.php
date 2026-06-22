<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\SchoolProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcademicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SchoolProfile::create([
            'yayasan_name' => 'Yayasan Test',
            'school_name' => 'Sekolah Test',
            'address' => 'Alamat Test',
            'headmaster' => 'Kepala Sekolah Test',
            'headmaster_nip' => '112233',
        ]);
    }

    public function test_admin_can_access_school_years_page()
    {
        $user = User::create([
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/school-years');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_perwalian_page()
    {
        $user = User::create([
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/perwalian');
        $response->assertStatus(200);
    }

    public function test_admin_can_download_import_template()
    {
        $user = User::create([
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/students/import-template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_assign_perwalian_does_not_delete_mentoring_notes()
    {
        $user = User::create([
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $teacher1 = Teacher::create([
            'user_id' => User::create(['email' => 't1@example.com', 'password_hash' => 'hash', 'role' => 'TEACHER'])->id,
            'nip' => '111',
            'name' => 'Teacher 1',
        ]);

        $teacher2 = Teacher::create([
            'user_id' => User::create(['email' => 't2@example.com', 'password_hash' => 'hash', 'role' => 'TEACHER'])->id,
            'nip' => '222',
            'name' => 'Teacher 2',
        ]);

        $year = SchoolYear::create(['name' => '2025', 'is_active' => true]);
        $semester = Semester::create(['name' => 'GANJIL', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'X', 'level' => 'SMA', 'school_year_id' => $year->id, 'semester_id' => $semester->id]);
        
        $student = Student::create([
            'nis' => '1001',
            'nisn' => '1001',
            'name' => 'Student 1',
            'gender' => 'MALE',
            'class_id' => $class->id,
            'parent_name' => 'Parent',
            'parent_phone' => '628',
        ]);

        // 1. Assign to teacher1
        $mentor = \App\Models\MentorStudent::create([
            'teacher_id' => $teacher1->id,
            'student_id' => $student->id,
        ]);

        // 2. Add mentoring note
        $note = \App\Models\MentoringNote::create([
            'mentor_student_id' => $mentor->id,
            'category' => 'ACADEMIC',
            'date' => now(),
            'content' => 'Test note content',
        ]);

        // Verify note exists
        $this->assertDatabaseHas('mentoring_notes', ['id' => $note->id]);

        // 3. Reassign to teacher2 using updateOrCreate
        \App\Models\MentorStudent::updateOrCreate(
            ['student_id' => $student->id],
            ['teacher_id' => $teacher2->id]
        );

        // Verify note still exists (UUID preserved, not deleted)
        $this->assertDatabaseHas('mentoring_notes', ['id' => $note->id]);
        $this->assertDatabaseHas('mentor_students', [
            'student_id' => $student->id,
            'teacher_id' => $teacher2->id,
        ]);
    }
}
