<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Collection;
use App\Models\Location;
use App\Mail\OutOfAreaEmail;
use Illuminate\Support\Facades\Mail;

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

    public function startCollection(Request $request, $id)
    {
        // Recherche de la collection par son ID
        $collection = Collection::findOrFail($id);

        // Mise à jour de la valeur de has_started à true
        $collection->has_started = true;
        $collection->save();

        // Renvoi de la collection mise à jour
        return response()->json([
            "collection"=>$collection
        ]);
    }
    public function stopCollection(Request $request, $id)
    {
        // Recherche de la collection par son ID
        $collection = Collection::findOrFail($id);

        $collection->has_started = false;
        $collection->is_finished = true;
        $collection->save();

        // Renvoi de la collection mise à jour
        return response()->json([
            "collection"=>$collection
        ]);
    }

    public function checkAgentLocation(Request $request ,$collectionId)
    {
        // Vérifier si l'agent a une collection active en cours
        $collection = Collection::find($collectionId);

        if (!$collection) {
            return response()->json(['error' => 'Collection not found'], 404);
        }
        $agent = User::findOrFail($collection->agent_id);

        // Récupérer la dernière localisation pour la collection
        $latestLocation = Location::where('collection_id', $collection->id)
            ->latest()
            ->first();

        if (!$latestLocation) {
            return response()->json(['error' => 'No location found for the collection'], 404);
        }

        // Calculer la distance entre la localisation et le centre de la zone
        $centerLat = $collection->center_lat;
        $centerLng = $collection->center_lng;
        $distance = $this->calculateDistance($latestLocation->lat, $latestLocation->lng, $centerLat, $centerLng);

        // Convertir le rayon en mètres
        $radiusInMeters = $collection->radius;

        // Vérifier si l'agent est dans la zone
        $isInArea = $distance <= $radiusInMeters;
        if($isInArea === false){
            $latestLocation->is_in_area = false;
            $latestLocation->save();

            if(!$collection->is_violated){
                Mail::to($request->user())->send(new OutOfAreaEmail($agent));
                $collection->is_violated = true;
                $collection->save();
            }else{

            }
        }

        // Vérifier si la collecte doit être arrêtée
        $collecteStop = false;
        $currentDateTime = now();
        $endDateTime = Carbon::parse($collection->end_date);

        if ($currentDateTime >= $endDateTime) {
            $collecteStop = true;
        }

        // Préparer la réponse JSON
        $response = [
            'location' => $latestLocation,
            'distance' => $distance,
            'isInArea' => $isInArea,
            'collecteStop' => $collecteStop,
        ];

        if ($collecteStop) {
            //stop collecte
            $collection->has_started = false;
            $collection->is_finished = true;
            $collection->save();

            // return response()->json(['collecteStop' => true], 200);
        }
        return response()->json($response, 200);

    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Calculer la distance entre deux coordonnées en utilisant la formule Haversine
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }

}
