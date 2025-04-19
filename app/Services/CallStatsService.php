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



  

    // public function getAgentStats(User $user, ?array $dateRange = null): array
    // {
    //     Log::info('Fetching agent stats', ['user_id' => $user->id, 'date_range' => $dateRange]);

    //     $phone_number = $user->phone_number;

    //     // Base query for outgoing calls
    //     $outgoingQuery = CallHistory::query()
    //         ->where('callerNumber', $phone_number)
    //         ->whereNull('deleted_at');

    //     // Base query for incoming calls
    //     $incomingQuery = CallHistory::query()
    //         ->where('user_id', $user->id)
    //         ->whereNull('deleted_at');

    //     if ($dateRange) {
    //         Log::debug('Applying date range filter', ['date_range' => $dateRange]);

    //         $incomingQuery->whereBetween('created_at', $dateRange);
    //         $outgoingQuery->whereBetween('created_at', $dateRange);
    //     }

    //     // Total calls = incoming + outgoing
    //     $incomingCalls = (clone $incomingQuery)->count();
    //     $outgoingCalls = (clone $outgoingQuery)->count();
    //     $totalCalls = $incomingCalls + $outgoingCalls;

    //     // Missed calls from incoming
    //     $missedCalls = (clone $incomingQuery)
    //         ->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
    //         ->count();

    //     $rejectedIncomingCalls = (clone $incomingQuery)
    //         ->whereIn('lastBridgeHangupCause', ['CALL_REJECTED', 'NORMAL_CLEARING'])
    //         ->count();

    //     $userBusyOutgoingCalls = (clone $outgoingQuery)
    //         ->where('lastBridgeHangupCause', 'USER_BUSY')
    //         ->count();

    //     $rejectedOutingCalls = (clone $outgoingQuery)
    //         ->where('lastBridgeHangupCause', 'CALL_REJECTED')
    //         ->count();

    //     // Total call duration from both
    //     $incomingDuration = (clone $incomingQuery)->sum('durationInSeconds') ?? 0;
    //     $outgoingDuration = (clone $outgoingQuery)->sum('durationInSeconds') ?? 0;
    //     $totalDuration = $incomingDuration + $outgoingDuration;


    //     // IVR statistics - correctly filtered by user is CallCentre
    //     $ivrOptions = IvrOption::all();



    //     $ivrStats = CallHistory::whereNotNull('ivr_option_id')
    //         ->where('user_id', $user->id)
    //         ->when($dateRange, function ($query) use ($dateRange) {
    //             return $query->whereBetween('created_at', $dateRange);
    //         })
    //         ->get();
    //     $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats);




    //     $result = [
    //         'id' => $user->id,
    //         'phone_number' => $user->phone_number,
    //         'status' => $user->status,
    //         'sessionId' => $user->sessionId,
    //         'summary_call_completed' => $totalCalls,
    //         'summary_inbound_call_completed' => $incomingCalls,
    //         'summary_outbound_call_completed' => $outgoingCalls,
    //         'summary_call_duration' => $totalDuration,
    //         'summary_call_missed' => $missedCalls,
    //         'summary_rejected_incoming_calls' => $rejectedIncomingCalls,
    //         'summary_user_busy_outgoing_calls' => $userBusyOutgoingCalls,
    //         'summary_rejected_outgoing_calls' => $rejectedOutingCalls,
    //         'updated_at' => $user->updated_at,
    //         'ivr_analysis' => $ivrAnalysis,
    //     ];

    //     Log::info('Agent stats fetched successfully', ['result' => $result]);

    //     return $result;
    // }



    public function getAgentStats(User $user, ?array $dateRange = null): array
{
    Log::info('Fetching agent stats', ['user_id' => $user->id, 'date_range' => $dateRange]);

    $isAdmin = $user->hasRole('call_centre_admin') || $user->hasRole('super_admin');

    $ivrOptions = IvrOption::all();

    if ($isAdmin) {
        // Admin: fetch overall stats
        $query = CallHistory::query()->whereNull('deleted_at');

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        $incomingCalls = (clone $query)->whereNotNull('user_id')->count();
        $outgoingCalls = (clone $query)->whereNotNull('callerNumber')->count();
        $totalCalls = $incomingCalls + $outgoingCalls;

        $missedCalls = (clone $query)
            ->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->count();

        $rejectedIncomingCalls = (clone $query)
            ->whereIn('lastBridgeHangupCause', ['CALL_REJECTED', 'NORMAL_CLEARING'])
            ->count();

        $userBusyOutgoingCalls = (clone $query)
            ->where('lastBridgeHangupCause', 'USER_BUSY')
            ->count();

        $rejectedOutingCalls = (clone $query)
            ->where('lastBridgeHangupCause', 'CALL_REJECTED')
            ->count();

        $incomingDuration = (clone $query)->sum('durationInSeconds') ?? 0;
        $totalDuration = $incomingDuration; // Since no outgoing specific separation here

        $ivrStats = CallHistory::whereNotNull('ivr_option_id')
            ->when($dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->get();

        $ivrAnalysis = $this->analyzeOverallIvrStatistics($ivrOptions, $ivrStats, $dateRange);

        Log::info('Returning overall stats for admin/super_admin', ['user_id' => $user->id]);
    } else {
        // Agent: fetch personal stats
        $phone_number = $user->phone_number;

        $outgoingQuery = CallHistory::query()
            ->where('callerNumber', $phone_number)
            ->whereNull('deleted_at');

        $incomingQuery = CallHistory::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at');

        if ($dateRange) {
            $incomingQuery->whereBetween('created_at', $dateRange);
            $outgoingQuery->whereBetween('created_at', $dateRange);
        }

        $incomingCalls = (clone $incomingQuery)->count();
        $outgoingCalls = (clone $outgoingQuery)->count();
        $totalCalls = $incomingCalls + $outgoingCalls;

        $missedCalls = (clone $incomingQuery)
            ->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->count();

        $rejectedIncomingCalls = (clone $incomingQuery)
            ->whereIn('lastBridgeHangupCause', ['CALL_REJECTED', 'NORMAL_CLEARING'])
            ->count();

        $userBusyOutgoingCalls = (clone $outgoingQuery)
            ->where('lastBridgeHangupCause', 'USER_BUSY')
            ->count();

        $rejectedOutingCalls = (clone $outgoingQuery)
            ->where('lastBridgeHangupCause', 'CALL_REJECTED')
            ->count();

        $incomingDuration = (clone $incomingQuery)->sum('durationInSeconds') ?? 0;
        $outgoingDuration = (clone $outgoingQuery)->sum('durationInSeconds') ?? 0;
        $totalDuration = $incomingDuration + $outgoingDuration;

        $ivrStats = CallHistory::whereNotNull('ivr_option_id')
            ->where('user_id', $user->id)
            ->when($dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->get();

        $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats, $dateRange, $user->id);

        Log::info('Returning personal stats for agent', ['user_id' => $user->id]);
    }

    return [
        'id' => $user->id,
        'phone_number' => $user->phone_number,
        'status' => $user->status,
        'sessionId' => $user->sessionId,
        'summary_call_completed' => $totalCalls,
        'summary_inbound_call_completed' => $incomingCalls,
        'summary_outbound_call_completed' => $outgoingCalls,
        'summary_call_duration' => $totalDuration,
        'summary_call_missed' => $missedCalls,
        'summary_rejected_incoming_calls' => $rejectedIncomingCalls,
        'summary_user_busy_outgoing_calls' => $userBusyOutgoingCalls,
        'summary_rejected_outgoing_calls' => $rejectedOutingCalls,
        'updated_at' => $user->updated_at,
        'ivr_analysis' => $ivrAnalysis,
    ];
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







    public function analyzeIvrStatistics(Collection $ivrOptions, Collection $ivrStats, ?array $dateRange = null, int $userId = null): Collection
    {
        if ($userId !== null) {
            $ivrStats = $ivrStats->where('user_id', $userId);
            Log::info('After filtering by user_id', ['user_id' => $userId, 'ivrStats' => $ivrStats->pluck('ivr_option_id')]);
        }

        if ($dateRange !== null) {
            $ivrStats = $ivrStats->filter(function ($stat) use ($dateRange) {
                $createdAt = Carbon::parse($stat->created_at);
                return $createdAt->between($dateRange[0], $dateRange[1]);
            });
            Log::info('After filtering by date range', ['dateRange' => $dateRange, 'ivrStats' => $ivrStats->pluck('ivr_option_id')]);
        }

        $totalSelections = $ivrStats->count();
        Log::info('Total selections after all filters', ['total' => $totalSelections]);

        return $ivrOptions->map(function ($ivrOption) use ($ivrStats, $totalSelections) {
            $matchedStats = $ivrStats->where('ivr_option_id', $ivrOption->id);

            Log::info('Stats for IVR Option', [
                'option_id' => $ivrOption->id,
                'description' => $ivrOption->description,
                'matched_count' => $matchedStats->count()
            ]);

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


    public function getOverallCallStats(array $filters = []): array
    {
        $startDate = isset($filters['start_date']) 
            ? Carbon::parse($filters['start_date'])->startOfDay() 
            : Carbon::today()->startOfDay();
    
        $endDate = isset($filters['end_date']) 
            ? Carbon::parse($filters['end_date'])->endOfDay() 
            : Carbon::today()->endOfDay();
    
        $baseQuery = CallHistory::whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at');
    
        // Call summaries
        $totalCalls = (clone $baseQuery)->count();
        $inboundCalls = (clone $baseQuery)->whereNotNull('user_id')->count();
        $outboundCalls = (clone $baseQuery)->whereNull('user_id')->count();
    
        $missedCalls = (clone $baseQuery)->whereIn('lastBridgeHangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])->count();
        $rejectedIncomingCalls = (clone $baseQuery)
            ->whereNotNull('user_id')
            ->whereIn('lastBridgeHangupCause', ['CALL_REJECTED', 'NORMAL_CLEARING'])
            ->count();
    
        $userBusyOutgoingCalls = (clone $baseQuery)
            ->whereNull('user_id')
            ->where('lastBridgeHangupCause', 'USER_BUSY')
            ->count();
    
        $rejectedOutgoingCalls = (clone $baseQuery)
            ->whereNull('user_id')
            ->where('lastBridgeHangupCause', 'CALL_REJECTED')
            ->count();
    
        $totalDuration = (clone $baseQuery)->sum('durationInSeconds') ?? 0;
    
        // IVR stats delegation
        $ivrStats = CallHistory::whereNotNull('ivr_option_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->get();
    
        $ivrOptions = IVROption::all(); 
    
        $ivrBreakdown = $this->analyzeOverallIvrStatistics($ivrOptions, $ivrStats, [$startDate, $endDate]);
    
        return [
            'summary_from' => $startDate->toDateTimeString(),
            'summary_to' => $endDate->toDateTimeString(),
            'summary_call_completed' => $totalCalls,
            'summary_inbound_call_completed' => $inboundCalls,
            'summary_outbound_call_completed' => $outboundCalls,
            'summary_call_duration' => $totalDuration,
            'summary_call_missed' => $missedCalls,
            'summary_rejected_incoming_calls' => $rejectedIncomingCalls,
            'summary_user_busy_outgoing_calls' => $userBusyOutgoingCalls,
            'summary_rejected_outgoing_calls' => $rejectedOutgoingCalls,
            'ivr_breakdown' => $ivrBreakdown,
        ];
    }



    public function analyzeOverallIvrStatistics(Collection $ivrOptions, Collection $ivrStats, ?array $dateRange = null): Collection
{
    if ($dateRange !== null) {
        $ivrStats = $ivrStats->filter(function ($stat) use ($dateRange) {
            $createdAt = Carbon::parse($stat->created_at);
            return $createdAt->between($dateRange[0], $dateRange[1]);
        });

        Log::info('Filtered IVR stats for overall report', [
            'dateRange' => $dateRange,
            'filtered_count' => $ivrStats->count(),
        ]);
    }

    $totalSelections = $ivrStats->count();

    return $ivrOptions->map(function ($ivrOption) use ($ivrStats, $totalSelections) {
        $matchedStats = $ivrStats->where('ivr_option_id', $ivrOption->id);

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
    

}
