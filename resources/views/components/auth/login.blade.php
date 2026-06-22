<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $showPassword = false;
    public $errorMessage = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:4',
    ];

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->validate();
        $this->errorMessage = '';

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            
            $user = Auth::user();
            if ($user->role === 'ADMIN') {
                return redirect()->intended('/admin');
            } else {
                return redirect()->intended('/teacher');
            }
        }

        $this->errorMessage = 'Email atau kata sandi yang Anda masukkan salah.';
    }
};
?>

<div class="min-h-screen flex flex-col md:flex-row bg-[#F8FAFC]">
    <!-- Kolom Branding Kiri -->
    <div class="hidden md:flex md:w-1/2 bg-gradient-to-tr from-[#4F46E5] to-[#0EA5E9] p-12 flex-col justify-between text-white relative overflow-hidden">
        <!-- Dekorasi Latar Belakang -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/10 rounded-full blur-3xl -mr-40 -mt-40"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-black/10 rounded-full blur-3xl -ml-40 -mb-40"></div>

        <div class="relative z-10">
            <span class="text-2xl font-bold tracking-tight">Guru<span class="text-[#0EA5E9] bg-white px-2 py-1 rounded ml-1 text-base font-semibold inline-block align-middle transform -rotate-2">Hub</span></span>
        </div>

        <div class="relative z-10 max-w-lg my-auto space-y-6">
            <h1 class="text-4xl lg:text-5xl font-bold leading-tight">
                Administrasi Mengajar Guru Jadi Lebih Cepat & Terstruktur.
            </h1>
            <p class="text-white/80 text-lg">
                Satu portal terpadu untuk Presensi Kelas, Jurnal Harian, Spreadsheet Nilai TP, Agenda Kegiatan, dan Catatan Pembinaan Guru Wali.
            </p>
            <div class="flex gap-4 pt-4">
                <div class="bg-white/10 backdrop-blur px-4 py-3 rounded-2xl border border-white/20 text-center">
                    <span class="block text-2xl font-bold">100%</span>
                    <span class="text-xs text-white/70">Sesuai TP</span>
                </div>
                <div class="bg-white/10 backdrop-blur px-4 py-3 rounded-2xl border border-white/20 text-center">
                    <span class="block text-2xl font-bold">5x</span>
                    <span class="text-xs text-white/70">Lebih Cepat</span>
                </div>
                <div class="bg-white/10 backdrop-blur px-4 py-3 rounded-2xl border border-white/20 text-center">
                    <span class="block text-2xl font-bold">Zero</span>
                    <span class="text-xs text-white/70">Kertas Fisik</span>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-sm text-white/60">
            &copy; 2026 GuruHub. Portal SaaS Administrasi Guru Modern.
        </div>
    </div>

    <!-- Kolom Form Kanan -->
    <div class="flex-1 flex items-center justify-center p-6 md:p-12 lg:p-16">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center md:text-left">
                <div class="md:hidden inline-block mb-4">
                    <span class="text-3xl font-bold tracking-tight text-[#4F46E5]">GuruHub</span>
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight text-[#0F172A]">
                    Selamat Datang Kembali
                </h2>
                <p class="mt-2 text-sm text-[#64748B]">
                    Silakan masuk untuk mengelola administrasi mengajar harian Anda.
                </p>
            </div>

            @if ($errorMessage)
                <div class="p-4 bg-rose-50 border border-rose-100 text-rose-600 rounded-2xl text-sm font-medium">
                    {{ $errorMessage }}
                </div>
            @endif

            <form class="mt-8 space-y-6" wire:submit="login">
                <div class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#0F172A]">
                            Email Sekolah
                        </label>
                        <div class="mt-1.5 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#64748B]">
                                <i data-lucide="mail" class="h-5 w-5"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                required
                                wire:model="email"
                                class="block w-full pl-11 pr-4 py-3.5 border border-[#E2E8F0] rounded-2xl bg-white text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] transition-all text-sm @error('email') border-red-500 @enderror"
                                placeholder="nama@sekolah.sch.id"
                            />
                        </div>
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kata Sandi -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#0F172A]">
                            Kata Sandi
                        </label>
                        <div class="mt-1.5 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#64748B]">
                                <i data-lucide="lock" class="h-5 w-5"></i>
                            </div>
                            <input
                                id="password"
                                type="{{ $showPassword ? 'text' : 'password' }}"
                                required
                                wire:model="password"
                                class="block w-full pl-11 pr-11 py-3.5 border border-[#E2E8F0] rounded-2xl bg-white text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 focus:border-[#4F46E5] transition-all text-sm @error('password') border-red-500 @enderror"
                                placeholder="Masukkan sandi Anda"
                            />
                            <button
                                type="button"
                                wire:click="togglePassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#64748B] hover:text-[#0F172A] transition-colors"
                            >
                                @if ($showPassword)
                                    <i data-lucide="eye-off" class="h-5 w-5"></i>
                                @else
                                    <i data-lucide="eye" class="h-5 w-5"></i>
                                @endif
                            </button>
                        </div>
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3.5 border border-transparent text-sm font-semibold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4F46E5] disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-[#4F46E5]/20 transition-all hover:scale-[1.01] active:scale-[0.99]"
                >
                    <span wire:loading.remove class="flex items-center gap-2">
                        <i data-lucide="log-in" class="h-5 w-5"></i>
                        Masuk ke Portal
                    </span>
                    <span wire:loading class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="h-5 w-5 animate-spin"></i>
                        Memproses Masuk...
                    </span>
                </button>
            </form>

            <div class="pt-6 border-t border-[#E2E8F0] text-center">
                <span class="text-xs text-[#64748B]">
                    Lupa sandi? Hubungi Administrator atau Wakil Kurikulum untuk penyetelan ulang.
                </span>
            </div>
        </div>
    </div>
</div>