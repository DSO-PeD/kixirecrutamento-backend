<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VagasController;
use App\Http\Controllers\PerguntasController;
use App\Http\Controllers\OpcaoController;
use App\Http\Controllers\PontuacaoController;
use App\Http\Controllers\CandidaturaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\EstatisticaController;
use App\Http\Controllers\RelatorioController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('newVaga',[VagasController::class,'registarVaga']);
Route::get('listVagas',[VagasController::class,'listarVagas']);
Route::get('listVagasEmCurso', [VagasController::class, 'listarVagasEmCurso']);
Route::get('delVaga/{idVaga}',[VagasController::class,'deleteVaga']);
Route::get('getVagaByID/{idVaga}',[VagasController::class,'pegarVagaById']);
Route::get('divulgarVaga/{idVaga}',[VagasController::class,'divulgarVaga']);
Route::get('getVagaEmCurso/{idVaga}',[VagasController::class,'pegaVagaEmCurso']);
Route::get('getVagaFormulario/{idVaga}',[VagasController::class,'pegarVagaFormulario']); //Perguntas que aparecerão no formulário 
Route::get('changeVagaEstado/{idVaga}',[VagasController::class,'mudarVagaEstado']);
//Route::get('testRegistarVaga',[VagasController::class,'testRegistarVaga']);

Route::post('newPergunta',[PerguntasController::class,'registarPergunta']);
Route::get('listPerguntas',[PerguntasController::class,'listarPerguntas']);
Route::get('listPerguntasPontuaveis/{idVaga}',[PerguntasController::class,'listarPerguntasPontuaveisVaga']);
Route::get('listPerguntasActivas/{idVaga}',[PerguntasController::class,'listarPerguntasActivas']);
Route::get('delPergunta/{idPergunta}',[PerguntasController::class,'eliminarPergunta']);
Route::get('mudarEstado/{idPergunta}',[PerguntasController::class,'mudarEstadoPergunta']);

Route::post('newOpcao',[OpcaoController::class,'registarOpcao']);
Route::get('listGeralOpcoes',[OpcaoController::class,'listarGeralOpcoes']);
Route::get('listOpcoes/{idPergunta}',[OpcaoController::class,'listarOpcoes']);
Route::get('delOpcao/{idOpcao}',[OpcaoController::class,'eliminarOpcao']);

Route::post('newPontuacaoPergunta',[PontuacaoController::class,'registarPontuacaoPergunta']);
Route::get('checkPontuacaoPergunta/{pergunta_id}/{vaga_id}',[PontuacaoController::class,'verificarPontuacaoPergunta']);
Route::get('removePerguntaVaga/{pergunta_id}',[PontuacaoController::class,'removerPerguntaVaga']);

//Route::post('newEfectuarCandidatura',[CandidaturaController::class,'efectuarCandidatura']);
Route::middleware('throttle:10,1')->post('newEfectuarCandidatura',[CandidaturaController::class,'efectuarCandidatura']);
Route::get('getCandidaturasFiltAvancado',[CandidaturaController::class,'listarCandidaturasAvancado']);
Route::get('getCandidaturasFiltradas/{idVaga}/{filtro}',[CandidaturaController::class,'listarCandidaturasFiltro']);
Route::get('getCandidato/{idCandidato}',[CandidaturaController::class,'pegarCandidatura']);
Route::get('getCandidatoPontos/{idCandidato}',[CandidaturaController::class,'pegarCandidaturaPontos']);

Route::get('getEstatisticaVaga/{idVaga}',[EstatisticaController::class,'mostrarEstatisticaVaga']);
Route::get('getPieCharts',[EstatisticaController::class,'pieChartMostrar']);
Route::get('getBarCharts',[EstatisticaController::class,'barChartMostrar']);

Route::get('getRelatorioResumo/{idVaga}',[RelatorioController::class,'gerarRelatorioResumo']);

