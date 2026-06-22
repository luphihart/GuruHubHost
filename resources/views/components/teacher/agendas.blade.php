<?php

use Livewire\Component;
use App\Models\Agenda;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add';
    public $currentId = null;
    
    // Form fields
    public $title = '';
    public $description = '';
    public $date = '';
    public $start_time = '08:00';
    public $end_time = '10:00';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'date' => 'required|date',
        'start_time' => 'required|string|max:5',
        'end_time' => 'required|string|max:5',
    ];

    public function mount()
    {
        $this->date = now()->toDateString();
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
        $this->modalMode = 'add';
        $this->currentId = null;
        $this->title = '';
        $this->description = '';
        $this->date = now()->toDateString();
        $this->start_time = '08:00';
        $this->end_time = '10:00';
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($agendaId)
    {
        $this->resetValidation();
        $agenda = Agenda::find($agendaId);
        if (!$agenda) return;

        $this->modalMode = 'edit';
        $this->currentId = $agenda->id;
        $this->title = $agenda->title;
        $this->description = $agenda->description ?? '';
        $this->date = $agenda->date->toDateString();
        $this->start_time = $agenda->start_time;
        $this->end_time = $agenda->end_time;
        
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveAgenda()
    {
        $this->validate();
        $teacher = Auth::user()->teacher;
        if (!$teacher) return;

        try {
            if ($this->modalMode === 'add') {
                Agenda::create([
                    'teacher_id' => $teacher->id,
                    'title' => $this->title,
                    'description' => $this->description ?: null,
                    'date' => $this->date,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                ]);
                $this->showFeedback('success', 'Agenda baru berhasil ditambahkan.');
            } else {
                $agenda = Agenda::find($this->currentId);
                if ($agenda) {
                    $agenda->update([
                        'title' => $this->title,
                        'description' => $this->description ?: null,
                        'date' => $this->date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                    ]);
                    $this->showFeedback('success', 'Data agenda berhasil diperbarui.');
                }
            }
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menyimpan agenda: ' . $e->getMessage());
        }
    }

    public function deleteAgenda($agendaId)
    {
        $agenda = Agenda::find($agendaId);
        if (!$agenda) return;

        try {
            $agenda->delete();
            $this->showFeedback('success', 'Agenda berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus agenda: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $teacher = Auth::user()->teacher;
        $query = Agenda::where('teacher_id', $teacher ? $teacher->id : null);
        
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        $agendas = $query->orderBy('date', 'desc')->orderBy('start_time')->get();

        return view('components.teacher.agendas', [
            'agendasList' => $agendas,
        ]);
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Buku Agenda
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Agenda Harian Guru</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Agenda Baru
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
            placeholder="Cari agenda berdasarkan judul atau deskripsi..."
            wire:model.live.debounce.300ms="searchQuery"
            class="w-full bg-transparent border-none text-xs text-[#0F172A] focus:outline-none placeholder-[#94A3B8]"
        />
    </div>

    <!-- Timeline of Agendas -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden p-6 space-y-4">
        @forelse($agendasList as $agenda)
            <div class="p-5 border border-[#F1F5F9] bg-[#F8FAFC] rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[9px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2.5 py-0.5 rounded-full font-mono">
                            {{ $agenda->date->format('d M Y') }} &bull; {{ $agenda->start_time }} - {{ $agenda->end_time }}
                        </span>
                    </div>
                    <h4 class="text-sm font-bold text-[#0F172A] font-display">
                        {{ $agenda->title }}
                    </h4>
                    @if ($agenda->description)
                        <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                            {{ $agenda->description }}
                        </p>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2">
                    <button
                        wire:click="openEditModal('{{ $agenda->id }}')"
                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                        title="Edit Agenda"
                    >
                        <i data-lucide="edit-2" class="h-4.5 w-4.5"></i>
                    </button>
                    <button
                        onclick="confirm('Apakah Anda yakin ingin menghapus agenda ini?') || event.stopImmediatePropagation()"
                        wire:click="deleteAgenda('{{ $agenda->id }}')"
                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                        title="Hapus Agenda"
                    >
                        <i data-lucide="trash-2" class="h-4.5 w-4.5"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="py-12 border border-dashed border-[#E2E8F0] rounded-2xl text-center text-[#94A3B8] italic flex flex-col items-center justify-center gap-2">
                <i data-lucide="calendar" class="h-8 w-8 text-[#94A3B8]"></i>
                <span class="text-xs">Belum ada agenda kegiatan yang dicatat.</span>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah / Edit -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#E2E8F0]">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">
                        {{ $modalMode === 'add' ? 'Tambah Agenda Baru' : 'Edit Agenda' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveAgenda" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">JUDUL AGENDA</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. Rapat Kerja MGMP"
                            wire:model="title"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">DESKRIPSI (OPSIONAL)</label>
                        <textarea
                            placeholder="Detail kegiatan..."
                            wire:model="description"
                            rows="3"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        ></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">TANGGAL KEGIATAN</label>
                        <input
                            type="date"
                            required
                            wire:model="date"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">JAM MULAI</label>
                            <input
                                type="text"
                                required
                                placeholder="e.g. 08:00"
                                wire:model="start_time"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all font-mono"
                            />
                            @error('start_time') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">JAM SELESAI</label>
                            <input
                                type="text"
                                required
                                placeholder="e.g. 10:00"
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
