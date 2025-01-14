<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ResellerAuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reseller = Reseller::where('email', $request->email)->first();

        if (!$reseller || !Hash::check($request->password, $reseller->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $reseller->createToken('ResellerLoginToken')->plainTextToken;

        $roles = $reseller->getRoleNames();
        $permissions = $reseller->getAllPermissions()->pluck('name');

        return response()->json([
            'token' => $token,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
