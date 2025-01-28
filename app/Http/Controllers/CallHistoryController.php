<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallHistoryRequest;
use App\Http\Requests\UpdateCallHistoryRequest;
use App\Models\CallHistory;

class CallHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCallHistoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CallHistory $callHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CallHistory $callHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCallHistoryRequest $request, CallHistory $callHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CallHistory $callHistory)
    {
        //
    }
}
