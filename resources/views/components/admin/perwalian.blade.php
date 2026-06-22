<?php

use Livewire\Component;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\MentorStudent;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $searchTeacher = '';
    public $selectedTeacherId = null;
    
    // Assign mass modal
    public $showMassModal = false;
    public $selectedClassId = '';
    
    // Assign individual modal
    public $showIndividualModal = false;
    public $searchStudent = '';
    public $selectedStudentIds = []; // Array of student UUIDs
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function selectTeacher($id)
    {
        $this->selectedTeacherId = $id;
        $this->resetValidation();
        $this->dispatch('init-lucide');
    }

    public function openMassAssignModal()
    {
        if (!$this->selectedTeacherId) return;
        $this->resetValidation();
        $this->selectedClassId = '';
        $this->showMassModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveMassAssign()
    {
        $this->validate([
            'selectedClassId' => 'required|exists:classes,id',
        ]);

        try {
            DB::transaction(function() {
                // Get all students in the selected class
                $students = Student::where('class_id', $this->selectedClassId)->get();
                
                foreach ($students as $student) {
                    // Check if already assigned, if yes, delete first (to satisfy unique index and reassign)
                    MentorStudent::where('student_id', $student->id)->delete();
                    
                    // Create new assignment
                    MentorStudent::create([
                        'teacher_id' => $this->selectedTeacherId,
                        'student_id' => $student->id,
                    ]);
                }
            });

            $this->showFeedback('success', 'Seluruh murid di kelas berhasil ditetapkan sebagai perwalian.');
            $this->showMassModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal memproses penetapan massal: ' . $e->getMessage());
        }
    }

    public function openIndividualAssignModal()
    {
        if (!$this->selectedTeacherId) return;
        $this->resetValidation();
        $this->searchStudent = '';
        $this->selectedStudentIds = [];
        $this->showIndividualModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveIndividualAssign()
    {
        if (empty($this->selectedStudentIds)) {
            $this->showFeedback('error', 'Silakan pilih minimal satu murid.');
            return;
        }

        try {
            DB::transaction(function() {
                foreach ($this->selectedStudentIds as $studentId) {
                    MentorStudent::where('student_id', $studentId)->delete();
                    
                    MentorStudent::create([
                        'teacher_id' => $this->selectedTeacherId,
                        'student_id' => $studentId,
                    ]);
                }
            });

            $this->showFeedback('success', 'Murid terpilih berhasil ditetapkan sebagai perwalian.');
            $this->showIndividualModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal memproses penetapan: ' . $e->getMessage());
        }
    }

    public function unassignStudent($mentorStudentId)
    {
        try {
            $ms = MentorStudent::find($mentorStudentId);
            if ($ms) {
                $ms->delete();
                $this->showFeedback('success', 'Hubungan perwalian berhasil dilepas.');
            }
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal melepas perwalian: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Fetch teachers list
        $teachersQuery = Teacher::withCount('mentorStudents');
        if (!empty($this->searchTeacher)) {
            $teachersQuery->where('name', 'like', '%' . $this->searchTeacher . '%')
                          ->orWhere('nip', 'like', '%' . $this->searchTeacher . '%');
        }
        $teachers = $teachersQuery->orderBy('name')->get();

        // Selected teacher details
        $selectedTeacher = null;
        $mentorStudents = collect();
        if ($this->selectedTeacherId) {
            $selectedTeacher = Teacher::find($this->selectedTeacherId);
            if ($selectedTeacher) {
                $mentorStudents = MentorStudent::with(['student.class'])
                    ->where('teacher_id', $this->selectedTeacherId)
                    ->get();
            }
        }

        // Classes for mass assign
        $classes = SchoolClass::orderBy('name')->get();

        // Students for individual assign
        $availableStudents = collect();
        if ($this->showIndividualModal) {
            $studentsQuery = Student::with(['class', 'mentorStudent.teacher']);
            if (!empty($this->searchStudent)) {
                $studentsQuery->where('name', 'like', '%' . $this->searchStudent . '%')
                              ->orWhere('nis', 'like', '%' . $this->searchStudent . '%');
            }
            $availableStudents = $studentsQuery->orderBy('name')->take(50)->get();
        }

        return view('components.admin.perwalian', [
            'teachers' => $teachers,
            'selectedTeacher' => $selectedTeacher,
            'mentorStudents' => $mentorStudents,
            'classes' => $classes,
            'availableStudents' => $availableStudents,
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
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Penetapan Murid Perwalian</h2>
        </div>
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
            <span>{{ $feedbackMessage }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Teachers List Panel -->
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden flex flex-col h-[650px]">
            <div class="p-6 border-b border-[#F1F5F9] space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-[#4F46E5]/10 rounded-xl text-[#4F46E5]">
                        <i data-lucide="users" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[#0F172A] text-sm font-display">Daftar Guru / Wali</h3>
                        <p class="text-[11px] text-[#64748B] font-medium">Pilih guru untuk mengelola murid binaan</p>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i data-lucide="search" class="h-4 w-4"></i>
                    </span>
                    <input
                        type="text"
                        placeholder="Cari guru..."
                        wire:model.live.debounce.300ms="searchTeacher"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl pl-10 pr-4 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8]"
                    />
                </div>
            </div>

            <!-- List -->
            <div class="flex-1 overflow-y-auto divide-y divide-[#F1F5F9]">
                @forelse ($teachers as $teacherItem)
                    <button
                        wire:click="selectTeacher('{{ $teacherItem->id }}')"
                        class="w-full text-left p-4 hover:bg-slate-50/50 transition-all flex items-center justify-between border-l-4 {{
                            $selectedTeacherId === $teacherItem->id 
                            ? 'border-[#4F46E5] bg-[#4F46E5]/5 font-semibold' 
                            : 'border-transparent'
                        }}"
                    >
                        <div class="space-y-1 pr-4">
                            <span class="text-xs text-[#0F172A] block font-semibold leading-tight">{{ $teacherItem->name }}</span>
                            <span class="text-[10px] text-[#64748B] font-medium block">NIP. {{ $teacherItem->nip }}</span>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-[#4F46E5]/10 text-[#4F46E5]">
                            {{ $teacherItem->mentor_students_count }} Murid
                        </span>
                    </button>
                @empty
                    <div class="p-8 text-center text-[#94A3B8] italic text-xs">
                        Tidak ada guru yang ditemukan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Mentored Students Detail Panel (Takes 2 span) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden flex flex-col min-h-[650px]">
            @if ($selectedTeacher)
                <!-- Panel Header -->
                <div class="p-6 border-b border-[#F1F5F9] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                    <div class="space-y-1">
                        <span class="text-[9px] font-bold text-[#64748B] uppercase tracking-wider block">
                            PENGATURAN PERWALIAN
                        </span>
                        <h3 class="font-bold text-[#0F172A] text-base font-display">
                            {{ $selectedTeacher->name }}
                        </h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            wire:click="openMassAssignModal"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-[#0EA5E9] bg-[#0EA5E9]/10 hover:bg-[#0EA5E9] hover:text-white transition-all shadow-sm"
                        >
                            <i data-lucide="users" class="h-4 w-4"></i>
                            Tetapkan Kelas (Massal)
                        </button>

                        <button
                            wire:click="openIndividualAssignModal"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10"
                        >
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah Individual
                        </button>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#F1F5F9] text-[10px] font-bold text-[#64748B] uppercase tracking-wider bg-[#F8FAFC]">
                                <th class="p-4 pl-6">NIS / NISN</th>
                                <th class="p-4">Nama Murid</th>
                                <th class="p-4">Kelas</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-xs">
                            @forelse ($mentorStudents as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 pl-6 font-semibold text-[#0F172A] space-y-0.5">
                                        <span>{{ $item->student->nis }}</span>
                                        <span class="text-[9px] text-[#94A3B8] font-medium block">NISN. {{ $item->student->nisn }}</span>
                                    </td>
                                    <td class="p-4 font-semibold text-[#0F172A]">
                                        {{ $item->student->name }}
                                    </td>
                                    <td class="p-4 text-[#64748B] font-medium">
                                        {{ $item->student->class->name }}
                                    </td>
                                    <td class="p-4 pr-6 text-right">
                                        <button
                                            wire:click="unassignStudent('{{ $item->id }}')"
                                            wire:confirm="Apakah Anda yakin ingin melepas status perwalian murid ini?"
                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition-all border border-rose-100"
                                            title="Lepas Binaan"
                                        >
                                            Lepas Binaan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-[#94A3B8]">
                                        <div class="max-w-sm mx-auto space-y-2">
                                            <i data-lucide="user-check" class="h-10 w-10 mx-auto text-[#CBD5E1]"></i>
                                            <h4 class="font-bold text-xs text-[#64748B]">Belum Ada Murid Binaan</h4>
                                            <p class="text-[10px] text-[#94A3B8]">Guru ini belum memiliki murid perwalian. Klik tombol di atas untuk menetapkan murid perwalian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <!-- No selection placeholder -->
                <div class="flex-grow flex flex-col items-center justify-center p-12 text-center text-[#94A3B8] min-h-[600px]">
                    <i data-lucide="user-check" class="h-14 w-14 mb-4 text-[#E2E8F0]"></i>
                    <h3 class="text-base font-bold text-[#475569] font-display">Pilih Guru Terlebih Dahulu</h3>
                    <p class="text-xs text-[#94A3B8] max-w-xs mt-1">Pilih salah satu guru dari panel sebelah kiri untuk menampilkan, mengelola, dan menetapkan murid perwalian/binaan mereka.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Mass Assign Modal -->
    @if ($showMassModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="$set('showMassModal', false)">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#E2E8F0] animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">
                        Tetapkan Murid secara Massal (Per Kelas)
                    </h3>
                    <button
                        wire:click="$set('showMassModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveMassAssign" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">PILIH KELAS</label>
                        <select
                            wire:model="selectedClassId"
                            required
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl px-4 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5]"
                        >
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($classes as $classItem)
                                <option value="{{ $classItem->id }}">{{ $classItem->name }} ({{ $classItem->level }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-amber-600 font-semibold bg-amber-50 p-3 rounded-xl border border-amber-100 mt-2">
                            <span class="font-bold">Perhatian:</span> Tindakan ini akan mengalihkan perwalian seluruh siswa dalam kelas ini kepada guru terpilih. Jika ada siswa yang sebelumnya dibina guru lain, perwalian mereka akan dipindahkan secara otomatis.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#F1F5F9]">
                        <button
                            type="button"
                            wire:click="$set('showMassModal', false)"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10"
                        >
                            Simpan Perwalian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Individual Assign Modal -->
    @if ($showIndividualModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="$set('showIndividualModal', false)">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-xl overflow-hidden border border-[#E2E8F0] flex flex-col h-[550px] animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">
                        Tetapkan Murid secara Individual
                    </h3>
                    <button
                        wire:click="$set('showIndividualModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <!-- Search inside Modal -->
                <div class="p-4 border-b border-[#F1F5F9]">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                        <input
                            type="text"
                            placeholder="Cari nama atau NIS murid..."
                            wire:model.live.debounce.300ms="searchStudent"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl pl-10 pr-4 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8]"
                        />
                    </div>
                </div>

                <!-- Students Selection List -->
                <div class="flex-1 overflow-y-auto divide-y divide-[#F1F5F9] px-6">
                    @forelse ($availableStudents as $studentItem)
                        <label class="flex items-center justify-between py-3 cursor-pointer hover:bg-slate-50/50 transition-all rounded-lg px-2">
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    value="{{ $studentItem->id }}"
                                    wire:model="selectedStudentIds"
                                    class="h-4.5 w-4.5 text-[#4F46E5] border-slate-300 rounded-lg focus:ring-[#4F46E5]"
                                />
                                <div class="space-y-0.5">
                                    <span class="text-xs text-[#0F172A] font-semibold block leading-tight">{{ $studentItem->name }}</span>
                                    <span class="text-[10px] text-[#64748B] font-medium block">NIS. {{ $studentItem->nis }} • Kelas {{ $studentItem->class->name }}</span>
                                </div>
                            </div>
                            
                            <!-- Display current mentor if any -->
                            @if ($studentItem->mentorStudent)
                                <span class="text-[9px] px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 font-bold shrink-0">
                                    Wali: {{ $studentItem->mentorStudent->teacher->name }}
                                </span>
                            @else
                                <span class="text-[9px] px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 font-bold shrink-0">
                                    Belum Ada Wali
                                </span>
                            @endif
                        </label>
                    @empty
                        <div class="p-8 text-center text-[#94A3B8] italic text-xs">
                            Tidak ada murid yang ditemukan.
                        </div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-[#F1F5F9] flex justify-between items-center bg-white px-6">
                    <span class="text-[10px] text-[#64748B] font-bold">
                        {{ count($selectedStudentIds) }} Murid dipilih
                    </span>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            wire:click="$set('showIndividualModal', false)"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="saveIndividualAssign"
                            class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10"
                        >
                            Tetapkan Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
