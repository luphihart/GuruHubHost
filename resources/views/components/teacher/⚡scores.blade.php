<?php

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\LearningObjective;
use App\Models\Student;
use App\Models\Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    #[Url]
    public $classId = '';
    #[Url]
    public $subjectId = '';

    // Spreadsheet Data
    public $classInfo = null;
    public $subjectInfo = null;
    public $objectives = [];
    public $rows = [];
    
    // Config Modal states
    public $showConfigModal = false;
    public $tpCode = '';
    public $tpDesc = '';
    public $modalError = '';

    // Auto-save Status indicator
    public $saveStatus = 'idle'; // 'idle', 'saving', 'saved', 'error'
    
    // Toast state
    public $feedbackType = '';
    public $feedbackMessage = '';

    public function mount()
    {
        $this->loadSpreadsheet();
    }

    public function updatedClassId()
    {
        $this->loadSpreadsheet();
    }

    public function updatedSubjectId()
    {
        $this->loadSpreadsheet();
    }

    public function loadSpreadsheet()
    {
        if (empty($this->classId) || empty($this->subjectId)) {
            $this->classInfo = null;
            $this->subjectInfo = null;
            $this->objectives = [];
            $this->rows = [];
            return;
        }

        $this->classInfo = SchoolClass::with(['schoolYear', 'semester'])->find($this->classId);
        $this->subjectInfo = Subject::find($this->subjectId);

        if (!$this->classInfo || !$this->subjectInfo) {
            $this->classId = '';
            $this->subjectId = '';
            return;
        }

        $teacher = Auth::user()->teacher;

        // Fetch Objectives
        $this->objectives = LearningObjective::where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('teacher_id', $teacher->id)
            ->orderBy('code')
            ->get()
            ->toArray();

        // Fetch Students and Scores
        $students = Student::where('class_id', $this->classId)->orderBy('name')->get();
        $scores = Score::whereIn('student_id', $students->pluck('id'))
            ->whereIn('learning_objective_id', collect($this->objectives)->pluck('id'))
            ->get();

        $sheetRows = [];
        foreach ($students as $student) {
            $studentScores = [];
            $totalScore = 0;
            $scoreCount = 0;

            foreach ($this->objectives as $tp) {
                $scoreRecord = $scores->where('student_id', $student->id)
                    ->where('learning_objective_id', $tp['id'])
                    ->first();
                
                $scoreVal = $scoreRecord ? $scoreRecord->score : null;
                $studentScores[] = [
                    'objectiveId' => $tp['id'],
                    'score' => $scoreVal,
                ];

                if ($scoreVal !== null) {
                    $totalScore += $scoreVal;
                    $scoreCount++;
                }
            }

            $average = $scoreCount > 0 ? round($totalScore / $scoreCount) : null;

            $sheetRows[] = [
                'studentId' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'scores' => $studentScores,
                'average' => $average,
            ];
        }

        $this->rows = $sheetRows;
        $this->dispatch('init-lucide');
    }

    public function handleScoreChange($studentId, $objectiveId, $value)
    {
        $value = trim($value);
        if ($value === '') {
            $numericValue = null;
        } else {
            if (!is_numeric($value)) {
                $this->saveStatus = 'error';
                return;
            }
            $numericValue = (int)$value;
            if ($numericValue < 0 || $numericValue > 100) {
                $this->saveStatus = 'error';
                return;
            }
        }

        $this->saveStatus = 'saving';

        try {
            if ($numericValue === null) {
                // Delete score if empty
                Score::where('student_id', $studentId)
                    ->where('learning_objective_id', $objectiveId)
                    ->delete();
            } else {
                // Update or create score
                Score::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'learning_objective_id' => $objectiveId,
                    ],
                    [
                        'score' => $numericValue,
                    ]
                );
            }

            // Recalculate average in the local rows array
            foreach ($this->rows as $rowIndex => $row) {
                if ($row['studentId'] === $studentId) {
                    $total = 0;
                    $count = 0;
                    foreach ($row['scores'] as $scoreIndex => $s) {
                        if ($s['objectiveId'] === $objectiveId) {
                            $this->rows[$rowIndex]['scores'][$scoreIndex]['score'] = $numericValue;
                            $currentVal = $numericValue;
                        } else {
                            $currentVal = $s['score'];
                        }

                        if ($currentVal !== null) {
                            $total += $currentVal;
                            $count++;
                        }
                    }
                    $this->rows[$rowIndex]['average'] = $count > 0 ? round($total / $count) : null;
                    break;
                }
            }

            $this->saveStatus = 'saved';
        } catch (\Exception $e) {
            $this->saveStatus = 'error';
        }
    }

    public function addTP()
    {
        $this->validate([
            'tpCode' => 'required|string|max:50',
            'tpDesc' => 'required|string|max:1000',
        ]);

        $teacher = Auth::user()->teacher;

        // Check unique code
        $exists = LearningObjective::where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('teacher_id', $teacher->id)
            ->where('code', $this->tpCode)
            ->exists();

        if ($exists) {
            $this->modalError = 'Kode TP ini sudah digunakan pada kelas & mata pelajaran ini.';
            return;
        }

        try {
            LearningObjective::create([
                'subject_id' => $this->subjectId,
                'class_id' => $this->classId,
                'teacher_id' => $teacher->id,
                'code' => $this->tpCode,
                'description' => $this->tpDesc,
            ]);

            $this->tpCode = '';
            $this->tpDesc = '';
            $this->modalError = '';
            
            $this->loadSpreadsheet();
            $this->showConfigModal = false;
            $this->showFeedback('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            $this->modalError = 'Gagal menambahkan TP: ' . $e->getMessage();
        }
    }

    public function deleteTP($tpId)
    {
        try {
            LearningObjective::where('id', $tpId)->delete();
            $this->loadSpreadsheet();
            $this->showFeedback('success', 'Tujuan Pembelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showFeedback('error', 'Gagal menghapus TP: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        if (!$this->classInfo || !$this->subjectInfo) return null;

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Nilai_' . $this->classInfo->name . '_' . $this->subjectInfo->name . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Header row
            $headerRow = ['No', 'NIS', 'Nama Murid'];
            foreach ($this->objectives as $tp) {
                $headerRow[] = $tp['code'];
            }
            $headerRow[] = 'Rata-Rata';
            fputcsv($file, $headerRow);

            // Data rows
            foreach ($this->rows as $index => $row) {
                $dataRow = [$index + 1, $row['nis'], $row['name']];
                foreach ($row['scores'] as $s) {
                    $dataRow[] = $s['score'] !== null ? $s['score'] : '';
                }
                $dataRow[] = $row['average'] !== null ? $row['average'] : '-';
                fputcsv($file, $dataRow);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'Nilai_' . $this->classInfo->name . '_' . $this->subjectInfo->name . '.csv', $headers);
    }

    public function getSchedulesList()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) return collect();

        return Schedule::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();
    }

    public function getIndonesianDayName($day)
    {
        $days = [
            'MONDAY' => 'Senin',
            'TUESDAY' => 'Selasa',
            'WEDNESDAY' => 'Rabu',
            'THURSDAY' => 'Kamis',
            'FRIDAY' => 'Jumat',
            'SATURDAY' => 'Sabtu',
            'SUNDAY' => 'Minggu',
        ];
        return $days[$day] ?? $day;
    }

    public function showFeedback($type, $message)
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
        $this->dispatch('init-lucide');
        $this->dispatch('clear-feedback');
    }
};
?>

