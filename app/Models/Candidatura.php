<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Candidatura extends Model
{
    protected $table = 'candidatura';
    use HasFactory;

    public static function getEstatisticaVaga($idVaga) {
        return DB::table('candidatura as cand')
                ->join('opcao as op_genero','op_genero.id','=','cand.genero')
                ->where('cand.vaga_id',base64_decode($idVaga))
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN opcao='Masculino' THEN 1 ELSE 0 END) as masculinos")
                ->selectRaw("SUM(CASE WHEN opcao='Feminino' THEN 1 ELSE 0 END) as femininos")
                ->selectRaw("FLOOR(AVG(TIMESTAMPDIFF(YEAR, nascimento, CURDATE()))) as idadeMedia")
                ->first();
    }
}
