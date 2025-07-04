<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Requerimentos</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <h1>Relatório de Requerimentos</h1>
    <p>Relatório gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Nível/Série/Turma</th>
                <th>Disciplina</th>
                <th>Professor</th>
                <th>Data</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requerimentos as $req)
                <tr>
                    <td>{{ $req->nome_completo }}</td>
                    <td>
                        @php
                            $nivelEnsino = match ($req->nivel_ensino) {
                                'fundamental1' => 'Fund. I',
                                'fundamental2' => 'Fund. II',
                                'medio' => 'Médio',
                                default => $req->nivel_ensino
                            };
                        @endphp
                        {{ $nivelEnsino }} - {{ $req->ano }}º Ano - Turma {{ $req->turma }}
                    </td>
                    <td>{{ $req->disciplina->nome ?? 'N/A' }}</td>
                    <td>{{ $req->professor->nome ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->data_requerimento)->format('d/m/Y') }}</td>
                    <td>{{ $req->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum requerimento encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        Página <span class="page-number"></span>
    </div>
</body>

</html>