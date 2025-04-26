<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Models\Template;
use Illuminate\Http\Request;

class ApiTemplateController extends Controller
{
    // public function __construct()
    // {
    //     // Automatically apply authorization based on TemplatePolicy
    //     $this->authorizeResource(Template::class, 'template');
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::latest()->paginate(20);
        return TemplateResource::collection($templates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTemplateRequest $request)
    {
        $template = Template::create($request->validated());

        return new TemplateResource($template);
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        return new TemplateResource($template);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTemplateRequest $request, Template $template)
    {
        $template->update($request->validated());

        return new TemplateResource($template);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully.',
        ]);
    }
}
