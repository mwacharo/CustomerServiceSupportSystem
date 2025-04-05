<?php

namespace App\Services;

use App\Models\CallHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;

class CallStatsService
{
    public function getAgentStats(User $user, ?array $dateRange = null): array
    {
        $clientName = $user->client_name;

        $callHistories = CallHistory::query()
            ->where('agentId', $clientName)
            ->where('isActive', 0)
            ->whereNull('deleted_at');

        if ($dateRange) {
            $callHistories->whereBetween('created_at', $dateRange);
        }

        $totalCalls = (clone $callHistories)->count();
        $incomingCalls = (clone $callHistories)->where('agentId', $user->id)->count();
        $outgoingCalls = (clone $callHistories)->where('callerNumber', $clientName)->count();
        $missedCalls = (clone $callHistories)
            ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->where('agentId', $user->id)
            ->count();
        $callDuration = (clone $callHistories)->sum('durationInSeconds') ?? 0;

        return [
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
    }
}
