<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Partner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class PartnerAuthController extends Controller
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

        $partner = Partner::where('email', $request->email)->first();

        if (!$partner || !Hash::check($request->password, $partner->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $partner->createToken('PartnerLoginToken')->plainTextToken;

        $roles = $partner->getRoleNames();
        $permissions = $partner->getAllPermissions()->pluck('name');
        $services = $partner->services()->get();

        return response()->json([
            'token' => $token,
            'roles' => $roles,
            'permissions' => $permissions,
            'services' => $services,
        ]);
    }
}
