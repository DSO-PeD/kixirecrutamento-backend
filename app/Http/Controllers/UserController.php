<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function save(Request $request)
    { 
        $request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]); 

        $randomSenha = Str::random(8);

        $user = new User;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($randomSenha);
        $user->first_login = 1;
        $user->activo = true;

        try{         
            if ($user->save()) {
                return response()->json([
                    'message' => 'Conta criada com sucesso. Senha temporária: ' . $randomSenha
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar a conta, contacte o administrador do sistema.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function all(){
        $users = User::all();

        return response()->json(['data' => $users]);
    }

    public function resetPassword($User_id)
    {
        $user = User::find($User_id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $randomSenha = Str::random(8);
        $user->password = Hash::make($randomSenha);
        $user->first_login = 1;

        if ($user->save()) {
            return response()->json([
                'message' => 'Senha redefinida com sucesso. Nova senha temporária: ' . $randomSenha
            ]);
        }

        return response()->json(['message' => 'Falha ao redefinir a senha.'], 500);
    }

    public function changePassword(Request $request){
    
        $request->validate([
            'UserId' => 'required|exists:users,id',
            'email' => 'required |email|exists:users,email',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:password',
        ]);

        $user = User::find($request->UserId);
        $user->password = Hash::make($request->password);
        $user->first_login = 0;

        if ($user->save()) {
            return response()->json(['message' => 'Senha alterada com sucesso.']);
        }

        return response()->json(['message' => 'Falha ao alterar a senha.'], 500);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where(['email'=>$request->email,'activo'=>1])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        if( $user->first_login == 1){
            return response()->json([
                'message' => 'Primeiro login. Necessário alterar a senha.',
                'first_login' => true,
                'user' => $user
            ]);
        }

        $tokenResult = $user->createToken(
            'api-token',
            ['*'],
            now()->addHours(72) // expires in 72 hours
        );
    
        // The expiration is in the database record
        $accessToken = $tokenResult->accessToken;

        // Return token and expires_at
        return response()->json([
            'token' => $tokenResult->plainTextToken,
            'expires_at' => $accessToken->expires_at,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Utilizador não autenticado'
            ], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout efetuado com sucesso'
        ]);
    }
}