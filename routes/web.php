<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. Root redirect route
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    $user = Auth::user();
    if ($user->role === 'ADMIN') {
        return redirect('/admin');
    }
    
    return redirect('/teacher');
});

// 2. Authentication routes
Route::livewire('/login', 'auth.login')->name('login')->middleware('guest');

Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// 3. Admin dashboard and management (role: ADMIN)
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::livewire('/', 'admin.dashboard')->name('admin.dashboard');
    Route::livewire('/teachers', 'admin.teachers')->name('admin.teachers');
    Route::livewire('/students', 'admin.students')->name('admin.students');
    Route::livewire('/classes', 'admin.classes')->name('admin.classes');
    Route::livewire('/subjects', 'admin.subjects')->name('admin.subjects');
    Route::livewire('/schedules', 'admin.schedules')->name('admin.schedules');
    Route::livewire('/school-profile', 'admin.school-profile')->name('admin.school-profile');
    Route::livewire('/account', 'common.account-settings')->name('admin.account');
});

// 4. Teacher portal (role: TEACHER)
Route::middleware(['auth', 'role:TEACHER'])->prefix('teacher')->group(function () {
    Route::livewire('/', 'teacher.dashboard')->name('teacher.dashboard');
    Route::livewire('/attendance', 'teacher.attendance')->name('teacher.attendance');
    Route::livewire('/journals', 'teacher.journals')->name('teacher.journals');
    Route::livewire('/scores', 'teacher.scores')->name('teacher.scores');
    Route::livewire('/agendas', 'teacher.agendas')->name('teacher.agendas');
    Route::livewire('/wali', 'teacher.wali')->name('teacher.wali');
    Route::livewire('/account', 'common.account-settings')->name('teacher.account');
});
