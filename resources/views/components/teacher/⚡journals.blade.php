<?php

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Schedule;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Url]
    public $scheduleId = '';

    public $date = '';
    public $material = '';
    public $activity = '';
    public $notes = '';
    public $status = 'DRAFT'; // DRAFT or COMPLETED
    
    public $feedbackType = '';
    public $feedbackMessage = '';

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->loadJournal();
    }

    public function updatedDate()
    {
        $this->loadJournal();
    }

    public function updatedScheduleId()
    {
        $this->loadJournal();
    }

    public function loadJournal()
    {
        if (empty($this->scheduleId)) return;

        $schedule = Schedule::find($this->scheduleId);
        if (!$schedule) {
            $this->scheduleId = '';
            return;
        }

        $journal = Journal::where('schedule_id', $this->scheduleId)
            ->whereDate('date', $this->date)
            ->first();

        if ($journal) {
            $this->material = $journal->material;
            $this->activity = $journal->activity;
            $this->notes = $journal->notes ?? '';
            $this->status = $journal->status;
        } else {
            $this->material = '';
            $this->activity = '';
            $this->notes = '';
            $this->status = 'DRAFT';
        }
        
        $this->dispatch('init-lucide');
    }

    public function saveJournal($targetStatus)
    {
        if (empty($this->scheduleId)) return;

        if ($targetStatus === 'COMPLETED') {
            if (empty(trim($this->material)) || empty(trim($this->activity))) {
                $this->showFeedback('error', 'Materi dan aktivitas pembelajaran wajib diisi untuk finalisasi jurnal.');
                return;
            }
        }

        try {
            $journal = Journal::updateOrCreate(
                [
                    'schedule_id' => $this->scheduleId,
                    'date' => $this->date,
                ],
                [
                    'material' => $this->material,
                    'activity' => $this->activity,
                    'notes' => $this->notes ?: null,
                    'status' => $targetStatus,
                ]
            );

            $this->status = $targetStatus;
            
            $msg = $targetStatus === 'COMPLETED' 
                ? 'Jurnal mengajar berhasil difinalisasi!' 
                : 'Draft jurnal berhasil disimpan!';
            $this->showFeedback('success', $msg);
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan jurnal: ' . $e->getMessage());
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
                <h3 class="text-base font-bold text-[#0F172A] font-display">Pilih Jadwal untuk Pengisian Jurnal</h3>
                <p class="text-xs text-[#64748B] mt-1">Pilih salah satu jadwal mengajar di bawah ini untuk memulai pengisian jurnal mengajar harian.</p>
            </div>

            @php $schedules = $this->getSchedulesList(); @endphp
            @if ($schedules->isEmpty())
                <div class="bg-white p-12 text-center rounded-3xl border border-[#E2E8F0] shadow-sm space-y-2">
                    <i data-lucide="book-open" class="h-12 w-12 text-[#94A3B8] mx-auto"></i>
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
        <!-- Halaman Pengisian Jurnal -->
        @php $selectedSched = $this->getSelectedSchedule(); @endphp
        @if ($selectedSched)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Input Jurnal (Kiri/Utama) -->
                <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] lg:col-span-2 shadow-sm space-y-5">
                    <h3 class="text-sm font-bold text-[#0F172A] font-display">Jurnal Pembelajaran</h3>

                    <div class="space-y-4">
                        <!-- Materi -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">Materi Pembelajaran</label>
                            <textarea
                                wire:model="material"
                                placeholder="Tuliskan materi pelajaran hari ini (contoh: Konsep dasar eksponen)"
                                rows="3"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl px-4 py-3 text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] transition-all"
                            ></textarea>
                            @error('material') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Aktivitas -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">Aktivitas Pembelajaran</label>
                            <textarea
                                wire:model="activity"
                                placeholder="Jelaskan aktivitas di kelas (contoh: Diskusi kelompok menyelesaikan latihan logaritma)"
                                rows="4"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl px-4 py-3 text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] transition-all"
                            ></textarea>
                            @error('activity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Catatan -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">Catatan Pembelajaran (Opsional)</label>
                            <textarea
                                wire:model="notes"
                                placeholder="Tambahkan catatan khusus kelas atau hambatan jika ada"
                                rows="2"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl px-4 py-3 text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] transition-all"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Form Action buttons -->
                    <div class="pt-4 border-t border-[#E2E8F0] flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="saveJournal('DRAFT')"
                            class="px-5 py-3 text-xs font-bold rounded-2xl text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC] border border-[#E2E8F0] transition-colors"
                        >
                            Simpan Draft
                        </button>
                        <button
                            type="button"
                            wire:click="saveJournal('COMPLETED')"
                            class="flex items-center gap-2 px-6 py-3 border border-transparent text-sm font-semibold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] focus:outline-none shadow-md shadow-[#4F46E5]/10 transition-all hover:scale-[1.01] active:scale-[0.99]"
                        >
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Finalisasi & Selesai
                        </button>
                    </div>
                </div>

                <!-- Status & Jadwal Details (Kanan) -->
                <div class="space-y-6">
                    <!-- Info Detail Jadwal -->
                    <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm space-y-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                Informasi Jadwal
                            </span>
                            <h3 class="text-base font-extrabold text-[#0F172A] pt-1.5 font-display">{{ $selectedSched->subject->name }}</h3>
                            <p class="text-xs text-[#64748B] font-semibold leading-relaxed">
                                Kelas {{ $selectedSched->class->name }} &bull; Jam {{ $selectedSched->start_time }} - {{ $selectedSched->end_time }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 rounded-2xl">
                            <i data-lucide="calendar" class="h-5 w-5 text-[#64748B]"></i>
                            <input
                                type="date"
                                wire:model.live="date"
                                class="bg-transparent focus:outline-none text-xs font-bold text-[#0F172A] w-full"
                            />
                        </div>
                    </div>

                    <!-- Status Jurnal Card -->
                    <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm space-y-4">
                        <h4 class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Status Administrasi Jurnal</h4>
                        @if ($status === 'COMPLETED')
                            <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-2xl flex gap-3 items-start">
                                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-bold">Jurnal Selesai</span>
                                    <span class="block text-[11px] leading-normal text-emerald-700">
                                        Jurnal untuk tanggal ini telah difinalisasi dan siap ditarik sebagai laporan kurikulum.
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-amber-50 text-amber-900 border border-amber-100 rounded-2xl flex gap-3 items-start">
                                <i data-lucide="alert-circle" class="h-5 w-5 text-amber-600 shrink-0"></i>
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-bold">Jurnal Masih Draft</span>
                                    <span class="block text-[11px] leading-normal text-amber-800">
                                        Tulis materi dan aktivitas pembelajaran lalu klik &quot;Finalisasi&quot; untuk mengirim data ke sistem.
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
