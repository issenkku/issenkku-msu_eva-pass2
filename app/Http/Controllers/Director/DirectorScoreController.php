<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use Illuminate\Http\Request;

class DirectorScoreController extends Controller
{
    protected $allowedEditStatuses = ['Director_assigned', 'Director_draft'];

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Director_assigned or Director_draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    public function director(Request $request, $id) {}

    public function storeDirectorScores(Request $request, $reportId) {}
}
