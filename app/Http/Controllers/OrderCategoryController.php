<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderCategoryRequest;
use App\Http\Requests\UpdateOrderCategoryRequest;
use App\Models\OrderCategory;

class OrderCategoryController extends Controller
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
    public function store(StoreOrderCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderCategory $orderCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderCategory $orderCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderCategoryRequest $request, OrderCategory $orderCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderCategory $orderCategory)
    {
        //
    }
}