<div class="space-y-6" x-data="{ feedback: false }" @clear-feedback.window="feedback = true; setTimeout(() => { feedback = false; }, 3000)">
    <!-- Header Link Kembali & Auto Save indicators -->
    <div class="flex items-center justify-between">
        <a
            href="{{ route('teacher.dashboard') }}"
            class="flex items-center gap-1.5 text-xs font-bold text-[#64748B] hover:text-[#0F172A] transition-colors"
        >
            <i data-lucide="chevron-left" class="h-4 w-4"></i>
            Kembali ke Dashboard
        </a>

        <!-- Save Indicator -->
        <div class="flex items-center gap-2">
            @if ($saveStatus === 'saving')
                <span class="text-xs text-[#64748B] flex items-center gap-1.5 font-semibold bg-white px-3 py-1.5 rounded-full border border-[#E2E8F0] shadow-sm">
                    <i data-lucide="loader-2" class="h-3.5 w-3.5 animate-spin text-[#4F46E5]"></i>
                    Menyimpan otomatis...
                </span>
            @endif
            @if ($saveStatus === 'saved')
                <span class="text-xs text-emerald-600 flex items-center gap-1.5 font-semibold bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">
                    <i data-lucide="check-circle" class="h-3.5 w-3.5 text-emerald-600"></i>
                    Semua perubahan disimpan
                </span>
            @endif
            @if ($saveStatus === 'error')
                <span class="text-xs text-rose-600 flex items-center gap-1.5 font-semibold bg-rose-50 px-3 py-1.5 rounded-full border border-rose-100">
                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 text-rose-600"></i>
                    Gagal menyimpan. Pastikan nilai 0-100.
                </span>
            @endif
            @if ($feedbackMessage)
                <span x-show="feedback" class="text-xs font-bold px-3 py-1.5 rounded-full {{
                    $feedbackType === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'
                }}">
                    {{ $feedbackMessage }}
                </span>
            @endif
        </div>
    </div>

    @if (empty($classId) || empty($subjectId))
        <!-- Grid Pemilihan Jadwal -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm">
                <h3 class="text-base font-bold text-[#0F172A] font-display">Pilih Jadwal untuk Input Nilai TP</h3>
                <p class="text-xs text-[#64748B] mt-1">Pilih salah satu jadwal mengajar di bawah ini untuk mengelola nilai Tujuan Pembelajaran siswa.</p>
            </div>

            @php $schedules = $this->getSchedulesList(); @endphp
            @if ($schedules->isEmpty())
                <div class="bg-white p-12 text-center rounded-3xl border border-[#E2E8F0] shadow-sm space-y-2">
                    <i data-lucide="file-spreadsheet" class="h-12 w-12 text-[#94A3B8] mx-auto"></i>
                    <h3 class="text-base font-bold text-[#0F172A] font-display">Belum Ada Jadwal</h3>
                    <p class="text-xs text-[#64748B] max-w-sm mx-auto">
                        Anda belum memiliki jadwal mengajar yang dikonfigurasi oleh Administrator.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($schedules as $item)
                        <button
                            type="button"
                            wire:click="$set('classId', '{{ $item->class_id }}'); $set('subjectId', '{{ $item->subject_id }}')"
                            class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-sm hover:border-[#4F46E5] text-left transition-all hover:scale-[1.01] active:scale-[0.99] flex flex-col justify-between h-40 space-y-3"
                        >
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 uppercase">
                                        {{ $this->getIndonesianDayName($item->day) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full font-mono">
                                        {{ $item->start_time }} - {{ $item->end_time }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-[#0F172A] line-clamp-1 font-display">{{ $item->subject->name }}</h4>
                                <span class="block text-xs font-semibold text-[#64748B]">Kelas {{ $item->class->name }}</span>
                            </div>
                            
                            <div class="text-xs font-bold text-[#4F46E5] flex items-center gap-1 mt-2">
                                Pilih Jadwal &rarr;
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <!-- Spreadsheet Nilai TP -->
        @if ($classInfo && $subjectInfo)
            <div class="space-y-6">
                <!-- Info Header & Action Buttons -->
                <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold text-[#4F46E5] bg-[#4F46E5]/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                            Spreadsheet Nilai
                        </span>
                        <h3 class="text-xl font-bold text-[#0F172A] font-display">{{ $subjectInfo->name }}</h3>
                        <p class="text-xs text-[#64748B] font-semibold">
                            Kelas {{ $classInfo->name }} &bull; Semester {{ $classInfo->semester->name }} &bull; Tahun Ajaran {{ $classInfo->schoolYear->name }}
                        </p>
                    </div>

                    <!-- Toolbar Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            wire:click="$set('showConfigModal', true)"
                            class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-[#64748B] hover:text-[#0F172A] bg-[#F8FAFC] hover:bg-[#F1F5F9] border border-[#E2E8F0] transition-colors"
                        >
                            <i data-lucide="settings" class="h-4 w-4"></i>
                            Konfigurasi TP
                        </button>
                        <button
                            wire:click="exportExcel"
                            class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-2xl text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-colors"
                        >
                            <i data-lucide="download" class="h-4 w-4"></i>
                            Ekspor CSV
                        </button>
                    </div>
                </div>

                <!-- Spreadsheet Grid -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs border-r border-[#E2E8F0]">
                            <thead>
                                <tr class="bg-[#F8FAFC] text-[#64748B] font-bold border-b border-[#E2E8F0]">
                                    <th class="p-4 border-r border-[#E2E8F0] text-center w-12">No</th>
                                    <th class="p-4 border-r border-[#E2E8F0] w-24">NIS</th>
                                    <th class="p-4 border-r border-[#E2E8F0] w-64">Nama Murid</th>
                                    @foreach($objectives as $tp)
                                        <th
                                            key="{{ $tp['id'] }}"
                                            class="p-4 border-r border-[#E2E8F0] text-center w-36 min-w-[140px]"
                                            title="{{ $tp['description'] }}"
                                        >
                                            <span class="block font-black text-[#0F172A]">{{ $tp['code'] }}</span>
                                            <span class="block font-medium text-[9px] text-[#64748B] mt-0.5 truncate max-w-[130px]">
                                                {{ $tp['description'] }}
                                            </span>
                                        </th>
                                    @endforeach
                                    <th class="p-4 text-center w-28 bg-[#F1F5F9]/30 text-[#0F172A] font-extrabold">
                                        Nilai Akhir
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @forelse($rows as $idx => $row)
                                    <tr class="hover:bg-[#F8FAFC]/50 transition-colors">
                                        <td class="p-4 border-r border-[#E2E8F0] text-center font-bold text-[#64748B] bg-[#F8FAFC]/20">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="p-4 border-r border-[#E2E8F0] font-mono text-[#64748B]">
                                            {{ $row['nis'] }}
                                        </td>
                                        <td class="p-4 border-r border-[#E2E8F0] font-bold text-[#0F172A]">
                                            {{ $row['name'] }}
                                        </td>
                                        @foreach($row['scores'] as $s)
                                            <td key="{{ $s['objectiveId'] }}" class="p-2 border-r border-[#E2E8F0] text-center">
                                                <input
                                                    type="text"
                                                    value="{{ $s['score'] !== null ? $s['score'] : '' }}"
                                                    onblur="Livewire.find('{{ $_instance->getId() }}').handleScoreChange('{{ $row['studentId'] }}', '{{ $s['objectiveId'] }}', this.value)"
                                                    class="w-16 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl px-2.5 py-1.5 text-center text-xs font-bold text-[#0f172a] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#4F46E5] focus:border-[#4F46E5] transition-all"
                                                />
                                            </td>
                                        @endforeach
                                        <td class="p-4 text-center bg-[#F1F5F9]/20 font-black text-sm text-[#4F46E5]">
                                            {{ $row['average'] !== null ? $row['average'] : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colSpan="{{ 4 + count($objectives) }}" class="p-12 text-center text-[#94A3B8] italic">
                                            Tidak ada data murid di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Modal Dialog Konfigurasi TP -->
    @if ($showConfigModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-xl max-h-[85vh] overflow-hidden border border-[#E2E8F0] flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#0F172A] font-display">Konfigurasi Tujuan Pembelajaran (TP)</h3>
                    <button
                        wire:click="$set('showConfigModal', false)"
                        class="text-[#64748B] hover:text-[#0F172A] text-sm font-semibold"
                    >
                        Tutup
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto space-y-6">
                    <!-- Form Tambah TP -->
                    <form wire:submit="addTP" class="space-y-4 p-5 bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl">
                        <h4 class="text-xs font-bold text-[#0F172A]">Tambah TP Baru</h4>
                        @if ($modalError)
                            <div class="p-3 text-xs bg-rose-50 text-rose-600 rounded-xl border border-rose-100 font-medium">
                                {{ $modalError }}
                            </div>
                        @endif
                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-[#64748B]">KODE TP</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="e.g. TP-03"
                                    wire:model="tpCode"
                                    class="w-full bg-white border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5] font-mono"
                                />
                            </div>
                            <div class="col-span-2 space-y-1">
                                <label class="block text-[10px] font-bold text-[#64748B]">DESKRIPSI TUJUAN PEMBELAJARAN</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="e.g. Menerapkan operasi bilangan kompleks"
                                    wire:model="tpDesc"
                                    class="w-full bg-white border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#4F46E5]"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="w-full flex items-center justify-center gap-1.5 py-2 px-4 rounded-xl text-xs font-bold text-white bg-[#4F46E5] hover:bg-[#4338CA] transition-colors"
                        >
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah TP
                        </button>
                    </form>

                    <!-- Daftar TP Aktif -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-[#64748B]">Daftar TP Aktif</h4>
                        @if (empty($objectives))
                            <p class="text-xs text-[#94A3B8] italic text-center py-4 border border-dashed border-[#E2E8F0] rounded-xl">
                                Belum ada TP dikonfigurasi.
                            </p>
                        @else
                            <div class="space-y-2">
                                @foreach($objectives as $tp)
                                    <div
                                        key="{{ $tp['id'] }}"
                                        class="p-4 border border-[#E2E8F0] rounded-2xl flex items-start justify-between gap-4"
                                    >
                                        <div class="space-y-1">
                                            <span class="text-xs font-black text-[#4F46E5] font-mono">{{ $tp['code'] }}</span>
                                            <p class="text-xs text-[#0F172A] leading-relaxed">{{ $tp['description'] }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="deleteTP('{{ $tp['id'] }}')"
                                            class="p-2 text-rose-500 hover:bg-rose-50 hover:text-rose-700 rounded-xl transition-all shrink-0"
                                            title="Hapus TP"
                                        >
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
