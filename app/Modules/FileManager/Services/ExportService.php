<?php

namespace App\Modules\FileManager\Services;


use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\CMS\Models\Download;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ExportService
{
    protected $collegeName;
    protected $collegeAddress;
    protected $collegeLogo;
    protected $collegePhone;
    protected $collegeEmail;
    protected $collegeWebsite;
    protected $collegeEstd;
    protected $collegeAffiliation;

    public function __construct()
    {
        $settings = \App\Models\SiteSetting::whereIn('key', [
            'college_name', 'college_affiliation', 'site_logo',
            'contact_address', 'contact_phone', 'contact_email',
            'principal_name',
        ])->pluck('value', 'key');

        $this->collegeName        = $settings->get('college_name') ?: config('app.name', 'Technical College');
        $this->collegeAddress     = $settings->get('contact_address', '');
        $this->collegePhone       = $settings->get('contact_phone', '');
        $this->collegeEmail       = $settings->get('contact_email', '');
        $this->collegeWebsite     = '';
        $this->collegeEstd        = '';
        $this->collegeAffiliation = $settings->get('college_affiliation', 'CTEVT');

        $logoPath = $settings->get('site_logo');
        $this->collegeLogo = $logoPath ? public_path('storage/' . $logoPath) : null;
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
        $xml .= '<Company>' . htmlspecialchars($this->collegeName) . '</Company>' . "\n";
        $xml .= '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
        $xml .= '</DocumentProperties>' . "\n";

        // Styles
        $xml .= '<Styles>' . "\n";

        // Default
        $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="10" ss:Color="#000000"/></Style>' . "\n";

        // College name row (large bold, black on white)
        $xml .= '<Style ss:ID="CollegeNameStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/></Style>' . "\n";

        // Address row
        $xml .= '<Style ss:ID="AddressStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#333333"/><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/></Style>' . "\n";

        // Affiliation row
        $xml .= '<Style ss:ID="AffiliationStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="8" ss:Italic="1" ss:Color="#333333"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/></Style>' . "\n";

        // Report title (bold, black on light grey)
        $xml .= '<Style ss:ID="TitleStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="13" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#E0E0E0" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/></Borders></Style>' . "\n";

        // Subtitle
        $xml .= '<Style ss:ID="SubtitleStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#555555"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/></Style>' . "\n";

        // Metadata label
        $xml .= '<Style ss:ID="MetaLabel"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#EEEEEE" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/></Borders></Style>' . "\n";

        // Metadata value
        $xml .= '<Style ss:ID="MetaValue"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#000000"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AAAAAA"/></Borders></Style>' . "\n";

        // Column header (bold black on mid grey)
        $xml .= '<Style ss:ID="HeaderStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#666666"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#666666"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/></Borders></Style>' . "\n";

        // Data — odd rows (white)
        $xml .= '<Style ss:ID="DataOdd"><Alignment ss:Vertical="Center" ss:WrapText="0"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#000000"/><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders></Style>' . "\n";

        // Data — even rows (very light grey)
        $xml .= '<Style ss:ID="DataEven"><Alignment ss:Vertical="Center" ss:WrapText="0"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#000000"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders></Style>' . "\n";

        // Number odd
        $xml .= '<Style ss:ID="NumOdd"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#000000"/><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders><NumberFormat ss:Format="0.0"/></Style>' . "\n";

        // Number even
        $xml .= '<Style ss:ID="NumEven"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#000000"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders><NumberFormat ss:Format="0.0"/></Style>' . "\n";

        // Pass / Fail / Absent — plain bold black (no color backgrounds)
        $xml .= '<Style ss:ID="PassStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders></Style>' . "\n";

        $xml .= '<Style ss:ID="FailStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders></Style>' . "\n";

        $xml .= '<Style ss:ID="AbsentStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#555555"/><Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBBBBB"/></Borders></Style>' . "\n";

        // Summary footer
        $xml .= '<Style ss:ID="SummaryStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#000000"/><Interior ss:Color="#DDDDDD" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#666666"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#666666"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#000000"/></Borders></Style>' . "\n";

        // Footer note
        $xml .= '<Style ss:ID="FooterStyle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="8" ss:Italic="1" ss:Color="#6B7280"/><Interior ss:Color="#F9FAFB" ss:Pattern="Solid"/></Style>' . "\n";

        $xml .= '</Styles>' . "\n";

        // Worksheet
        $sheetName = mb_substr(preg_replace('/[\\\\\/\*\?\[\]:]+/', '-', $config['title']), 0, 31);
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">' . "\n";
        $xml .= '<Table>' . "\n";

        $columnCount = count($config['columns']) + 1; // +1 for S.N.

        // Column widths
        $xml .= '<Column ss:Width="30"/>' . "\n"; // S.N.
        foreach ($config['columns'] as $key => $label) {
            $xml .= '<Column ss:Width="' . $this->getColumnWidth($key, $label) . '"/>' . "\n";
        }

        // ── ROW 1: College Name ──
        $xml .= '<Row ss:Height="28"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="CollegeNameStyle"><Data ss:Type="String">' . htmlspecialchars($this->collegeName) . '</Data></Cell></Row>' . "\n";

        // ── ROW 2: Address + Phone ──
        $addrLine = implode('  |  ', array_filter([$this->collegeAddress, $this->collegePhone, $this->collegeEmail]));
        $xml .= '<Row ss:Height="18"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="AddressStyle"><Data ss:Type="String">' . htmlspecialchars($addrLine ?: ' ') . '</Data></Cell></Row>' . "\n";

        // ── ROW 3: Affiliation / Website ──
        $affLine = implode('  |  ', array_filter([$this->collegeAffiliation ? 'Affiliated to: ' . $this->collegeAffiliation : '', $this->collegeWebsite, $this->collegeEstd ? 'Est. ' . $this->collegeEstd : '']));
        if ($affLine) {
            $xml .= '<Row ss:Height="15"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="AffiliationStyle"><Data ss:Type="String">' . htmlspecialchars($affLine) . '</Data></Cell></Row>' . "\n";
        }

        // ── ROW 4: Blank separator ──
        $xml .= '<Row ss:Height="5"><Cell ss:MergeAcross="' . ($columnCount - 1) . '"><Data ss:Type="String"></Data></Cell></Row>' . "\n";

        // ── ROW 5: Report Title ──
        $xml .= '<Row ss:Height="22"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="TitleStyle"><Data ss:Type="String">' . htmlspecialchars($config['title']) . '</Data></Cell></Row>' . "\n";

        // ── ROW 6: Subtitle ──
        if (isset($config['subtitle'])) {
            $xml .= '<Row ss:Height="16"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="SubtitleStyle"><Data ss:Type="String">' . htmlspecialchars($config['subtitle']) . '</Data></Cell></Row>' . "\n";
        }

        // ── ROW 7: Department ──
        if (isset($config['department'])) {
            $xml .= '<Row ss:Height="14"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="SubtitleStyle"><Data ss:Type="String">Department: ' . htmlspecialchars($config['department']) . '</Data></Cell></Row>' . "\n";
        }

        // ── Metadata rows (2 items per row) ──
        if (!empty($config['metadata'])) {
            $xml .= '<Row ss:Height="5"><Cell ss:MergeAcross="' . ($columnCount - 1) . '"><Data ss:Type="String"></Data></Cell></Row>' . "\n";
            $metaItems = array_map(null, array_keys($config['metadata']), array_values($config['metadata']));
            $metaPairs = array_chunk($metaItems, 2);
            $halfCols = intval($columnCount / 2) - 1;
            foreach ($metaPairs as $pair) {
                $xml .= '<Row ss:Height="14">' . "\n";
                $xml .= '<Cell ss:MergeAcross="' . $halfCols . '" ss:StyleID="MetaLabel"><Data ss:Type="String">' . htmlspecialchars($pair[0][0] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:MergeAcross="' . ($columnCount - $halfCols - 2) . '" ss:StyleID="MetaValue"><Data ss:Type="String">' . htmlspecialchars((string)($pair[0][1] ?? '')) . '</Data></Cell>' . "\n";
                if (isset($pair[1])) {
                    // second pair occupies nothing (already used all columns in this row)
                }
                $xml .= '</Row>' . "\n";
            }
            // Export date
            $xml .= '<Row ss:Height="14"><Cell ss:MergeAcross="' . $halfCols . '" ss:StyleID="MetaLabel"><Data ss:Type="String">Export Date</Data></Cell><Cell ss:MergeAcross="' . ($columnCount - $halfCols - 2) . '" ss:StyleID="MetaValue"><Data ss:Type="String">' . date('Y-m-d H:i:s') . '</Data></Cell></Row>' . "\n";
        }

        // ── Blank before headers ──
        $xml .= '<Row ss:Height="8"><Cell ss:MergeAcross="' . ($columnCount - 1) . '"><Data ss:Type="String"></Data></Cell></Row>' . "\n";

        // ── Column Headers ──
        $xml .= '<Row ss:Height="28">' . "\n";
        $xml .= '<Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">S.N.</Data></Cell>' . "\n";
        foreach ($config['columns'] as $key => $label) {
            $xml .= '<Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">' . htmlspecialchars($label) . '</Data></Cell>' . "\n";
        }
        $xml .= '</Row>' . "\n";

        // ── Data Rows ──
        foreach ($config['data'] as $index => $row) {
            $isEven   = ($index % 2 === 1);
            $baseStyle = $isEven ? 'DataEven' : 'DataOdd';
            $numStyle  = $isEven ? 'NumEven'  : 'NumOdd';

            $xml .= '<Row ss:Height="16">' . "\n";
            $xml .= '<Cell ss:StyleID="' . $baseStyle . '"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";

            foreach ($config['columns'] as $key => $label) {
                $value    = $this->getNestedProperty($row, $key);
                $dataType = $this->getExcelDataType($key, $value);

                // Choose cell style
                if ($key === 'result_remark') {
                    $lower = strtolower((string)$value);
                    $styleId = match($lower) { 'pass' => 'PassStyle', 'fail' => 'FailStyle', 'absent' => 'AbsentStyle', default => $baseStyle };
                } elseif ($this->getExcelStyleId($key, $value) === 'NumberStyle') {
                    $styleId = $numStyle;
                } else {
                    $styleId = $baseStyle;
                }

                $formatted = $this->formatValue($value);
                $xml .= '<Cell ss:StyleID="' . $styleId . '"><Data ss:Type="' . $dataType . '">' . htmlspecialchars($formatted) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }

        // ── Summary row ──
        $total  = count($config['data']);
        $passed = collect($config['data'])->filter(fn($r) => strtolower((string)data_get($r,'result_remark')) === 'pass')->count();
        $failed = collect($config['data'])->filter(fn($r) => strtolower((string)data_get($r,'result_remark')) === 'fail')->count();
        $absent = collect($config['data'])->filter(fn($r) => strtolower((string)data_get($r,'result_remark')) === 'absent')->count();
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) . '%' : 'N/A';

        $xml .= '<Row ss:Height="18"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="SummaryStyle"><Data ss:Type="String">' .
            "Total: $total  |  Pass: $passed  |  Fail: $failed  |  Absent: $absent  |  Pass Rate: $passRate" .
            '</Data></Cell></Row>' . "\n";

        // ── Footer note ──
        $xml .= '<Row ss:Height="14"><Cell ss:MergeAcross="' . ($columnCount - 1) . '" ss:StyleID="FooterStyle"><Data ss:Type="String">This is a computer-generated official report from ' . htmlspecialchars($this->collegeName) . '. Generated on ' . date('F d, Y \a\t H:i') . '</Data></Cell></Row>' . "\n";

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
            'config'             => $config,
            'collegeName'        => $this->collegeName,
            'collegeAddress'     => $this->collegeAddress,
            'collegeLogo'        => $this->collegeLogo,
            'collegePhone'       => $this->collegePhone,
            'collegeEmail'       => $this->collegeEmail,
            'collegeWebsite'     => $this->collegeWebsite,
            'collegeEstd'        => $this->collegeEstd,
            'collegeAffiliation' => $this->collegeAffiliation,
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

        // Single subject? (per-subject export)
        $singleSubject = $marks->pluck('subject_id')->unique()->count() === 1
            ? $marks->first()?->subject
            : null;

        // Single semester?
        $singleSemester = $marks->pluck('semester')->filter()->unique()->count() === 1
            ? $marks->first()?->semester
            : null;

        // Build compact metadata — only what's needed to understand the report
        $metadata = [
            'Exam'             => $exam->name,
            'Academic Session' => $exam->academicSession->name ?? 'N/A',
            'Department'       => $department ? $department->name : ($exam->department->name ?? 'N/A'),
        ];
        if ($singleSubject) {
            $metadata['Subject'] = $singleSubject->name . ($singleSubject->code ? ' (' . $singleSubject->code . ')' : '');
        }
        if ($singleSemester) {
            $metadata['Semester'] = 'Semester ' . $singleSemester;
        } else {
            $metadata['Semester(s)'] = $semesterText;
        }
        $metadata['Date']           = bsDate($exam->start_date, 'F d, Y');
        $metadata['Total Students'] = $marks->count();

        $config = [
            'title'      => $exam->name . ' — Marks Report',
            'subtitle'   => ($singleSubject ? $singleSubject->name . ' • ' : '') . $exam->category_label . ' • ' . ($singleSemester ? 'Semester ' . $singleSemester : $semesterText),
            'department' => $department ? $department->name : ($exam->department->name ?? 'N/A'),
            'metadata'   => $metadata,
            'data'       => $marks,
        ];

        if ($exam->category === 'monthly_assessment') {
            $fullMarks = $exam->assessment_full_marks ?? 100;
            $passMarks = $exam->assessment_pass_marks ?? 40;
            $config['metadata']['Full Marks'] = $fullMarks;
            $config['metadata']['Pass Marks'] = $passMarks;

            // Only show subject/semester columns when data spans multiple subjects/semesters
            $columns = ['student.user.name' => 'Student Name', 'student.roll_number' => 'Roll No'];
            if (!$singleSubject) {
                $columns['semester']      = 'Sem';
                $columns['subject.name']  = 'Subject';
            }
            $columns['assessment_obtained_marks'] = 'Obtained (' . $fullMarks . '/' . $passMarks . ')';
            $columns['result_remark']             = 'Result';
            $columns['remarks']                   = 'Remarks';
            $config['columns'] = $columns;
        } else {
            // Only show subject/semester columns when data spans multiple subjects/semesters
            $columns = ['student.user.name' => 'Student Name', 'student.roll_number' => 'Roll No'];
            if (!$singleSubject) {
                $columns['semester']     = 'Sem';
                $columns['subject.name'] = 'Subject';
            }
            $columns['internal_theory_marks']   = 'Int. Theory';
            $columns['external_theory_marks']   = 'Ext. Theory';
            $columns['internal_practical_marks']  = 'Int. Practical';
            $columns['external_practical_marks']  = 'Ext. Practical';
            $columns['total_marks']              = 'Total';
            $columns['result_remark']            = 'Result';
            $columns['remarks']                  = 'Remarks';
            $config['columns'] = $columns;
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
