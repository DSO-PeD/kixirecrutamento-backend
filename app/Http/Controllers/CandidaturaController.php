<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidatura;
use App\Models\Pergunta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CandidaturaController extends Controller
{
    public function efectuarCandidatura(Request $request){
        try{
            $request->validate([
                'nome' => 'required|string|min:2|max:100|regex:/^[\pL\s\-]+$/u',
                'numero_bilhete' => 'min:8|max:15',
                'anexo_bilhete' => 'required|mimes:pdf|max:2048', // 2MB = 2048 KB
                'anexo_foto' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
                'links_profissional' => 'max:100',
                'telefone1' => 'min:9|max:9',
                'morada' => 'max:100',
                'area_formacao' => 'max:100',
                'experiencias' => 'max:255',
                //'turnstile_token' => 'required|string'
            ],[
                'experiencias' => 'A experiência profissional não pode exceder 400 caractéres.',
                //'turnstile_token.required' => 'Deve confirmar que não é robô ou actualiza a página'
            ]);

            // Verify Turnstile token with Cloudflare
            /*$response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => env('TURNSTILE_SECRET_KEY'),
                'response' => $request->turnstile_token,
                'remoteip' => $request->ip(),
            ]);
            $result = $response->json();
            if (!$result['success']) {
                return response()->json(['error' => 'Turnstile verification failed.'], 403);
            }*/
        
            // Upload foto
            $paths = [];
            if ($request->hasFile('anexo_foto')) {
                $paths['anexo_foto'] = $request->file('anexo_foto')->store('fotos', 'public');
            }

            // Upload do bilhete
            if ($request->hasFile('anexo_bilhete')) {
                $paths['anexo_bilhete'] = $request->file('anexo_bilhete')->store('bilhetes', 'public');
            }
            
            // Upload do CV
            if ($request->hasFile('anexo_cv')) {
                $paths['anexo_cv'] = $request->file('anexo_cv')->store('curriculos', 'public');
            }

            DB::beginTransaction();
            $status1 = false; $status2 = false;

            $candidatura = new Candidatura;
            $candidatura->nome = $request->nome;
            $candidatura->email = $request->email;
            $candidatura->onde_viu_vaga = $request->onde_viu_vaga;
            $candidatura->nascimento = $request->nascimento;
            $candidatura->genero = $request->genero;
            $candidatura->anexo_foto = $paths['anexo_foto'];
            $candidatura->numero_bilhete = $request->numero_bilhete;
            $candidatura->anexo_bilhete = $paths['anexo_bilhete'];
            $candidatura->anexo_cv = $paths['anexo_cv'];
            $candidatura->links_profissional = $request->links_profissional;
            $candidatura->telefone1 = $request->telefone1;
            $candidatura->telefone2 = $request->telefone2;
            $candidatura->morada = $request->morada;
            $candidatura->grau_academico = $request->grau_academico;
            $candidatura->area_formacao = $request->area_formacao;
            $candidatura->ingles = $request->ingles;
            $candidatura->referencias = ($request->referencias=='|') ? '' : $request->referencias;
            $candidatura->experiencias = $request->experiencias;
            $candidatura->trabalho_actual = $request->trabalho_actual;
            $candidatura->word = $request->word;
            $candidatura->excel = $request->excel;
            $candidatura->software_designer = $request->software_designer;
            $candidatura->primavera = $request->primavera;   
            $candidatura->minimo_experiencia = $request->minimo_experiencia;
            $candidatura->certificacao = $request->certificacao;
            $candidatura->capacidade_elaborar_planos = $request->capacidade_elaborar_planos;
            $candidatura->capacidade_redigir_relatorio = $request->capacidade_redigir_relatorio;
            $candidatura->capacidade_avaliar = $request->capacidade_avaliar;
            $candidatura->analise_processo = $request->analise_processo;
            $candidatura->dominio_normas = $request->dominio_normas;
            $candidatura->provincia_candidatura = $request->provincia_candidatura;
            $candidatura->capacidade_monitoria_avaliacao = $request->capacidade_monitoria_avaliacao;
            $candidatura->experiencia_gestao_dados = $request->experiencia_gestao_dados;
            $candidatura->experiencia_transformacao_digital = $request->experiencia_transformacao_digital;
            $candidatura->experiencia_gestao_startups = $request->experiencia_gestao_startups;
            
            $candidatura->vaga_id = base64_decode($request->vaga_id);   
            
            if($candidatura->save()){
                $status1 = true;
            }

            $candidatura->pontuacao = $this->calcularCandidaturaPontuacao($candidatura->id)->p_total;
            if($candidatura->save()){
                $status2 = true;
            }

            if($status1 && $status2){
                DB::commit();
                return response()->json('Candidatura enviada com sucesso',200);
            }
            DB::rollBack();
            return response()->json('Houve um problema ao submeter a candidatura');
        } catch(Exception $e){
            return 'erro ao registar';
        }
    }

    public function listarCandidaturasAvancado(Request $request){ 
        $idVaga = $request->query('idVaga');
        $perguntasFiltradas = $request->query('perguntasFiltradas');
        $arrayPerguntasPontuaveis = Pergunta::arrayPerguntasPontuaveis(); //Array contendo todas perguntas e suas posições (ID)7        
        
        $query = DB::table('candidatura as cand')
                        ->join('opcao as op_genero','op_genero.id','=','cand.genero')
                        ->select(
                            'cand.id',
                            'cand.nome',
                            'cand.morada',
                            'cand.pontuacao',
                            'cand.anexo_foto',
                            'cand.anexo_bilhete',
                            'cand.anexo_cv',
                            'op_genero.opcao as genero',
                            'cand.email',
                            'cand.telefone1',
                            DB::raw("TIMESTAMPDIFF(YEAR, cand.nascimento, CURDATE()) as idade")
                        )
                        ->where('cand.vaga_id',base64_decode($idVaga));

                        //Constroi-se as o campo da base de dados em função da pergunta escolhida e passa opcao como parametro de consulta
                        foreach($perguntasFiltradas as $pergunta){
                            $perguntaId = $pergunta['pergunta'];
                            $opcaoId = $pergunta['opcao'];

                            if(isset($arrayPerguntasPontuaveis[$perguntaId])){
                                $query->where($arrayPerguntasPontuaveis[$perguntaId], $opcaoId);
                            }
                        }
                        
                        $candidaturas = $query
                                        ->paginate(50)
                                        ->appends($request->query());

                        $candidaturas->getCollection()->transform(function ($candidato) {
                            $candidato->anexo_foto = asset('storage/' . $candidato->anexo_foto); 
                            $candidato->anexo_bilhete = asset('storage/' . $candidato->anexo_bilhete); 
                            $candidato->anexo_cv = asset('storage/' . $candidato->anexo_cv);   
                            return $candidato;
                        });

        return response()->json($candidaturas);
    }
    
    public function listarCandidaturasFiltro($idVaga,$filtro,Request $request){
        $query = DB::table('candidatura as cand')
                    ->join('opcao as op_genero','op_genero.id','=','cand.genero')
                    ->select(
                        'cand.id',
                        'cand.nome',
                        'op_genero.opcao as genero',
                        'cand.email',
                        'cand.telefone1',
                        'cand.morada',
                        'cand.anexo_foto',
                        'cand.anexo_bilhete',
                        'cand.anexo_cv',
                        'cand.pontuacao',
                        DB::raw("TIMESTAMPDIFF(YEAR, cand.nascimento, CURDATE()) as idade")
                    );
                    
                    $query->where('cand.vaga_id',base64_decode($idVaga));
                    
                    if($filtro==1 || $filtro==2){
                        $genero = ($filtro==1) ? 'Masculino':'Feminino';
                        $query->where('op_genero.opcao',$genero);
                    } else if($filtro !=0 && $filtro !=1 && $filtro !=2){
                        $query->whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($filtro) . '%']);
                    }

                    $query->orderBy('cand.pontuacao','desc');

                    $candidaturas = $query
                                        ->paginate(100)
                                        ->through(function($candidato){
                                            $candidato->anexo_foto = asset('storage/' . $candidato->anexo_foto); 
                                            $candidato->anexo_bilhete = asset('storage/' . $candidato->anexo_bilhete); 
                                            $candidato->anexo_cv = asset('storage/' . $candidato->anexo_cv);                
                                            return $candidato;
                                        });
                                        
        return response()->json($candidaturas);
    }

    public function pegarCandidatura($idCandidato){
        $candidatura = DB::table('candidatura as cand')
                        ->join('opcao as op_genero','op_genero.id','=','cand.genero')
                        ->join('opcao as op_grau_acad','op_grau_acad.id','=','cand.grau_academico')
                        ->join('opcao as op_ondeViu','op_ondeViu.id','=','cand.onde_viu_vaga')
                        ->join('opcao as op_trabalho_actual','op_trabalho_actual.id','=','cand.trabalho_actual')
                        ->leftjoin('opcao as op_ingles','op_ingles.id','=','cand.ingles')
                        ->leftjoin('opcao as op_word','op_word.id','=','cand.word')
                        ->leftjoin('opcao as op_excel','op_excel.id','=','cand.excel')
                        ->leftjoin('opcao as op_software_designer','op_software_designer.id','=','cand.software_designer')
                        ->leftjoin('opcao as op_primavera','op_primavera.id','=','cand.primavera')
                        ->leftjoin('opcao as op_minimo_experiencia','op_minimo_experiencia.id','=','cand.minimo_experiencia')
                        ->leftjoin('opcao as op_certificacao','op_certificacao.id','=','cand.certificacao')
                        ->leftjoin('opcao as op_capacidade_elaborar_planos','op_capacidade_elaborar_planos.id','=','cand.capacidade_elaborar_planos')
                        ->leftjoin('opcao as op_capacidade_redigir_relatorio','op_capacidade_redigir_relatorio.id','=','cand.capacidade_redigir_relatorio')
                        ->leftjoin('opcao as op_capacidade_avaliar','op_capacidade_avaliar.id','=','cand.capacidade_avaliar')
                        ->leftjoin('opcao as op_analise_processo','op_analise_processo.id','=','cand.analise_processo')
                        ->leftjoin('opcao as op_dominio_normas','op_dominio_normas.id','=','cand.dominio_normas')
                        ->leftjoin('opcao as op_provincia_candidatura','op_provincia_candidatura.id','=','cand.provincia_candidatura')
                        ->leftjoin('opcao as op_capacidade_monitoria_avaliacao','op_capacidade_monitoria_avaliacao.id','=','cand.capacidade_monitoria_avaliacao')
                        ->select(
                            DB::raw("(DATE_FORMAT(cand.created_at,'%d-%m-%Y')) as data_candidatura"),
                            'cand.nome',
                            'cand.anexo_foto',
                            'cand.anexo_bilhete',
                            'cand.anexo_cv',
                            DB::raw("(DATE_FORMAT(cand.nascimento,'%d-%m-%Y')) as nascimento"),
                            'op_genero.opcao as genero',
                            'cand.email',
                            'cand.telefone1',
                            'cand.telefone2',
                            'cand.numero_bilhete',
                            'cand.morada',
                            'op_grau_acad.opcao as grau_academico',
                            'op_ondeViu.opcao as onde_viu_vaga',
                            'op_trabalho_actual.opcao as trabalho_actual',
                            'cand.area_formacao',
                            'cand.links_profissional',
                            'cand.referencias',
                            'cand.experiencias',
                            'op_ingles.opcao as ingles',
                            'op_word.opcao as word',
                            'op_excel.opcao as excel',
                            'op_primavera.opcao as primavera',
                            'op_software_designer.opcao as software_designer',
                            'op_minimo_experiencia.opcao as minimo_experiencia',
                            'op_certificacao.opcao as certificacao',
                            'op_capacidade_elaborar_planos.opcao as capacidade_elaborar_planos',
                            'op_capacidade_redigir_relatorio.opcao as capacidade_redigir_relatorio',
                            'op_capacidade_avaliar.opcao as capacidade_avaliar',
                            'op_analise_processo.opcao as analise_processo',
                            'op_dominio_normas.opcao as dominio_normas',
                            'op_provincia_candidatura.opcao as provincia_candidatura',
                            'op_capacidade_monitoria_avaliacao.opcao as capacidade_monitoria_avaliacao',
                            'cand.experiencia_gestao_dados',
                            'cand.experiencia_transformacao_digital',
                            'cand.experiencia_gestao_startups'
                            )
                        ->where('cand.id',$idCandidato)
                        ->first();

        if(is_object($candidatura)){ 
            if($candidatura->referencias=='|'){
                $candidatura->referencias = '';
            }

            if($candidatura->anexo_foto)
                $candidatura->anexo_foto = asset('storage/' . $candidatura->anexo_foto); 
            if($candidatura->anexo_bilhete)
                $candidatura->anexo_bilhete = asset('storage/' . $candidatura->anexo_bilhete); 
            if($candidatura->anexo_cv)
                $candidatura->anexo_cv = asset('storage/' . $candidatura->anexo_cv);
            
            $candidatura->idade = Carbon::parse($candidatura->nascimento)->age.' Anos';
        }

        return response()->json($candidatura);
    }

    public function calcularCandidaturaPontuacao($idCandidato){
        //Retorna as pontuações das perguntas
        $candidatura = DB::table('candidatura as cand')                        
                        ->leftjoin('pontuacao as pont_genero','pont_genero.opcao_id','=','cand.genero')
                        ->leftjoin('pontuacao as pont_grau_acad','pont_grau_acad.opcao_id','=','cand.grau_academico')
                        ->leftjoin('pontuacao as pont_ingles','pont_ingles.opcao_id','=','cand.ingles')
                        ->leftjoin('pontuacao as pont_word','pont_word.opcao_id','=','cand.word')
                        ->leftjoin('pontuacao as pont_excel','pont_excel.opcao_id','=','cand.excel')
                        ->leftjoin('pontuacao as pont_soft_designer','pont_soft_designer.opcao_id','=','cand.software_designer')
                        ->leftjoin('pontuacao as pont_primavera','pont_primavera.opcao_id','=','cand.primavera')                        
                        ->leftjoin('pontuacao as pont_minimo_experiencia','pont_minimo_experiencia.opcao_id','=','cand.minimo_experiencia')
                        ->leftjoin('pontuacao as pont_certificacao','pont_certificacao.opcao_id','=','cand.certificacao')
                        ->leftjoin('pontuacao as pont_capacidade_elaborar_planos','pont_capacidade_elaborar_planos.opcao_id','=','cand.capacidade_elaborar_planos')
                        ->leftjoin('pontuacao as pont_capacidade_redigir_relatorio','pont_capacidade_redigir_relatorio.opcao_id','=','cand.capacidade_redigir_relatorio')
                        ->leftjoin('pontuacao as pont_capacidade_avaliar','pont_capacidade_avaliar.opcao_id','=','cand.capacidade_avaliar')
                        ->leftjoin('pontuacao as pont_analise_processo','pont_analise_processo.opcao_id','=','cand.analise_processo')
                        ->leftjoin('pontuacao as pont_dominio_normas','pont_dominio_normas.opcao_id','=','cand.dominio_normas')
                        ->leftjoin('pontuacao as pont_provincia_candidatura','pont_provincia_candidatura.opcao_id','=','cand.provincia_candidatura')
                        ->leftjoin('pontuacao as pont_capacidade_monitoria_avaliacao','pont_capacidade_monitoria_avaliacao.opcao_id','=','cand.capacidade_monitoria_avaliacao')
                        ->select(  
                            'cand.vaga_id as vaga_id',  
                            'pont_genero.ponto as p_genero',
                            'pont_grau_acad.ponto as p_grau_acad',
                            'pont_ingles.ponto as p_ingles',
                            'pont_word.ponto as p_word',
                            'pont_excel.ponto as p_excel',
                            'pont_primavera.ponto as p_primavera',
                            'pont_soft_designer.ponto as p_soft_designer',
                            'pont_minimo_experiencia.ponto as p_minimo_experiencia',
                            'pont_certificacao.ponto as p_certificacao',
                            'pont_capacidade_elaborar_planos.ponto as p_capacidade_elaborar_planos',
                            'pont_capacidade_redigir_relatorio.ponto as p_capacidade_redigir_relatorio',
                            'pont_capacidade_avaliar.ponto as p_capacidade_avaliar',
                            'pont_analise_processo.ponto as p_analise_processo',
                            'pont_dominio_normas.ponto as p_dominio_normas',
                            'pont_provincia_candidatura.ponto as p_provincia_candidatura',
                            'pont_capacidade_monitoria_avaliacao.ponto as p_capacidade_monitoria_avaliacao',
                            )
                        ->where('cand.id',$idCandidato)
                        ->first();

        //Pega quantas referencias tem um candidato
        $contReferencias = Pergunta::contReferenciasCandidato($idCandidato);

        //Pega referencias e pontuações da mesma para a vaga em parametro
        $referenciasVaga = Pergunta::getPerguntaReferencia($candidatura->vaga_id);

        //As pontuações: 0 Ref,1 Ref,2 Ref, n Ref... vem do array, e pegamos o nº no inicio da opcao para ver a pontuação  
        
        $ponto_referencia = 0;
        foreach($referenciasVaga as $ref){
            if(Str::before($ref->opcao, ' ')==$contReferencias){
                $ponto_referencia = $ref->ponto;
                break;
            }
        }

        //Se o candidato tiver n ex: 3 referencias e para essa vaga apenas n-x ex:(até 2 referencias) referencias, logo a pontuação deve ser da ultima referencia
        if($contReferencias>0 && $ponto_referencia==0 && count($referenciasVaga)>0){
            $ponto_referencia = $referenciasVaga[count($referenciasVaga)-1]->ponto;
        }


        $candidatura->p_referencia = $ponto_referencia;

        //Pega objecto e converte para array e depois acha o somatorio de todos pontos
        $arrayCandidaturas = (array) $candidatura;
        $p_values = array_filter($arrayCandidaturas, function ($value, $key) {
            return substr($key, 0, 2) === 'p_' && is_numeric($value);
        }, ARRAY_FILTER_USE_BOTH);
        $total = array_sum($p_values);
        $candidatura->p_total = $total;
        
        return $candidatura;
    }

    public function pegarCandidaturaPontos($idCandidato){
        return response()->json($this->calcularCandidaturaPontuacao($idCandidato));
    }

    public function debug(){
        $cont = 0;
        $cand = Candidatura::all();
        foreach($cand as $c){
            if(DB::table('candidatura')->where('id',$c->id)
            ->update([
                'pontuacao' => $this->calcularCandidaturaPontuacao($c->id)->p_total,
                'updated_at' => now()
            ])){
                $cont++;
            }
        }
        return 'Actualizados: '.$cont;
    }
}
