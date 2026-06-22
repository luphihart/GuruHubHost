<?php

use Livewire\Component;
use App\Models\SchoolProfile;

new class extends Component
{
    // Form fields
    public $yayasan_name = '';
    public $school_name = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $website = '';
    public $headmaster = '';
    public $headmaster_nip = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'yayasan_name' => 'required|string|max:255',
        'school_name' => 'required|string|max:255',
        'address' => 'required|string',
        'phone' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:100',
        'website' => 'nullable|string|max:100',
        'headmaster' => 'required|string|max:255',
        'headmaster_nip' => 'required|string|max:50',
    ];

    public function mount()
    {
        $profile = SchoolProfile::find('singleton');
        if ($profile) {
            $this->yayasan_name = $profile->yayasan_name;
            $this->school_name = $profile->school_name;
            $this->address = $profile->address;
            $this->phone = $profile->phone;
            $this->email = $profile->email;
            $this->website = $profile->website;
            $this->headmaster = $profile->headmaster;
            $this->headmaster_nip = $profile->headmaster_nip;
        }
    }

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function saveProfile()
    {
        $this->validate();

        try {
            $profile = SchoolProfile::updateOrCreate(
                ['id' => 'singleton'],
                [
                    'yayasan_name' => $this->yayasan_name,
                    'school_name' => $this->school_name,
                    'address' => $this->address,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'website' => $this->website,
                    'headmaster' => $this->headmaster,
                    'headmaster_nip' => $this->headmaster_nip,
                ]
            );

            $this->showFeedback('success', 'Profil sekolah berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Identitas Sekolah
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Profil Sekolah</h2>
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

    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden p-6 max-w-2xl">
        <form wire:submit="saveProfile" class="space-y-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-[#0F172A]">NAMA YAYASAN</label>
                <input
                    type="text"
                    required
                    placeholder="e.g. YAYASAN PENDIDIKAN GURUHUB INDONESIA"
                    wire:model="yayasan_name"
                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                />
                @error('yayasan_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-[#0F172A]">NAMA SEKOLAH</label>
                <input
                    type="text"
                    required
                    placeholder="e.g. SMA GURUHUB UTAMA"
                    wire:model="school_name"
                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                />
                @error('school_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-[#0F172A]">ALAMAT SEKOLAH</label>
                <textarea
                    required
                    rows="3"
                    placeholder="Alamat lengkap sekolah..."
                    wire:model="address"
                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                ></textarea>
                @error('address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">TELEPON SEKOLAH</label>
                    <input
                        type="text"
                        placeholder="e.g. (022) 1234567"
                        wire:model="phone"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">EMAIL SEKOLAH</label>
                    <input
                        type="email"
                        placeholder="e.g. info@sekolah.sch.id"
                        wire:model="email"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-[#0F172A]">WEBSITE SEKOLAH</label>
                <input
                    type="text"
                    placeholder="e.g. www.sekolah.sch.id"
                    wire:model="website"
                    class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                />
                @error('website') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">NAMA KEPALA SEKOLAH</label>
                    <input
                        type="text"
                        required
                        placeholder="e.g. Dr. H. Mulyadi, M.Pd."
                        wire:model="headmaster"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('headmaster') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">NIP KEPALA SEKOLAH</label>
                    <input
                        type="text"
                        required
                        placeholder="e.g. 197208201998031003"
                        wire:model="headmaster_nip"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('headmaster_nip') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-6 py-3 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
                >
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
