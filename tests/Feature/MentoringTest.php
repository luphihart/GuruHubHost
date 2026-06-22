<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\MentorStudent;
use App\Models\MentoringNote;
use App\Models\SchoolProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup a basic school profile
        SchoolProfile::create([
            'yayasan_name' => 'Yayasan Test',
            'school_name' => 'Sekolah Test',
            'address' => 'Alamat Test',
            'headmaster' => 'Kepala Sekolah Test',
            'headmaster_nip' => '112233',
        ]);
    }

    public function test_teacher_can_access_mentoring_page()
    {
        $user = User::create([
            'email' => 'testguru@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'TEACHER',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '123456',
            'name' => 'Test Teacher',
        ]);

        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'name' => 'GENAP',
            'is_active' => true,
        ]);

        $class = SchoolClass::create([
            'name' => 'X MIPA 1',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        $student = Student::create([
            'nis' => '10001',
            'nisn' => '0012345678',
            'name' => 'Ahmad Test',
            'gender' => 'MALE',
            'class_id' => $class->id,
            'parent_name' => 'Orang Tua Test',
            'parent_phone' => '628122334455',
        ]);

        $mentor = MentorStudent::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($user)->get('/teacher/wali');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Test');
    }

    public function test_teacher_can_create_mentoring_note()
    {
        $user = User::create([
            'email' => 'testguru2@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'TEACHER',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '1234567',
            'name' => 'Test Teacher 2',
        ]);

        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'name' => 'GENAP',
            'is_active' => true,
        ]);

        $class = SchoolClass::create([
            'name' => 'X MIPA 1',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        $student = Student::create([
            'nis' => '10002',
            'nisn' => '0012345679',
            'name' => 'Budi Test',
            'gender' => 'MALE',
            'class_id' => $class->id,
            'parent_name' => 'Orang Tua Test 2',
            'parent_phone' => '628122334456',
        ]);

        $mentor = MentorStudent::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test('teacher.wali')
            ->set('selectedMentorStudentId', $mentor->id)
            ->set('category', 'DISCIPLINE')
            ->set('content', 'Murid kedapatan melanggar aturan sekolah')
            ->set('action_taken', 'Diberikan bimbingan konseling awal')
            ->call('saveNote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mentoring_notes', [
            'mentor_student_id' => $mentor->id,
            'category' => 'DISCIPLINE',
            'content' => 'Murid kedapatan melanggar aturan sekolah',
            'action_taken' => 'Diberikan bimbingan konseling awal',
        ]);
    }

    public function test_teacher_can_delete_mentoring_note()
    {
        $user = User::create([
            'email' => 'testguru3@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'TEACHER',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '12345678',
            'name' => 'Test Teacher 3',
        ]);

        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'name' => 'GENAP',
            'is_active' => true,
        ]);

        $class = SchoolClass::create([
            'name' => 'X MIPA 1',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        $student = Student::create([
            'nis' => '10003',
            'nisn' => '0012345680',
            'name' => 'Caca Test',
            'gender' => 'FEMALE',
            'class_id' => $class->id,
            'parent_name' => 'Orang Tua Test 3',
            'parent_phone' => '628122334457',
        ]);

        $mentor = MentorStudent::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $note = MentoringNote::create([
            'mentor_student_id' => $mentor->id,
            'category' => 'ACADEMIC',
            'date' => now(),
            'content' => 'Catatan akademik untuk dihapus',
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test('teacher.wali')
            ->set('selectedMentorStudentId', $mentor->id)
            ->call('deleteNote', $note->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('mentoring_notes', [
            'id' => $note->id,
        ]);
    }

    public function test_teacher_can_download_pdf_report()
    {
        $user = User::create([
            'email' => 'testguru4@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'TEACHER',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '123456789',
            'name' => 'Test Teacher 4',
        ]);

        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'name' => 'GENAP',
            'is_active' => true,
        ]);

        $class = SchoolClass::create([
            'name' => 'X MIPA 1',
            'level' => 'SMA',
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);

        $student = Student::create([
            'nis' => '10004',
            'nisn' => '0012345681',
            'name' => 'Deni Test',
            'gender' => 'MALE',
            'class_id' => $class->id,
            'parent_name' => 'Orang Tua Test 4',
            'parent_phone' => '628122334458',
        ]);

        $mentor = MentorStudent::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $note = MentoringNote::create([
            'mentor_student_id' => $mentor->id,
            'category' => 'ACADEMIC',
            'date' => now(),
            'content' => 'Catatan akademik Ahmad',
        ]);

        $this->actingAs($user);

        $response = \Livewire\Livewire::test('teacher.wali')
            ->call('downloadPdf', $mentor->id);

        $response->assertFileDownloaded('X_MIPA_1_Deni_Test_PEMBINAAN.pdf');
    }
}

