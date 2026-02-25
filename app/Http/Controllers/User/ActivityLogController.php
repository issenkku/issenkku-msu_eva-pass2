<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า user.management.log
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า user.management.log
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Search by user name
        if ($request->filled('search')) {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by log name
        if ($request->filled('log_name') && $request->log_name !== 'all') {
            $query->where('log_name', $request->log_name);
        }

        // Filter by time period
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
                default:
                    // 'all' - no additional filter
                    break;
            }
        }

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

        $activities = $query->paginate(20)->appends($request->all());

        $activities->getCollection()->transform(function ($activity) use ($formatThai) {
            $date = Carbon::parse($activity->created_at)->timezone('Asia/Bangkok');
            $activity->thai_created_at = $formatThai($date);
            $activity->thai_time = $date->format('H:i:s');
            return $activity;
        });

        // Get unique log names for filter dropdown
        $logNames = Activity::distinct()->pluck('log_name')->filter();

        return view('user.management.log', compact('activities', 'logNames'));
    }

    /**
     * เมธอด: show
     * จุดประสงค์: แสดงหน้า user.management.log-detail
     * อินพุต: โมเดล Activity
     * เอาต์พุต: หน้า user.management.log-detail
     * @param Activity $activity ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show(Activity $activity)
    {
        return view('user.management.log-detail', compact('activity'));
    }
}
