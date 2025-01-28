<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiCallAgentController extends Controller
{
    //



    public function getCallAgentList()
    {

        $call_agents = DB::table('call_agents')
            ->where('deleted_at', null)
            ->get();

        return json_encode($call_agents);

    }

    public function getCallAgentListAvailable()
    {

        $call_agents = DB::table('call_agents')
            ->where('status', 'available')
            ->where('deleted_at', null)
            ->get();

        return json_encode($call_agents);

    }

    public function getCallAgentDetails(Request $request)
    {

        $admin_id = $request->id;
        $call_agent = DB::table('call_agents')
            ->where('admin_id', $admin_id)
            ->where('deleted_at', null)
            ->first();

        if($call_agent){

            $admin = DB::table('admins')
                ->where('id', $admin_id)
                ->where('deleted_at', null)
                ->first();

            $json_array = array(
                'id' => $call_agent->id,
                'phone_number' => $call_agent->phone_number,
                'client_name' => $call_agent->client_name,
                'admin_id' => $call_agent->admin_id,
                'status' => $call_agent->status,
                'sessionId' => $call_agent->sessionId,
                'token' => $call_agent->token,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'email' => $admin->email,
                'role' => $admin->role,
                'created_at' => $call_agent->created_at,
                'updated_at' => $call_agent->updated_at,

            );

            $response = $json_array;
            return json_encode($response);
        }


    }

    public function getCallAgentDetails2(Request $request)
    {

        $id = $request->id;
        $call_agent = DB::table('call_agents')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->first();

        return json_encode($call_agent);
    }

    public function createCallAgentDetails(Request $request){


        $call_agent_object = new Officer();
        $call_agent_created = $call_agent_object->create([
            'name'=>$request->name,
            'address'=>$request->address,
            'phone_number'=>$request->phone_number,
            'email'=>$request->email,
            'country_id'=>$request->country_id,
        ]);

        if($call_agent_created){

            $json_array = array(
                'success' => 1,
                'redirect' => route('admin.call-agent')
            );

            $response = $json_array;
            return json_encode($response);

        }else{

            $json_array = array(
                'success' => 0,
            );

            $response = $json_array;
            return json_encode($response);

        }
    }

    public function editCallAgentDetails(Request $request){

        $update = DB::table('call_agents')
            ->where('id', $request->id)
            ->update([
                'phone_number'=>$request->phone_number,
                'client_name'=>$request->client_name,
                'admin_id'=>$request->admin_id,
                'status'=>$request->status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if($update) {

            $json_array = array(
                'success' => 1,
            );

            $response = $json_array;
            return json_encode($response);

        } else {

            $json_array = array(
                'success' => 0,
            );

            $response = $json_array;
            return json_encode($response);
        }

    }

    public function editCallAgentStatus(Request $request){

        $admin_id = $request->id;
        $call_agent = DB::table('call_agents')
            ->where('admin_id', $admin_id)
            ->where('deleted_at', null)
            ->first();

        if($call_agent){

            $update = DB::table('call_agents')
                ->where('id', $call_agent->id)
                ->update([
                    'status'=>$request->status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if($update) {

                $json_array = array(
                    'success' => 1,
                );

                $response = $json_array;
                return json_encode($response);

            } else {

                $json_array = array(
                    'success' => 0,
                );

                $response = $json_array;
                return json_encode($response);
            }

        }else{

            $json_array = array(
                'success' => 0,
            );

            $response = $json_array;
            return json_encode($response);
        }


    }

    public function deleteCallAgentDetails(Request $request){

        $update = DB::table('call_agents')
            ->where('id', $request->id)
            ->update([
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

        if($update) {

            $call_agent = DB::table('call_agents')
                ->where('id', $request->id)
                ->first();

            // TODO - Redirect route after delete
            $json_array = array(
                'success' => 1,
                'redirect' => route('admin.call_agent')
            );

            $response = $json_array;
            return json_encode($response);

        } else {

            $json_array = array(
                'success' => 0,
            );

            $response = $json_array;
            return json_encode($response);
        }

    }

    public function getCallAgentSummary(Request $request){

        $admin_id = $request->id;
        $summary_call_completed = DB::table('call_histories')
            ->where('adminId', $admin_id)
            ->where('isActive', 0)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $summary_call_duration = DB::table('call_histories')
            ->where('adminId', $admin_id)
            ->where('isActive', 0)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->sum('durationInSeconds');

        $summary_call_missed = DB::table('call_histories')
            ->where('adminId', $admin_id)
            ->where('isActive', 0)
            ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->whereDate('created_at', Carbon::today())
            ->where('deleted_at', null)
            ->count();

        $summary_call_waiting = DB::table('call_histories')
            ->where('isActive', 1)
            ->where('nextCallStep', 'enqueue')
            ->where('conference', '!=', null)
            ->where('deleted_at', null)
            ->count();

        $json_array = array(
            'summary_call_completed' => $summary_call_completed,
            'summary_call_duration' => $summary_call_duration,
            'summary_call_missed' => $summary_call_missed,
            'summary_call_waiting' => $summary_call_waiting,
        );

        $response = $json_array;
        return json_encode($response);

    }


}
