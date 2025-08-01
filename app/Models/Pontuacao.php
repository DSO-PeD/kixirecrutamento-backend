<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pontuacao extends Model
{
    use HasFactory;
    protected $table = 'pontuacao';

    protected $fillable = ['ponto','pergunta_id', 'opcao_id','vaga_id'];
}
