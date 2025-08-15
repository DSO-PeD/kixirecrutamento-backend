<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pergunta extends Model
{
    use HasFactory;

    public static function contReferenciasCandidato($idCandidato){
        //As referencias vêm: |Pessoa1|Pessoa2|Pessoa3, ele conta quantas referencias temos
        $referenciasCandidato = DB::table('candidatura')->where('id',$idCandidato)->value('referencias');
        return substr_count($referenciasCandidato, '|');     
    }

    public static function getPerguntaReferencia($vaga_id){
        return DB::table('perguntas as perg')
                ->leftJoin('opcao as op','op.pergunta_id','=','perg.id')
                ->leftJoin('pontuacao as pont','pont.opcao_id','=','op.id')
                ->where('perg.pergunta','Referências')
                ->where('pont.vaga_id',$vaga_id)
                ->select('op.opcao','pont.ponto')
                ->get();
    }

    
    public static function arrayPerguntasPontuaveis(){
        return [
            38 => 'onde_viu_vaga',
            40 => 'genero',
            49 => 'grau_academico',
            51 => 'ingles',
            53 => 'trabalho_actual',
            54 => 'word',
            55 => 'excel',
            56 => 'referencias',
            57 => 'software_designer',
            58 => 'primavera',
            61 => 'minimo_experiencia',
            62 => 'certificacao',
            63 => 'capacidade_elaborar_planos',
            64 => 'capacidade_redigir_relatorio',
            65 => 'capacidade_avaliar',
            66 => 'analise_processo',
            67 => 'dominio_normas',
            68 => 'provincia_candidatura',
            69 => 'capacidade_monitoria_avaliacao'
        ];
    }
}
