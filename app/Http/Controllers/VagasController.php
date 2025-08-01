<?php

namespace App\Http\Controllers;

use App\Models\Pontuacao;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VagasController extends Controller
{
    public function registarVaga(Request $request){
        $request->validate([
            'funcao' => 'required|max:60',
            'provincia' => 'required|max:50',
            'data_inicio' => 'required',
            'data_fim' => 'required',
            'descricao' => 'required',
        ],[
            'funcao.required'=>'A funçao deve ser fornecida.'
        ]);

        $exist = Vaga::whereRaw('LOWER(funcao) = LOWER(?)',$request->funcao)
            ->whereRaw('LOWER(provincia) = LOWER(?)',$request->provincia)
            ->where('data_inicio',$request->data_inicio)
            ->exists();

        if($exist)
            return response()->json('Esta vaga já se encontra registada',201);
        

        $vaga = new Vaga;
        $vaga->funcao = $request->funcao;
        $vaga->provincia = $request->provincia;
        $vaga->data_inicio = $request->data_inicio;
        $vaga->data_fim = $request->data_fim;
        $vaga->estado = 0;
        $vaga->descricao = $request->descricao;
        
        if($vaga->save()){
            return response()->json('Registado com sucesso');
        }
        return response()->json('Houve erro ao registar, verifique e tente novamente');
    }
    
    public function listarVagas() {
        $vagas = Vaga::select(
            'id', 'funcao', 'provincia', 'data_inicio', 'data_fim', 'estado',
            DB::raw("
                CASE estado
                    WHEN 0 THEN 'Registada'                  
                    WHEN 1 THEN 'Divulgada'  
                    WHEN 2 THEN 'Fechada'  
                END AS estado
            ")
        )->orderBy('data_fim','desc')
            ->paginate(10)
            ->through(function ($vaga) {
                $vaga->id_encoded = base64_encode((string) $vaga->id);

                return $vaga;
            });
            
        return response()->json($vagas);
    }
    
    public function listarVagasEmCurso(){
        $vagas = Cache::remember('vagas',60, function () {
                        return Vaga::select(
                            'id',
                            'funcao',
                            'provincia',
                            'data_inicio',
                            'data_fim',
                            'descricao'
                        )
                        ->where('estado',1)
                        ->orderBy('data_fim','desc')
                        ->get()
                        ->map(function ($vaga) {
                            $vaga->id_encoded = base64_encode((string) $vaga->id);
                            return $vaga;
                        });
                });
        return response()->json($vagas);
    }
    
    public function pegarVagaById($idVaga){
        $vaga = Vaga::select(
                            'id',
                            'funcao',
                            'provincia',
                            'descricao',
                            'data_inicio',
                            'data_fim',
                            'estado'
                            //DB::raw("(DATE_FORMAT(data_inicio,'%d-%m-%Y')) as data_inicio"),
                            //DB::raw("(DATE_FORMAT(data_fim,'%d-%m-%Y')) as data_fim")
                        )
                        ->where('id',base64_decode($idVaga))
                        ->first();
        return response()->json($vaga);
    }
    
    public function deleteVaga($idVaga){
        $isDeleted = Vaga::where('id',$idVaga)->delete();

        if($isDeleted){
            return response()->json('Eliminado com sucesso');
        }
        return response()->json('Erro ao eliminar a vaga, verifique e tente novamente');
    }

    public function divulgarVaga($idVaga){
        //Verificar se essa vaga contêm pelo menos uma pergunta
        $exist = Pontuacao::where('vaga_id',$idVaga)->exists();
        if(!$exist)
            return response()->json('Esta vaga deve possuir pelo menos uma pergunta, antes de ser divulgada.',201);

        $vaga = Vaga::find($idVaga);
        $vaga->estado = 1;
        
        if($vaga->save()){
            return response()->json('Divulgada com sucesso');
        }
        return response()->json('Houve erro, verifique e tente novamente');
    }

    public function pegaVagaEmCurso($idVaga){
        $vagas = Cache::remember('vagasEmCurso_'.$idVaga,60, function () use ($idVaga) {
                    $query1 = DB::table('vagas as vag')
                        ->join('pontuacao as pot', 'vag.id', '=', 'pot.vaga_id')
                        ->join('perguntas as per', 'per.id', '=', 'pot.pergunta_id')
                        ->where('vag.id', $idVaga)
                        ->where('per.estado', 1)
                        ->where('per.is_pontuado', 0)
                        ->select('per.pergunta', 'per.ordem', 'pot.ponto', DB::raw("'' as opcao"),DB::raw("'' as opcao_id"));
            
                    $query2 = DB::table('vagas as vag')
                        ->join('pontuacao as pot', 'pot.vaga_id', '=', 'vag.id')
                        ->join('opcao as op', 'op.id', '=', 'pot.opcao_id')
                        ->join('perguntas as per', 'per.id', '=', 'op.pergunta_id')
                        ->where('vag.id', $idVaga)
                        ->where('per.estado', 1)
                        ->select('per.pergunta', 'per.ordem', 'pot.ponto', 'op.opcao','op.id as opcao_id');
            
                    return $query1
                        ->union($query2)
                        ->orderBy('ordem') // funciona com union no Laravel
                        ->get();
                });
        return response()->json($vagas);
    }

    public function pegarVagaFormulario($idVaga){
        $idVaga = base64_decode($idVaga);
        $query1 = DB::table('vagas as vag')
                    ->join('pontuacao as pot', 'vag.id', '=', 'pot.vaga_id')
                    ->join('perguntas as per', 'per.id', '=', 'pot.pergunta_id')
                    ->where('vag.id', $idVaga)
                    ->where('vag.estado', 1)
                    ->where('per.estado', 1)
                    ->where('per.is_pontuado', 0)
                    ->select('per.pergunta', 'per.ordem', 'pot.ponto', DB::raw("'' as opcao"), DB::raw("'' as opcao_id"));
            
        $query2 = DB::table('vagas as vag')
                    ->join('pontuacao as pot', 'pot.vaga_id', '=', 'vag.id')
                    ->join('opcao as op', 'op.id', '=', 'pot.opcao_id')
                    ->join('perguntas as per', 'per.id', '=', 'op.pergunta_id')
                    ->where('vag.id', $idVaga)
                    ->where('vag.estado', 1)
                    ->where('per.estado', 1)
                    ->select('per.pergunta', 'per.ordem', 'pot.ponto', 'op.opcao','op.id as opcao_id');
            
        $vagas = $query1
                ->union($query2)
                ->orderBy('ordem')
                ->get();

        $vagas_agrupado = collect($vagas)->groupBy('pergunta')->map(function ($items, $pergunta) {
            return [
                'pergunta' => $pergunta,
                'ordem' => $items->first()->ordem,            
                'opcoes' => $items->filter(fn ($item) => !empty($item->opcao))->map(function ($item) {
                        return [
                            'opcao' => $item->opcao,
                            'opcao_id' => $item->opcao_id,
                            'ponto' => $item->ponto,
                        ];
                    })->values()
            ];
        })->values();
        
        return response()->json($vagas_agrupado);
    }

    //Fecha a vaga em causa
    public function mudarVagaEstado($idVaga){
        $vaga = Vaga::find($idVaga);
        $vaga->estado = 2;

        if($vaga->save()){
            return response()->json('Alterada com sucesso');
        }
        return response()->json('Houve erro ao actualizar, verifique e tente novamente');
    }

    public function testRegistarVaga(){
        $vagas = [];

        for($i=1;$i<=1000;$i++){
            $vagas [] = [
                'funcao' => 'Vaga_'.$i,
                'provincia' => 'Luanda',
                'data_inicio' => date('Y-m-d'),
                'data_fim' => date('Y-m-d'),
                'estado' => 0,
                'descricao' => 'Vaga d eteste apenas'    
            ];
        }

        $status = Vaga::insert($vagas);
        return response()->json('Registado com sucesso: '.$status);
    }
}
