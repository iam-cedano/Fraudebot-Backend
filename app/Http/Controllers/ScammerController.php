<?php

namespace App\Http\Controllers;

use App\Models\Scammer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScammerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Scammer::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'iso_country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer = Scammer::create($request->all());

        return response()->json($scammer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scammer $scammer)
    {
        return response()->json($scammer->load(['profiles', 'paymentMethods', 'organizations']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'iso_country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer->update($request->all());

        return response()->json($scammer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scammer $scammer)
    {
        $scammer->delete();

        return response()->json(null, 204);
    }
}
