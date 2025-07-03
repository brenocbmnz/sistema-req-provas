<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Geral - Por Série, Disciplina e Professor</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
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

        .summary {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .total-row {
            background-color: #e8f4f8;
            font-weight: bold;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório Geral - Número de Alunos por Série, Disciplina e Professor</h1>
        <p>Relatório gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['data_inicial'])->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($filtros['data_final'])->format('d/m/Y') }}</p>
    </div>

    <div class="divider">
        **************************************************************
    </div>

    <table>
        <thead>
            <tr>
                <th>Série</th>
                <th>Disciplina</th>
                <th>Professor</th>
                <th>Alunos Inscritos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $item)
                <tr>
                    <td>{{ $item['serie'] }}</td>
                    <td>{{ $item['disciplina'] }}</td>
                    <td>{{ $item['professor'] }}</td>
                    <td style="text-align: center;">{{ $item['total_alunos'] }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>TOTAL GERAL:</strong></td>
                <td style="text-align: center;"><strong>{{ $total_geral }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        Total de solicitações de 2ª chamada: {{ $total_geral }}
    </div>

    <div class="footer">
        Página <span class="page-number"></span> - Sistema de Requerimentos de Provas
    </div>
</body>

</html>