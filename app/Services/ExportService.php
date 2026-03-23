<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export data ke Excel (XLSX).
     *
     * @param string $title Judul laporan (untuk row 1)
     * @param array $headers List header kolom (misal: ['Tanggal', 'Keterangan', 'Nominal'])
     * @param array $data List data array (setiap elemen adalah array nilai per kolom)
     * @param string $filename Nama file (tanpa .xlsx)
     * @return StreamedResponse
     */
    public function toExcel(string $title, array $headers, array $data, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul Laporan
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $this->getColumnLetter(count($headers)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Kolom
        $row = 3;
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        // Style Header
        $headerRange = 'A3:' . $this->getColumnLetter(count($headers)) . '3';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E5E7EB']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data Rows
        $row = 4;
        foreach ($data as $rowData) {
            $col = 1;
            foreach ($rowData as $cellData) {
                $sheet->setCellValueByColumnAndRow($col, $row, $cellData);
                $col++;
            }
            $row++;
        }

        // Style Data Rows
        if (count($data) > 0) {
            $dataRange = 'A4:' . $this->getColumnLetter(count($headers)) . ($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
        }

        // Auto-size columns
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export view ke PDF.
     *
     * @param string $viewPath Path blade view
     * @param array $data Data untuk view
     * @param string $filename Nama file (tanpa .pdf)
     * @param string $paperSize Ukuran kertas (A4, dll)
     * @param string $orientation Orientasi (portrait, landscape)
     * @return \Illuminate\Http\Response
     */
    public function toPdf(string $viewPath, array $data, string $filename, string $paperSize = 'A4', string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($viewPath, $data)
            ->setPaper($paperSize, $orientation);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Helper untuk mendapatkan huruf kolom Excel (A, B, C, ..., Z, AA, AB, dst)
     */
    private function getColumnLetter(int $colNumber): string
    {
        $letter = '';
        while ($colNumber > 0) {
            $temp = ($colNumber - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $colNumber = ($colNumber - $temp - 1) / 26;
        }
        return $letter;
    }
}
