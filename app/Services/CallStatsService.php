<?php

namespace App\Services;

use App\Models\CallHistory;
use App\Models\IvrOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;
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
            ->where('adminId', $user->id)
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
        $ivrStats = CallHistory::all(); 
        $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats);
    

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




        $query = CallHistory::with('agent:id,name');

        // Apply filters
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('created_at', [$filters['startDate'], $filters['endDate']]);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        // Get all filtered data
        $callHistories = $query->get();

        $ivrOptions = IvrOption::all();
        $ivrStats = CallHistory::all(); 

        $ivrAnalysis = $this->analyzeIvrStatistics($ivrOptions, $ivrStats);


        // Group by agentId and aggregate with collection methods
        return $callHistories->groupBy('agentId')->map(function ($calls, $agentId) {
            return [
                'agent' => optional($calls->first()->agent)->name ?? 'N/A',
                'total_calls' => $calls->count(),
                'answered' => $calls->where('status', 'Answered')->count(),
                'missed' => $calls->where('status', 'Missed')->count(),
                'escalated' => $calls->where('status', 'Escalated')->count(),
                'total_duration' => $calls->sum('durationInSeconds') ?? 0,
                // 'ivr_analysis' => $ivrAnalysis,
            ];
        })->values(); // Reset keys
    }



    public function analyzeIvrStatistics(Collection $ivrOptions, Collection $ivrStats): Collection
{
    return $ivrOptions->map(function ($ivrOption) use ($ivrStats) {
        // Filter stats matching the current IVR option
        $matchedStats = $ivrStats->where('agentId', $ivrOption->id);

        // Add computed values
        $ivrOption->total_selected = $matchedStats->count();
        $ivrOption->total_duration = $matchedStats->sum('durationInSeconds') ?? 0;

        return $ivrOption;
    });
}
}
