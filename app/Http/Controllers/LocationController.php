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
            'long' => 'required|numeric',
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
            'long' => $request->long,
            'collection_id' => $collection->id,
        ]);

        return response()->json(['location' => $location], 200);
    }

}
