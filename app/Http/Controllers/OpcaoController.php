<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Opcao;

class OpcaoController extends Controller
{
    public function registarOpcao(Request $request){
        try {
            $opcao = new Opcao;
            $opcao->opcao = $request->opcao;
            $opcao->pergunta_id = $request->pergunta_id;

            $exist = Opcao::whereRaw('LOWER(opcao) = LOWER(?)',$request->opcao)
                            ->where('pergunta_id',$request->pergunta_id)
                            ->exists();

            if($exist) return response()->json('Esta opção já se encontra registada',400);
            
            if($opcao->save()){
                return response()->json('Registado com sucesso');
            }
            return response()->json('Erro ao registar, verifique e tente novamente.',400);
        } catch (\Exception $e) {
            Log::error("Error occurred: " . $e->getMessage());
            return response()->json('Erro ao registar, verifique e tente novamente: '+$e);
        }
    }

    public function listarGeralOpcoes(){
        $opcoes = Opcao::select('id','opcao','pergunta_id')->get();
        return response()->json($opcoes);
    }
    
    public function listarOpcoes($idPergunta){
        $opcoes = Opcao::select('id','opcao')->where('pergunta_id',$idPergunta)->get();
        return response()->json($opcoes);
    }

    public function eliminarOpcao($idOpcao){
        $isDeleted = Opcao::where('id',$idOpcao)->delete();

        if($isDeleted){
            return response()->json('Eliminado com sucesso');
        }
        return response()->json('Erro ao eliminar a opção, verifique e tente novamente');
    }
}
