<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Completo - 2ª Chamada</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        @page {
            size: A4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            page-break-after: avoid;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0;
            font-size: 11px;
        }

        .requerimento {
            border: 1px solid;
            border-color: #333;
            margin-bottom: 15px;
            background-color: #fafafa;
            page-break-inside: avoid;
            break-inside: avoid;
            orphans: 3;
            widows: 3;
        }

        .requerimento-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            color: #eeeeeeff;
            background-color: #800000;
            padding: 5px 0 5px 0;
            border-bottom: 1px solid #333;
        }

        .requerimento-content {
            overflow: hidden;
            display: table;
            width: 100%;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .info-esquerda {
            display: table-cell;
            width: 68%;
            padding: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .info-direita {
            display: table-cell;
            width: 28%;
            padding: 0;
            background-color: #fdf2f4;
            text-align: center;
            box-sizing: border-box;
            vertical-align: middle;
            border-left: 1px solid #ccc;
        }

        .aluno-info {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .disciplina-info {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .motivo-box {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            padding: 8px;
        }

        .motivo-titulo {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 10px;
            font-weight: normal;
            text-align: center;
        }

        .motivo-texto {
            font-size: 11px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            text-align: center;
            margin: 0;
        }

        .clearfix {
            clear: both;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>


@foreach($requerimentos as $index => $req)
    <div class="requerimento">
        <div class="requerimento-header">
            2ª Chamada – {{ $req->trimestre->nome ?? 'N/A' }}
        </div>

        <div class="requerimento-content">
            <div class="info-esquerda">
                <div class="aluno-info">
                    @php
                        $nivelEnsino = match ($req->nivel_ensino) {
                            'Fundamental I' => 'Ensino Fundamental I',
                            'Fundamental II' => 'Ensino Fundamental II',
                            'Ensino Médio' => 'Ensino Médio',
                            'Terceirão' => 'Terceirão',
                            default => $req->nivel_ensino
                        };
                        $anoSerie = ($req->nivel_ensino === 'Ensino Médio' || $req->nivel_ensino === 'Terceirão')
                            ? $req->ano . 'ª Série'
                            : $req->ano . 'º Ano';
                    @endphp
                    <strong>Aluno:</strong> {{ $req->nome_completo }}
                </div>

                <div class="disciplina-info">
                    <strong>Turma:</strong> {{ $nivelEnsino }} - {{ $anoSerie }} {{ $req->turma }}
                </div>

                <div class="disciplina-info">
                    <strong>Disciplina:</strong> {{ $req->disciplina->nome ?? 'N/A' }}
                </div>

                <div class="disciplina-info">
                    <strong>Professor(a):</strong> {{ $req->professor->nome ?? 'N/A' }}
                </div>

                @if($req->observacao)
                    <div style="margin-top: 8px; font-size: 10px; color: #666;">
                        <strong>Observação:</strong> {{ $req->observacao }}
                    </div>
                @endif
            </div>

            <div class="info-direita">
                <div class="motivo-box">
                    <div class="motivo-titulo">MOTIVO</div>
                    <div class="motivo-texto">{{ $req->motivo ?? 'N/A' }}</div>
                </div>
            </div>

        </div>
    </div>
@endforeach

<div style="text-align: center; margin-top: 30px; font-weight: bold; font-size: 14px;">
    Total de solicitações: {{ $requerimentos->count() }}
</div>
</body>

</html>