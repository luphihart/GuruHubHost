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
}
