<?php

use App\Http\Controllers\ApiBranchController;
use App\Http\Controllers\ApiCallAgentController;
use App\Http\Controllers\ApiPermissionsController;
use App\Http\Controllers\ApiRolesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\ApiCallCentreController;
use App\Http\Controllers\ApiIvrOptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});



Route::get('v1/roles', [ApiRolesController::class, 'index']);
Route::get('v1/permissions', [ApiPermissionsController::class, 'index']);



Route::put('/users/{userId}/role', [ApiUserController::class, 'updateRole']);
Route::put('/users/{userId}/permissions', [ApiUserController::class, 'updatePermissions']);


Route::get('v1/users/{id}/permissions', [ApiPermissionsController::class, 'show']);
Route::put('v1/users/{id}/permissions', [ApiPermissionsController::class, 'update']);

Route::get('/v1/roles/{roleId}/permissions', [ApiPermissionsController::class, 'getRolePermissions']);
Route::put('v1/roles/{roleId}/permissions', [ApiPermissionsController::class, 'updateRolePermissions']);



Route::post('/v1/permissions', [ApiPermissionsController::class, 'storePermission']);

// specific permission
Route::get('/v1/permissions/{permissionId}', [ApiPermissionsController::class, 'showPermission']);
Route::put('/v1/permissions/{permissionId}', [ApiPermissionsController::class, 'updatePermission']);
Route::delete('/v1/permissions/{permissionId}', [ApiPermissionsController::class, 'destroyPermission']);


//  roles
Route::post('/v1/roles', [ApiPermissionsController::class, 'storeRole']);
// Route::get('/v1/roles/{roleId}', [ApiPermissionsController::class, 'showRole']);
Route::put('/v1/roles/{roleId}', [ApiPermissionsController::class, 'updateRole']);
Route::delete('/v1/roles/{roleId}', [ApiPermissionsController::class, 'destroyRole']);


//  specific user
Route::get('/users/{userId}/permissions', [ApiPermissionsController::class, 'showUserPermissions']);
Route::put('/users/{userId}/permissions', [ApiPermissionsController::class, 'updateUserPermissions']);


// Show permissions for a specific role
Route::get('/roles/{roleId}/permissions', [ApiPermissionsController::class, 'getRolePermissions']);
Route::put('/roles/{roleId}/permissions', [ApiPermissionsController::class, 'updateRolePermissions']);



Route::apiResource('v1/branches', ApiBranchController::class)->only([
  'index',
  'store',
  'update',
  'destroy'
]);




// callcentre 


Route::post('v1/africastalking-handle-callback', [ApiCallCentreController::class, 'handleVoiceCallback']);
Route::post('v1/africastalking-handle-event', [ApiCallCentreController::class, 'handleEventCallback']);
Route::get('v1/voice-token', [ApiCallCentreController::class, 'generateToken']);
Route::get('v1/call-history', [ApiCallCentreController::class, 'fetchCallHistory']);

// Route::post('/africastalking-handle-selection', [ApiCallCentreController::class, 'handleSelection']);
// make a new call
// Route::post('/v1/call-centre-make-call', [ApiCallCentreController::class, 'makeCall']);



Route::get('/v1/queued-calls', [ApiCallCentreController::class, 'getQueuedCalls']);
Route::post('/v1/dequeue-call', [ApiCallCentreController::class, 'dequeueCall']);

Route::post('/v1/transfer-call', [ApiCallCentreController::class, 'transferCall']);



// Route::get('v1/call-centre-make-call', [ApiCallCentreController::class, 'makeOutboundCall']);
Route::get('v1/call-centre-upload-media', [ApiCallCentreController::class, 'uploadMediaFile']);
Route::get('v1/call-centre-play-welcome', [ApiCallCentreController::class, 'messageBuilderPlayWelcome']);
Route::post('v1/call-centre-transfer-call', [ApiCallCentreController::class, 'transferCall']);
Route::post('v1/call-centre-dequeue-call', [ApiCallCentreController::class, 'dequeueCall']);
Route::post('v1/call-centre-generate-token', [ApiCallCentreController::class, 'getToken']);
Route::get('v1/call-waiting-history', [ApiCallCentreController::class, 'getCallWaitingHistory']);
Route::get('v1/call-agent-history/{id}', [ApiCallCentreController::class, 'getAgentCallHistory']);

// statistics and reports
// /v1/agent-stats

Route::get('v1/agent-stats/{id}', [ApiCallCentreController::class, 'AgentCallStats']);
Route::post('/reports/call-summary', [ApiCallCentreController::class, 'callSummaryReport']);


Route::post('v1/report-call-agent-list-summary-filter', [ApiCallCentreController::class, 'getAgentListSummaryFilter']);
Route::get('v1/report-call-waiting-history', [ApiCallCentreController::class, 'getCallWaitingHistory']);
Route::get('v1/report-call-ongoing-history', [ApiCallCentreController::class, 'getCallOngoingHistory']);
Route::get('v1/report-call-agent-list-summary', [ApiCallCentreController::class, 'getAgentListSummary']);
Route::get('v1/report-call-history-list', [ApiCallCentreController::class, 'getCallHistory']);
Route::post('v1/report-call-history-list-filter', [ApiCallCentreController::class, 'getCallHistoryFilter']);
Route::get('v1/report-call-centre-summary', [ApiCallCentreController::class, 'getSummaryReport']);
Route::get('v1/call-order-history/{phone_number}', [ApiCallCentreController::class, 'callOrderHistory']);

Route::post('v1/call-agent-create', [ApiCallAgentController::class, 'createCallAgentDetails']);
Route::post('v1/call-agent-edit', [ApiCallAgentController::class, 'editCallAgentDetails']);
Route::post('v1/call-agent-edit-status', [ApiCallAgentController::class, 'editCallAgentStatus']);
Route::post('v1/call-agent-delete', [ApiCallAgentController::class, 'deleteCallAgentDetails']);
Route::get('v1/call-agent-list', [ApiCallAgentController::class, 'getCallAgentList']);
Route::get('v1/call-agent-available-list', [ApiCallAgentController::class, 'getCallAgentListAvailable']);
Route::get('v1/call-agent-details/{id}', [ApiCallAgentController::class, 'getCallAgentDetails']);
Route::get('v1/call-agent-details-2/{id}', [ApiCallAgentController::class, 'getCallAgentDetails2']);
Route::get('v1/call-agent-summary/{id}', [ApiCallAgentController::class, 'getCallAgentSummary']);

Route::get('v1/ivr-options', [ApiIvrOptionController::class, 'index']); 
Route::get('v1/ivr-options/search', [ApiIvrOptionController::class, 'search']); 
Route::post('v1/ivr-options', [ApiIvrOptionController::class, 'store']); 
Route::get('v1/ivr-options/{id}', [ApiIvrOptionController::class, 'show']); 
Route::put('v1/ivr-options/{id}', [ApiIvrOptionController::class, 'update']); 
Route::delete('v1/ivr-options/{id}', [ApiIvrOptionController::class, 'destroy']); 

// Route::get('v1/delete-records', [ApiController::class, 'deleteRecords']);
// https://support.solssa.com/api/v1/africastalking-handle-callback
// // https://support.solssa.com/api/v1/handleEventCallback


// Route::post('v1/webhook/whatsapp', [WhatsAppController::class, 'handleIncoming']);
