<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Candidatura;
use App\Models\Vaga;

class RelatorioController extends Controller
{
    public function gerarRelatorioResumo($idVaga){
        $vaga = Vaga::select(
                            'funcao',
                            DB::raw("(DATE_FORMAT(data_inicio,'%d-%m-%Y')) as data_inicio"),
                            DB::raw("(DATE_FORMAT(data_fim,'%d-%m-%Y')) as data_fim")
                        )
                        ->find(base64_decode($idVaga));

        //Pegar a contagem dos candidatos (Geral | Masculinos | Femininos) e suas Percentagens
        $candidatos = Candidatura::getEstatisticaVaga($idVaga);

            if ($candidatos->total > 0) {
                $candidatos->perc_Masculinos = round((100 * $candidatos->masculinos)/$candidatos->total);
                $candidatos->perc_Femininos = round((100 * $candidatos->femininos)/$candidatos->total);
            }else{
                $candidatos->perc_Masculinos = 0;
                $candidatos->perc_Femininos = 0;
            }

        //Pegar Localidades e Candidatos pertecentes a mesma, bem como as suas percentagens
        $candProvincia = DB::table('candidatura as cand')
                    ->join('opcao as op_prov_candidato','op_prov_candidato.id','=','cand.provincia_candidatura')
                    ->select('op_prov_candidato.opcao as prov_candidato', DB::raw('COUNT(*) as total'))
                    ->where('cand.vaga_id',base64_decode($idVaga))
                    ->groupBy('op_prov_candidato.opcao')
                    ->orderBy('total','desc')
                    ->get();
            if(count($candProvincia) > 0){ //Quando é de várias províncias
                $totalCandidaturas = $candProvincia->sum('total');

                $candProvincia->transform(function ($item) use ($totalCandidaturas) {
                    $item->percent = round(($item->total / $totalCandidaturas) * 100, 2); // 2 decimal places
                    return $item;
                });
            }else{ //Apenas Luanda
                $candProvincia = collect([
                                            (object)[
                                                'prov_candidato' => 'Luanda',
                                                'total' => $candidatos->total,
                                                'percent' => 100
                                            ]
                                        ]);
            }

        /*Potencias candidatos em função de uma escala
            Alto: 85% a 100%
            Médio: 60% a 84%
            Baixo: 0% a 59%
        */
        $perguntas = DB::table('perguntas as perg')
                    ->leftjoin('pontuacao as pot','pot.pergunta_id','=','perg.id')
                    ->select('perg.id','perg.pergunta', DB::raw('MAX(pot.ponto) as max_ponto'))
                    ->where('vaga_id', base64_decode($idVaga))
                    ->where('perg.is_pontuado', 1)
                    ->groupBy('perg.id','perg.pergunta')
                    ->get();
            $totalMaxPonto = $perguntas->sum('max_ponto');
            
            if($totalMaxPonto >0){
                
                //Converter as percentagem em pontuações
                $baixoA = round((0*$totalMaxPonto)/100);
                $baixoB = round((59*$totalMaxPonto)/100);
                $medioA = round((60*$totalMaxPonto)/100);
                $medioB = round((84*$totalMaxPonto)/100);
                $altoA = round((85*$totalMaxPonto)/100);
                $altoB = round((100*$totalMaxPonto)/100);

                //dd($baixoA.'='.$baixoB.'='.$medioA.'='.$medioB.'='.$altoA.'='.$altoB);

                $candidaturas = DB::table('candidatura')
                                ->selectRaw("
                                    CASE
                                        WHEN pontuacao BETWEEN $baixoA AND $baixoB THEN 'Baixa'
                                        WHEN pontuacao BETWEEN $medioA AND $medioB THEN 'Média'
                                        WHEN pontuacao BETWEEN $altoA AND $altoB THEN 'Alta'
                                    END as faixa,
                                    COUNT(*) as total
                                ")
                                ->where('vaga_id',base64_decode($idVaga))
                                ->groupBy('faixa')
                                ->orderBy('faixa')
                                ->get();

                    //Separar e verificar antes de mandar para front na construção do PDF, a fim de evitar erro qndo uma faixa é 0 ou não tiver candidatos
                    $faixas = [];

                    $Baixa = $candidaturas->first(fn($item) => $item->faixa === 'Baixa');
                    if($Baixa){
                        $faixas['Baixa'] = $Baixa->total;
                    }else{
                        $faixas['Baixa'] = 0;
                    }
                    
                    $Media = $candidaturas->first(fn($item) => $item->faixa === 'Média');
                    if($Media){
                        $faixas['Media'] = $Media->total;
                    }else{
                        $faixas['Media'] = 0;
                    }
                    
                    $Alta = $candidaturas->first(fn($item) => $item->faixa === 'Alta');
                    if($Alta){
                        $faixas['Alta'] = $Alta->total;
                    }else{
                        $faixas['Alta'] = 0;
                    }
            }

        $data = ['vaga'=>$vaga,'candidatos'=>$candidatos,'candProvincia'=>$candProvincia,'faixas'=>$faixas];
        $pdf = Pdf::loadView('pdf.RelatorioResumo', $data);
        return $pdf->stream('Relatorio_Resumo_'.date('d-m-Y h:m:i').'.pdf'); 
    }
}
