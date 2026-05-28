<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Setting\Departments;
use App\Models\Setting\JobLevel;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'position',
            'jobLevel',
            'department',
            'roles',
            'assignment.assignmentData.evaluatorUser',
            'assignment.assignmentData.evaluatorPosition',
            'assignment.report.reportData',
            'assignment.report.workloadEntries.subject',
            'assignment.report.evidenceAnswers',
            'assignment.report.quantityScores',
            'assignment.report.qualityScores',
        ]);

        $evaluatedWorks = $this->buildEvaluatedWorks($user);

        return view('user.profile.show-profile', compact('user', 'evaluatedWorks'));
    }

    public function showPublic($uuid)
    {
        $user = User::where('public_profile_uuid', $uuid)
            ->where('is_public_profile_enabled', true)
            ->firstOrFail();

        $user = $user->load([
            'position',
            'jobLevel',
            'department',
            'roles',
            'assignment.assignmentData.evaluatorUser',
            'assignment.assignmentData.evaluatorPosition',
            'assignment.report.reportData',
            'assignment.report.workloadEntries.subject',
            'assignment.report.evidenceAnswers',
            'assignment.report.quantityScores',
            'assignment.report.qualityScores',
        ]);

        $evaluatedWorks = $this->buildEvaluatedWorks($user);

        return view('user.profile.show-profile-public', compact('user', 'evaluatedWorks'));
    }

    public function edit()
    {
        $user = auth()->user();
        $positions = Positions::all();
        $jobLevels = JobLevel::all();
        $departments = Departments::all();

        return view('user.profile.edit-profile', compact('user', 'positions', 'jobLevels', 'departments'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['education_history'] = $this->normalizeEducationHistory($validated['education_history'] ?? []);
        $validated['bio'] = $this->buildEducationBio($validated['education_history'], $validated['bio'] ?? null);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $photoPath;
        }

        unset($validated['profile_photo']);

        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
            }

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }
        }

        if (! $request->filled('password')) {
            unset($validated['current_password'], $validated['password'], $validated['password_confirmation']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'โปรไฟล์ได้รับการอัปเดตเรียบร้อยแล้ว');
    }

    private function normalizeEducationHistory(array $entries): ?array
    {
        $normalized = collect($entries)
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'graduation_year' => filled($entry['graduation_year'] ?? null) ? (string) $entry['graduation_year'] : null,
                'degree' => filled($entry['degree'] ?? null) ? trim((string) $entry['degree']) : null,
                'university' => filled($entry['university'] ?? null) ? trim((string) $entry['university']) : null,
            ])
            ->filter(fn (array $entry) => filled($entry['graduation_year']) || filled($entry['degree']) || filled($entry['university']))
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }

    private function buildEducationBio(?array $educationHistory, ?string $fallbackBio = null): ?string
    {
        if (! empty($educationHistory)) {
            return collect($educationHistory)
                ->map(function (array $entry) {
                    return collect([
                        $entry['graduation_year'] ?? null,
                        $entry['degree'] ?? null,
                        $entry['university'] ?? null,
                    ])->filter(fn ($value) => filled($value))->implode(' ');
                })
                ->filter(fn ($line) => filled($line))
                ->implode(PHP_EOL);
        }

        return filled($fallbackBio) ? trim($fallbackBio) : null;
    }

    private function buildEvaluatedWorks(User $user)
    {
        return $user->assignment
            ->filter(fn ($assignment) => $assignment->report && $assignment->report->status === 'Completed')
            ->map(function ($assignment) {
                $report = $assignment->report;
                $entries = $report->workloadEntries ?? collect();
                $subjects = $entries
                    ->map(fn ($entry) => $entry->subject?->code ? $entry->subject->code.' '.$entry->subject->name : ($entry->subject->name ?? null))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'report_title' => $report->reportData->report_title ?? 'ไม่ระบุชื่อรอบประเมิน',
                    'report_description' => $report->reportData->report_description ?? null,
                    'status' => $report->status ?? '-',
                    'evaluator_name' => $assignment->assignmentData?->evaluatorUser?->name ?? '-',
                    'evaluator_position' => $assignment->assignmentData?->evaluatorPosition?->name ?? null,
                    'period_start' => optional($assignment->assignmentData?->start_time)->format('d/m/Y'),
                    'period_end' => optional($assignment->assignmentData?->end_time)->format('d/m/Y'),
                    'workload_entries_count' => $entries->count(),
                    'subjects' => $subjects,
                    'evidence_count' => ($report->evidenceAnswers ?? collect())->count(),
                    'quantity_score' => round((float) ($report->quantityScores?->sum('score_D') ?? 0), 2),
                    'quality_score' => round((float) ($report->qualityScores?->sum('score') ?? 0), 2),
                    'updated_at' => optional($report->updated_at)?->format('d/m/Y H:i'),
                ];
            })
            ->sortByDesc(function ($item) {
                return $item['updated_at'] ?? '';
            })
            ->values();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard('web')->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'ลิงก์รีเซ็ตรหัสผ่านถูกส่งไปยังอีเมลของคุณแล้ว');
        }

        return back()->with('error', 'เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองใหม่อีกครั้ง');
    }
}
