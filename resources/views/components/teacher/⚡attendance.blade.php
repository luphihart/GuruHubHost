<?php

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    #[Url]
    public $scheduleId = '';

    public $date = '';
    public $details = []; // Array to store student attendance states
    
    public $feedbackType = '';
    public $feedbackMessage = '';

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->loadAttendance();
    }

    public function updatedDate()
    {
        $this->loadAttendance();
    }

    public function updatedScheduleId()
    {
        $this->loadAttendance();
    }

    public function loadAttendance()
    {
        if (empty($this->scheduleId)) {
            $this->details = [];
            return;
        }

        $schedule = Schedule::with('class.students')->find($this->scheduleId);
        if (!$schedule) {
            $this->scheduleId = '';
            return;
        }

        // Fetch existing attendance record for date
        $attendance = Attendance::where('schedule_id', $this->scheduleId)
            ->whereDate('date', $this->date)
            ->first();

        $loadedDetails = [];

        if ($attendance) {
            // Load existing details
            $attendanceDetails = AttendanceDetail::where('attendance_id', $attendance->id)
                ->with('student')
                ->get();

            // Match with students in class (in case student list changed)
            foreach ($schedule->class->students as $student) {
                $existing = $attendanceDetails->firstWhere('student_id', $student->id);
                $loadedDetails[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'status' => $existing ? $existing->status : 'HADIR',
                    'notes' => $existing ? $existing->notes : '',
                ];
            }
        } else {
            // New attendance sheet
            foreach ($schedule->class->students as $student) {
                $loadedDetails[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'status' => 'HADIR',
                    'notes' => '',
                ];
            }
        }

        // Sort by name
        usort($loadedDetails, fn($a, $b) => strcmp($a['name'], $b['name']));
        $this->details = $loadedDetails;
        $this->dispatch('init-lucide');
    }

    public function setStatus($studentId, $status)
    {
        foreach ($this->details as $index => $item) {
            if ($item['student_id'] === $studentId) {
                $this->details[$index]['status'] = $status;
                break;
            }
        }
    }

    public function saveAttendance()
    {
        if (empty($this->scheduleId)) return;

        try {
            DB::transaction(function () {
                // Find or create Attendance
                $attendance = Attendance::firstOrCreate(
                    [
                        'schedule_id' => $this->scheduleId,
                        'date' => $this->date,
                    ]
                );

                // Save details
                foreach ($this->details as $item) {
                    AttendanceDetail::updateOrCreate(
                        [
                            'attendance_id' => $attendance->id,
                            'student_id' => $item['student_id'],
                        ],
                        [
                            'status' => $item['status'],
                            'notes' => $item['notes'] ?: null,
                        ]
                    );
                }
            });

            $this->showFeedback('success', 'Presensi kelas berhasil disimpan!');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan presensi: ' . $e->getMessage());
        }
    }

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function getSchedulesList()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) return collect();

        return Schedule::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();
    }

    public function getSelectedSchedule()
    {
        if (empty($this->scheduleId)) return null;
        return Schedule::with(['class', 'subject'])->find($this->scheduleId);
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

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Back to Dashboard / Feedback Header -->
    <div class="flex items-center justify-between">
        <a
            href="{{ route('teacher.dashboard') }}"
            class="flex items-center gap-1.5 text-xs font-bold text-[#64748B] hover:text-[#0F172A] transition-colors"
        >
            <i data-lucide="chevron-left" class="h-4 w-4"></i>
            Kembali ke Dashboard
        </a>

        @if ($feedbackMessage)
            <div x-show="feedback" x-transition class="text-xs font-bold px-3 py-1.5 rounded-full border {{
                $feedbackType === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
            }}">
                {{ $feedbackMessage }}
            </div>
        @endif
    </div>

    @if (empty($scheduleId))
        <!-- Grid Pemilihan Jadwal -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm">
                <h3 class="text-base font-bold text-[#0F172A] font-display">Pilih Jadwal untuk Pengisian Presensi</h3>
                <p class="text-xs text-[#64748B] mt-1">Pilih salah satu jadwal mengajar di bawah ini untuk memulai pengisian presensi siswa kelas.</p>
            </div>

            @php $schedules = $this->getSchedulesList(); @endphp
            @if ($schedules->isEmpty())
                <div class="bg-white p-12 text-center rounded-3xl border border-[#E2E8F0] shadow-sm space-y-2">
                    <i data-lucide="clipboard-check" class="h-12 w-12 text-[#94A3B8] mx-auto"></i>
                    <h3 class="text-base font-bold text-[#0F172A] font-display">Belum Ada Jadwal</h3>
                    <p class="text-xs text-[#64748B] max-w-sm mx-auto">
                        Anda belum memiliki jadwal mengajar yang dikonfigurasi oleh Administrator.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($schedules as $item)
                        <button
                            type="button"
                            wire:click="$set('scheduleId', '{{ $item->id }}')"
                            class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm hover:border-[#4F46E5] text-left transition-all hover:scale-[1.01] active:scale-[0.99] flex flex-col justify-between h-40 space-y-3"
                        >
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 uppercase">
                                        {{ $this->getIndonesianDayName($item->day) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full font-mono">
                                        {{ $item->start_time }} - {{ $item->end_time }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-[#0F172A] line-clamp-1 font-display">{{ $item->subject->name }}</h4>
                                <span class="block text-xs font-semibold text-[#64748B]">Kelas {{ $item->class->name }}</span>
                            </div>
                            
                            <div class="text-xs font-bold text-[#4F46E5] flex items-center gap-1 mt-2">
                                Pilih Jadwal &rarr;
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <!-- Lembar Presensi Kelas -->
        @php $selectedSched = $this->getSelectedSchedule(); @endphp
        @if ($selectedSched)
            <div class="space-y-6">
                <!-- Info Detail Jadwal -->
                <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                            Jadwal Terpilih
                        </span>
                        <h3 class="text-xl font-bold text-[#0F172A] font-display">{{ $selectedSched->subject->name }}</h3>
                        <p class="text-xs text-[#64748B] font-semibold">
                            Kelas {{ $selectedSched->class->name }} &bull; Hari {{ $this->getIndonesianDayName($selectedSched->day) }} &bull; Jam {{ $selectedSched->start_time }} - {{ $selectedSched->end_time }}
                        </p>
                    </div>

                    <!-- Date Picker -->
                    <div class="flex items-center gap-3 bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 rounded-2xl">
                        <i data-lucide="calendar" class="h-5 w-5 text-[#64748B]"></i>
                        <input
                            type="date"
                            wire:model.live="date"
                            class="bg-transparent focus:outline-none text-xs font-bold text-[#0F172A]"
                        />
                    </div>
                </div>

                <!-- Tabel Presensi Murid -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-[#F8FAFC] text-[#64748B] font-bold border-b border-[#E2E8F0]">
                                    <th class="p-4 w-12 text-center">No</th>
                                    <th class="p-4">NIS / NISN</th>
                                    <th class="p-4">Nama Murid</th>
                                    <th class="p-4 w-72 text-center">Status Kehadiran</th>
                                    <th class="p-4">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @forelse($details as $index => $item)
                                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                                        <td class="p-4 text-center font-semibold text-[#64748B]">{{ $index + 1 }}</td>
                                        <td class="p-4 font-mono text-xs text-[#64748B]">
                                            {{ $item['nis'] }} / {{ $item['nisn'] }}
                                        </td>
                                        <td class="p-4 font-bold text-[#0F172A]">{{ $item['name'] }}</td>
                                        <td class="p-4">
                                            <div class="flex justify-center bg-[#F8FAFC] p-1 border border-[#E2E8F0] rounded-2xl w-fit mx-auto">
                                                @foreach(['HADIR', 'IZIN', 'SAKIT', 'ALPA'] as $status)
                                                    @php
                                                        $isSelected = $item['status'] === $status;
                                                        $colorMap = [
                                                            'HADIR' => 'bg-[#10B981] text-white',
                                                            'IZIN' => 'bg-[#0EA5E9] text-white',
                                                            'SAKIT' => 'bg-[#F59E0B] text-white',
                                                            'ALPA' => 'bg-[#EF4444] text-white',
                                                        ];
                                                    @endphp
                                                    <button
                                                        key="{{ $status }}"
                                                        type="button"
                                                        wire:click="setStatus('{{ $item['student_id'] }}', '{{ $status }}')"
                                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{
                                                            $isSelected
                                                                ? $colorMap[$status] . ' shadow-sm'
                                                                : 'text-[#64748B] hover:text-[#0F172A]'
                                                        }}"
                                                    >
                                                        {{ $status }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <input
                                                type="text"
                                                wire:model="details.{{ $index }}.notes"
                                                placeholder="Catatan jika ada"
                                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-1 focus:ring-[#4F46E5]"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colSpan="5" class="p-12 text-center text-[#94A3B8] italic">
                                            Belum ada data murid terdaftar di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Save Button -->
                    <div class="p-6 border-t border-[#E2E8F0] flex justify-end">
                        <button
                            wire:click="saveAttendance"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-6 py-3 border border-transparent text-sm font-semibold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] focus:outline-none disabled:opacity-50 shadow-md shadow-[#4F46E5]/10 transition-all hover:scale-[1.01] active:scale-[0.99]"
                        >
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Simpan Presensi
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
