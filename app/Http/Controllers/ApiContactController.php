<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ApiContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }
    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create($request->validated());
    
        return new ContactResource($contact);
    }

    public function show(string $id)
    {
        $contact = Contact::findOrFail($id);
    
        $this->authorize('view', $contact);
    
        return new ContactResource($contact);
    }
    
    public function update(UpdateContactRequest $request, string $id)
    {
        $contact = Contact::findOrFail($id);
    
        $this->authorize('update', $contact);
    
        $contact->update($request->validated());
    
        return new ContactResource($contact);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
