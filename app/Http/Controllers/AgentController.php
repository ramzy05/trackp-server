<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        // $agent->makeVisible('password');
        return response()->json($agent, 200);
    }

    public function updateLocation(Request $request, $userId)
    {
        $agent = User::where('role', 'agent')->find($userId);

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        $agent->lat = $request->input('lat');
        $agent->lng = $request->input('lng');
        $agent->save();

        return response()->json($agent, 200);
    }

}
