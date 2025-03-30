<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
    

    protected $fillable = [
        'isActive',
        'direction',
        'sessionId',
        'callerNumber',
        'destinationNumber',
        'durationInSeconds',
        'currencyCode',
        'recordingUrl',
        'amount',
        'hangupCause',
        'adminId',
        'agentId',
        'orderNo',
        'notes',
        'nextCallStep',
        'conference',
        'status',
        'clientDialedNumber',
        'callSessionState',
        'callerCountryCode',
        'callerCarrierName',
        'callStartTime',
        'lastBridgeHangupCause'
    ];

    // public function getAttributeCreatedAt()
    // {
    //     return $this->attributes['created_at'] ?? null;
    // }
}
