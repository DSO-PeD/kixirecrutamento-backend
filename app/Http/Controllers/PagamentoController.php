<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PagamentoController extends Controller
{
    public function gerarHmacAssinatura($uri,$method, $body = null){
        $key = "16a853b6d01a4a2188bba340b0c40f2e";
        $secret_decoded = base64_decode("HWEVCUo//KbNuh7F41AMTa9+vruP4juhWmGRnqeNKco=");
        
        $timestamp = time();
        $nonce = uniqid();
        $content = '';                  
        if($body) {
            $body_md5 = md5($body, true);
            $content = base64_encode($body_md5);
        }
        $valueToSign = $key . $method . $uri . $timestamp . $nonce . $content;
        $signedValue = hash_hmac('sha256', $valueToSign, $secret_decoded, true);
        $signature = base64_encode($signedValue);
        return sprintf('%s:%s:%s:%s', $key, $signature, $nonce, $timestamp);
    }

    public function gerarReferencia(){
        try{
            //$uri = "https://api-sandbox.izipay.ao/v1/referencias";
            $uri = "https://api.izipay.ao/v1/referencias";

            $referenceRequest = [
                'montante' => 525000
            ];

            $jsonBody = json_encode($referenceRequest);

            //Retorno do HMAC gerado que será passado para API
            $hmac = $this->gerarHmacAssinatura($uri,'POST',$jsonBody);
            
            $response = Http::withHeaders([
                'Authorization' => 'Apikey ' . $hmac,
                'Accept' => 'application/json',
            ])->post($uri, $jsonBody);
        

            dd($response);

            //Retorna a resposta como array ou lança exceção em caso de erro
            if ($response->successful()) {
                dd($response->json());
            } 
            
            throw new \Exception('Erro ao consumir API: ' . $response->body());
            
        } catch (Exception $e){
            return $e;
        }
    }
    
    public function listarReferencias(){
        try{
            //$uri = "https://api-sandbox.izipay.ao/v1/referencias";
            $uri = "https://api.izipay.ao/v1/referencias";

            $referenceRequest = [];

            $jsonBody = json_encode($referenceRequest);

            //Retorno do HMAC gerado que será passado para API
            $hmac = $this->gerarHmacAssinatura($uri,'GET',$jsonBody);
            
            $response = Http::withHeaders([
                'Authorization' => 'Apikey '.$hmac,
                'Accept' => 'application/json',
            ])->get($uri, $jsonBody);

            dd($response);
        
            //Retorna a resposta como array ou lança exceção em caso de erro
            if ($response->successful()) {
                dd($response->json());
            } 
            
            throw new \Exception('Erro ao consumir API: ' . $response->body());

        } catch (Exception $e){
            return $e;
        }
    }
}
