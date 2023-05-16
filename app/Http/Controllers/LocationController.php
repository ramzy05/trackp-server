<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Collection;
use App\Models\Location;


class LocationController extends Controller
{
    public function addLocation(Request $request, $collectionId)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $collection = Collection::findOrFail($collectionId);

        if (!$collection) {
            return response()->json(['error' => 'Collection not found'], 404);
        }

        $location = Location::create([
            'lat' => $request->lat,
            'lng' => $request->lng,
            'collection_id' => $collection->id,
        ]);

        return response()->json(['location' => $location], 200);
    }

    public function updatePosition(Request $request)
    {
        $agentId = $request->user()->id; // ID de l'agent connecté
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $agent = $request->user();

        $agent->lat = $lat;
        $agent->lng = $lng;
        $agent->save();

        print($agentId);
        // Vérifier si l'agent est associé à une collecte active
        $collection = Collection::where('agent_id', $agentId)
            ->where('has_started', false)
            ->first();

        if ($collection) {
            // Créer une nouvelle location pour la collecte active
            $location = new Location();
            $location->collection_id = $collection->id;
            $location->lat = $lat;
            $location->lng = $lng;
            $location->save();

            return response()->json(['message' => 'Location updated',
                'frequency'=> $collection->frequency,
                'period'=> $collection->period,
                'center_lat'=> $collection->center_lat,
                'center_lng'=> $collection->center_lat,
                'lat'=> $agent->lat,
                'lng'=> $agent->lng,
            ], 200);
        }
        return response()->json(['message' => 'Location updated',
            'lat'=> $agent->lat,
            'lng'=> $agent->lng,
        ], 200);
    }

}
