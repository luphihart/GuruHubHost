<?php

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\Journal;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $viewMode = 'today'; // 'today' or 'all'
    public $stats = [
        'today' => 0,
        'attendance_overdue' => 0,
        'journal_overdue' => 0,
        'score_incomplete' => 0,
    ];
    public $completionPercentage = 100;
    public $weeklyActivity = [];
    public $notificationsList = [];

    public function getDayNameInEnglish()
    {
        $days = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
        return $days[now()->dayOfWeek];
    }

    public function getSchedulesProperty()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) return collect();

        return Schedule::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();
    }

    public function getDisplayedSchedulesProperty()
    {
        $schedules = $this->schedules;
        if ($this->viewMode === 'today') {
            $today = $this->getDayNameInEnglish();
            return $schedules->filter(fn($s) => $s->day === $today);
        }
        
        // Sort by day and time
        return $schedules->sortBy(function($schedule) {
            $dayOrder = [
                'MONDAY' => 1, 'TUESDAY' => 2, 'WEDNESDAY' => 3, 
                'THURSDAY' => 4, 'FRIDAY' => 5, 'SATURDAY' => 6, 'SUNDAY' => 7
            ];
            return ($dayOrder[$schedule->day] ?? 9) . '-' . $schedule->start_time;
        });
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'TEACHER') {
            return redirect()->route('login');
        }

        $teacher = $user->teacher;
        if (!$teacher) return;

        // 1. Calculate today's schedules count
        $today = $this->getDayNameInEnglish();
        $schedules = $this->schedules;
        $this->stats['today'] = $schedules->filter(fn($s) => $s->day === $today)->count();

        // 2. Fetch Notifications/Warnings
        $notifs = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->get();
        
        $this->notificationsList = $notifs->toArray();
        $this->stats['attendance_overdue'] = $notifs->where('type', 'ATTENDANCE_OVERDUE')->count();
        $this->stats['journal_overdue'] = $notifs->where('type', 'JOURNAL_OVERDUE')->count();
        $this->stats['score_incomplete'] = $notifs->where('type', 'SCORE_INCOMPLETE')->count();

        // 3. Weekly hours calculation
        $daysEng = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
        $daysInd = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $weeklyHours = [];
        
        foreach ($daysEng as $index => $dayEng) {
            $daySchedules = $schedules->filter(fn($s) => $s->day === $dayEng);
            $hours = 0;
            foreach ($daySchedules as $s) {
                $hours += $this->calculateDuration($s->start_time, $s->end_time);
            }
            $weeklyHours[] = [
                'name' => $daysInd[$index],
                'jam' => $hours
            ];
        }
        $this->weeklyActivity = $weeklyHours;

        // 4. Calculate journal completion rate
        $totalJournals = Journal::whereIn('schedule_id', $schedules->pluck('id'))->count();
        if ($totalJournals > 0) {
            $completedJournals = Journal::whereIn('schedule_id', $schedules->pluck('id'))
                ->where('status', 'COMPLETED')
                ->count();
            $this->completionPercentage = round(($completedJournals / $totalJournals) * 100);
        } else {
            $this->completionPercentage = 100;
        }
    }

    private function calculateDuration($start, $end)
    {
        $partsStart = explode(':', $start);
        $partsEnd = explode(':', $end);
        if (count($partsStart) < 2 || count($partsEnd) < 2) return 0;
        
        $minutesStart = ($partsStart[0] * 60) + $partsStart[1];
        $minutesEnd = ($partsEnd[0] * 60) + $partsEnd[1];
        return max(0, ($minutesEnd - $minutesStart) / 60);
    }

    public function getIndonesianDayName($day)
    {
        $days = [
            'MONDAY' => 'Senin',
            'TUESDAY' => 'Selasa',
            'WEDNESDAY' => 'Rabu',
            'THURSDAY' => 'Kamis',
            'FRIDAY' => 'Jumat',
            'SATURDAY' => 'Sabtu',
            'SUNDAY' => 'Minggu',
        ];
        return $days[$day] ?? $day;
    }
};
?>

