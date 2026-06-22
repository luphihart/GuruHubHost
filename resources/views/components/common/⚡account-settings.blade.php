<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // Form fields
    public $email = '';
    public $old_password = '';
    public $password = '';
    public $password_confirmation = '';
    
    // Additional Profile fields for teachers
    public $name = '';
    public $phone = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->email = $user->email;
            
            if ($user->role === 'TEACHER' && $user->teacher) {
                $this->name = $user->teacher->name;
                $this->phone = $user->teacher->phone;
            }
        }
    }

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }

    public function updateAccount()
    {
        $user = Auth::user();
        if (!$user) return;

        // Base Validation
        $validationRules = [
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ];

        if ($user->role === 'TEACHER') {
            $validationRules['name'] = 'required|string|max:255';
            $validationRules['phone'] = 'nullable|string|max:20';
        }

        $this->validate($validationRules);

        try {
            // Update User Email
            $user->update(['email' => $this->email]);

            // Update Teacher Profile if applicable
            if ($user->role === 'TEACHER' && $user->teacher) {
                $user->teacher->update([
                    'name' => $this->name,
                    'phone' => $this->normalizePhone($this->phone),
                ]);
            }

            $this->showFeedback('success', 'Profil akun berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal memperbarui akun: ' . $e->getMessage());
        }
    }

    public function updatePassword()
    {
        $user = Auth::user();
        if (!$user) return;

        $this->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Check old password
        if (!Hash::check($this->old_password, $user->password_hash)) {
            $this->addError('old_password', 'Kata sandi lama tidak cocok.');
            return;
        }

        try {
            $user->update([
                'password_hash' => Hash::make($this->password),
            ]);

            $this->old_password = '';
            $this->password = '';
            $this->password_confirmation = '';

            $this->showFeedback('success', 'Kata sandi berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal mengubah kata sandi: ' . $e->getMessage());
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
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                Pengaturan
            </span>
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Pengaturan Akun</h2>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">
        <!-- Edit Profile -->
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight border-b border-slate-100 pb-2">Informasi Profil</h3>
            
            <form wire:submit="updateAccount" class="space-y-4">
                @if (auth()->user()->role === 'TEACHER')
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA GURU</label>
                        <input
                            type="text"
                            required
                            wire:model="name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NOMOR HP AKTIF (WA)</label>
                        <input
                            type="text"
                            wire:model="phone"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">EMAIL AKUN</label>
                    <input
                        type="email"
                        required
                        wire:model="email"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all font-mono"
                    />
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button
                        type="submit"
                        class="flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
                    >
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Ubah Kata Sandi -->
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight border-b border-slate-100 pb-2">Ubah Kata Sandi</h3>
            
            <form wire:submit="updatePassword" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">KATA SANDI SEKARANG</label>
                    <input
                        type="password"
                        required
                        wire:model="old_password"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('old_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">KATA SANDI BARU</label>
                    <input
                        type="password"
                        required
                        wire:model="password"
                        placeholder="Min. 6 karakter"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#0F172A]">KONFIRMASI KATA SANDI BARU</label>
                    <input
                        type="password"
                        required
                        wire:model="password_confirmation"
                        class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                    />
                    @error('password_confirmation') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button
                        type="submit"
                        class="flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
                    >
                        Perbarui Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
