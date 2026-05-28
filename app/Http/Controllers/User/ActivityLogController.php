<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer');

        if ($request->filled('search')) {
            $keyword = trim((string) $request->search);
            $like = '%'.$keyword.'%';

            $query->where(function ($q) use ($like) {
                $q->where('description', 'like', $like)
                    ->orWhere('log_name', 'like', $like)
                    ->orWhere('event', 'like', $like)
                    ->orWhere('subject_type', 'like', $like)
                    ->orWhere('subject_id', 'like', $like)
                    ->orWhere('properties', 'like', $like)
                    ->orWhereHas('causer', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('employee_id', 'like', $like);
                    });
            });
        }

        // Filter by log name
        if ($request->filled('log_name') && $request->log_name !== 'all') {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event') && $request->event !== 'all') {
            $query->where('event', $request->event);
        }

        if ($request->filled('actor') && $request->actor !== 'all') {
            if ($request->actor === 'system') {
                $query->whereNull('causer_id');
            } elseif ($request->actor === 'user') {
                $query->whereNotNull('causer_id');
            }
        }

        if ($request->filled('ip')) {
            $query->where('properties', 'like', '%'.trim((string) $request->ip).'%');
        }

        if ($request->filled('period') && $request->period !== 'all') {
            switch ($request->period) {
                case 'today':
                    $query->whereBetween('created_at', $this->bangkokRange(Carbon::now('Asia/Bangkok')->startOfDay(), Carbon::now('Asia/Bangkok')->endOfDay()));
                    break;
                case 'yesterday':
                    $query->whereBetween('created_at', $this->bangkokRange(Carbon::now('Asia/Bangkok')->subDay()->startOfDay(), Carbon::now('Asia/Bangkok')->subDay()->endOfDay()));
                    break;
                case 'week':
                    $query->whereBetween('created_at', $this->bangkokRange(Carbon::now('Asia/Bangkok')->startOfWeek(), Carbon::now('Asia/Bangkok')->endOfWeek()));
                    break;
                case 'month':
                    $query->whereBetween('created_at', $this->bangkokRange(Carbon::now('Asia/Bangkok')->startOfMonth(), Carbon::now('Asia/Bangkok')->endOfMonth()));
                    break;
                case 'year':
                    $query->whereBetween('created_at', $this->bangkokRange(Carbon::now('Asia/Bangkok')->startOfYear(), Carbon::now('Asia/Bangkok')->endOfYear()));
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from, 'Asia/Bangkok')->startOfDay()->timezone('UTC'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to, 'Asia/Bangkok')->endOfDay()->timezone('UTC'));
        }

        $sort = $request->input('sort', 'latest') === 'oldest' ? 'oldest' : 'latest';
        $query->{$sort}();

        $formatThai = function ($datetime) {
            if (! $datetime) {
                return '-';
            }
            Carbon::setLocale('th');
            setlocale(LC_TIME, 'th_TH.UTF-8');
            $date = Carbon::parse($datetime);
            $year = $date->year + 543;

            return $date->translatedFormat('j F')." {$year}";
        };

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true)
            ? (int) $request->input('per_page')
            : 20;

        $activities = $query->paginate($perPage)->appends($request->all());

        $activities->getCollection()->transform(function ($activity) use ($formatThai) {
            $date = Carbon::parse($activity->created_at)->timezone('Asia/Bangkok');
            $activity->thai_created_at = $formatThai($date);
            $activity->thai_time = $date->format('H:i:s');
            $activity->ip_address = data_get($activity->properties?->toArray() ?? [], 'ip', '-');

            return $activity;
        });

        $logNames = Activity::query()->distinct()->pluck('log_name')->filter()->sort()->values();
        $eventNames = Activity::query()->distinct()->pluck('event')->filter()->sort()->values();
        $activeFilterLabels = $this->activeFilterLabels($request, $logNames, $eventNames);

        return view('user.management.log', compact('activities', 'logNames', 'eventNames', 'activeFilterLabels'));
    }

    public function show(Activity $activity)
    {
        return view('user.management.log-detail', compact('activity'));
    }

    private function bangkokRange(Carbon $start, Carbon $end): array
    {
        return [
            $start->copy()->timezone('UTC'),
            $end->copy()->timezone('UTC'),
        ];
    }

    private function activeFilterLabels(Request $request, $logNames, $eventNames): array
    {
        $labels = [];

        if ($request->filled('search')) {
            $labels[] = 'คำค้น: '.$request->search;
        }

        if ($request->filled('log_name') && $request->log_name !== 'all') {
            $labels[] = 'ประเภท: '.$request->log_name;
        }

        if ($request->filled('event') && $request->event !== 'all') {
            $labels[] = 'เหตุการณ์: '.$request->event;
        }

        if ($request->filled('actor') && $request->actor !== 'all') {
            $labels[] = 'ผู้ทำรายการ: '.($request->actor === 'system' ? 'ระบบ' : 'ผู้ใช้');
        }

        if ($request->filled('ip')) {
            $labels[] = 'IP: '.$request->ip;
        }

        $periodLabels = [
            'today' => 'วันนี้',
            'yesterday' => 'เมื่อวาน',
            'week' => 'สัปดาห์นี้',
            'month' => 'เดือนนี้',
            'year' => 'ปีนี้',
        ];

        if ($request->filled('period') && $request->period !== 'all') {
            $labels[] = 'ช่วงเวลา: '.($periodLabels[$request->period] ?? $request->period);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $labels[] = 'วันที่: '.($request->date_from ?: 'เริ่มต้น').' - '.($request->date_to ?: 'ปัจจุบัน');
        }

        return $labels;
    }
}
