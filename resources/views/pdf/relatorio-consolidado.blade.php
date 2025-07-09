<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Consolidado - Estatísticas por {{ $ordenacao_info['campo_nome'] }}</title>
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
            font-size: 11px;
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

        .status-pendente { background-color: #fef3c7; }
        .status-aprovado { background-color: #d1fae5; }
        .status-reprovado { background-color: #fee2e2; }
        .status-concluido { background-color: #dbeafe; }

        .chart-container {
            margin: 20px 0;
            text-align: center;
        }

        .chart-bar {
            display: inline-block;
            margin: 0 2px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório Consolidado - Estatísticas por {{ $ordenacao_info['campo_nome'] }}</h1>
        <p>Relatório gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['data_inicial'])->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($filtros['data_final'])->format('d/m/Y') }}
        </p>
        <p><strong>Ordenação:</strong> {{ $ordenacao_info['campo_nome'] }} 
           ({{ $ordenacao_info['direcao'] === 'asc' ? 'Crescente' : 'Decrescente' }})</p>
    </div>

    <div class="divider">
        **************************************************************
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ $ordenacao_info['campo_nome'] }}</th>
                <th>Total</th>
                <th>Pendentes</th>
                <th>Aprovados</th>
                <th>Reprovados</th>
                <th>Concluídos</th>
                <th>% Aprovados</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totals = [
                    'total' => 0,
                    'pendentes' => 0,
                    'aprovados' => 0,
                    'reprovados' => 0,
                    'concluidos' => 0
                ];
            @endphp
            
            @foreach($dados as $item)
                @php
                    $totals['total'] += $item['total'];
                    $totals['pendentes'] += $item['pendentes'];
                    $totals['aprovados'] += $item['aprovados'];
                    $totals['reprovados'] += $item['reprovados'];
                    $totals['concluidos'] += $item['concluidos'];
                    
                    $percentualAprovados = $item['total'] > 0 ? round(($item['aprovados'] + $item['concluidos']) / $item['total'] * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $item['campo'] }}</strong></td>
                    <td style="text-align: center;">{{ $item['total'] }}</td>
                    <td style="text-align: center;" class="status-pendente">{{ $item['pendentes'] }}</td>
                    <td style="text-align: center;" class="status-aprovado">{{ $item['aprovados'] }}</td>
                    <td style="text-align: center;" class="status-reprovado">{{ $item['reprovados'] }}</td>
                    <td style="text-align: center;" class="status-concluido">{{ $item['concluidos'] }}</td>
                    <td style="text-align: center;"><strong>{{ $percentualAprovados }}%</strong></td>
                </tr>
            @endforeach
            
            @php
                $percentualTotalAprovados = $totals['total'] > 0 ? round(($totals['aprovados'] + $totals['concluidos']) / $totals['total'] * 100, 1) : 0;
            @endphp
            
            <tr class="total-row">
                <td><strong>TOTAL GERAL</strong></td>
                <td style="text-align: center;"><strong>{{ $totals['total'] }}</strong></td>
                <td style="text-align: center;"><strong>{{ $totals['pendentes'] }}</strong></td>
                <td style="text-align: center;"><strong>{{ $totals['aprovados'] }}</strong></td>
                <td style="text-align: center;"><strong>{{ $totals['reprovados'] }}</strong></td>
                <td style="text-align: center;"><strong>{{ $totals['concluidos'] }}</strong></td>
                <td style="text-align: center;"><strong>{{ $percentualTotalAprovados }}%</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Resumo Geral:</strong></p>
        <p>Total de Requerimentos: {{ $total_geral }} | 
           Taxa de Aprovação: {{ $percentualTotalAprovados }}% | 
           Critério de Ordenação: {{ $ordenacao_info['campo_nome'] }}</p>
    </div>

    <div style="margin-top: 20px; font-size: 10px;">
        <p><strong>Legenda de Cores:</strong></p>
        <p>🟡 Pendentes | 🟢 Aprovados | 🔴 Reprovados | 🔵 Concluídos</p>
    </div>

    <div class="footer">
        Página <span class="page-number"></span> - Sistema de Requerimentos de Provas
    </div>
</body>

</html>
