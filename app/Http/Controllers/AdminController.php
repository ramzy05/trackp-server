<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function updateAgent(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $agent = User::findOrFail($id);

        $agent->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Agent updated successfully', 'user'=> $agent], 200);
    }

    public function deleteAgent($userId)
    {
        $agent = User::where('role', 'agent')->find($userId);

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        $agent->delete();

        return response()->json(['message' => 'Agent deleted successfully'], 200);
    }
}
