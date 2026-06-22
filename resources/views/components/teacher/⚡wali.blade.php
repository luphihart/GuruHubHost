<?php

use Livewire\Component;
use App\Models\MentorStudent;
use App\Models\MentoringNote;
use App\Models\SchoolProfile;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

new class extends Component
{
    public $searchQuery = '';
    public $selectedMentorStudentId = null;

    // Modal state
    public $showModal = false;

    // Form fields for new note
    public $category = 'ACADEMIC';
    public $date = '';
    public $content = '';
    public $action_taken = '';

    // Feedback toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'category' => 'required|in:ACADEMIC,ATTENDANCE,DISCIPLINE,ACHIEVEMENT,COUNSELING,OTHER',
        'date' => 'required|date',
        'content' => 'required|string|min:5',
        'action_taken' => 'nullable|string',
    ];

    public function mount()
    {
        $this->date = Carbon::now()->toDateString();
        
        // Auto-select first student if available
        $teacher = Auth::user()->teacher;
        if ($teacher) {
            $first = MentorStudent::where('teacher_id', $teacher->id)->first();
            if ($first) {
                $this->selectedMentorStudentId = $first->id;
            }
        }
    }

    public function selectStudent($id)
    {
        $this->selectedMentorStudentId = $id;
        $this->resetValidation();
        $this->dispatch('init-lucide');
    }

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
        $this->category = 'ACADEMIC';
        $this->date = Carbon::now()->toDateString();
        $this->content = '';
        $this->action_taken = '';
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveNote()
    {
        $this->validate();

        if (!$this->selectedMentorStudentId) {
            $this->showFeedback('error', 'Silakan pilih murid binaan terlebih dahulu.');
            return;
        }

        try {
            MentoringNote::create([
                'mentor_student_id' => $this->selectedMentorStudentId,
                'category' => $this->category,
                'date' => $this->date,
                'content' => $this->content,
                'action_taken' => $this->action_taken ?: null,
            ]);

            $this->showFeedback('success', 'Catatan pembinaan berhasil ditambahkan.');
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan catatan: ' . $e->getMessage());
        }
    }

    public function deleteNote($noteId)
    {
        try {
            $note = MentoringNote::findOrFail($noteId);
            $note->delete();
            $this->showFeedback('success', 'Catatan pembinaan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus catatan: ' . $e->getMessage());
        }
    }

    public function downloadPdf($mentorStudentId)
    {
        try {
            $mentorStudent = MentorStudent::with(['student.class.semester', 'student.class.schoolYear', 'teacher'])->findOrFail($mentorStudentId);
            $notes = MentoringNote::where('mentor_student_id', $mentorStudentId)->orderBy('date', 'desc')->get();
            $schoolProfile = SchoolProfile::first() ?? new SchoolProfile();

            $pdf = Pdf::loadView('exports.mentoring_pdf', [
                'mentorStudent' => $mentorStudent,
                'notes' => $notes,
                'schoolProfile' => $schoolProfile,
            ]);

            // Replace spaces and special characters to ensure filename format [KELAS]_[NAMA_MURID]_PEMBINAAN.pdf
            $className = preg_replace('/[^A-Za-z0-9\-]/', '_', $mentorStudent->student->class->name);
            $studentName = preg_replace('/[^A-Za-z0-9\-]/', '_', $mentorStudent->student->name);
            
            // Clean duplicates of underscores
            $className = preg_replace('/_+/', '_', $className);
            $studentName = preg_replace('/_+/', '_', $studentName);

            $fileName = "{$className}_{$studentName}_PEMBINAAN.pdf";

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName);
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $teacher = Auth::user()->teacher;
        
        $mentorStudentsQuery = MentorStudent::with(['student.class'])
            ->where('teacher_id', $teacher ? $teacher->id : null);

        if (!empty($this->searchQuery)) {
            $mentorStudentsQuery->whereHas('student', function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('nis', 'like', '%' . $this->searchQuery . '%');
            });
        }

        $mentorStudents = $mentorStudentsQuery->get();

        // Load active selected student details and notes
        $selectedMentorStudent = null;
        $notes = collect();

        if ($this->selectedMentorStudentId) {
            $selectedMentorStudent = MentorStudent::with(['student.class', 'teacher'])
                ->find($this->selectedMentorStudentId);
            
            if ($selectedMentorStudent) {
                $notes = MentoringNote::where('mentor_student_id', $this->selectedMentorStudentId)
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }

        return view('components.teacher.⚡wali', [
            'mentorStudents' => $mentorStudents,
            'selectedMentorStudent' => $selectedMentorStudent,
            'notes' => $notes,
        ]);
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#0EA5E9] bg-[#0EA5E9]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Pembinaan Siswa
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Kartu Pembinaan Guru Wali</h2>
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
            {{ $feedbackMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Left: Student List Card -->
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden flex flex-col h-[250px] lg:h-[600px]">
            <div class="p-5 border-b border-[#F1F5F9] space-y-3">
                <h3 class="font-bold text-[#0F172A] text-sm font-display flex items-center gap-2">
                    <i data-lucide="users" class="h-4 w-4 text-[#4F46E5]"></i>
                    Murid Binaan (Wali)
                </h3>
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Cari murid..."
                        wire:model.live.debounce.300ms="searchQuery"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl pl-9 pr-4 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8]"
                    />
                    <i data-lucide="search" class="absolute left-3 top-2.5 h-4 w-4 text-[#94A3B8]"></i>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                @forelse($mentorStudents as $ms)
                    <button
                        wire:click="selectStudent('{{ $ms->id }}')"
                        class="w-full text-left p-4 rounded-2xl border transition-all flex items-center justify-between gap-3 {{
                            $selectedMentorStudentId === $ms->id
                                ? 'bg-[#4F46E5]/5 border-[#4F46E5] shadow-sm shadow-[#4F46E5]/5'
                                : 'bg-[#F8FAFC] border-[#F1F5F9] hover:bg-[#F1F5F9] hover:border-[#E2E8F0]'
                        }}"
                    >
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-xs text-[#0F172A] truncate">{{ $ms->student->name }}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-[#64748B] font-medium">NIS: {{ $ms->student->nis }}</span>
                                <span class="h-1 w-1 bg-slate-300 rounded-full"></span>
                                <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-1.5 py-0.5 rounded">
                                    {{ $ms->student->class->name }}
                                </span>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-[#94A3B8] transition-transform {{ $selectedMentorStudentId === $ms->id ? 'translate-x-0.5 text-[#4F46E5]' : '' }}"></i>
                    </button>
                @empty
                    <div class="text-center py-8 text-[#94A3B8]">
                        <i data-lucide="info" class="h-8 w-8 mx-auto mb-2 text-[#CBD5E1]"></i>
                        <p class="text-xs">Tidak ada murid binaan yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Case / Note Timeline View -->
        <div class="lg:col-span-2 space-y-6">
            @if ($selectedMentorStudent)
                <!-- Student Details Banner -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-[#0EA5E9]/10 text-[#0EA5E9] flex items-center justify-center font-bold text-lg">
                            {{ substr($selectedMentorStudent->student->name, 0, 2) }}
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-[#0F172A] tracking-tight font-display">
                                {{ $selectedMentorStudent->student->name }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#64748B]">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="credit-card" class="h-3.5 w-3.5"></i>
                                    NIS: {{ $selectedMentorStudent->student->nis }} | NISN: {{ $selectedMentorStudent->student->nisn }}
                                </span>
                                <span class="hidden md:inline text-slate-300">|</span>
                                <span class="flex items-center gap-1 font-bold text-[#4F46E5]">
                                    <i data-lucide="school" class="h-3.5 w-3.5 text-[#4F46E5]"></i>
                                    Kelas {{ $selectedMentorStudent->student->class->name }}
                                </span>
                            </div>
                            <div class="text-xs text-[#64748B] flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 pt-1.5 border-t border-[#F1F5F9]">
                                <span>Orang Tua: <span class="font-semibold text-slate-700">{{ $selectedMentorStudent->student->parent_name }}</span></span>
                                <span class="hidden md:inline text-slate-300">|</span>
                                <a href="tel:{{ $selectedMentorStudent->student->parent_phone }}" class="hover:text-[#4F46E5] flex items-center gap-1 font-medium">
                                    <i data-lucide="phone" class="h-3 w-3"></i>
                                    {{ $selectedMentorStudent->student->parent_phone }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button
                            wire:click="downloadPdf('{{ $selectedMentorStudent->id }}')"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all"
                        >
                            <i data-lucide="file-text" class="h-4 w-4 text-slate-600"></i>
                            Cetak PDF
                        </button>
                        <button
                            wire:click="openAddModal"
                            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
                        >
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Catat Kasus
                        </button>
                    </div>
                </div>

                <!-- Notes Timeline -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-6 space-y-6">
                    <h3 class="font-bold text-[#0F172A] text-sm font-display flex items-center gap-2 pb-4 border-b border-[#F1F5F9]">
                        <i data-lucide="history" class="h-4 w-4 text-[#64748B]"></i>
                        Riwayat Catatan Kasus & Pembinaan
                    </h3>

                    @if($notes->count() > 0)
                        <div class="relative pl-6 border-l-2 border-slate-100 space-y-6 ml-3">
                            @foreach($notes as $note)
                                @php
                                    $badgeClass = match($note->category) {
                                        'ACADEMIC' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'ATTENDANCE' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'DISCIPLINE' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        'ACHIEVEMENT' => 'bg-violet-50 text-violet-700 border-violet-100',
                                        'COUNSELING' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default => 'bg-slate-50 text-slate-700 border-slate-100'
                                    };
                                    $categoryText = match($note->category) {
                                        'ACADEMIC' => 'Akademik',
                                        'ATTENDANCE' => 'Kehadiran',
                                        'DISCIPLINE' => 'Disiplin',
                                        'ACHIEVEMENT' => 'Prestasi',
                                        'COUNSELING' => 'Konseling',
                                        default => 'Lainnya'
                                    };
                                    $bulletColor = match($note->category) {
                                        'ACADEMIC' => 'bg-emerald-500 ring-emerald-100',
                                        'ATTENDANCE' => 'bg-blue-500 ring-blue-100',
                                        'DISCIPLINE' => 'bg-rose-500 ring-rose-100',
                                        'ACHIEVEMENT' => 'bg-violet-500 ring-violet-100',
                                        'COUNSELING' => 'bg-amber-500 ring-amber-100',
                                        default => 'bg-slate-500 ring-slate-100'
                                    };
                                @endphp
                                <div class="relative">
                                    <!-- Timeline Bullet -->
                                    <span class="absolute -left-[33px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-white ring-4 {{ $bulletColor }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                    </span>

                                    <!-- Note Content Box -->
                                    <div class="bg-[#F8FAFC] border border-[#F1F5F9] p-5 rounded-2xl space-y-3 group hover:border-[#E2E8F0] hover:bg-white hover:shadow-sm transition-all">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-lg border {{ $badgeClass }}">
                                                    {{ $categoryText }}
                                                </span>
                                                <span class="text-[10px] text-[#94A3B8] font-medium">
                                                    {{ \Carbon\Carbon::parse($note->date)->format('d F Y') }}
                                                </span>
                                            </div>
                                            <button
                                                wire:click="deleteNote('{{ $note->id }}')"
                                                wire:confirm="Apakah Anda yakin ingin menghapus catatan pembinaan ini?"
                                                class="opacity-0 group-hover:opacity-100 text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 rounded-lg transition-all"
                                                title="Hapus Catatan"
                                            >
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            <p class="text-xs text-[#334155] font-semibold leading-relaxed">
                                                {{ $note->content }}
                                            </p>

                                            @if($note->action_taken)
                                                <div class="pt-2.5 border-t border-dashed border-[#E2E8F0] text-[11px] text-[#64748B]">
                                                    <span class="font-bold text-[9px] text-[#475569] uppercase tracking-wider block mb-0.5">Tindak Lanjut / Penanganan:</span>
                                                    {{ $note->action_taken }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-[#94A3B8]">
                            <i data-lucide="heart-handshake" class="h-12 w-12 mx-auto mb-3 text-[#CBD5E1]"></i>
                            <h4 class="font-bold text-xs text-[#64748B]">Belum Ada Catatan Pembinaan</h4>
                            <p class="text-[11px] mt-1 max-w-sm mx-auto">Siswa ini tidak memiliki riwayat kasus atau bimbingan khusus. Klik tombol "Catat Kasus" untuk menambahkan catatan baru.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-8 lg:p-12 text-center text-[#94A3B8] flex flex-col items-center justify-center h-[300px] lg:h-[600px]">
                    <i data-lucide="user-check" class="h-16 w-16 mb-4 text-[#E2E8F0]"></i>
                    <h3 class="text-lg font-black text-[#475569] font-display">Pilih Murid Binaan</h3>
                    <p class="text-xs text-[#94A3B8] max-w-sm mt-1">Pilih salah satu murid binaan Anda dari panel sebelah kiri untuk melihat informasi profil lengkap, riwayat bimbingan, dan mencetak laporan pembinaan PDF.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Case/Note Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-slate-600/50 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal box -->
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-[#F1F5F9]">
                            <h3 class="text-base font-black text-[#0F172A] font-display flex items-center gap-2">
                                <i data-lucide="plus-circle" class="h-5 w-5 text-[#4F46E5]"></i>
                                Catat Kasus & Pembinaan Siswa
                            </h3>
                            <button wire:click="$set('showModal', false)" class="text-[#94A3B8] hover:text-[#475569]">
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>

                        <form wire:submit.prevent="saveNote" class="space-y-4">
                            <!-- Category -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Kasus</label>
                                <select
                                    wire:model="category"
                                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] @error('category') border-rose-400 @enderror"
                                >
                                    <option value="ACADEMIC">Akademik (Nilai, Pemahaman Materi)</option>
                                    <option value="ATTENDANCE">Kehadiran (Alpa, Terlambat, Cabut)</option>
                                    <option value="DISCIPLINE">Disiplin (Atribut, Perkelahian, Pelanggaran Aturan)</option>
                                    <option value="ACHIEVEMENT">Prestasi (Lomba, Pencapaian Positif)</option>
                                    <option value="COUNSELING">Konseling (Psikologis, Masalah Pribadi/Keluarga)</option>
                                    <option value="OTHER">Lainnya</option>
                                </select>
                                @error('category') <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Kejadian / Bimbingan</label>
                                <input
                                    type="date"
                                    wire:model="date"
                                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] @error('date') border-rose-400 @enderror"
                                />
                                @error('date') <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Case Content -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan / Detail Masalah</label>
                                <textarea
                                    wire:model="content"
                                    rows="4"
                                    placeholder="Jelaskan secara detail masalah siswa atau pencapaian yang terjadi..."
                                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8] @error('content') border-rose-400 @enderror"
                                ></textarea>
                                @error('content') <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Action Taken -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tindak Lanjut / Solusi (Opsional)</label>
                                <textarea
                                    wire:model="action_taken"
                                    rows="3"
                                    placeholder="Langkah penanganan yang diambil atau kesepakatan bersama siswa..."
                                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8] @error('action_taken') border-rose-400 @enderror"
                                ></textarea>
                                @error('action_taken') <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-2 pt-3 border-t border-[#F1F5F9]">
                                <button
                                    type="button"
                                    wire:click="$set('showModal', false)"
                                    class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10"
                                >
                                    Simpan Catatan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
