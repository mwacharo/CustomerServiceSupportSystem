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

        $incomingCalls = (clone $callHistories)->where('agentId', $user->id)->count();
        Log::debug('Incoming calls calculated', ['incoming_calls' => $incomingCalls]);

        $outgoingCalls = (clone $callHistories)->where('callerNumber', $clientName)->count();
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
            'client_name' => $clientName,
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
}
