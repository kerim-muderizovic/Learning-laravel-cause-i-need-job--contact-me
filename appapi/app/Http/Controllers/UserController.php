<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    public function updatePhoto(Request $request, $id)
{
    // $request->validate([
    //     'url' => 'required|url',
    // ]);

    $user = User::findOrFail($id);
    $user->url = $request->url;
    $user->save();

    return response()->json(['message' => 'URL updated successfully', 'user' => $user]);
}

public function updateName(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $user = User::findOrFail($id);
    if(!$user)
    {
        return response()->json(['message' => 'User not found'], 404);
    }
    $user->name = $request->name;
    $user->save();
    return response()->json(['message' => 'Name updated successfully', 'user' => $user], 200);
}


}
