<?php

use Livewire\Component;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;

new class extends Component
{
    public $teacherCount = 0;
    public $studentCount = 0;
    public $classCount = 0;
    public $subjectCount = 0;
    public $teachersList = [];

    public function mount()
    {
        $this->teacherCount = Teacher::count();
        $this->studentCount = Student::count();
        $this->classCount = SchoolClass::count();
        $this->subjectCount = Subject::count();
        
        $this->teachersList = Teacher::latest()->take(10)->get()->toArray();
    }

    public function getMockStatus($index)
    {
        $statuses = [
            ['text' => 'Lengkap', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
            ['text' => 'Belum Lengkap', 'bg' => 'bg-amber-50 text-amber-700 border-amber-100'],
            ['text' => 'Terlambat', 'bg' => 'bg-rose-50 text-rose-700 border-rose-100'],
        ];
        return $statuses[$index % 3];
    }
};
?>

<div class="space-y-6">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#4F46E5] to-[#0EA5E9] p-6 lg:p-8 rounded-3xl text-white shadow-xl shadow-[#4F46E5]/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/10 rounded-full blur-2xl -mr-20 -mt-20"></div>
        <div class="relative z-10 space-y-2">
            <span class="text-white/80 text-sm font-semibold tracking-wide uppercase">Dashboard Admin</span>
            <h2 class="text-3xl font-extrabold tracking-tight font-display">
                Selamat Datang, Administrator 💻
            </h2>
            <p class="text-white/90 text-sm lg:text-base max-w-xl">
                Pantau keaktifan mengajar guru dan pastikan seluruh kelengkapan dokumen administrasi sekolah (Presensi & Jurnal) berjalan teratur.
            </p>
        </div>
    </section>

    <!-- Statistics Counters -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Jumlah Guru -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#4F46E5]/10 text-[#4F46E5] flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Jumlah Guru</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $teacherCount }} Guru
                </span>
            </div>
        </div>

        <!-- Jumlah Murid -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#0EA5E9]/10 text-[#0EA5E9] flex items-center justify-center flex-shrink-0">
                <i data-lucide="graduation-cap" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Jumlah Murid</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $studentCount }} Murid
                </span>
            </div>
        </div>

        <!-- Jumlah Kelas -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#10B981]/10 text-[#10B981] flex items-center justify-center flex-shrink-0">
                <i data-lucide="school" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Jumlah Kelas</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $classCount }} Kelas
                </span>
            </div>
        </div>

        <!-- Jumlah Mapel -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#F59E0B]/10 text-[#F59E0B] flex items-center justify-center flex-shrink-0">
                <i data-lucide="book-open" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Jumlah Mapel</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $subjectCount }} Mapel
                </span>
            </div>
        </div>
    </section>

    <!-- Analitik Kelengkapan Administrasi & Grafik Premium -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart Column -->
        <section class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm space-y-4 lg:col-span-1">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">Penyelesaian Administrasi</h3>
                <span class="text-xs font-semibold text-[#10B981] flex items-center gap-0.5">
                    <i data-lucide="trending-up" class="h-3.5 w-3.5"></i> +4.2%
                </span>
            </div>
            
            <!-- Custom HTML/CSS Bar Chart -->
            <div class="space-y-5 pt-4">
                <!-- Bar 1: Presensi -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-600">Presensi Mengajar</span>
                        <span class="text-[#10B981]">85% Lengkap</span>
                    </div>
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden flex">
                        <div class="bg-[#10B981] h-full" style="width: 85%"></div>
                        <div class="bg-[#F59E0B] h-full" style="width: 15%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400">
                        <span>Lengkap: 85%</span>
                        <span>Belum: 15%</span>
                    </div>
                </div>

                <!-- Bar 2: Jurnal -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-600">Jurnal Harian</span>
                        <span class="text-[#10B981]">78% Lengkap</span>
                    </div>
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden flex">
                        <div class="bg-[#10B981] h-full" style="width: 78%"></div>
                        <div class="bg-[#F59E0B] h-full" style="width: 22%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400">
                        <span>Lengkap: 78%</span>
                        <span>Belum: 22%</span>
                    </div>
                </div>

                <!-- Bar 3: Nilai TP -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-600">Nilai Tujuan Pembelajaran</span>
                        <span class="text-[#10B981]">65% Lengkap</span>
                    </div>
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden flex">
                        <div class="bg-[#10B981] h-full" style="width: 65%"></div>
                        <div class="bg-[#F59E0B] h-full" style="width: 35%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400">
                        <span>Lengkap: 65%</span>
                        <span>Belum: 35%</span>
                    </div>
                </div>
            </div>
            
            <div class="pt-4 border-t border-slate-100 flex justify-around text-[10px] font-bold">
                <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 bg-[#10B981] rounded-sm"></span> Lengkap</span>
                <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 bg-[#F59E0B] rounded-sm"></span> Belum Lengkap</span>
            </div>
        </section>

        <!-- Tabel Pemantauan Guru Column -->
        <section class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm space-y-4 lg:col-span-2">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">Pemantauan Administrasi Guru</h3>
            
            <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0]">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] text-[#64748B] font-bold border-b border-[#E2E8F0]">
                            <th class="p-3">No</th>
                            <th class="p-3">Nama Guru</th>
                            <th class="p-3">No. Telepon</th>
                            <th class="p-3">Status Presensi</th>
                            <th class="p-3">Status Jurnal</th>
                            <th class="p-3">Status Nilai TP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($teachersList as $index => $teacher)
                            @php
                                $statusPresensi = $this->getMockStatus($index);
                                $statusJurnal = $this->getMockStatus($index + 1);
                                $statusNilai = $this->getMockStatus($index + 2);
                            @endphp
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="p-3 font-semibold text-[#0F172A]">{{ $index + 1 }}</td>
                                <td class="p-3 font-bold text-[#0F172A]">{{ $teacher['name'] }}</td>
                                <td class="p-3 text-[#64748B] font-mono text-[10px]">{{ $teacher['phone'] ?? '-' }}</td>
                                <td class="p-3">
                                    <span class="inline-block text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $statusPresensi['bg'] }}">
                                        {{ $statusPresensi['text'] }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="inline-block text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $statusJurnal['bg'] }}">
                                        {{ $statusJurnal['text'] }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="inline-block text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $statusNilai['bg'] }}">
                                        {{ $statusNilai['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colSpan="6" class="p-6 text-center text-[#64748B]">
                                    Belum ada data guru terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
