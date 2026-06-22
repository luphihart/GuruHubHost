<?php

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add';
    public $currentId = null;
    
    // Form fields
    public $teacher_id = '';
    public $class_id = '';
    public $subject_id = '';
    public $day = 'MONDAY'; // MONDAY, TUESDAY, etc.
    public $start_time = '07:30';
    public $end_time = '09:00';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'teacher_id' => 'required|string|exists:teachers,id',
        'class_id' => 'required|string|exists:classes,id',
        'subject_id' => 'required|string|exists:subjects,id',
        'day' => 'required|string|in:MONDAY,TUESDAY,WEDNESDAY,THURSDAY,FRIDAY,SATURDAY,SUNDAY',
        'start_time' => 'required|string|max:5',
        'end_time' => 'required|string|max:5',
    ];

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->modalMode = 'add';
        $this->currentId = null;
        
        $firstTeacher = Teacher::first();
        $this->teacher_id = $firstTeacher ? $firstTeacher->id : '';
        
        $firstClass = SchoolClass::first();
        $this->class_id = $firstClass ? $firstClass->id : '';
        
        $firstSubject = Subject::first();
        $this->subject_id = $firstSubject ? $firstSubject->id : '';
        
        $this->day = 'MONDAY';
        $this->start_time = '07:30';
        $this->end_time = '09:00';

        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($scheduleId)
    {
        $this->resetValidation();
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) return;

        $this->modalMode = 'edit';
        $this->currentId = $schedule->id;
        $this->teacher_id = $schedule->teacher_id;
        $this->class_id = $schedule->class_id;
        $this->subject_id = $schedule->subject_id;
        $this->day = $schedule->day;
        $this->start_time = $schedule->start_time;
        $this->end_time = $schedule->end_time;
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveSchedule()
    {
        $this->validate();

        try {
            if ($this->modalMode === 'add') {
                Schedule::create([
                    'teacher_id' => $this->teacher_id,
                    'class_id' => $this->class_id,
                    'subject_id' => $this->subject_id,
                    'day' => $this->day,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                ]);
                $this->showFeedback('success', 'Jadwal pelajaran baru berhasil ditambahkan.');
            } else {
                $schedule = Schedule::find($this->currentId);
                if ($schedule) {
                    $schedule->update([
                        'teacher_id' => $this->teacher_id,
                        'class_id' => $this->class_id,
                        'subject_id' => $this->subject_id,
                        'day' => $this->day,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                    ]);
                    $this->showFeedback('success', 'Data jadwal pelajaran berhasil diperbarui.');
                }
            }
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    public function deleteSchedule($scheduleId)
    {
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) return;

        try {
            $schedule->delete();
            $this->showFeedback('success', 'Jadwal pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
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

    public function render()
    {
        $query = Schedule::with(['teacher', 'class', 'subject']);
        
        if (!empty($this->searchQuery)) {
            $query->whereHas('teacher', function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%');
            })->orWhereHas('class', function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%');
            })->orWhereHas('subject', function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        $schedules = $query->get()->sortBy(function($schedule) {
            $dayOrder = [
                'MONDAY' => 1, 'TUESDAY' => 2, 'WEDNESDAY' => 3, 
                'THURSDAY' => 4, 'FRIDAY' => 5, 'SATURDAY' => 6, 'SUNDAY' => 7
            ];
            return ($dayOrder[$schedule->day] ?? 9) . '-' . $schedule->start_time;
        });

        $teachers = Teacher::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('components.admin.⚡schedules', [
            'schedulesList' => $schedules,
            'teachersList' => $teachers,
            'classesList' => $classes,
            'subjectsList' => $subjects,
        ]);
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Manajemen Akademik
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Jadwal Pelajaran</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Jadwal Baru
        </button>
    </div>

    <!-- Feedback Toast -->
    @if ($feedbackMessage)
        <div x-show="feedback" x-transition class="p-4 rounded-2xl border text-sm font-medium flex items-center gap-3 {{
            $feedbackType === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100'
        }}">
            @if ($feedbackType === 'success')
                <i data-lucide="check-circle" class="h-5 w-5 shrink-0 text-emerald-600"></i>
            @else
                <i data-lucide="alert-circle" class="h-5 w-5 shrink-0 text-rose-600"></i>
            @endif
            {{ $feedbackMessage }}
        </div>
    @endif

    <!-- Search Box -->
    <div class="bg-white p-4 rounded-3xl border border-[#E2E8F0] shadow-sm flex items-center gap-3">
        <i data-lucide="search" class="h-5 w-5 text-[#94A3B8]"></i>
        <input
            type="text"
            placeholder="Cari jadwal berdasarkan nama guru, kelas, atau nama mapel..."
            wire:model.live.debounce.300ms="searchQuery"
            class="w-full bg-transparent border-none text-xs text-[#0F172A] focus:outline-none placeholder-[#94A3B8]"
        />
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F8FAFC] text-[#64748B] font-bold border-b border-[#E2E8F0]">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Hari</th>
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Kelas</th>
                        <th class="p-4">Mata Pelajaran</th>
                        <th class="p-4">Guru Pengajar</th>
                        <th class="p-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($schedulesList as $index => $sched)
                        <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                            <td class="p-4 text-center font-bold text-[#64748B] bg-[#F8FAFC]/10">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-[#0F172A]">{{ $this->getIndonesianDayName($sched->day) }}</td>
                            <td class="p-4 font-mono text-xs text-[#4F46E5] font-bold">{{ $sched->start_time }} - {{ $sched->end_time }}</td>
                            <td class="p-4 font-semibold text-slate-700 text-xs">{{ $sched->class->name }}</td>
                            <td class="p-4 text-[#0F172A] text-xs font-semibold">{{ $sched->subject->name }}</td>
                            <td class="p-4 text-[#0F172A] text-xs font-bold">{{ $sched->teacher->name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-1">
                                    <button
                                        wire:click="openEditModal('{{ $sched->id }}')"
                                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        onclick="confirm('Apakah Anda yakin ingin menghapus jadwal ini?') || event.stopImmediatePropagation()"
                                        wire:click="deleteSchedule('{{ $sched->id }}')"
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                        title="Hapus"
                                    >
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colSpan="7" class="p-12 text-center text-[#94A3B8] italic">
                                Tidak ada data jadwal pelajaran ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah / Edit -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#E2E8F0]">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">
                        {{ $modalMode === 'add' ? 'Tambah Jadwal Baru' : 'Edit Jadwal Pelajaran' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveSchedule" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">GURU PENGAJAR</label>
                        <select
                            wire:model="teacher_id"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        >
                            @foreach($teachersList as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">KELAS</label>
                            <select
                                wire:model="class_id"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            >
                                @foreach($classesList as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">MATA PELAJARAN</label>
                            <select
                                wire:model="subject_id"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            >
                                @foreach($subjectsList as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">HARI MENGAJAR</label>
                        <select
                            wire:model="day"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        >
                            <option value="MONDAY">Senin</option>
                            <option value="TUESDAY">Selasa</option>
                            <option value="WEDNESDAY">Rabu</option>
                            <option value="THURSDAY">Kamis</option>
                            <option value="FRIDAY">Jumat</option>
                            <option value="SATURDAY">Sabtu</option>
                            <option value="SUNDAY">Minggu</option>
                        </select>
                        @error('day') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">JAM MULAI</label>
                            <input
                                type="text"
                                placeholder="HH:MM (e.g. 07:30)"
                                wire:model="start_time"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all font-mono"
                            />
                            @error('start_time') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">JAM SELESAI</label>
                            <input
                                type="text"
                                placeholder="HH:MM (e.g. 09:00)"
                                wire:model="end_time"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all font-mono"
                            />
                            @error('end_time') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E2E8F0] flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="px-4 py-2.5 text-xs font-bold rounded-xl text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC] border border-[#E2E8F0] transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-colors disabled:opacity-50"
                        >
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
