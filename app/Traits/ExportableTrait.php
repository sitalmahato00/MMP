<?php

namespace App\Traits;

use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait ExportableTrait
{
    protected $exportService;

    /**
     * Initialize export service
     */
    protected function initializeExportService(): ExportService
    {
        if (!$this->exportService) {
            $this->exportService = new ExportService();
        }
        
        return $this->exportService;
    }

    /**
     * Handle generic export request
     */
    protected function handleExport(Request $request, array $config)
    {
        $format = $request->get('format', 'csv');
        $config['format'] = $format;
        
        $exportService = $this->initializeExportService();
        
        return $exportService->export($config);
    }

    /**
     * Export marks data
     */
    protected function exportMarksData($exam, $marks, $department = null, $format = 'csv')
    {
        $config = ExportService::createMarksExportConfig($exam, $marks, $department);
        $config['format'] = $format;
        
        $exportService = $this->initializeExportService();
        
        return $exportService->export($config);
    }

    /**
     * Export students data
     */
    protected function exportStudentsData($students, $department = null, $format = 'csv')
    {
        $config = ExportService::createStudentsExportConfig($students, $department);
        $config['format'] = $format;
        
        $exportService = $this->initializeExportService();
        
        return $exportService->export($config);
    }

    /**
     * Export attendance data
     */
    protected function exportAttendanceData($attendanceData, $session = null, $department = null, $format = 'csv')
    {
        $config = ExportService::createAttendanceExportConfig($attendanceData, $session, $department);
        $config['format'] = $format;
        
        $exportService = $this->initializeExportService();
        
        return $exportService->export($config);
    }

    /**
     * Create custom export configuration
     */
    protected function createCustomExportConfig(string $title, array $columns, $data, array $metadata = []): array
    {
        return [
            'title' => $title,
            'columns' => $columns,
            'data' => $data,
            'metadata' => $metadata,
        ];
    }
}