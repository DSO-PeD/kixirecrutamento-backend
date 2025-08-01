<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pergunta;


class PerguntasController extends Controller
{
    /**
     * 1-Texto
     * 2-Número
     * 3-Email
     * 4-Multipla escolha
     * 5-Ficheiro
     * 6-Data
     */
    public function registarPergunta(Request $request){
        try{
            $request->validate([
                'pergunta' => 'required|min:4|max:120',
            ],[
                'pergunta.required'=>'A funçao deve ser fornecida.'
            ]);

            $exist = Pergunta::whereRaw('LOWER(pergunta) = LOWER(?)',$request->pergunta)->exists();
            if($exist)
                return response()->json('Esta vaga já se encontra registada',201);

            $exist = Pergunta::where('ordem',$request->ordem)->exists();
            if($exist)
                return response()->json('Escolha outra ordem, está já foi associada',201);


            $pergunta = new Pergunta;
            $pergunta->pergunta = $request->pergunta;
            $pergunta->estado = 1;
            $pergunta->is_pontuado = $request->isPontuado;
            $pergunta->ordem = $request->ordem;
            $pergunta->tipo = $request->tipo;

            if($pergunta->save()){
                return response()->json('Registado com sucesso');
            }
        } catch(Exception $e){
            return response()->json('Houve erro ao registar, verifique e tente novamente.',500);
        }
    }

    public function listarPerguntas(){
        $perguntas = Pergunta::select(
                            'id',
                            'pergunta',
                            'estado',
                            'is_pontuado',
                            'ordem',
                            DB::raw("
                                CASE tipo
                                    WHEN 1 THEN 'Texto'                  
                                    WHEN 2 THEN 'Número'                  
                                    WHEN 3 THEN 'Email'                  
                                    WHEN 4 THEN 'Multipla escolha'                  
                                    WHEN 5 THEN 'Ficheiro'  
                                    WHEN 6 THEN 'Data'  
                                    WHEN 7 THEN 'Text Area'  
                                END AS tipo
                            ")
                    )
                    ->orderBy('ordem')
                    ->get();
        return response()->json($perguntas);
    }

    /**
     * Retorna as perguntas que são pontuaveis e de multiseleção
     */
    public function listarPerguntasPontuaveisVaga($idVaga){
        $perguntas = DB::table('perguntas as perg')
                    ->leftJoin('pontuacao as pot','pot.pergunta_id','=','perg.id')
                    ->select(
                        'perg.id',
                        'perg.ordem',
                        'perg.pergunta',
                        'perg.estado'
                    )
                    ->where('pot.vaga_id', base64_decode($idVaga))
                    ->where('perg.is_pontuado', 1)
                    ->distinct('perg.pergunta')
                    ->orderBy('perg.ordem')
                    ->get();

        return response()->json($perguntas);
    }

    /**
     * Perguntas para serem escolhidas numa determinada vaga
     */
    public function listarPerguntasActivas($idVaga){
        $perguntas = DB::table('perguntas as p')
                    ->leftJoinSub(
                        DB::table('pontuacao')->where('vaga_id', base64_decode($idVaga)),
                        'po',
                        'po.pergunta_id',
                        'p.id'
                    )
                    ->select(
                        'p.id',
                        'p.pergunta',
                        'p.ordem',
                        'p.estado',
                        DB::raw('CASE WHEN po.id IS NOT NULL THEN 1 ELSE 0 END as is_selecionado')
                    )
                    ->distinct()
                    ->orderBy('p.ordem')
                    ->get();
    
        return response()->json($perguntas);
    }

    public function eliminarPergunta($idPergunta){
        $isDeleted = Pergunta::where('id',$idPergunta)->delete();

        if($isDeleted){
            return response()->json('Eliminado com sucesso');
        }
        return response()->json('Erro ao eliminar a pergunta, verifique e tente novamente');
    }
    
    public function mudarEstadoPergunta($idPergunta){
        $pergunta = Pergunta::Find($idPergunta);

        if (!$pergunta) {
            return response()->json('Pergunta não encontrada', 404);
        }

        $pergunta->estado = ($pergunta->estado == 1) ? 0 : 1;

        if($pergunta->save())
            return response()->json('Alterado com sucesso');
        return response()->json('Houve erro ao alterar o estado');
    }
}