<div class="space-y-6">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#4F46E5] to-[#0EA5E9] p-6 lg:p-8 rounded-3xl text-white shadow-xl shadow-[#4F46E5]/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/10 rounded-full blur-2xl -mr-20 -mt-20"></div>
        <div class="relative z-10 space-y-2">
            <span class="text-white/80 text-sm font-semibold tracking-wide uppercase">Dashboard Guru</span>
            <h2 class="text-3xl font-extrabold tracking-tight font-display">
                Halo, Bapak/Ibu Guru 👋
            </h2>
            <p class="text-white/90 text-sm lg:text-base max-w-xl">
                Yuk, selesaikan administrasi presensi dan jurnal mengajar Anda hari ini agar rekapitulasi data kurikulum sekolah tetap up to date.
            </p>
        </div>
    </section>

    <!-- Statistics Cards -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Jadwal Hari Ini -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#4F46E5]/10 text-[#4F46E5] flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Jadwal Hari Ini</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">{{ $stats['today'] }} Kelas</span>
            </div>
        </div>

        <!-- Presensi Belum Diisi -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#EF4444]/10 text-[#EF4444] flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-warning" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Belum Presensi</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $stats['attendance_overdue'] }} Jadwal
                </span>
            </div>
        </div>

        <!-- Jurnal Belum Diisi -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#F59E0B]/10 text-[#F59E0B] flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Belum Selesai Jurnal</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $stats['journal_overdue'] }} Jurnal
                </span>
            </div>
        </div>

        <!-- Nilai TP Belum Lengkap -->
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="h-12 w-12 rounded-2xl bg-[#10B981]/10 text-[#10B981] flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="h-6 w-6"></i>
            </div>
            <div>
                <span class="block text-xs font-semibold text-[#64748B]">Nilai Belum Lengkap</span>
                <span class="block text-lg font-black text-[#0F172A] mt-0.5 font-display">
                    {{ $stats['score_incomplete'] }} Murid
                </span>
            </div>
        </div>
    </section>

    <!-- Charts & Activity Row -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Weekly Activity Chart (Bar) -->
        <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm lg:col-span-2 space-y-4">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">Jam Mengajar Mingguan</h3>
            
            <!-- Custom HTML-based Bar Chart -->
            <div class="h-64 flex items-end justify-between gap-2 pt-6">
                @foreach($weeklyActivity as $item)
                    <div class="flex-1 flex flex-col items-center gap-2 group">
                        <!-- Bar Container -->
                        <div class="w-full bg-[#4F46E5]/10 rounded-xl relative flex items-end justify-center h-48 overflow-hidden">
                            <!-- Tooltip on Hover -->
                            <div class="absolute bottom-full mb-2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-md pointer-events-none">
                                {{ $item['jam'] }} Jam
                            </div>
                            
                            <!-- Active Bar Column -->
                            @php
                                $maxHeightPercent = min(100, max(5, ($item['jam'] / 8) * 100));
                            @endphp
                            <div class="bg-[#4F46E5] w-full rounded-t-xl group-hover:bg-[#4338CA] transition-all" style="height: {{ $maxHeightPercent }}%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">{{ $item['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Donut / circular progress chart -->
        <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm flex flex-col justify-between items-center gap-4">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight w-full text-left">Penyelesaian Administrasi</h3>
            
            <div class="h-40 w-40 relative flex items-center justify-center">
                <!-- Circular SVG Progress -->
                @php
                    $radius = 60;
                    $circumference = 2 * pi() * $radius;
                    $strokeDashoffset = $circumference - ($this->completionPercentage / 100) * $circumference;
                @endphp
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="80" cy="80" r="{{ $radius }}" stroke="#E2E8F0" stroke-width="12" fill="transparent" />
                    <circle cx="80" cy="80" r="{{ $radius }}" stroke="#10B981" stroke-width="12" fill="transparent" 
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $strokeDashoffset }}"
                            class="transition-all duration-500 ease-out" />
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-2xl font-black text-[#0F172A] font-display">{{ $this->completionPercentage }}%</span>
                    <span class="text-[10px] font-semibold text-[#64748B]">Selesai</span>
                </div>
            </div>

            <div class="flex justify-center gap-4 text-[10px] font-bold text-[#64748B] w-full border-t border-slate-100 pt-3">
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 bg-[#10B981] rounded-full"></div>
                    <span>Selesai ({{ $this->completionPercentage }}%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 bg-slate-200 rounded-full"></div>
                    <span>Draft/Belum ({{ 100 - $this->completionPercentage }}%)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Today's Schedule & Actions Required -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Today's Schedule -->
        <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm lg:col-span-2 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F1F5F9] pb-3">
                <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">Jadwal Mengajar Anda</h3>
                <div class="flex bg-[#F8FAFC] p-1 border border-[#E2E8F0] rounded-xl text-[10px] font-bold w-fit">
                    <button
                        type="button"
                        wire:click="$set('viewMode', 'today')"
                        class="px-3 py-1.5 rounded-lg transition-all {{
                            $viewMode === 'today' ? 'bg-[#4F46E5] text-white shadow-sm' : 'text-[#64748B] hover:text-[#0F172A]'
                        }}"
                    >
                        Hari Ini
                    </button>
                    <button
                        type="button"
                        wire:click="$set('viewMode', 'all')"
                        class="px-3 py-1.5 rounded-lg transition-all {{
                            $viewMode === 'all' ? 'bg-[#4F46E5] text-white shadow-sm' : 'text-[#64748B] hover:text-[#0F172A]'
                        }}"
                    >
                        Semua Jadwal
                    </button>
                </div>
            </div>

            @forelse($this->displayedSchedules as $schedule)
                <div class="p-4 border border-[#F1F5F9] bg-[#F8FAFC] rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if ($viewMode === 'all')
                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                    {{ $this->getIndonesianDayName($schedule->day) }}
                                </span>
                            @endif
                            <span class="text-[9px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full font-mono">
                                {{ $schedule->start_time }} - {{ $schedule->end_time }}
                            </span>
                            <span class="text-[9px] font-bold text-[#0EA5E9] bg-[#0EA5E9]/10 px-2 py-0.5 rounded-full">
                                Kelas {{ $schedule->class->name }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-[#0F172A]">
                            {{ $schedule->subject->name }}
                        </h4>
                    </div>
                    
                    <!-- Shortcut Actions -->
                    <div class="flex flex-wrap gap-1.5">
                        <a
                            href="{{ route('teacher.attendance') }}?scheduleId={{ $schedule->id }}"
                            class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-colors"
                        >
                            <i data-lucide="clipboard-check" class="h-3.5 w-3.5"></i>
                            Presensi
                        </a>
                        <a
                            href="{{ route('teacher.journals') }}?scheduleId={{ $schedule->id }}"
                            class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold rounded-xl text-slate-700 bg-white border border-[#E2E8F0] hover:bg-slate-50 transition-colors"
                        >
                            <i data-lucide="book-open" class="h-3.5 w-3.5"></i>
                            Jurnal
                        </a>
                        <a
                            href="{{ route('teacher.scores') }}?classId={{ $schedule->class_id }}&subjectId={{ $schedule->subject_id }}"
                            class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold rounded-xl text-slate-700 bg-white border border-[#E2E8F0] hover:bg-slate-50 transition-colors"
                        >
                            <i data-lucide="file-spreadsheet" class="h-3.5 w-3.5"></i>
                            Nilai TP
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 border border-dashed border-[#E2E8F0] rounded-2xl flex flex-col items-center justify-center gap-2">
                    <i data-lucide="clock" class="h-8 w-8 text-[#94A3B8]"></i>
                    <span class="text-xs text-[#64748B] font-medium">
                        {{ $viewMode === 'today' ? 'Tidak ada jadwal mengajar hari ini.' : 'Belum ada jadwal mengajar dikonfigurasi.' }}
                    </span>
                </div>
            @endforelse
        </div>

        <!-- Warnings Panel -->
        <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">Tindakan Diperlukan</h3>
            
            <div class="space-y-3">
                @forelse($notificationsList as $n)
                    <div class="p-3 bg-amber-50 text-[#92400E] border border-[#FDE68A] rounded-2xl text-[11px] flex gap-2.5 items-start">
                        <i data-lucide="alert-circle" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <div class="space-y-0.5">
                            <span class="block font-bold">{{ $n['title'] }}</span>
                            <span class="block text-[#B45309] leading-relaxed">{{ $n['message'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-2xl text-xs font-semibold text-center flex flex-col items-center gap-1.5">
                        <i data-lucide="sparkles" class="h-6 w-6 text-emerald-600"></i>
                        <span>Administrasi mengajar Anda lengkap! Luar biasa! 🎉</span>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
