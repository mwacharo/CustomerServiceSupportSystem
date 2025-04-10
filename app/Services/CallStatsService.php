<?php

namespace App\Services;

use App\Models\Call;
use App\Models\CallHistory;
use App\Models\IvrOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallStatsService
{



    public function getAgentStats(User $user, ?array $dateRange = null): array
    {
        Log::info('Fetching agent stats', ['user_id' => $user->id, 'date_range' => $dateRange]);

        $phone_number = $user->phone_number;

        // Base query for outgoing calls
        $outgoingQuery = CallHistory::query()
            ->where('callerNumber', $phone_number)
            ->whereNull('deleted_at');

        // Base query for incoming calls
        $incomingQuery = CallHistory::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at');

        if ($dateRange) {
            Log::debug('Applying date range filter', ['date_range' => $dateRange]);

            $incomingQuery->whereBetween('created_at', $dateRange);
            $outgoingQuery->whereBetween('created_at', $dateRange);
        }

        // Total calls = incoming + outgoing
        $incomingCalls = (clone $incomingQuery)->count();
        $outgoingCalls = (clone $outgoingQuery)->count();
        $totalCalls = $incomingCalls + $outgoingCalls;

        // Missed calls from incoming
        $missedCalls = (clone $incomingQuery)
            ->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->count();

        // Total call duration from both
        $incomingDuration = (clone $incomingQuery)->sum('durationInSeconds') ?? 0;
        $outgoingDuration = (clone $outgoingQuery)->sum('durationInSeconds') ?? 0;
        $totalDuration = $incomingDuration + $outgoingDuration;


        // ivr statistics

        $ivrOptions = IvrOption::all();
        // $ivrStats = CallHistory::all();
        $ivrStats = CallHistory::whereNotNull('ivr_option_id')->get();
        // $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats ,$dateRange=null,$user->id);
        $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats, $dateRange, $user->id);



        $result = [
            'id' => $user->id,
            'phone_number' => $user->phone_number,
            'status' => $user->status,
            'sessionId' => $user->sessionId,
            'summary_call_completed' => $totalCalls,
            'summary_inbound_call_completed' => $incomingCalls,
            'summary_outbound_call_completed' => $outgoingCalls,
            'summary_call_duration' => $totalDuration,
            'summary_call_missed' => $missedCalls,
            'updated_at' => $user->updated_at,
            'ivr_analysis' => $ivrAnalysis,
        ];

        Log::info('Agent stats fetched successfully', ['result' => $result]);

        return $result;
    }




    /**
     * Generate a call summary report using Eloquent collections.
     *
     * @param  array  $filters
     * @return \Illuminate\Support\Collection
     */
    public function generateCallSummaryReport(array $filters)
    {
        // Enable query log
        DB::enableQueryLog();

        $query = CallHistory::with('agent')
            ->whereNull('deleted_at')
            ->where('user_id', '!=', null);

        // Apply filters
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }
        // if (!empty($filters['status'])) {
        //     if (is_array($filters['status'])) {
        //         $query->whereIn('status', $filters['status']);
        //     } else {
        //         $query->where('status', $filters['status']);
        //     }
        // }


        if (!empty($filters['status'])) {

            if (is_array($filters['status'])) {
                $query->whereIn('lastBridgeHangupCause', $filters['status']);
            } else {
                $query->where('lastBridgeHangupCause', $filters['status']);
            }
        }

        // Apply agent filter
        if (!empty($filters['user_id'])) {
            if (is_array($filters['user_id'])) {
                $query->whereIn('user_id', $filters['user_id']);
            } else {
                $query->where('user_id', $filters['user_id']);
            }
        }

        // Execute query
        $callHistories = $query->get();

        // Log the executed SQL query
        Log::info('Executed Query:', DB::getQueryLog());


        Log::info("Bindings: ", $query->getBindings());


        $ivrOptions = IvrOption::all();
        $ivrStats = CallHistory::all();
        // pass user_id or user_id array also 
        // $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats, $filters,$user_id = null);


        Log::info('Call summary report generated', ['call_histories' => $callHistories]);
        // $callHistory is null log warning  no data 

        if ($callHistories->isEmpty()) {
            Log::warning('No call history found for the given filters.', $filters);
        }
        // return $callHistories;


        return $callHistories->groupBy('user_id')->map(function ($calls, $user_) {
            return [
                'agent' => optional($calls->first()->agent)->name ?? 'N/A',
                'total_calls' => $calls->count(),
                'total_airtime' => $calls->sum('amount') ?? 0,
                'answered' => $calls->where('status', 'Answered')->count(),
                // 'missed' => $calls->where('status', 'Missed')->count(),
                'escalated' => $calls->where('status', 'Escalated')->count(),
                'total_duration' => $calls->sum('durationInSeconds') ?? 0,
            ];
        })->values();
    }






//     public function analyzeIvrStatistics(Collection $ivrOptions, Collection $ivrStats, ): Collection
// {
//     // Filter the already loaded ivrStats collection based on filters

