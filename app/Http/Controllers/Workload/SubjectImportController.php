<?php

namespace App\Http\Controllers\Workload;

use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\ConfirmSubjectImportRequest;
use App\Http\Requests\Workload\StoreSubjectImportPreviewRequest;
use App\Models\Subject;
use App\Services\Subjects\SubjectImportCommitter;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportResultStore;
use App\Services\Subjects\SubjectImportSnapshotStore;
use App\Services\Subjects\SubjectWorkbookFactory;
use App\Services\Subjects\SubjectWorkbookReader;
use App\Support\Subjects\SubjectCode;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SubjectImportController extends Controller
{
    public function __construct(
        private SubjectWorkbookFactory $workbooks,
        private SubjectWorkbookReader $reader,
        private SubjectImportPreviewService $previews,
        private SubjectImportSnapshotStore $snapshots,
        private SubjectImportResultStore $results,
        private SubjectImportCommitter $committer,
    ) {}

    public function template(): BinaryFileResponse
    {
        return Excel::download($this->workbooks->template(), 'subject_import_template.xlsx');
    }

    public function export(): BinaryFileResponse
    {
        $subjects = Subject::query()->orderBy('sort_order')->orderBy('id')->get();

        return Excel::download($this->workbooks->current($subjects), 'subjects_current.xlsx');
    }

    public function storePreview(StoreSubjectImportPreviewRequest $request): RedirectResponse
    {
        try {
            $file = $request->file('import_file');
            $read = $this->reader->read($file->getRealPath());
            $preview = $this->previews->build($read);
            $token = $this->snapshots->put($request->user()->id, $file->getClientOriginalName(), $preview);

            return redirect()->route('subjects.import.preview.show', $token);
        } catch (SubjectWorkbookException $exception) {
            return redirect()->route('subjects.index')->withErrors([
                'import_file' => $exception->getMessage(),
            ], 'subjectImport');
        }
    }

    public function showPreview(Request $request, string $token): View
    {
        $snapshot = $this->snapshots->getForUser($token, $request->user()->id);
        abort_if($snapshot === null, 404);

        return view('subjects.imports.show', ['token' => $token, 'preview' => $snapshot['preview']]);
    }

    public function confirm(ConfirmSubjectImportRequest $request, string $token): RedirectResponse
    {
        $snapshot = $this->snapshots->getForUser($token, $request->user()->id);
        abort_if($snapshot === null, 404);
        $selected = collect($request->validated('selected_codes', []))
            ->map(fn ($code) => SubjectCode::normalize($code))->unique()->values()->all();
        $allowed = collect($snapshot['preview']['changed'])->pluck('row.code');

        if ($snapshot['preview']['errors'] !== [] || collect($selected)->diff($allowed)->isNotEmpty()) {
            return back()->withErrors(['selected_codes' => 'Preview มีข้อผิดพลาดหรือรายการที่เลือกไม่ถูกต้อง']);
        }

        $claimed = $this->snapshots->claimForUser($token, $request->user()->id);
        abort_if($claimed === null, 409);
        $claimed['token_hash'] = hash('sha256', $token);

        try {
            $result = $this->committer->commit($claimed, $selected, $request->user());
        } catch (StaleSubjectImportException|QueryException $exception) {
            return redirect()->route('subjects.index')->with('error', $exception instanceof StaleSubjectImportException
                ? $exception->getMessage()
                : 'ไม่สามารถนำเข้าข้อมูลได้ กรุณาสร้าง Preview ใหม่');
        }

        $resultToken = $this->results->put($request->user()->id, $result);

        return redirect()->route('subjects.index')->with('subject_import_result_token', $resultToken);
    }

    public function cancel(Request $request, string $token): RedirectResponse
    {
        $this->snapshots->forget($token, $request->user()->id);

        return redirect()->route('subjects.index');
    }
}
