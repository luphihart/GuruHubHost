<?php

use Livewire\Component;
use App\Models\Subject;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add';
    public $currentId = null;
    
    // Form fields
    public $code = '';
    public $name = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'code' => 'required|string|max:50',
        'name' => 'required|string|max:255',
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
        $this->code = '';
        $this->name = '';
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($subjectId)
    {
        $this->resetValidation();
        $subject = Subject::find($subjectId);
        if (!$subject) return;

        $this->modalMode = 'edit';
        $this->currentId = $subject->id;
        $this->code = $subject->code;
        $this->name = $subject->name;
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveSubject()
    {
        $this->validate();

        try {
            if ($this->modalMode === 'add') {
                $this->validate(['code' => 'unique:subjects,code']);
                Subject::create([
                    'code' => $this->code,
                    'name' => $this->name,
                ]);
                $this->showFeedback('success', 'Mata pelajaran baru berhasil ditambahkan.');
            } else {
                $subject = Subject::find($this->currentId);
                if ($subject) {
                    $this->validate(['code' => 'unique:subjects,code,' . $this->currentId]);
                    $subject->update([
                        'code' => $this->code,
                        'name' => $this->name,
                    ]);
                    $this->showFeedback('success', 'Data mata pelajaran berhasil diperbarui.');
                }
            }
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan mata pelajaran: ' . $e->getMessage());
        }
    }

    public function deleteSubject($subjectId)
    {
        $subject = Subject::find($subjectId);
        if (!$subject) return;

        try {
            $subject->delete();
            $this->showFeedback('success', 'Data mata pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus mata pelajaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Subject::query();
        
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('code', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        $subjects = $query->orderBy('name')->get();

        return view('components.admin.⚡subjects', [
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
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Mata Pelajaran</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Mapel Baru
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
            placeholder="Cari mata pelajaran berdasarkan nama atau kode (e.g. MAT-SMA)..."
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
                        <th class="p-4">Kode Mapel</th>
                        <th class="p-4">Nama Mata Pelajaran</th>
                        <th class="p-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($subjectsList as $index => $sub)
                        <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                            <td class="p-4 text-center font-bold text-[#64748B] bg-[#F8FAFC]/10">{{ $index + 1 }}</td>
                            <td class="p-4 font-mono text-xs text-[#64748B] font-bold">{{ $sub->code }}</td>
                            <td class="p-4 font-bold text-[#0F172A]">{{ $sub->name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-1">
                                    <button
                                        wire:click="openEditModal('{{ $sub->id }}')"
                                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        onclick="confirm('Apakah Anda yakin ingin menghapus mapel ini?') || event.stopImmediatePropagation()"
                                        wire:click="deleteSubject('{{ $sub->id }}')"
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
                            <td colSpan="4" class="p-12 text-center text-[#94A3B8] italic">
                                Tidak ada data mata pelajaran ditemukan.
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
                        {{ $modalMode === 'add' ? 'Tambah Mapel Baru' : 'Edit Data Mapel' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveSubject" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">KODE MATA PELAJARAN</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. MAT-SMA"
                            wire:model="code"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all font-mono"
                        />
                        @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA MATA PELAJARAN</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. Matematika Peminatan"
                            wire:model="name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
