<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('nip')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. School Years
        Schema::create('school_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 3. Semesters
        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // "GANJIL" atau "GENAP"
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 4. Classes
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('level'); // "SD", "SMP", "SMA", "SMK"
            $table->uuid('school_year_id');
            $table->uuid('semester_id');
            $table->timestamps();

            $table->foreign('school_year_id')->references('id')->on('school_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });

        // 5. Students
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->string('name');
            $table->string('gender'); // MALE, FEMALE
            $table->uuid('class_id');
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });

        // 6. Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // 7. Schedules
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->uuid('class_id');
            $table->uuid('subject_id');
            $table->string('day'); // MONDAY, TUESDAY, etc.
            $table->string('start_time'); // HH:MM
            $table->string('end_time'); // HH:MM
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });

        // 8. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schedule_id');
            $table->dateTime('date');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->unique(['schedule_id', 'date']);
        });

        // 9. Attendance Details
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_id');
            $table->uuid('student_id');
            $table->string('status')->default('HADIR'); // HADIR, IZIN, SAKIT, ALPA
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['attendance_id', 'student_id']);
        });

        // 10. Journals
        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schedule_id');
            $table->dateTime('date');
            $table->text('material');
            $table->text('activity');
            $table->text('notes')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, COMPLETED
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->unique(['schedule_id', 'date']);
        });

        // 11. Learning Objectives
        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subject_id');
            $table->uuid('class_id');
            $table->uuid('teacher_id');
            $table->string('code'); // contoh: "TP-01"
            $table->text('description');
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->unique(['subject_id', 'class_id', 'teacher_id', 'code']);
        });

        // 12. Scores
        Schema::create('scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('learning_objective_id');
            $table->integer('score'); // 0-100
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('learning_objective_id')->references('id')->on('learning_objectives')->onDelete('cascade');
            $table->unique(['student_id', 'learning_objective_id']);
        });

        // 13. Agendas
        Schema::create('agendas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->string('start_time'); // HH:MM
            $table->string('end_time'); // HH:MM
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });

        // 14. Mentor Students
        Schema::create('mentor_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->uuid('student_id')->unique();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // 15. Mentoring Notes
        Schema::create('mentoring_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mentor_student_id');
            $table->string('category'); // ACADEMIC, ATTENDANCE, DISCIPLINE, etc.
            $table->dateTime('date');
            $table->text('content');
            $table->text('action_taken')->nullable();
            $table->timestamps();

            $table->foreign('mentor_student_id')->references('id')->on('mentor_students')->onDelete('cascade');
        });

        // 16. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('type')->default('GENERAL');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 17. School Profiles
        Schema::create('school_profiles', function (Blueprint $table) {
            $table->string('id')->primary()->default('singleton');
            $table->string('yayasan_name');
            $table->string('school_name');
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('headmaster');
            $table->string('headmaster_nip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mentoring_notes');
        Schema::dropIfExists('mentor_students');
        Schema::dropIfExists('agendas');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('learning_objectives');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('attendance_details');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('teachers');
    }
};
