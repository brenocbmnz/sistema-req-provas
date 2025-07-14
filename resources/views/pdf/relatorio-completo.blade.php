<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Completo - 2ª Chamada</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        @page {
            margin: 20mm;
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
            border: 2px solid #333;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #fafafa;
            min-height: 120px;
            page-break-inside: avoid;
            break-inside: avoid;
            orphans: 3;
            widows: 3;
        }

        .requerimento-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            background-color: #e8f4f8;
            padding: 4px;
            border: 1px solid #333;
        }

        .requerimento-content {
            margin: 5px 0;
            overflow: hidden;
            height: auto;
            display: table;
            width: 100%;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .info-esquerda {
            display: table-cell;
            width: 68%;
            padding: 8px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            box-sizing: border-box;
            vertical-align: top;
        }

        .info-direita {
            display: table-cell;
            width: 28%;
            padding: 0;
            border: 1px solid #ccc;
            background-color: #f0f8ff;
            text-align: center;
            box-sizing: border-box;
            vertical-align: middle;
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
            line-height: 1.3;
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

<body>
    <div class="header">
        <h1>Relatório Completo - 2ª Chamada</h1>
        <p>Relatório gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['data_inicial'])->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($filtros['data_final'])->format('d/m/Y') }}
        </p>
        @if(isset($ordenacao_info))
            <p><strong>Ordenação:</strong> {{ $ordenacao_info['campo_nome'] }}
                ({{ $ordenacao_info['direcao_nome'] }})</p>
        @endif
        @if(isset($filtros['niveis_incluidos']) && !empty($filtros['niveis_incluidos']))
            <p><strong>Níveis de Ensino incluídos:</strong> {{ implode(', ', $filtros['niveis_incluidos']) }}</p>
        @endif
    </div>

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
                                default => $req->nivel_ensino
                            };
                            $anoSerie = $req->nivel_ensino === 'Ensino Médio'
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