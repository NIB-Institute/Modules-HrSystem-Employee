<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Employee\Exports\EmployeesExport;
use Modules\Employee\Exports\FailedRowsExport;
use Modules\Employee\Imports\EmployeesImport;
use Modules\Employee\Http\Requests\Dashboard\V1\ImportEmployeeRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeImportExportController extends Controller
{
    /**
     * Export employees to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status', 'employee_type', 'school_id', 'department_id']);

        $filename = 'employees_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new EmployeesExport($filters), $filename);
    }

    /**
     * Show import form.
     */
    public function showImport(): Response
    {
        return Inertia::render('employee::Dashboard/V1/Employee/Import', [
            'duplicateOptions' => [
                ['value' => 'skip', 'label' => 'Skip duplicates', 'description' => 'Skip rows with existing emails'],
                ['value' => 'update', 'label' => 'Update existing', 'description' => 'Update existing employees with new data'],
                ['value' => 'fail', 'label' => 'Fail on duplicate', 'description' => 'Stop import if duplicates found'],
            ],
        ]);
    }

    /**
     * Preview import data.
     */
    public function preview(ImportEmployeeRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $duplicateHandling = $request->input('duplicate_handling', EmployeesImport::DUPLICATE_SKIP);

            $import = new EmployeesImport($duplicateHandling, true);
            Excel::import($import, $file);

            $previewData = $import->getPreviewData();
            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'preview' => $previewData,
                'stats' => $results['preview_stats'],
                'total_rows' => count($previewData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Import employees from Excel.
     */
    public function import(ImportEmployeeRequest $request): RedirectResponse
    {
        try {
            $file = $request->file('file');
            $duplicateHandling = $request->input('duplicate_handling', EmployeesImport::DUPLICATE_SKIP);

            $import = new EmployeesImport($duplicateHandling, false);
            Excel::import($import, $file);

            $results = $import->getResults();

            // Store failed rows in session for download
            if (!empty($results['failed_rows'])) {
                session()->flash('import_failed_rows', $results['failed_rows']);
            }

            // Build success message
            $messages = [];
            if ($results['imported'] > 0) {
                $messages[] = "{$results['imported']} imported";
            }
            if ($results['updated'] > 0) {
                $messages[] = "{$results['updated']} updated";
            }
            if ($results['skipped'] > 0) {
                $messages[] = "{$results['skipped']} skipped";
            }
            if ($results['failed'] > 0) {
                $messages[] = "{$results['failed']} failed";
            }

            $message = 'Import completed: ' . implode(', ', $messages);

            if ($results['failed'] > 0) {
                return redirect()
                    ->route('employee.employees.index')
                    ->with('warning', $message)
                    ->with('show_failed_download', true);
            }

            return redirect()
                ->route('employee.employees.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('employee.employees.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download failed rows as Excel.
     */
    public function downloadFailedRows(Request $request): BinaryFileResponse|RedirectResponse
    {
        $failedRows = session('import_failed_rows', []);

        if (empty($failedRows)) {
            return redirect()
                ->route('employee.employees.index')
                ->with('error', 'No failed rows to download');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Failed Rows');

        // Headers
        $headers = ['Row #', 'First Name', 'Last Name', 'Email', 'Phone', 'Error'];
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        // Style headers
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
        ]);

        // Data
        $row = 2;
        foreach ($failedRows as $failedRow) {
            $data = $failedRow['data'];
            $sheet->setCellValue('A' . $row, $failedRow['row_number']);
            $sheet->setCellValue('B' . $row, $data['first_name'] ?? '');
            $sheet->setCellValue('C' . $row, $data['last_name'] ?? '');
            $sheet->setCellValue('D' . $row, $data['email'] ?? '');
            $sheet->setCellValue('E' . $row, $data['phone_number'] ?? '');
            $sheet->setCellValue('F' . $row, implode('; ', $failedRow['errors']));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'failed_rows_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'failed_import_rows_' . now()->format('Y-m-d_His') . '.xlsx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Download import template.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Gender',
            'Date of Birth',
            'Birth Place',
            'Current Address',
            'School',
            'Department',
            'Employee Type Name',
            'Job Title',
            'Employment Type',
            'Salary',
            'Hire Date',
            'Probation Date',
            'Probation End Date',
            'Status',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Employees Import Template');

        // Add headers
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        // Style header row
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add instructions on second sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        $instructionsSheet->setCellValue('A1', 'Import Instructions');
        $instructionsSheet->setCellValue('A3', '1. Fill in employee data in the "Employees Import Template" sheet');
        $instructionsSheet->setCellValue('A4', '2. First Name and Last Name are required fields');
        $instructionsSheet->setCellValue('A5', '3. Email must be unique if provided');
        $instructionsSheet->setCellValue('A6', '4. Gender: male, female, or other');
        $instructionsSheet->setCellValue('A7', '5. Employment Type: full_time, part_time, contract, or intern');
        $instructionsSheet->setCellValue('A8', '6. Status: active or inactive (defaults to active)');
        $instructionsSheet->setCellValue('A9', '7. Date format: YYYY-MM-DD (e.g., 2024-01-15)');
        $instructionsSheet->setCellValue('A10', '8. School name should match an existing school');
        $instructionsSheet->setCellValue('A12', 'Duplicate Handling Options:');
        $instructionsSheet->setCellValue('A13', '- Skip: Skip rows with existing emails');
        $instructionsSheet->setCellValue('A14', '- Update: Update existing employees with new data');
        $instructionsSheet->setCellValue('A15', '- Fail: Stop import if duplicates found');
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructionsSheet->getStyle('A12')->getFont()->setBold(true);

        // Add sample row
        $spreadsheet->setActiveSheetIndex(0);
        $sampleData = [
            'John',
            'Doe',
            'john.doe@example.com',
            '+1234567890',
            'male',
            '1990-01-15',
            'New York',
            '123 Main St, City',
            '', // School - leave empty to use default
            '', // Department
            '', // Employee Type Name
            'Software Engineer',
            'full_time',
            '50000',
            '2024-01-01',
            '2024-01-01',
            '2024-04-01',
            'active',
        ];

        foreach ($sampleData as $index => $value) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '2', $value);
        }

        // Style sample row as italic
        $sheet->getStyle('A2:' . $lastColumn . '2')->getFont()->setItalic(true);

        // Create temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'employee_template_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'employee_import_template.xlsx')->deleteFileAfterSend(true);
    }
}