//     if (!empty($filters['user_id'])) {
//         if (is_array($filters['user_id'])) {
//             $ivrStats = $ivrStats->whereIn('user_id', $filters['user_id']);
//         } else {
//             $ivrStats = $ivrStats->where('user_id', $filters['user_id']);
//         }
//     }

//     if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
//         $ivrStats = $ivrStats->filter(function ($stat) use ($filters) {
//             return $stat->created_at >= $filters['startDate'] && $stat->created_at <= $filters['endDate'];
//         });
//     }

//     $totalSelections = $ivrStats->count();

//     return $ivrOptions->map(function ($ivrOption) use ($ivrStats, $totalSelections) {
//         $matchedStats = $ivrStats->where('agentId', $ivrOption->id); // Adjust to match your DB if needed

//         $totalSelected = $matchedStats->count();
//         $totalDuration = $matchedStats->sum('durationInSeconds') ?? 0;

//         return [
//             'id' => $ivrOption->id,
//             'option_number' => $ivrOption->option_number,
//             'description' => $ivrOption->description,
//             'total_selected' => $totalSelected,
//             'total_duration' => $totalDuration,
//             'average_duration' => $totalSelected ? round($totalDuration / $totalSelected, 2) : 0,
//             'selection_percentage' => $totalSelections ? round(($totalSelected / $totalSelections) * 100, 2) : 0,
//         ];
//     });
// }

    public function analyzeIvrStatistics(Collection $ivrOptions, Collection $ivrStats): Collection

    {

        $totalSelections = $ivrStats->count();

        return $ivrOptions->map(function ($ivrOption) use ($ivrStats, $totalSelections) {
            $matchedStats = $ivrStats->where('agentId', $ivrOption->id);

            $totalSelected = $matchedStats->count();
            $totalDuration = $matchedStats->sum('durationInSeconds') ?? 0;

            return [
                'id' => $ivrOption->id,
                'option_number' => $ivrOption->option_number,
                'description' => $ivrOption->description,
                'total_selected' => $totalSelected,
                'total_duration' => $totalDuration,
                'average_duration' => $totalSelected ? round($totalDuration / $totalSelected, 2) : 0,
                'selection_percentage' => $totalSelections ? round(($totalSelected / $totalSelections) * 100, 2) : 0,
            ];
        });
    }


    // IVR Trends Over Time
    public function ivrTrendsByDate(array $filters = []): Collection
    {
        $query = CallHistory::query()
            ->whereNotNull('agentId')
            ->whereNull('deleted_at');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        return $query->selectRaw('DATE(created_at) as date, agentId, COUNT(*) as total')
            ->groupBy('date', 'agentId')
            ->get()
            ->groupBy('agentId');
    }

    // Call Drop-off / Short Duration Analysis


    public function analyzeCallDropOffs(array $filters = []): array
    {
        $query = CallHistory::query()->whereNull('deleted_at');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        $shortCalls = (clone $query)->where('durationInSeconds', '<', 10)->count();
        $missedCalls = (clone $query)->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])->count();
        $totalCalls = (clone $query)->count();

        return [
            'total_calls' => $totalCalls,
            'short_calls' => $shortCalls,
            'missed_calls' => $missedCalls,
            'drop_off_rate' => $totalCalls > 0 ? round(($shortCalls + $missedCalls) / $totalCalls * 100, 2) . '%' : '0%'
        ];
    }

    // Peak Call Times (Hour of Day + Day of Week)


    public function peakCallTimes(array $filters = []): array
    {
        $query = CallHistory::query()->whereNull('deleted_at');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        $hourly = (clone $query)->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $daily = (clone $query)->selectRaw('DAYNAME(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->pluck('total', 'day');

        return [
            'hourly_distribution' => $hourly,
            'daily_distribution' => $daily,
        ];
    }


    //  Top Calling Countries
    public function topCallerCountries(array $filters = []): Collection
    {
        $query = CallHistory::query()
            ->whereNull('deleted_at')
            ->whereNotNull('callerCountryCode');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        return $query->selectRaw('callerCountryCode, COUNT(*) as total')
            ->groupBy('callerCountryCode')
            ->orderByDesc('total')
            ->get();
    }
    // . Frequent Callers


    public function frequentCallers(array $filters = [], int $limit = 10): Collection
    {
        $query = CallHistory::query()
            ->whereNull('deleted_at');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        return $query->selectRaw('callerNumber, COUNT(*) as call_count')
            ->groupBy('callerNumber')
            ->orderByDesc('call_count')
            ->limit($limit)
            ->get();
    }
    // Agent Comparison Report


    public function agentPerformanceComparison(array $filters = []): Collection
    {
        $query = CallHistory::query()
            ->whereNull('deleted_at')
            ->whereNotNull('user_id');

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        return $query->with('agent')
            ->get()
            ->groupBy('user_id')
            ->map(function ($calls) {
                return [
                    'agent' => optional($calls->first()->agent)->name ?? 'Unknown',
                    'total_calls' => $calls->count(),
                    'average_duration' => round($calls->avg('durationInSeconds'), 2),
                    'answered_calls' => $calls->where('status', 'Answered')->count(),
                    'missed_calls' => $calls->where('status', 'Missed')->count(),
                ];
            })->values();
    }
}
