<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Requerimentos</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
            margin-bottom: 5px;
        }

        .header-info {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .filters-applied {
            margin-top: 30px;
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            font-size: 10px;
            color: #666;
            border-radius: 3px;
        }

        .filters-applied h4 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #555;
        }

        .filters-applied ul {
            margin: 0;
            padding-left: 15px;
        }

        .filters-applied li {
            margin-bottom: 2px;
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
    <div class="header-info">
        <p>Relatório gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Turma</th>
                <th>Disciplina</th>
                <th>Professor</th>
                <th>Data</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requerimentos as $req)
                <tr>
                    <td>{{ $req->nome_completo }}</td>
                    <td>
                        @php
                            $nivelEnsino = match ($req->nivel_ensino) {
                                'Fundamental I' => 'Fund. I',
                                'Fundamental II' => 'Fund. II',
                                'Ensino Médio' => 'Ens. Médio',
                                'Terceirão' => 'Terceirão',
                                default => $req->nivel_ensino
                            };
                            $anoSerie = ($req->nivel_ensino === 'Ensino Médio' || $req->nivel_ensino === 'Terceirão')
                                ? $req->ano . 'ª Série'
                                : $req->ano . 'º Ano';
                        @endphp
                        {{ $nivelEnsino }} | {{ $anoSerie }} {{ $req->turma }}
                    </td>
                    <td>{{ $req->disciplina->nome ?? 'N/A' }}</td>
                    <td>{{ $req->professor->nome ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->data_requerimento)->format('d/m/Y') }}</td>
                    <td>{{ $req->motivo }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum requerimento encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        // Verificar se há filtros realmente ativos ou agrupamento
        $hasActiveFilters = false;
        $activeFiltersList = [];
        
        // Verificar filtros básicos
        if (isset($filters['status']) && !empty($filters['status'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Status';
        }
        if (isset($filters['trimestre_id']) && !empty($filters['trimestre_id'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Trimestre';
        }
        if (isset($filters['data_inicial']) && !empty($filters['data_inicial'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Data Inicial';
        }
        if (isset($filters['data_final']) && !empty($filters['data_final'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Data Final';
        }
        
        // Verificar filtros avançados (apenas se estão habilitados E preenchidos)
        if (isset($filters['filtrar_nivel_ensino']) && $filters['filtrar_nivel_ensino'] && isset($filters['nivel_ensino']) && !empty($filters['nivel_ensino'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Nível de Ensino';
        }
        if (isset($filters['filtrar_ano']) && $filters['filtrar_ano'] && isset($filters['ano']) && !empty($filters['ano'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Ano/Série';
        }
        if (isset($filters['filtrar_turma']) && $filters['filtrar_turma'] && isset($filters['turma']) && !empty($filters['turma'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Turma';
        }
        if (isset($filters['filtrar_disciplina']) && $filters['filtrar_disciplina'] && isset($filters['disciplina_id']) && !empty($filters['disciplina_id'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Disciplina';
        }
        if (isset($filters['filtrar_professor']) && $filters['filtrar_professor'] && isset($filters['professor_id']) && !empty($filters['professor_id'])) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Professor';
        }
    @endphp

    @if($hasActiveFilters)
        <div class="filters-applied">
            <h4>Filtros aplicados:</h4>
            <ul>
                @if(isset($filters['status']) && !empty($filters['status']))
                    <li><strong>Status:</strong> 
                        @if(is_array($filters['status']))
                            {{ implode(', ', $filters['status']) }}
                        @else
                            {{ $filters['status'] }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['trimestre_id']) && !empty($filters['trimestre_id']))
                    <li><strong>Trimestre:</strong> 
                        @if(is_array($filters['trimestre_id']))
                            @php
                                $trimestres = \App\Models\Trimestre::whereIn('id', $filters['trimestre_id'])->pluck('nome')->toArray();
                            @endphp
                            {{ implode(', ', $trimestres) }}
                        @else
                            {{ \App\Models\Trimestre::find($filters['trimestre_id'])->nome ?? 'N/A' }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['data_inicial']) && !empty($filters['data_inicial']))
                    <li><strong>Data Inicial:</strong> {{ \Carbon\Carbon::parse($filters['data_inicial'])->format('d/m/Y') }}</li>
                @endif
                
                @if(isset($filters['data_final']) && !empty($filters['data_final']))
                    <li><strong>Data Final:</strong> {{ \Carbon\Carbon::parse($filters['data_final'])->format('d/m/Y') }}</li>
                @endif
                
                @if(isset($filters['nivel_ensino']) && !empty($filters['nivel_ensino']) && isset($filters['filtrar_nivel_ensino']) && $filters['filtrar_nivel_ensino'])
                    <li><strong>Nível de Ensino:</strong> 
                        @if(is_array($filters['nivel_ensino']))
                            {{ implode(', ', $filters['nivel_ensino']) }}
                        @else
                            {{ $filters['nivel_ensino'] }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['ano']) && !empty($filters['ano']) && isset($filters['filtrar_ano']) && $filters['filtrar_ano'])
                    <li><strong>Ano/Série:</strong> 
                        @if(is_array($filters['ano']))
                            {{ implode(', ', $filters['ano']) }}
                        @else
                            {{ $filters['ano'] }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['turma']) && !empty($filters['turma']) && isset($filters['filtrar_turma']) && $filters['filtrar_turma'])
                    <li><strong>Turma:</strong> 
                        @if(is_array($filters['turma']))
                            {{ implode(', ', $filters['turma']) }}
                        @else
                            {{ $filters['turma'] }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['disciplina_id']) && !empty($filters['disciplina_id']) && isset($filters['filtrar_disciplina']) && $filters['filtrar_disciplina'])
                    <li><strong>Disciplina:</strong> 
                        @if(is_array($filters['disciplina_id']))
                            @php
                                $disciplinas = \App\Models\Disciplina::whereIn('id', $filters['disciplina_id'])->pluck('nome')->toArray();
                            @endphp
                            {{ implode(', ', $disciplinas) }}
                        @else
                            {{ \App\Models\Disciplina::find($filters['disciplina_id'])->nome ?? 'N/A' }}
                        @endif
                    </li>
                @endif
                
                @if(isset($filters['professor_id']) && !empty($filters['professor_id']) && isset($filters['filtrar_professor']) && $filters['filtrar_professor'])
                    <li><strong>Professor:</strong> 
                        @if(is_array($filters['professor_id']))
                            @php
                                $professores = \App\Models\Professor::whereIn('id', $filters['professor_id'])->pluck('nome')->toArray();
                            @endphp
                            {{ implode(', ', $professores) }}
                        @else
                            {{ \App\Models\Professor::find($filters['professor_id'])->nome ?? 'N/A' }}
                        @endif
                    </li>
                @endif
            </ul>
        </div>
    @endif


</body>

</html>