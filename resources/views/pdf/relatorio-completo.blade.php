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
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
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
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }

        .requerimento-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            background-color: #e8f4f8;
            padding: 5px;
            border: 1px solid #333;
        }

        .requerimento-content {
            margin: 10px 0;
        }

        .divider {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .aluno-info {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .disciplina-info {
            margin-bottom: 5px;
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
    </div>

    @foreach($requerimentos as $index => $req)
        @if($index > 0 && $index % 3 == 0)
            <div class="page-break"></div>
        @endif

        <div class="requerimento">
            <div class="requerimento-header">
                2ª Chamada – {{ $req->trimestre->nome ?? 'N/A' }}
            </div>

            <div class="requerimento-content">
                <div class="aluno-info">
                    @php
                        $nivelEnsino = match ($req->nivel_ensino) {
                            'fundamental1' => 'Ensino Fundamental I',
                            'fundamental2' => 'Ensino Fundamental II',
                            'medio' => 'Ensino Médio',
                            default => $req->nivel_ensino
                        };
                    @endphp
                    Aluno: {{ $req->nome_completo }} - {{ $nivelEnsino }} - {{ $req->ano }}º Ano {{ $req->turma }}
                </div>

                <div class="disciplina-info">
                    Disciplina: {{ $req->disciplina->nome ?? 'N/A' }}
                    Professor(a): {{ $req->professor->nome ?? 'N/A' }}
                </div>

                @if($req->observacao)
                    <div style="margin-top: 10px; font-size: 10px; color: #666;">
                        <strong>Observação:</strong> {{ $req->observacao }}
                    </div>
                @endif

                <div style="margin-top: 10px; font-size: 10px; color: #666;">
                    <strong>Data da solicitação:</strong>
                    {{ \Carbon\Carbon::parse($req->data_requerimento)->format('d/m/Y') }}
                    | <strong>Status:</strong> {{ $req->status }}
                </div>
            </div>
        </div>

        <div class="divider">
            **************************************************************
        </div>
    @endforeach

    <div style="text-align: center; margin-top: 30px; font-weight: bold; font-size: 14px;">
        Total de solicitações: {{ $requerimentos->count() }}
    </div>

    <div class="footer">
        Página <span class="page-number"></span> - Sistema de Requerimentos de Provas
    </div>
</body>

</html>