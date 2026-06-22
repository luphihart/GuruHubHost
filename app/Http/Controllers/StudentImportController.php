<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Murid');
        
        // Header
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'NISN');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Jenis Kelamin (L/P)');
        $sheet->setCellValue('E1', 'Nama Kelas');
        $sheet->setCellValue('F1', 'Nama Orang Tua / Wali');
        $sheet->setCellValue('G1', 'No HP Orang Tua / Wali');
        
        // Style Header
        $headerStyle = $sheet->getStyle('A1:G1');
        $headerStyle->getFont()->setBold(true);
        
        // Auto-fit columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Example row
        $sheet->setCellValue('A2', '10005');
        $sheet->setCellValue('B2', '0012345682');
        $sheet->setCellValue('C2', 'Siswa Contoh');
        $sheet->setCellValue('D2', 'L');
        $sheet->setCellValue('E2', 'X MIPA 1');
        $sheet->setCellValue('F2', 'Orang Tua Contoh');
        $sheet->setCellValue('G2', '08123456789');
        
        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="template_import_murid.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }
}
