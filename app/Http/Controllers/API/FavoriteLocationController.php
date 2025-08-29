<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FavoriteLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FavoriteLocationResource;

class FavoriteLocationController extends Controller
{
    // GET /favorites
    public function index()
    {
        $favorites = Auth::user()->favoriteLocations;

        return response()->json([
            'success' => true,
            'message' => 'Favorite locations fetched successfully',
            'data' => FavoriteLocationResource::collection($favorites),
        ], 200);
    }

    // POST /favorites
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $favorite = FavoriteLocation::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favorite created successfully',
            'data' => new FavoriteLocationResource($favorite),
        ], 201);
    }

    // GET /favorites/{id}
    public function show($id)
    {
        $favorite = FavoriteLocation::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Favorite location retrieved successfully',
            'data' => new FavoriteLocationResource($favorite),
        ], 200);
    }

    // PUT/PATCH /favorites/{id}
    public function update(Request $request, $id)
    {
        $favorite = FavoriteLocation::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:50',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
        ]);

        $favorite->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Favorite updated successfully',
            'data' => new FavoriteLocationResource($favorite),
        ], 200);
    }

    // DELETE /favorites/{id}
    public function destroy($id)
    {
        $favorite = FavoriteLocation::where('user_id', Auth::id())->findOrFail($id);
        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorite deleted successfully',
            'data' => null,
        ], 200);
    }
}