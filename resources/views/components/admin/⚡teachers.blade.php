<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $searchQuery = '';
    
    // Modal states
    public $showModal = false;
    public $modalMode = 'add'; // 'add' or 'edit'
    public $currentId = null;
    
    // Form fields
    public $name = '';
    public $nip = '';
    public $phone = '';
    public $email = '';
    public $password = '';
    
    // Reset password states
    public $showResetModal = false;
    public $resetTeacherId = null;
    public $resetTeacherName = '';
    public $newResetPassword = '';
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'nip' => 'required|string|max:50',
        'phone' => 'nullable|string|max:20',
        'email' => 'required|email|max:255',
    ];

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        
        // Hide after 3 seconds
        $this->dispatch('clear-feedback');
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->modalMode = 'add';
        $this->currentId = null;
        $this->name = '';
        $this->nip = '';
        $this->phone = '';
        $this->email = '';
        $this->password = '';
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function openEditModal($teacherId)
    {
        $this->resetValidation();
        $teacher = Teacher::with('user')->find($teacherId);
        if (!$teacher) return;

        $this->modalMode = 'edit';
        $this->currentId = $teacher->id;
        $this->name = $teacher->name;
        $this->nip = $teacher->nip;
        $this->phone = $teacher->phone;
        $this->email = $teacher->user->email;
        $this->password = '';
        $this->showModal = true;
        $this->dispatch('init-lucide');
    }

    public function saveTeacher()
    {
        $this->validate();

        // Extra validation for Add
        if ($this->modalMode === 'add') {
            $this->validate([
                'password' => 'required|min:6',
                'email' => 'unique:users,email',
                'nip' => 'unique:teachers,nip',
            ]);

            try {
                DB::transaction(function () {
                    // Normalize phone (hlr 62)
                    $normalizedPhone = $this->normalizePhone($this->phone);

                    $user = User::create([
                        'email' => $this->email,
                        'password_hash' => Hash::make($this->password),
                        'role' => 'TEACHER',
                    ]);

                    Teacher::create([
                        'user_id' => $user->id,
                        'nip' => $this->nip,
                        'name' => $this->name,
                        'phone' => $normalizedPhone,
                    ]);
                });

                $this->showModal = false;
                $this->showFeedback('success', 'Guru baru berhasil ditambahkan.');
            } catch (\Exception $e) {
                $this->showFeedback('error', 'Gagal menambahkan guru: ' . $e->getMessage());
            }
        } else {
            // Edit
            $teacher = Teacher::find($this->currentId);
            if (!$teacher) return;

            $this->validate([
                'nip' => 'unique:teachers,nip,' . $this->currentId,
            ]);

            try {
                DB::transaction(function () use ($teacher) {
                    $normalizedPhone = $this->normalizePhone($this->phone);

                    $teacher->update([
                        'nip' => $this->nip,
                        'name' => $this->name,
                        'phone' => $normalizedPhone,
                    ]);
                });

                $this->showModal = false;
                $this->showFeedback('success', 'Data guru berhasil diperbarui.');
            } catch (\Exception $e) {
                $this->showFeedback('error', 'Gagal memperbarui data guru: ' . $e->getMessage());
            }
        }
    }

    public function openResetModal($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher) return;

        $this->resetTeacherId = $teacher->id;
        $this->resetTeacherName = $teacher->name;
        $this->newResetPassword = '';
        $this->showResetModal = true;
        $this->dispatch('init-lucide');
    }

    public function resetPassword()
    {
        $teacher = Teacher::with('user')->find($this->resetTeacherId);
        if (!$teacher) return;

        $passwordToSet = $this->newResetPassword ?: $teacher->nip;
        if (strlen($passwordToSet) < 4) {
            $this->showFeedback('error', 'Kata sandi harus minimal 4 karakter (atau gunakan NIP yang memenuhi syarat).');
            return;
        }

        try {
            $teacher->user->update([
                'password_hash' => Hash::make($passwordToSet),
            ]);

            $this->showResetModal = false;
            $this->showFeedback('success', 'Kata sandi guru "' . $teacher->name . '" berhasil di-reset.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal mereset kata sandi: ' . $e->getMessage());
        }
    }

    public function deleteTeacher($teacherId)
    {
        $teacher = Teacher::with('user')->find($teacherId);
        if (!$teacher) return;

        try {
            DB::transaction(function () use ($teacher) {
                // Delete user (cascade will delete teacher record)
                if ($teacher->user) {
                    $teacher->user->delete();
                } else {
                    $teacher->delete();
                }
            });

            $this->showFeedback('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus guru: ' . $e->getMessage());
        }
    }

    private function normalizePhone($phone)
    {
        if (empty($phone)) return null;
        
        // Remove non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phone);
        
        // If starting with 0, change to 62
        if (strpos($clean, '0') === 0) {
            $clean = '62' . substr($clean, 1);
        }
        
        // If it doesn't start with 62, prepend it
        if (strpos($clean, '62') !== 0) {
            $clean = '62' . $clean;
        }
        
        return $clean;
    }

    public function render()
    {
        $query = Teacher::with('user');
        
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('nip', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        $teachers = $query->orderBy('name')->get();

        return view('components.admin.⚡teachers', [
            'teachersList' => $teachers,
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
            <h2 class="text-2xl font-black text-[#0F172A] tracking-tight font-display">Data Guru</h2>
        </div>
        
        <button
            wire:click="openAddModal"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-all shadow-md shadow-[#4F46E5]/10 hover:scale-[1.01]"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Guru Baru
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
            placeholder="Cari guru berdasarkan nama atau NIP..."
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
                        <th class="p-4">NIP</th>
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">No. Telepon</th>
                        <th class="p-4">Email Akun</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($teachersList as $index => $teacher)
                        <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                            <td class="p-4 text-center font-bold text-[#64748B] bg-[#F8FAFC]/10">{{ $index + 1 }}</td>
                            <td class="p-4 font-mono text-xs text-[#64748B]">{{ $teacher->nip }}</td>
                            <td class="p-4 font-bold text-[#0F172A]">{{ $teacher->name }}</td>
                            <td class="p-4 text-[#64748B] font-mono text-xs">{{ $teacher->phone ?? '-' }}</td>
                            <td class="p-4 text-[#64748B] font-mono text-xs">{{ $teacher->user->email ?? '-' }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-1">
                                    <button
                                        wire:click="openResetModal('{{ $teacher->id }}')"
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition-all"
                                        title="Reset Sandi"
                                    >
                                        <i data-lucide="key" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        wire:click="openEditModal('{{ $teacher->id }}')"
                                        class="p-2 text-[#4F46E5] hover:bg-[#4F46E5]/10 rounded-xl transition-all"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </button>
                                    <button
                                        onclick="confirm('Apakah Anda yakin ingin menghapus guru ini?') || event.stopImmediatePropagation()"
                                        wire:click="deleteTeacher('{{ $teacher->id }}')"
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
                                Tidak ada data guru ditemukan.
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
                        {{ $modalMode === 'add' ? 'Tambah Guru Baru' : 'Edit Data Guru' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="saveTeacher" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NAMA LENGKAP & GELAR</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. Budi Santoso, S.Pd."
                            wire:model="name"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NOMOR INDUK PEGAWAI (NIP)</label>
                        <input
                            type="text"
                            required
                            placeholder="e.g. 198804152015031002"
                            wire:model="nip"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('nip') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">NOMOR TELEPON (WA/AKTIF)</label>
                        <input
                            type="text"
                            placeholder="e.g. 081234567890"
                            wire:model="phone"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                        @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">EMAIL SEKOLAH</label>
                        <input
                            type="email"
                            required
                            placeholder="e.g. guru@sekolah.sch.id"
                            wire:model="email"
                            @if ($modalMode === 'edit') disabled @endif
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all disabled:opacity-50"
                        />
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if ($modalMode === 'add')
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#0F172A]">KATA SANDI AKUN</label>
                            <input
                                type="password"
                                required
                                placeholder="Min. 6 karakter"
                                wire:model="password"
                                class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                            />
                            @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

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

    <!-- Modal Reset Password -->
    @if ($showResetModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#E2E8F0]">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">Reset Sandi Guru</h3>
                    <button
                        wire:click="$set('showResetModal', false)"
                        class="p-1.5 hover:bg-[#F8FAFC] rounded-xl text-[#64748B] hover:text-[#0F172A] transition-colors"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form wire:submit="resetPassword" class="p-6 space-y-4">
                    <div class="bg-[#FFFBEB] border border-[#FDE68A] p-4 rounded-2xl text-xs text-[#92400E] space-y-1">
                        <span class="font-bold block">Perhatian:</span>
                        <p>
                            Mengubah sandi untuk guru <strong>{{ $resetTeacherName }}</strong>. Jika Anda mengosongkan kolom sandi baru di bawah, sandi guru tersebut akan di-reset menggunakan **NIP**-nya sebagai sandi default.
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#0F172A]">SANDI BARU (OPSIONAL)</label>
                        <input
                            type="password"
                            placeholder="Kosongkan untuk menggunakan NIP sebagai sandi"
                            wire:model="newResetPassword"
                            class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-3 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                        />
                    </div>

                    <div class="pt-4 border-t border-[#E2E8F0] flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="$set('showResetModal', false)"
                            class="px-4 py-2.5 text-xs font-bold rounded-xl text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC] border border-[#E2E8F0] transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-amber-600 hover:bg-amber-700 transition-colors disabled:opacity-50"
                        >
                            Reset Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
