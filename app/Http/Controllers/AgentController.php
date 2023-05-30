<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Collection;
use App\Models\Location;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::where('role', 'agent')->get();

        return response()->json($agents, 200);
    }

    public function show($userId)
    {
        $agent = User::where('role', 'agent')->find($userId);

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }
        return response()->json($agent, 200);
    }

    public function updateLocation(Request $request, $userId)
    {
        $agent = User::where('role', 'agent')->find($userId);

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        // Check if the agent is associated with an active collection
        $collection = $agent->collections()
            ->where('has_started', true)
            ->where('is_finished', false)
            ->first();

        if ($collection) {

            $lat = floatval($request->input('lat'));
            $lng = floatval($request->input('lng'));
            // Create a new location entry
            $location = new Location();
            $location->collection_id = $collection->id;
            $location->lat = $lat;
            $location->lng = $lng;
            $location->save();

            // Update agent's coordinates
            $agent->lat = $lat;
            $agent->lng = $lng;
            $agent->save();

            return response()->json([
                'agent'=>$agent,
                'collection'=>$collection
            ], 200);
        }

        return response()->json([
            'agent'=>$agent
        ], 200);
    }


    public function getAgentsWithLatestIncompleteCollection()
    {
        $agents = User::where('role', 'agent')->get();

        $agentsWithCollections = [];

        foreach ($agents as $agent) {
            $latestCollection = $agent->collections()
                ->where('is_finished', false)
                ->latest('created_at')
                ->first();

            if ($latestCollection) {
                $agentsWithCollections[] = [
                    'agent' => $agent,
                    'collection' => $latestCollection,
                ];
            }
        }

        return response()->json([
            'agentsWithCollections' => $agentsWithCollections,
        ]);
    }
    public function agentsWithPendingCollections(Request $request)
    {
        $agents = User::where('role', 'agent')->get();

        $tempAgents = [];

        foreach ($agents as $agent) {
            $latestCollection = Collection::where('agent_id', $agent->id)
                ->where('is_finished', false)
                ->latest('created_at')
                ->first();

            if ($latestCollection) {
                $agent->collection = $latestCollection;
                $tempAgents[] = $agent;
            }
        }

        $agents = $tempAgents;

        return response()->json([
            'agents' => $agents
        ]);
    }

    public function agentsWithNoCollections(Request $request)
    {
        $agents = User::where('role', 'agent')
            ->whereDoesntHave('collections', function ($query) {
                $query->where('is_finished', false);
            })
            ->with('collections')
            ->get();

        if ($agents->count() === 1) {
            $agents = [$agents];
        }

        return response()->json([
            'agents' => $agents,
        ]);
    }


}
