<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Collection;

class CollectionController extends Controller
{
    public function collecte(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'center_lat' => 'required|numeric',
            'center_long' => 'required|numeric',
            'radius' => 'required|numeric',
            'frequency' => 'required|numeric',
            'period' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::where(function($query) use ($userId) {
            $query->where('id', $userId)
                  ->orWhere('email', $userId)
                  ->orWhere('username', $userId);
        })->where('role', 'agent')->first();

        if (!$user) {
            return response()->json(['error' => 'User not found or not an agent'], 401);
        }

        $collection = $user->collections()->create([
            'center_lat' => $request->center_lat,
            'center_long' => $request->center_long,
            'radius' => $request->radius,
            'frequency' => $request->frequency,
            'period' => $request->period,
        ]);

        return response()->json(['collection' => $collection], 200);
    }

    public function getAllLocations(Request $request, $collectionId)
{
    $collection = Collection::find($collectionId);

    if (!$collection) {
        return response()->json(['error' => 'Collection not found'], 404);
    }

    $locations = $collection->locations()->get();

    if (!$locations) {
        return response()->json(['error' => 'No locations found for this collection'], 404);
    }

    return response()->json(['locations' => $locations], 200);
}


    public function getLastLocation(Request $request, $collectionId)
    {
        $collection = Collection::find($collectionId);

        if (!$collection) {
            return response()->json(['error' => 'Collection not found'], 404);
        }

        $location = $collection->locations()->latest()->first();

        if (!$location) {
            return response()->json(['error' => 'No location found for this collection'], 404);
        }

        return response()->json(['location' => $location], 200);
    }

}
