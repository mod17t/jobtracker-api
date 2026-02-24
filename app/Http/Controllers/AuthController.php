<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //creation de compte
    public function register(Request $request){

        //vérifier si les données sont bonnes
        $request->validate([
            'name'=> 'required|string|max:255',
            'email'=> 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        //si les données sont bonne crée un user
        $user = User::create([
            'name' => $request-> name,
            'email' => $request-> email,
            'password' => $request-> password,
        ]);
        
        // puis génerer un token pour l'utilisateur en question
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'compte crée avec succès',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request){

        //verifier les données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user= User::where('email', $request->email)->first();

        if(! $user || ! Hash::check($request->password, $user->password)){
            throw ValidationException  ::withMessages([
                'email' => ['Email ou mot de passe incorrect']
            ]);
        }

        //crée un nouveau token 
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message'=> 'connecter avec succès',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request){

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=> 'Deconnecté']);

    }

    public function me(Request $request){
        return response()->json($request->user());
    }
}
