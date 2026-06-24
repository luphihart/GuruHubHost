<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Pembinaan Siswa</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .text-center {
            text-align: center;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .font-bold {
            font-weight: bold;
        }
        /* Kop Surat */
        .kop-container {
            text-align: center;
            margin-bottom: 10px;
        }
        .kop-yayasan {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }
        .kop-sekolah {
            font-size: 15px;
            font-weight: bold;
            margin: 2px 0;
        }
        .kop-alamat {
            font-size: 8px;
            margin: 0;
            color: #475569;
        }
        .kop-divider {
            border-bottom: 2px solid #000000;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        /* Judul */
        .judul {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        /* Metadata Table */
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .meta-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
        }
        /* Notes Table */
        .notes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .notes-table th, .notes-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .notes-table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }
        .badge-academic { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .badge-attendance { background-color: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
        .badge-discipline { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .badge-achievement { background-color: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
        .badge-counseling { background-color: #fffbeb; color: #b45309; border-color: #fef3c7; }
        .badge-other { background-color: #f8fafc; color: #334155; border-color: #e2e8f0; }

        /* Signature block */
        .signature-container {
            margin-top: 40px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Sekolah -->
    <div class="kop-container">
        <p class="kop-yayasan">{{ strtoupper($schoolProfile->yayasan_name) }}</p>
        <p class="kop-sekolah">{{ strtoupper($schoolProfile->school_name) }}</p>
        <p class="kop-alamat">
            {{ $schoolProfile->address }}
            @if($schoolProfile->phone) | Telp: {{ $schoolProfile->phone }} @endif
            @if($schoolProfile->email) | Email: {{ $schoolProfile->email }} @endif
            @if($schoolProfile->website) | Web: {{ $schoolProfile->website }} @endif
        </p>
    </div>
    <div class="kop-divider"></div>

    <!-- Judul Dokumen -->
    <div class="judul">
        KARTU LAPORAN PEMBINAAN SISWA<br/>
        <span class="uppercase">SEMESTER {{ $mentorStudent->student->class->semester->name }} - TAHUN AJARAN {{ $mentorStudent->student->class->schoolYear->name }}</span>
    </div>

    <!-- Metadata Layout -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <div class="meta-title">DATA SISWA BINAAN</div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%;">Nama Siswa</td>
                        <td style="width: 5%;">:</td>
                        <td style="font-weight: bold;">{{ $mentorStudent->student->name }}</td>
                    </tr>
                    <tr>
                        <td>NIS / NISN</td>
                        <td>:</td>
                        <td>{{ $mentorStudent->student->nis }} / {{ $mentorStudent->student->nisn }}</td>
                    </tr>
                    <tr>
                        <td>Kelas</td>
                        <td>:</td>
                        <td>{{ $mentorStudent->student->class->name }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 20px;">
                <div class="meta-title">GURU WALI / PEMBINA</div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%;">Nama Guru</td>
                        <td style="width: 5%;">:</td>
                        <td style="font-weight: bold;">{{ $mentorStudent->teacher->name }}</td>
                    </tr>
                    <tr>
                        <td>NIP</td>
                        <td>:</td>
                        <td>{{ $mentorStudent->teacher->nip }}</td>
                    </tr>
                    <tr>
                        <td>No. Telp</td>
                        <td>:</td>
                        <td>{{ $mentorStudent->teacher->phone ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabel Riwayat Catatan Pembinaan -->
    <div class="meta-title">RIWAYAT CATATAN BIMBINGAN & PEMBINAAN</div>
    <table class="notes-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 65%;">Catatan Kasus & Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notes as $index => $note)
                @php
                    $badgeClass = match($note->category) {
                        'ACADEMIC' => 'badge-academic',
                        'ATTENDANCE' => 'badge-attendance',
                        'DISCIPLINE' => 'badge-discipline',
                        'ACHIEVEMENT' => 'badge-achievement',
                        'COUNSELING' => 'badge-counseling',
                        default => 'badge-other'
                    };
                    $categoryText = match($note->category) {
                        'ACADEMIC' => 'Akademik',
                        'ATTENDANCE' => 'Kehadiran',
                        'DISCIPLINE' => 'Disiplin',
                        'ACHIEVEMENT' => 'Prestasi',
                        'COUNSELING' => 'Konseling',
                        default => 'Lainnya'
                    };
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $note->date->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $categoryText }}</span>
                    </td>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 4px;">{{ $note->content }}</div>
                        @if($note->action_taken)
                            <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e2e8f0; font-size: 10px; color: #475569;">
                                <span style="font-weight: bold; font-size: 8px;">TINDAK LANJUT:</span><br/>
                                {{ $note->action_taken }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #64748b; font-style: italic;">
                        Belum ada catatan bimbingan dan pembinaan untuk murid ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br/>
                    Kepala Sekolah
                    <div class="signature-space"></div>
                    <span style="font-weight: bold; text-decoration: underline;">{{ $schoolProfile->headmaster }}</span><br/>
                    NIP. {{ $schoolProfile->headmaster_nip }}
                </td>
                <td>
                    {{ $schoolProfile->city ?? 'Bandung' }}, {{ now()->format('d M Y') }}<br/>
                    Guru Wali / Pembina
                    <div class="signature-space"></div>
                    <span style="font-weight: bold; text-decoration: underline;">{{ $mentorStudent->teacher->name }}</span><br/>
                    NIP. {{ $mentorStudent->teacher->nip }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
