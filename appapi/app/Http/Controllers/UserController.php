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
}
