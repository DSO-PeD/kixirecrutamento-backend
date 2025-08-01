<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pontuacao;

class PontuacaoController extends Controller
{
    /** SOMENTE QUANDO PERGUNTA É PONTUAVEL 
     * contListaPonto pega todos dados da opção IdOpcao e Ponto da vaga, também manda nessa lista na ultima posição 
     * a pergunta_id, que deverá ser inserida a cada linha da pontuação, então fazendo contListaPonto - 1, estamos tirando ele do loop
     * 
    */
    public function registarPontuacaoPergunta(Request $request){
        $listaPonto = $request->all();
        $contListaPonto = count($listaPonto);

        if($contListaPonto <= 2){ //Pergunta não pontuável
            $pontuacao = new Pontuacao;
            $pontuacao->ponto = 0;
            $pontuacao->pergunta_id = $listaPonto[0]["value"];
            $pontuacao->vaga_id = $listaPonto[1]["value"];

            if($pontuacao->save()){
                return response()->json("Selecionada com sucesso");
            }
            return response()->json("Houve erro ao pontuar, verifique e tente novamente",201);
        }else{ //Pergunta pontuável
            $pergunta_id = $listaPonto[$contListaPonto-2]["value"]; //Pegar penúltimo valor do array que é a pergunta_id
            $vaga_id = $listaPonto[$contListaPonto-1]["value"]; //Pegar último valor do array que é a vaga_id

            for($i=0;$i<($contListaPonto-2);$i++){ 
                $status = Pontuacao::create([
                    'ponto'=>$listaPonto[$i]["value"],
                    'opcao_id'=>$listaPonto[$i]["id"],
                    'pergunta_id'=>$pergunta_id,
                    'vaga_id'=>$vaga_id
                ]);
            }

            if(is_object($status))
                return response()->json("Selecionada com sucesso");
            else
                return response()->json("Houve erro ao pontuar, verifique e tente novamente",201);
        }
    }

    public function verificarPontuacaoPergunta($pergunta_id,$vaga_id){
        $status = Pontuacao::where('pergunta_id',$pergunta_id)
                ->where('vaga_id',$vaga_id)
                ->exists();
        return response()->json($status);
    }

    public function removerPerguntaVaga($pergunta_id){
        $isDeleted = Pontuacao::where('pergunta_id',$pergunta_id)->delete();

        if($isDeleted){
            return response()->json('Eliminado com sucesso');
        }
        return response()->json('Erro ao eliminar a opção, verifique e tente novamente',404);
    }
}
