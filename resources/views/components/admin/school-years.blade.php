<?php

use Livewire\Component;
use App\Models\SchoolYear;
use App\Models\Semester;

new class extends Component
{
    // Modal states
    public $showYearModal = false;
    public $yearModalMode = 'add';
    public $currentYearId = null;
    
    // Form fields for SchoolYear
    public $yearName = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'yearName' => 'required|string|max:50',
    ];

    public function mount()
    {
        // Auto-seed semesters if not present
        if (Semester::count() === 0) {
            Semester::create(['name' => 'GANJIL', 'is_active' => true]);
            Semester::create(['name' => 'GENAP', 'is_active' => false]);
        }
    }

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function openAddYearModal()
    {
        $this->resetValidation();
        $this->yearModalMode = 'add';
        $this->currentYearId = null;
        $this->yearName = '';
        $this->showYearModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditYearModal($id)
    {
        $this->resetValidation();
        $year = SchoolYear::find($id);
        if (!$year) return;

        $this->yearModalMode = 'edit';
        $this->currentYearId = $year->id;
        $this->yearName = $year->name;
        $this->showYearModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveYear()
    {
        $this->validate();

        try {
            if ($this->yearModalMode === 'add') {
                $this->validate(['yearName' => 'unique:school_years,name']);
                SchoolYear::create([
                    'name' => $this->yearName,
                    'is_active' => false,
                ]);
                $this->showFeedback('success', 'Tahun pelajaran baru berhasil ditambahkan.');
            } else {
                $year = SchoolYear::find($this->currentYearId);
                if ($year) {
                    $this->validate(['yearName' => 'unique:school_years,name,' . $this->currentYearId]);
                    $year->update([
                        'name' => $this->yearName,
                    ]);
                    $this->showFeedback('success', 'Tahun pelajaran berhasil diperbarui.');
                }
            }
            $this->showYearModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan tahun pelajaran: ' . $e->getMessage());
        }
    }

    public function activateYear($id)
    {
        try {
            SchoolYear::query()->update(['is_active' => false]);
            $year = SchoolYear::find($id);
            if ($year) {
                $year->update(['is_active' => true]);
                $this->showFeedback('success', 'Tahun pelajaran ' . $year->name . ' berhasil diaktifkan.');
            }
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal mengaktifkan tahun pelajaran: ' . $e->getMessage());
        }
    }

    public function deleteYear($id)
    {
        $year = SchoolYear::find($id);
        if (!$year) return;

        try {
            if ($year->is_active) {
                $this->showFeedback('error', 'Tidak dapat menghapus tahun pelajaran yang sedang aktif.');
                return;
            }
            
            // Check if there are related classes
            if ($year->classes()->count() > 0) {
                $this->showFeedback('error', 'Tidak dapat menghapus tahun pelajaran yang sudah terhubung dengan data kelas.');
                return;
            }

            $year->delete();
            $this->showFeedback('success', 'Tahun pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus tahun pelajaran: ' . $e->getMessage());
        }
    }

    public function activateSemester($id)
    {
        try {
            Semester::query()->update(['is_active' => false]);
            $sem = Semester::find($id);
            if ($sem) {
                $sem->update(['is_active' => true]);
                $this->showFeedback('success', 'Semester ' . $sem->name . ' berhasil diaktifkan.');
            }
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal mengaktifkan semester: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $years = SchoolYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('components.admin.school-years', [
            'years' => $years,
            'semesters' => $semesters,
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
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Tahun Pelajaran & Semester</h2>
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

    <!-- Two Columns Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- School Years Column (Takes 2 span on large screens) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[#F1F5F9] flex justify-between items-center bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-[#4F46E5]/10 rounded-xl text-[#4F46E5]">
                        <i data-lucide="calendar-days" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[#0F172A] text-sm font-display">Daftar Tahun Pelajaran</h3>
                        <p class="text-[11px] text-[#64748B] font-medium">Kelola dan aktifkan periode tahun ajaran aktif</p>
                    </div>
                </div>
                <button
                    wire:click="openAddYearModal"
                    class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Tambah Tahun
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#F1F5F9] text-[10px] font-bold text-[#64748B] uppercase tracking-wider bg-[#F8FAFC]">
                            <th class="p-4 pl-6">Tahun Pelajaran</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 pr-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9] text-xs">
                        @forelse ($years as $year)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6 font-semibold text-[#0F172A]">
                                    {{ $year->name }}
                                </td>
                                <td class="p-4 text-center">
                                    @if ($year->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (!$year->is_active)
                                            <button
                                                wire:click="activateYear('{{ $year->id }}')"
                                                class="px-2.5 py-1 text-[10px] font-bold rounded-lg text-white bg-[#0EA5E9] hover:bg-[#0284C7] transition-all"
                                                title="Aktifkan Tahun Pelajaran"
                                            >
                                                Aktifkan
                                            </button>
                                            <button
                                                wire:click="deleteYear('{{ $year->id }}')"
                                                wire:confirm="Apakah Anda yakin ingin menghapus tahun pelajaran ini?"
                                                class="p-1 hover:bg-rose-50 text-rose-500 hover:text-rose-700 rounded-lg transition-all"
                                                title="Hapus"
                                            >
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        @endif
                                        <button
                                            wire:click="openEditYearModal('{{ $year->id }}')"
                                            class="p-1 hover:bg-slate-100 text-slate-500 hover:text-slate-700 rounded-lg transition-all"
                                            title="Edit"
                                        >
                                            <i data-lucide="edit" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-[#94A3B8] italic">
                                    Belum ada data tahun pelajaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Semester Column (Takes 1 span) -->
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[#F1F5F9] bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-[#0EA5E9]/10 rounded-xl text-[#0EA5E9]">
                        <i data-lucide="milestone" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[#0F172A] text-sm font-display">Semester Aktif</h3>
                        <p class="text-[11px] text-[#64748B] font-medium">Pilih periode semester akademik aktif</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                @foreach ($semesters as $sem)
                    <div class="p-4 rounded-2xl border transition-all flex items-center justify-between {{
                        $sem->is_active 
                        ? 'bg-emerald-50/50 border-emerald-200 text-emerald-900' 
                        : 'bg-slate-50/50 border-[#E2E8F0] text-slate-600'
                    }}">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold uppercase tracking-wider block">SEMESTER {{ $sem->name }}</span>
                            @if ($sem->is_active)
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-600">
                                    <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                                    Sedang Digunakan
                                </span>
                            @else
                                <span class="text-[9px] text-[#94A3B8]">Tidak Aktif</span>
                            @endif
                        </div>

                        @if (!$sem->is_active)
                            <button
                                wire:click="activateSemester('{{ $sem->id }}')"
                                class="px-3 py-1.5 text-[10px] font-bold rounded-xl text-[#0EA5E9] bg-[#0EA5E9]/10 hover:bg-[#0EA5E9] hover:text-white transition-all shadow-sm"
                            >
                                Aktifkan
                            </button>
                        @else
                            <span class="p-1.5 bg-emerald-500 rounded-full text-white">
                                <i data-lucide="check" class="h-4 w-4"></i>
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add/Edit School Year Modal -->
    @if ($showYearModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="$set('showYearModal', false)">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#E2E8F0] animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">
                        {{ $yearModalMode === 'add' ? 'Tambah Tahun Pelajaran' : 'Edit Tahun Pelajaran' }}
                    </h3>
                    <button
                        wire:click="$set('showYearModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveYear" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">TAHUN PELAJARAN</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. 2025/2026"
                            wire:model="yearName"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl px-4 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] placeholder-[#94A3B8] @error('yearName') border-rose-400 @enderror"
                        />
                        @error('yearName') <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        <p class="text-[9px] text-[#94A3B8] italic mt-1">Format rekomendasi: YYYY/YYYY (contoh: 2025/2026)</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#F1F5F9]">
                        <button
                            type="button"
                            wire:click="$set('showYearModal', false)"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
