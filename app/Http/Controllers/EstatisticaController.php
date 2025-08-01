<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vaga;
use App\Models\Candidatura;

class EstatisticaController extends Controller
{
    public function mostrarEstatisticaVaga($idVaga){
        $candidaturas = Candidatura::getEstatisticaVaga($idVaga);
        return response()->json($candidaturas);
    }

    public function pieChartMostrar(){
        //Contagem de vagasv Registadas, Em curso e Fechadas 1:Em curso | 2: Fechadas | 3: Não publicadas
        $vagas = DB::table('vagas')->get();
        $contVagas = [$vagas->count(),$vagas->where('estado',1)->count(),$vagas->where('estado',2)->count(),$vagas->where('estado',0)->count()];

        //Filtro por genero
        $candGenero = DB::table('candidatura as cand')
                        ->join('opcao as op_genero','op_genero.id','=','cand.genero')
                        ->select('op_genero.opcao as genero', DB::raw('COUNT(*) as total'))
                        ->groupBy('op_genero.opcao')
                        ->get()
                        ->pluck('total', 'genero');

                    $masculino = $candGenero->get('Masculino', 0);
                    $feminino = $candGenero->get('Feminino', 0);
        
                    $generos = [$masculino,$feminino]; 
        
        //Filtro quantos trabalham
        $candTrabalhoActual = DB::table('candidatura as cand')
                        ->join('opcao as op_trabalho_actual','op_trabalho_actual.id','=','cand.trabalho_actual')
                        ->select('op_trabalho_actual.opcao as trabalho_actual', DB::raw('COUNT(*) as total'))
                        ->groupBy('op_trabalho_actual.opcao')
                        ->get()
                        ->pluck('total', 'trabalho_actual');

                    $sim = $candTrabalhoActual->get('Sim', 0);
                    $nao = $candTrabalhoActual->get('Não', 0);
                        
                    $trabalhoActual = [$sim,$nao]; //POS: 0 = Trabalham || POS: 1 = Não trabalham
        
        //Filtro onde viu a vaga
        $candOndeViu = DB::table('candidatura as cand')
                        ->join('opcao as op_ondeViu','op_ondeViu.id','=','cand.onde_viu_vaga')
                        ->select('op_ondeViu.opcao as onde_viu', DB::raw('COUNT(*) as total'))
                        ->groupBy('op_ondeViu.opcao')
                        ->get();

        $arrayOndeViuRedes = array();    
        $arrayOndeViuCont = array();    
        foreach($candOndeViu as $cand){
            array_push($arrayOndeViuRedes,$cand->onde_viu);
            array_push($arrayOndeViuCont,$cand->total);
        }
        $ondeViu = [$arrayOndeViuRedes,$arrayOndeViuCont];

        return response()->json(['contVagas'=>$contVagas,'pieGeneros'=>$generos,'pieOndeViu'=>$ondeViu,'pieTrabalhoActual'=>$trabalhoActual]);
    }

    public function barChartMostrar(){
        //Grafico grau academico 
        $candGrauAcad = DB::table('candidatura as cand')
                    ->join('opcao as op_grau_acad','op_grau_acad.id','=','cand.grau_academico')
                    ->select('op_grau_acad.opcao as grau_acad', DB::raw('COUNT(*) as total'))
                    ->groupBy('op_grau_acad.opcao')
                    ->get();
        
        $arrayGrauText = array();
        $arrayGrauCont = array();

        foreach($candGrauAcad as $cand){
            array_push($arrayGrauText,$cand->grau_acad);
            array_push($arrayGrauCont,$cand->total);
        }
        $grau_academico = [$arrayGrauText,$arrayGrauCont];

        //Grafico dominio de inglês
        $candIngles = DB::table('candidatura as cand')
            ->join('opcao as op_ingles','op_ingles.id','=','cand.ingles')
            ->select('op_ingles.opcao as ingles', DB::raw('COUNT(*) as total'))
            ->groupBy('op_ingles.opcao')
            ->get();

        $arrayInglesText = array();
        $arrayInglesCont = array();

        foreach($candIngles as $cand){
            array_push($arrayInglesText,$cand->ingles);
            array_push($arrayInglesCont,$cand->total);
        }
        $ingles = [$arrayInglesText,$arrayInglesCont];

        return response()->json(['grau_academico'=>$grau_academico,'ingles'=>$ingles]);
    }
}
