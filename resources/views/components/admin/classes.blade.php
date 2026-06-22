<?php

use Livewire\Component;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Semester;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add';
    public $currentId = null;
    
    // Form fields
    public $name = '';
    public $level = 'SMA'; // SD, SMP, SMA, SMK
    public $school_year_id = '';
    public $semester_id = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'level' => 'required|string|in:SD,SMP,SMA,SMK',
        'school_year_id' => 'required|string|exists:school_years,id',
        'semester_id' => 'required|string|exists:semesters,id',
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
        $this->name = '';
        $this->level = 'SMA';
        
        $activeYear = SchoolYear::where('is_active', true)->first() ?: SchoolYear::first();
        $this->school_year_id = $activeYear ? $activeYear->id : '';
        
        $activeSem = Semester::where('is_active', true)->first() ?: Semester::first();
        $this->semester_id = $activeSem ? $activeSem->id : '';

        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($classId)
    {
        $this->resetValidation();
        $class = SchoolClass::find($classId);
        if (!$class) return;

        $this->modalMode = 'edit';
        $this->currentId = $class->id;
        $this->name = $class->name;
        $this->level = $class->level;
        $this->school_year_id = $class->school_year_id;
        $this->semester_id = $class->semester_id;
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveClass()
    {
        $this->validate();

        try {
            if ($this->modalMode === 'add') {
                SchoolClass::create([
                    'name' => $this->name,
                    'level' => $this->level,
                    'school_year_id' => $this->school_year_id,
                    'semester_id' => $this->semester_id,
                ]);
                $this->showFeedback('success', 'Kelas baru berhasil ditambahkan.');
            } else {
                $class = SchoolClass::find($this->currentId);
                if ($class) {
                    $class->update([
                        'name' => $this->name,
                        'level' => $this->level,
                        'school_year_id' => $this->school_year_id,
                        'semester_id' => $this->semester_id,
                    ]);
                    $this->showFeedback('success', 'Data kelas berhasil diperbarui.');
                }
            }
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan kelas: ' . $e->getMessage());
        }
    }

    public function deleteClass($classId)
    {
        $class = SchoolClass::find($classId);
        if (!$class) return;

        try {
            $class->delete();
            $this->showFeedback('success', 'Data kelas berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = SchoolClass::with(['schoolYear', 'semester']);
        
        if (!empty($this->searchQuery)) {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }
        
        $classes = $query->orderBy('name')->get();
        
        $schoolYears = SchoolYear::orderBy('name', 'desc')->get();
        $semesters = Semester::all();

        return view('components.admin.classes', [
            'classesList' => $classes,
            'schoolYearsList' => $schoolYears,
            'semestersList' => $semesters,
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
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Data Kelas</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Kelas Baru
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
            placeholder="Cari kelas berdasarkan nama (e.g. X IPA 1)..."
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
                        <th class="p-4">Nama Kelas</th>
                        <th class="p-4">Tingkat Sekolah</th>
                        <th class="p-4">Tahun Pelajaran</th>
                        <th class="p-4">Semester</th>
                        <th class="p-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($classesList as $index => $cls)
                        <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                            <td class="p-4 text-center font-bold text-[#64748B] bg-[#F8FAFC]/10">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-[#0F172A]">{{ $cls->name }}</td>
                            <td class="p-4"><span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg border border-slate-200">{{ $cls->level }}</span></td>
                            <td class="p-4 text-slate-600 font-medium text-xs">{{ $cls->schoolYear->name }}</td>
                            <td class="p-4 text-slate-600 font-semibold text-xs">{{ $cls->semester->name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-1">
                                    <button
                                        wire:click="openEditModal('{{ $cls->id }}')"
                                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        onclick="confirm('Apakah Anda yakin ingin menghapus kelas ini?') || event.stopImmediatePropagation()"
                                        wire:click="deleteClass('{{ $cls->id }}')"
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
                            <td colSpan="6" class="p-12 text-center text-[#94A3B8] italic">
                                Tidak ada data kelas ditemukan.
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
                        {{ $modalMode === 'add' ? 'Tambah Kelas Baru' : 'Edit Data Kelas' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveClass" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA KELAS</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. X MIPA 1"
                            wire:model="name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">TINGKAT SEKOLAH</label>
                        <select
                            wire:model="level"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        >
                            <option value="SD">SD (Sekolah Dasar)</option>
                            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                            <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                            <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
                        </select>
                        @error('level') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">TAHUN PELAJARAN</label>
                        <select
                            wire:model="school_year_id"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        >
                            @foreach($schoolYearsList as $sy)
                                <option value="{{ $sy->id }}">{{ $sy->name }}</option>
                            @endforeach
                        </select>
                        @error('school_year_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">SEMESTER</label>
                        <select
                            wire:model="semester_id"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        >
                            @foreach($semestersList as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        </select>
                        @error('semester_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
