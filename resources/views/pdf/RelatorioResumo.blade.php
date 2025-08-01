<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Relatório de Vaga</title>
    <!--<link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">-->
    <style>
        .body{
            font-family: 'Calibri', Arial, sans-serif;
        }

        .icon {
            width: 25px;
            height: 25px;
            margin: 10px 0 15px 0;
        }

        .card-title {
            font-size: 16px;
            font-family: 'Calibri', Arial, sans-serif;
        }

        table th {
            font-size: 15px;
            font-family: 'Calibri', Arial, sans-serif;
        }

        table td {
            font-size: 14px;
            text-align: center;
            font-family: 'Calibri', Arial, sans-serif;
        }

        .card-text {
            font-size: 16px;
            color:#1f1f1f;
            font-family: 'Calibri', Arial, sans-serif;
        }

        .bold{
            font-weight: bold;
        }

        .txt-green{
            color: green;
        }

        .txt-card{
            font-size:14px;
            font-family: 'Calibri', Arial, sans-serif;
        }

        .info-p{
            margin-top:-10px
        }

        .success{
            color: green;
        }

        .warning{
            color: orange;
        }

        .danger{
            color: red;
        }
    </style>
</head>
<body class="bg-white text-gray-900 p-8">
  <div class="max-w-3xl mx-auto">
    <!-- Header -->
    <header class="row align-items-center mb-4 pb-2 border-bottom">
        <table style="width:100%">
            <tr>
                <td style="text-align: left">
                    <img src="{{ public_path('logo.jpg') }}" style="width: 180px;">
                </td>
                <td style="text-align: right">
                    <h2 class="mb-1">KixiRecrutamento</h2>
                    <small style="font-size:10px">Imprenso aos: {{ date('d-m-Y H:i:s') }}</small>
                </td>
            <tr>
        </table>
    </header>
    <hr>
    
    <br><br>
    
    <!-- Section -->
    <section class="pt-8">
        <div style="width:100%">
            <div style="border: 1px solid gray;border-radius:10px; padding: 0px 10px 0 10px">
                <p class="card-title">Vaga: <span class="txt-green bold">{{ $vaga->funcao }}</span></p>
                <p class="card-text">Data Início: <small class="bold">{{$vaga->data_inicio}}</small> | Data Fim: <small class="bold">{{$vaga->data_fim}}</small></p>
            </div>
        </div>
        
        <div class="clearfix" style="width: 100%;margin-top: 20px">
            <div style="width: 23.0%; float: left;">
                <div style="border: 1px solid gray;border-radius:10px;text-align:center;padding:0 0 4px 0">
                    <br>
                    <img class="icon" src="icons/pessoas.png">
                    <p class="card-title txt-green bold info-p">{{ $candidatos->total }}</p>
                    <p class="txt-card info-p">Total Candidatos</p> 
                </div>
            </div>

            <div style="width: 23.0%; float: left; margin-left:7px">
                <div style="border: 1px solid gray;border-radius:10px; text-align:center;padding:0 0 4px 0">
                    <br>
                    <img class="icon" src="icons/man.png">
                    <p class="card-title txt-green bold info-p">{{ $candidatos->masculinos }} ({{$candidatos->perc_Masculinos}} %)</p>
                    <p class="txt-card info-p">Masculinos</p>
                </div>
            </div>
            
            <div style="width: 23.0%; float: left; margin-left:7px">
                <div style="border: 1px solid gray;border-radius:10px; text-align:center;padding:0 0 4px 0">
                    <br>
                    <img class="icon" src="icons/woman.png">
                    <p class="card-title txt-green bold info-p">{{ $candidatos->femininos }} ({{$candidatos->perc_Femininos}} %)</p>
                    <p class="txt-card info-p">Femininos</p>
                </div>
            </div>          
            <div style="width: 28.0%; float: left; margin-left:7px">
                <div class="txt-card" style="border: 1px solid gray;border-radius:10px; padding:0px 0px 0px 10px"> 
                    <p class="bold" style="text-decoration:underline">Faixa de Pontuação</p>
                    <p class="txt-card info-p bold success" style="margin-top:0px">Alta (85% - 100%) = {{$faixas['Alta']}}</p>
                    <p class="txt-card info-p bold warning">Média (60% - 84%) = {{$faixas['Media']}}</p>
                    <p class="txt-card info-p bold danger">Baixa (0% - 59%) = {{$faixas['Baixa']}}</p>
                </div>
            </div>          
        </div>
        
        <div style="margin-top:150px">
            <table style="width: 100%; border-collapse: collapse" border="1">
                <thead style="background-color: #f2f2f2;">
                    <tr style="border-bottom: 1px solid #ccc;">
                        <th colspan="4" style="padding: 6px;text-align:center">Distribuição Regional</th>
                    </tr>
                    <tr>
                        <th style="padding: 6px;">#</th>
                        <th style="padding: 6px;">Localidade</th>
                        <th style="padding: 6px;">Candidaturas</th>
                        <th style="padding: 6px;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candProvincia as $cand)                  
                    <tr style="border-bottom: 1px solid #ccc;">
                        <td style="padding: 6px;">{{$loop->iteration}}</td>
                        <td style="padding: 6px;">{{$cand->prov_candidato}}</td>
                        <td style="padding: 6px;">{{$cand->total}}</td>
                        <td style="padding: 6px;">{{$cand->percent}} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </section>
  </div>
</body>
</html>
