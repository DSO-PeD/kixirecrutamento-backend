<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Helper extends Model
{
    use HasFactory;

    public static function saveAnexo($request){
        $ficheiro = null;
        if ($request->hasFile('anexo')) {
            $pasta = public_path('uploads');
            $anexo = $request->file('anexo');
            $nome = time().'.'.$anexo->getClientOriginalExtension();

            $anexo->move($pasta,$nome);
            $ficheiro = $pasta.'/'.$nome;           
        }else{
            $ficheiro='';
        }
        return $ficheiro;
    }
}
