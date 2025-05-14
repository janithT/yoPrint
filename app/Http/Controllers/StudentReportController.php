<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Illuminate\Support\Facades\Log;

class StudentReportController extends Controller
{
    
    public $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('diagnostic.student.index');
    }

    public function getReport($student_id, $report_id)
    {

        // read student and json reports

        try {

            if ($report_id && $report_id == 1) {
                $data = $this->reportService->generateDiagnosticReport($student_id);
            } else if ($report_id && $report_id == 2) {
                $data = $this->reportService->generateProgressReport($student_id);
            } else if ($report_id && $report_id == 3) {
                $data = $this->reportService->generateFeedbackReport($student_id);
            } else {
                // default to diagnostic report
                $data = $this->reportService->generateDiagnosticReport($student_id);
            }
            

            return response()->json([
                'student_id' => $student_id,
                'report_id' => $report_id,
                'status' => 'success',
                'message' => 'Sample report data here'
            ]);


        } catch (\Throwable $th) {
            // ... the rest
            Log::error('Error generating report: ' . $th->getMessage());
            return response()->json([
                'student_id' => $student_id,
                'report_id' => $report_id,
                'status' => 'error',
                'message' => 'Error generating report',
            ]);
        }

    }
}
