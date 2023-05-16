<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Collection;

class CollectionController extends Controller
{
    public function collecte(Request $request, $agentId)
    {
        $validator = Validator::make($request->all(), [
            'center_lat' => 'required|numeric',
            'center_lng' => 'required|numeric',
            'radius' => 'required|numeric',
            'frequency' => 'required|numeric',
            'period' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = $request->user();
        $agent = User::where(function($query) use ($agentId) {
            $query->where('id', $agentId)
                  ->orWhere('email', $agentId)
                  ->orWhere('username', $agentId);
        })->where('role', 'agent')->first();

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 401);
        }

        $collection = $user->collections()->create([
            'center_lat' => $request->center_lat,
            'center_lng' => $request->center_lng,
            'radius' => $request->radius,
            'frequency' => $request->frequency,
            'period' => $request->period,
            'agent_id' => $agent->id
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
