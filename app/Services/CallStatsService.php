<?php

namespace App\Services;

use App\Models\CallHistory;
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

        Log::debug('Client name retrieved', ['client_name' => $phone_number]);

        $callHistories = CallHistory::query()
            ->where('callerNumber', $phone_number)
            // ->where('isActive', 0)
            ->whereNull('deleted_at');

        if ($dateRange) {
            Log::debug('Applying date range filter', ['date_range' => $dateRange]);
            $callHistories->whereBetween('created_at', $dateRange);
        }

        $totalCalls = (clone $callHistories)->count();
        Log::debug('Total calls calculated', ['total_calls' => $totalCalls]);

        Log::debug('User ID', ['user_id' => $user->id]);
        $incomingCalls = (clone $callHistories)->where('adminId', $user->id)->count();
        Log::debug('Incoming calls calculated', ['incoming_calls' => $incomingCalls]);

        $outgoingCalls = (clone $callHistories)->where('callerNumber', $phone_number)->count();
        Log::debug('Outgoing calls calculated', ['outgoing_calls' => $outgoingCalls]);

        $missedCalls = (clone $callHistories)
            ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->where('agentId', $user->id)
            ->count();
        Log::debug('Missed calls calculated', ['missed_calls' => $missedCalls]);

        $callDuration = (clone $callHistories)->sum('durationInSeconds') ?? 0;
        Log::debug('Call duration calculated', ['call_duration' => $callDuration]);

        $result = [
            'id' => $user->id,
            'phone_number' => $user->phone_number,
            'status' => $user->status,
            'sessionId' => $user->sessionId,
            // 'token' => $user->token,
            'summary_call_completed' => $totalCalls,
            'summary_inbound_call_completed' => $incomingCalls,
            'summary_outbound_call_completed' => $outgoingCalls,
            'summary_call_duration' => $callDuration,
            'summary_call_missed' => $missedCalls,
            'updated_at' => $user->updated_at,
        ];

        Log::info('Agent stats fetched successfully', ['result' => $result]);

        return $result;
    }



    /**
     * Get the summary of call agents with filters.
     */
    // public function getAgentListSummaryFilter(Request $request)
    // {
    //     $call_date = $request->call_date;
    //     $custom_date = $request->custom_date;
    //     $custom_start_date = $request->custom_start_date;
    //     $custom_end_date = $request->custom_end_date;


    //     $json_results = [];
    //     // $call_agents = User::role('callCentre') 
    //     //     ->whereNull('deleted_at')
    //     //     ->orderBy('client_name')
    //     //     ->get();

    //     $call_agents = User::all();

    //     foreach ($call_agents as $agent) {
    //         $admin_name = $agent->admin ? $agent->admin->first_name . ' ' . $agent->admin->last_name : '';

    //         $baseCallQuery = CallHistory::where('agentId', $agent->client_name)
    //             ->where('isActive', 0)
    //             ->whereNull('deleted_at');


    //         $dateFilter = $this->getDateFilter($call_date, $custom_date, $custom_start_date, $custom_end_date);

    //         $summary_call_completed = (clone $baseCallQuery)->when($dateFilter, fn($q) => $q->whereBetween('created_at', $dateFilter))->count();
    //         $summary_inbound_call_completed = (clone $baseCallQuery)->where('direction', 'inbound')->when($dateFilter, fn($q) => $q->whereBetween('created_at', $dateFilter))->count();
    //         $summary_outbound_call_completed = (clone $baseCallQuery)->where('direction', 'outbound')->when($dateFilter, fn($q) => $q->whereBetween('created_at', $dateFilter))->count();
    //         $summary_call_duration = (clone $baseCallQuery)->when($dateFilter, fn($q) => $q->whereBetween('created_at', $dateFilter))->sum('durationInSeconds');
    //         $summary_call_missed = (clone $baseCallQuery)->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])->when($dateFilter, fn($q) => $q->whereBetween('created_at', $dateFilter))->count();


    //         $json_results[] = [
    //             'id' => $agent->id,
    //             'phone_number' => $agent->phone_number,
    //             'client_name' => $agent->client_name,
    //             'admin_id' => $agent->admin_id,
    //             'admin_name' => $admin_name,
    //             'status' => $agent->status,
    //             'sessionId' => $agent->sessionId,
    //             'token' => $agent->token,
    //             'summary_call_completed' => $summary_call_completed,
    //             'summary_inbound_call_completed' => $summary_inbound_call_completed,
    //             'summary_outbound_call_completed' => $summary_outbound_call_completed,
    //             'summary_call_duration' => $summary_call_duration,
    //             'summary_call_missed' => $summary_call_missed,

    //         ];
    //     }

    //     return response()->json($json_results);
    // }


}
