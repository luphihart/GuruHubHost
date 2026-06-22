<?php

use Livewire\Component;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add'; // 'add' or 'edit'
    public $currentId = null;
    
    // Form fields
    public $nis = '';
    public $nisn = '';
    public $name = '';
    public $gender = 'MALE'; // MALE or FEMALE
    public $class_id = '';
    public $parent_name = '';
    public $parent_phone = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'nis' => 'required|string|max:50',
        'nisn' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'gender' => 'required|string|in:MALE,FEMALE',
        'class_id' => 'required|string|exists:classes,id',
        'parent_name' => 'required|string|max:255',
        'parent_phone' => 'required|string|max:25',
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
        $this->nis = '';
        $this->nisn = '';
        $this->name = '';
        $this->gender = 'MALE';
        $this->parent_name = '';
        $this->parent_phone = '';
        
        $firstClass = SchoolClass::first();
        $this->class_id = $firstClass ? $firstClass->id : '';

        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($studentId)
    {
        $this->resetValidation();
        $student = Student::find($studentId);
        if (!$student) return;

        $this->modalMode = 'edit';
        $this->currentId = $student->id;
        $this->nis = $student->nis;
        $this->nisn = $student->nisn;
        $this->name = $student->name;
        $this->gender = $student->gender;
        $this->class_id = $student->class_id;
        $this->parent_name = $student->parent_name;
        $this->parent_phone = $student->parent_phone;
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveStudent()
    {
        $this->validate();

        // Normalize phone number (hlr 62)
        $normalizedPhone = $this->normalizePhone($this->parent_phone);

        if ($this->modalMode === 'add') {
            $this->validate([
                'nis' => 'unique:students,nis',
                'nisn' => 'unique:students,nisn',
            ]);

            try {
                Student::create([
                    'nis' => $this->nis,
                    'nisn' => $this->nisn,
                    'name' => $this->name,
                    'gender' => $this->gender,
                    'class_id' => $this->class_id,
                    'parent_name' => $this->parent_name,
                    'parent_phone' => $normalizedPhone,
                ]);

                $this->showModal = false;
                $this->showFeedback('success', 'Murid baru berhasil ditambahkan.');
            } catch (\Exception $e) {
                $this->showFeedback('error', 'Gagal menambahkan murid: ' . $e->getMessage());
            }
        } else {
            $student = Student::find($this->currentId);
            if (!$student) return;

            $this->validate([
                'nis' => 'unique:students,nis,' . $this->currentId,
                'nisn' => 'unique:students,nisn,' . $this->currentId,
            ]);

            try {
                $student->update([
                    'nis' => $this->nis,
                    'nisn' => $this->nisn,
                    'name' => $this->name,
                    'gender' => $this->gender,
                    'class_id' => $this->class_id,
                    'parent_name' => $this->parent_name,
                    'parent_phone' => $normalizedPhone,
                ]);

                $this->showModal = false;
                $this->showFeedback('success', 'Data murid berhasil diperbarui.');
            } catch (\Exception $e) {
                $this->showFeedback('error', 'Gagal memperbarui data murid: ' . $e->getMessage());
            }
        }
    }

    public function deleteStudent($studentId)
    {
        $student = Student::find($studentId);
        if (!$student) return;

        try {
            $student->delete();
            $this->showFeedback('success', 'Data murid berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus murid: ' . $e->getMessage());
        }
    }

    private function normalizePhone($phone)
    {
        if (empty($phone)) return null;
        
        $clean = preg_replace('/[^0-9]/', '', $phone);
        
        if (strpos($clean, '0') === 0) {
            $clean = '62' . substr($clean, 1);
        }
        
        if (strpos($clean, '62') !== 0) {
            $clean = '62' . $clean;
        }
        
        return $clean;
    }

    public function render()
    {
        $query = Student::with('class');
        
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('nis', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('nisn', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        $students = $query->orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return view('components.admin.students', [
            'studentsList' => $students,
            'classesList' => $classes,
        ]);
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Manajemen Data
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Data Murid</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Murid Baru
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
            placeholder="Cari murid berdasarkan nama, NIS atau NISN..."
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
                        <th class="p-4">NIS / NISN</th>
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">L/P</th>
                        <th class="p-4">Kelas</th>
                        <th class="p-4">Orang Tua / Wali</th>
                        <th class="p-4">Kontak Wali</th>
                        <th class="p-4 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($studentsList as $index => $student)
                        <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                            <td class="p-4 text-center font-bold text-[#64748B] bg-[#F8FAFC]/10">{{ $index + 1 }}</td>
                            <td class="p-4 font-mono text-xs text-[#64748B]">{{ $student->nis }}<br/><span class="text-[10px] text-slate-400">{{ $student->nisn }}</span></td>
                            <td class="p-4 font-bold text-[#0F172A]">{{ $student->name }}</td>
                            <td class="p-4 text-[#64748B] text-xs font-semibold">{{ $student->gender === 'MALE' ? 'L' : 'P' }}</td>
                            <td class="p-4 text-[#4F46E5] text-xs font-bold">{{ $student->class->name }}</td>
                            <td class="p-4 text-[#0F172A] text-xs font-medium">{{ $student->parent_name }}</td>
                            <td class="p-4 text-[#64748B] font-mono text-xs">{{ $student->parent_phone }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-1">
                                    <button
                                        wire:click="openEditModal('{{ $student->id }}')"
                                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        onclick="confirm('Apakah Anda yakin ingin menghapus murid ini?') || event.stopImmediatePropagation()"
                                        wire:click="deleteStudent('{{ $student->id }}')"
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
                            <td colSpan="8" class="p-12 text-center text-[#94A3B8] italic">
                                Tidak ada data murid ditemukan.
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
                        {{ $modalMode === 'add' ? 'Tambah Murid Baru' : 'Edit Data Murid' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveStudent" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">NIS</label>
                            <input
                                type="text"
                                required
                                placeholder="e.g. 10001"
                                wire:model="nis"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            />
                            @error('nis') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">NISN</label>
                            <input
                                type="text"
                                required
                                placeholder="e.g. 0012345678"
                                wire:model="nisn"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            />
                            @error('nisn') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA LENGKAP</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. Ahmad Fauzi"
                            wire:model="name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">JENIS KELAMIN</label>
                            <select
                                wire:model="gender"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            >
                                <option value="MALE">Laki-Laki (L)</option>
                                <option value="FEMALE">Perempuan (P)</option>
                            </select>
                            @error('gender') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

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
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA ORANG TUA / WALI</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. H. Budi"
                            wire:model="parent_name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('parent_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">KONTAK HP ORANG TUA (WA)</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. 081223344556"
                            wire:model="parent_phone"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('parent_phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
