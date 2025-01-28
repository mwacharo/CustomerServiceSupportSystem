<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallQueueRequest;
use App\Http\Requests\UpdateCallQueueRequest;
use App\Models\CallQueue;

class CallQueueController extends Controller
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
    public function store(StoreCallQueueRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CallQueue $callQueue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CallQueue $callQueue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCallQueueRequest $request, CallQueue $callQueue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CallQueue $callQueue)
    {
        //
    }
}
