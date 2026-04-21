<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Http\Response;

class ExportService
{
    protected $collegeName;
    protected $collegeAddress;
    protected $collegeLogo;

    public function __construct()
    {
        $this->collegeName = config('app.name', 'Technical College');
        
        // Get college address from site settings
        $addressSetting = \App\Models\SiteSetting::where('key', 'contact_address')->first();
        $this->collegeAddress = $addressSetting?->value ?? 'Nepal';
        
        // Get college logo from site settings
        $logoSetting = \App\Models\SiteSetting::where('key', 'site_logo')->first();
        $this->collegeLogo = $logoSetting?->value ? asset('storage/' . $logoSetting->value) : null;
    }

    /**
     * Export data in the specified format
     */
    public function export(array $config)
    {
        $format = $config['format'] ?? 'csv';
        
        switch ($format) {
            case 'pdf':
                return $this->exportPDF($config);
            case 'excel':
                return $this->exportExcel($config);
            default:
                return $this->exportCSV($config);
        }
    }

    /**
     * Export as CSV
     */
    protected function exportCSV(array $config)
    {
        $filename = $this->generateFilename($config['title'], 'csv');
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($config) {
            $file = fopen('php://output', 'w');
            
            // Add header information
            $this->addCSVHeaders($file, $config);
            
            // Add column headers
            fputcsv($file, $config['columns']);
            
            // Add data rows
            foreach ($config['data'] as $row) {
                fputcsv($file, $this->formatRowData($row, $config['columns']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export as Excel (proper Excel format with formatting)
     */
    protected function exportExcel(array $config)
    {
        $filename = $this->generateFilename($config['title'], 'xls');
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($config) {
            // Create Excel-compatible XML content
            echo $this->generateExcelXML($config);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate Excel XML content
     */
    protected function generateExcelXML(array $config): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        // Document Properties
        $xml .= '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . "\n";
        $xml .= '<Title>' . htmlspecialchars($config['title']) . '</Title>' . "\n";
        $xml .= '<Author>' . htmlspecialchars($this->collegeName) . '</Author>' . "\n";
        $xml .= '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
        $xml .= '</DocumentProperties>' . "\n";

        // Styles
        $xml .= '<Styles>' . "\n";
        
        // Default style
        $xml .= '<Style ss:ID="Default" ss:Name="Normal">' . "\n";
        $xml .= '<Alignment ss:Vertical="Bottom"/>' . "\n";
        $xml .= '<Borders/>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>' . "\n";
        $xml .= '<Interior/>' . "\n";
        $xml .= '<NumberFormat/>' . "\n";
        $xml .= '<Protection/>' . "\n";
        $xml .= '</Style>' . "\n";

        // Header style
        $xml .= '<Style ss:ID="HeaderStyle">' . "\n";
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" ss:Size="12" ss:Color="#000000" ss:Bold="1"/>' . "\n";
        $xml .= '<Interior ss:Color="#E6E6FA" ss:Pattern="Solid"/>' . "\n";
        $xml .= '</Style>' . "\n";

        // Title style
        $xml .= '<Style ss:ID="TitleStyle">' . "\n";
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" ss:Size="16" ss:Color="#000080" ss:Bold="1"/>' . "\n";
        $xml .= '</Style>' . "\n";

        // Subtitle style
        $xml .= '<Style ss:ID="SubtitleStyle">' . "\n";
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" ss:Size="12" ss:Color="#666666" ss:Bold="1"/>' . "\n";
        $xml .= '</Style>' . "\n";

        // Data style
        $xml .= '<Style ss:ID="DataStyle">' . "\n";
        $xml .= '<Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" ss:Size="10"/>' . "\n";
        $xml .= '</Style>' . "\n";

        // Number style
        $xml .= '<Style ss:ID="NumberStyle">' . "\n";
        $xml .= '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Font ss:FontName="Calibri" ss:Size="10"/>' . "\n";
        $xml .= '<NumberFormat ss:Format="0.00"/>' . "\n";
        $xml .= '</Style>' . "\n";

        $xml .= '</Styles>' . "\n";

        // Worksheet
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($config['title']) . '">' . "\n";
        
        // Column definitions for proper width
        $xml .= '<Table>' . "\n";
        $columnCount = count($config['columns']) + 1; // +1 for S.N. column
        
        // Define column widths
        $xml .= '<Column ss:Width="40"/>' . "\n"; // S.N. column
        foreach ($config['columns'] as $key => $label) {
            $width = $this->getColumnWidth($key, $label);
            $xml .= '<Column ss:Width="' . $width . '"/>' . "\n";
        }

        $currentRow = 1;

        // College header
        $xml .= '<Row ss:Height="25">' . "\n";
        $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="TitleStyle">' . "\n";
        $xml .= '<Data ss:Type="String">' . htmlspecialchars($this->collegeName) . '</Data>' . "\n";
        $xml .= '</Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        $currentRow++;

        // College address
        $xml .= '<Row ss:Height="20">' . "\n";
        $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="SubtitleStyle">' . "\n";
        $xml .= '<Data ss:Type="String">' . htmlspecialchars($this->collegeAddress) . '</Data>' . "\n";
        $xml .= '</Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        $currentRow++;

        // Department
        if (isset($config['department'])) {
            $xml .= '<Row ss:Height="18">' . "\n";
            $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '">' . "\n";
            $xml .= '<Data ss:Type="String">Department: ' . htmlspecialchars($config['department']) . '</Data>' . "\n";
            $xml .= '</Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $currentRow++;
        }

        // Report title
        $xml .= '<Row ss:Height="20">' . "\n";
        $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="TitleStyle">' . "\n";
        $xml .= '<Data ss:Type="String">' . htmlspecialchars($config['title']) . '</Data>' . "\n";
        $xml .= '</Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        $currentRow++;

        // Subtitle
        if (isset($config['subtitle'])) {
            $xml .= '<Row ss:Height="18">' . "\n";
            $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="SubtitleStyle">' . "\n";
            $xml .= '<Data ss:Type="String">' . htmlspecialchars($config['subtitle']) . '</Data>' . "\n";
            $xml .= '</Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $currentRow++;
        }

        // Metadata
        if (isset($config['metadata']) && count($config['metadata']) > 0) {
            foreach ($config['metadata'] as $key => $value) {
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '">' . "\n";
                $xml .= '<Data ss:Type="String">' . htmlspecialchars($key . ': ' . $value) . '</Data>' . "\n";
                $xml .= '</Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $currentRow++;
            }
        }

        // Export date
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:MergeAcross="' . ($columnCount - 1) . '">' . "\n";
        $xml .= '<Data ss:Type="String">Export Date: ' . date('Y-m-d H:i:s') . '</Data>' . "\n";
        $xml .= '</Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        $currentRow++;

        // Empty row
        $xml .= '<Row/>' . "\n";
        $currentRow++;

        // Column headers
        $xml .= '<Row ss:Height="25">' . "\n";
        $xml .= '<Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">S.N.</Data></Cell>' . "\n";
        foreach ($config['columns'] as $key => $label) {
            $xml .= '<Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">' . htmlspecialchars($label) . '</Data></Cell>' . "\n";
        }
        $xml .= '</Row>' . "\n";
        $currentRow++;

        // Data rows
        foreach ($config['data'] as $index => $row) {
            $xml .= '<Row>' . "\n";
            
            // S.N. column
            $xml .= '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";
            
            foreach ($config['columns'] as $key => $label) {
                $value = $this->getNestedProperty($row, $key);
                $formattedValue = $this->formatValue($value);
                $dataType = $this->getExcelDataType($key, $value);
                $styleId = $this->getExcelStyleId($key, $value);
                
                $xml .= '<Cell ss:StyleID="' . $styleId . '">' . "\n";
                $xml .= '<Data ss:Type="' . $dataType . '">' . htmlspecialchars($formattedValue) . '</Data>' . "\n";
                $xml .= '</Cell>' . "\n";
            }
            
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>' . "\n";

        return $xml;
    }

    /**
     * Get column width based on content type
     */
    protected function getColumnWidth(string $key, string $label): int
    {
        $widthMap = [
            'student.user.name' => 150,
            'name' => 150,
            'student.roll_number' => 80,
            'student.program.name' => 120,
            'program.name' => 120,
            'subject.name' => 120,
            'subject.code' => 80,
            'roll_number' => 80,
            'email' => 150,
            'phone' => 100,
            'assessment_attendance_percent' => 80,
            'assessment_obtained_marks' => 80,
            'assessment_full_marks' => 70,
            'assessment_pass_marks' => 70,
            'internal_theory_marks' => 80,
            'external_theory_marks' => 80,
            'internal_practical_marks' => 80,
            'external_practical_marks' => 80,
            'ctevt_full_marks_internal_theory' => 60,
            'ctevt_pass_marks_internal_theory' => 60,
            'ctevt_full_marks_external_theory' => 60,
            'ctevt_pass_marks_external_theory' => 60,
            'ctevt_full_marks_internal_practical' => 60,
            'ctevt_pass_marks_internal_practical' => 60,
            'ctevt_full_marks_external_practical' => 60,
            'ctevt_pass_marks_external_practical' => 60,
            'total_marks' => 80,
            'result_remark' => 80,
            'status' => 80,
            'remarks' => 200,
            'current_semester' => 80,
            'semester' => 60,
            'section' => 60,
        ];

        return $widthMap[$key] ?? 100;
    }

    /**
     * Get Excel data type
     */
    protected function getExcelDataType(string $key, $value): string
    {
        if (in_array($key, [
            'assessment_obtained_marks', 'assessment_full_marks', 'assessment_pass_marks',
            'internal_theory_marks', 'external_theory_marks',
            'internal_practical_marks', 'external_practical_marks', 'total_marks',
            'ctevt_full_marks_internal_theory', 'ctevt_pass_marks_internal_theory',
            'ctevt_full_marks_external_theory', 'ctevt_pass_marks_external_theory',
            'ctevt_full_marks_internal_practical', 'ctevt_pass_marks_internal_practical',
            'ctevt_full_marks_external_practical', 'ctevt_pass_marks_external_practical',
            'assessment_attendance_percent', 'current_semester', 'semester'
        ])) {
            return 'Number';
        }

        return 'String';
    }

    /**
     * Get Excel style ID
     */
    protected function getExcelStyleId(string $key, $value): string
    {
        if (in_array($key, [
            'assessment_obtained_marks', 'internal_theory_marks', 'external_theory_marks',
            'internal_practical_marks', 'external_practical_marks', 'total_marks',
            'assessment_attendance_percent'
        ])) {
            return 'NumberStyle';
        }

        return 'DataStyle';
    }

    /**
     * Export as PDF
     */
    protected function exportPDF(array $config)
    {
        $filename = $this->generateFilename($config['title'], 'pdf');
        
        $html = view('components.export-pdf-template', [
            'config' => $config,
            'collegeName' => $this->collegeName,
            'collegeAddress' => $this->collegeAddress,
            'collegeLogo' => $this->collegeLogo,
        ])->render();

        // Use DomPDF for proper PDF generation
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'landscape'); // Use landscape for better table display
        
        return $pdf->download($filename);
    }

    /**
     * Add CSV header information
     */
    protected function addCSVHeaders($file, array $config): void
    {
        fputcsv($file, [$this->collegeName]);
        fputcsv($file, [$this->collegeAddress]);
        
        if (isset($config['department'])) {
            fputcsv($file, ['Department: ' . $config['department']]);
        }
        
        fputcsv($file, ['Report: ' . $config['title']]);
        
        if (isset($config['subtitle'])) {
            fputcsv($file, [$config['subtitle']]);
        }
        
        if (isset($config['metadata'])) {
            foreach ($config['metadata'] as $key => $value) {
                fputcsv($file, [$key . ': ' . $value]);
            }
        }
        
        fputcsv($file, ['Export Date: ' . date('Y-m-d H:i:s')]);
        fputcsv($file, ['']); // Empty row
    }

    /**
     * Format row data according to columns
     */
    protected function formatRowData($row, array $columns): array
    {
        $formattedRow = [];
        
        foreach ($columns as $column) {
            $value = '';
            
            if (is_array($row)) {
                $value = $row[$column] ?? '';
            } elseif (is_object($row)) {
                // Handle nested properties like 'student.user.name'
                $value = $this->getNestedProperty($row, $column);
            }
            
            // Format specific data types
            $value = $this->formatValue($value);
            
            $formattedRow[] = $value;
        }
        
        return $formattedRow;
    }

    /**
     * Get nested property from object
     */
    protected function getNestedProperty($object, string $property)
    {
        $keys = explode('.', $property);
        $value = $object;
        
        foreach ($keys as $key) {
            if (is_object($value) && isset($value->$key)) {
                $value = $value->$key;
            } elseif (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return '';
            }
        }
        
        return $value;
    }

    /**
     * Format individual values
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if (is_numeric($value) && !is_string($value)) {
            return number_format($value, 2);
        }
        
        return (string) $value;
    }

    /**
     * Generate filename
     */
    protected function generateFilename(string $title, string $extension): string
    {
        $cleanTitle = preg_replace('/[^A-Za-z0-9\-_]/', '_', $title);
        return strtolower($cleanTitle) . '_' . date('Y-m-d') . '.' . $extension;
    }

    /**
     * Create export configuration for marks
     */
    public static function createMarksExportConfig($exam, $marks, $department = null): array
    {
        // Get semester information from exam programs
        $semesters = $exam->programs->pluck('pivot.semester')->filter()->unique()->sort()->values();
        $semesterText = $semesters->count() > 0 
            ? ($semesters->count() === 1 ? 'Semester ' . $semesters->first() : 'Semesters ' . $semesters->implode(', '))
            : 'All Semesters';
        
        // Get program names
        $programNames = $exam->programs->pluck('name')->unique()->implode(', ');
        
        $config = [
            'title' => $exam->name . ' - Marks Report',
            'subtitle' => $exam->category_label . ' • ' . $semesterText . ' • ' . bsDate($exam->start_date, 'F d, Y'),
            'department' => $department ? $department->name : ($exam->department->name ?? 'N/A'),
            'metadata' => [
                'Exam Name' => $exam->name,
                'Academic Session' => $exam->academicSession->name ?? 'N/A',
                'Department' => $department ? $department->name : ($exam->department->name ?? 'N/A'),
                'Exam Type' => $exam->type ? ucfirst($exam->type) : 'N/A',
                'Exam Category' => $exam->category_label,
                'Programs' => $programNames ?: 'N/A',
                'Semester(s)' => $semesterText,
                'Start Date' => bsDate($exam->start_date, 'Y-m-d'),
                'End Date' => bsDate($exam->end_date, 'Y-m-d'),
                'Total Students' => $marks->count(),
                'Status' => $exam->status_label ?? 'N/A',
            ],
            'data' => $marks,
        ];

        if ($exam->category === 'monthly_assessment') {
            $config['columns'] = [
                'student.user.name' => 'Student Name',
                'student.roll_number' => 'Roll No',
                'student.program.name' => 'Program',
                'semester' => 'Semester',
                'subject.name' => 'Subject',
                'subject.code' => 'Subject Code',
                'assessment_attendance_percent' => 'Attendance %',
                'assessment_obtained_marks' => 'Obtained Marks',
                'assessment_full_marks' => 'Full Marks',
                'assessment_pass_marks' => 'Pass Marks',
                'was_present_on_exam_date' => 'Exam Attendance',
                'result_remark' => 'Result',
                'status' => 'Status',
                'remarks' => 'Remarks',
            ];
            
            $config['metadata']['Assessment Full Marks'] = $exam->assessment_full_marks ?? 100;
            $config['metadata']['Assessment Pass Marks'] = $exam->assessment_pass_marks ?? 40;
        } else {
            $config['columns'] = [
                'student.user.name' => 'Student Name',
                'student.roll_number' => 'Roll No',
                'student.program.name' => 'Program',
                'semester' => 'Semester',
                'subject.name' => 'Subject',
                'subject.code' => 'Subject Code',
                'internal_theory_marks' => 'Internal Theory',
                'ctevt_full_marks_internal_theory' => 'IT Full',
                'ctevt_pass_marks_internal_theory' => 'IT Pass',
                'external_theory_marks' => 'External Theory',
                'ctevt_full_marks_external_theory' => 'ET Full',
                'ctevt_pass_marks_external_theory' => 'ET Pass',
                'internal_practical_marks' => 'Internal Practical',
                'ctevt_full_marks_internal_practical' => 'IP Full',
                'ctevt_pass_marks_internal_practical' => 'IP Pass',
                'external_practical_marks' => 'External Practical',
                'ctevt_full_marks_external_practical' => 'EP Full',
                'ctevt_pass_marks_external_practical' => 'EP Pass',
                'total_marks' => 'Total Marks',
                'result_remark' => 'Result',
                'status' => 'Status',
                'remarks' => 'Remarks',
            ];
        }

        return $config;
    }

    /**
     * Create export configuration for students
     */
    public static function createStudentsExportConfig($students, $department = null): array
    {
        return [
            'title' => 'Students Report',
            'subtitle' => 'Department Students List',
            'department' => $department ? $department->name : 'All Departments',
            'metadata' => [
                'Total Students' => $students->count(),
                'Export Date' => date('Y-m-d H:i:s'),
            ],
            'columns' => [
                'user.name' => 'Student Name',
                'roll_number' => 'Roll Number',
                'program.name' => 'Program',
                'current_semester' => 'Semester',
                'section' => 'Section',
                'status' => 'Status',
                'user.email' => 'Email',
                'phone' => 'Phone',
            ],
            'data' => $students,
        ];
    }

    /**
     * Create export configuration for attendance
     */
    public static function createAttendanceExportConfig($attendanceData, $session = null, $department = null): array
    {
        return [
            'title' => 'Attendance Report',
            'subtitle' => $session ? 'Session: ' . $session->date : 'Attendance Summary',
            'department' => $department ? $department->name : 'All Departments',
            'metadata' => [
                'Total Records' => $attendanceData->count(),
                'Session Date' => $session ? bsDate($session->date, 'F d, Y') : 'Multiple Sessions',
            ],
            'columns' => [
                'student.user.name' => 'Student Name',
                'student.roll_number' => 'Roll Number',
                'student.program.name' => 'Program',
                'student.current_semester' => 'Semester',
                'is_present' => 'Present',
                'remarks' => 'Remarks',
            ],
            'data' => $attendanceData,
        ];
    }
}