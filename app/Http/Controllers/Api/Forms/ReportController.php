<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\RepairOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'department');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            $departmentId = $request->get('department_id');

            $materialQuery = MaterialRequest::query();
            $repairQuery = RepairOrder::query();

            if ($fromDate) { $materialQuery->where('date_bs', '>=', $fromDate); $repairQuery->where('date_bs', '>=', $fromDate); }
            if ($toDate) { $materialQuery->where('date_bs', '<=', $toDate); $repairQuery->where('date_bs', '<=', $toDate); }
            if ($departmentId) { $materialQuery->where('department_id', $departmentId); $repairQuery->where('department_id', $departmentId); }

            $labels = [];
            $values = [];

            switch ($type) {
                case 'department':
                    $labels = ['Computer', 'Electronics', 'Civil', 'Mechanical', 'Architecture'];
                    $values = [12, 8, 15, 6, 4];
                    break;
                case 'monthly':
                    $labels = ['Baisakh', 'Jestha', 'Ashad', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'];
                    $values = [5, 8, 12, 6, 9, 15, 10, 7, 11, 4, 6, 3];
                    break;
                case 'yearly':
                    $labels = ['2077', '2078', '2079', '2080', '2081'];
                    $values = [45, 52, 68, 85, 92];
                    break;
                case 'status':
                    $labels = ['Draft', 'Submitted', 'Recommended', 'Approved', 'Rejected', 'Printed', 'Completed'];
                    $values = [
                        MaterialRequest::where('status', 'draft')->count() + RepairOrder::where('status', 'draft')->count(),
                        MaterialRequest::where('status', 'submitted')->count() + RepairOrder::where('status', 'submitted')->count(),
                        MaterialRequest::where('status', 'recommended')->count() + RepairOrder::where('status', 'recommended')->count(),
                        MaterialRequest::where('status', 'approved')->count() + RepairOrder::where('status', 'approved')->count(),
                        MaterialRequest::where('status', 'rejected')->count() + RepairOrder::where('status', 'rejected')->count(),
                        MaterialRequest::where('status', 'printed')->count() + RepairOrder::where('status', 'printed')->count(),
                        MaterialRequest::where('status', 'completed')->count() + RepairOrder::where('status', 'completed')->count(),
                    ];
                    break;
                default:
                    $labels = ['No data'];
                    $values = [0];
            }

            return response()->json(['success' => true, 'data' => ['labels' => $labels, 'values' => $values]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pdf(Request $request)
    {
        $type = $request->get('type', 'department');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', ['type' => $type]);
        return $pdf->download("report-{$type}.pdf");
    }

    public function excel(Request $request)
    {
        $type = $request->get('type', 'department');
        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Label', 'Value']);
            fputcsv($handle, ['Sample Data 1', 10]);
            fputcsv($handle, ['Sample Data 2', 20]);
            fclose($handle);
        }, "report-{$type}.csv");
    }
}
