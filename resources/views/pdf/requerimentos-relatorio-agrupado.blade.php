<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Requerimentos - Agrupado por {{ $titulo_agrupamento }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .header-info {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .group-header {
            background-color: #f5e6ea;
            padding: 12px;
            margin: 30px 0 15px 0;
            border-left: 4px solid #a8344a;
            border-radius: 4px;
            /* Evitar quebra de página dentro do header */
            page-break-inside: avoid;
            /* Manter sempre junto com a tabela seguinte */
            page-break-after: avoid;
        }

        .group-section:first-child .group-header {
            margin-top: 5px;
        }

        .group-title {
            font-size: 16px;
            font-weight: bold;
            color: #881337;
            margin: 0;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .group-subtitle {
            font-size: 12px;
            color: #666;
            font-weight: normal;
        }

        .group-table {
            margin-bottom: 40px;
            clear: both;
            width: 100%;
            display: block;
            /* Manter sempre junto com o header anterior */
            page-break-before: avoid;
        }

        .group-table table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            display: table;
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

        .summary-box {
            background-color: #fdf2f4;
            border: 1px solid #e5a3b3;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #881337;
            margin-bottom: 10px;
        }

        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #881337;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }

        /* Quebra de página entre grupos */
        .group-section {
            page-break-inside: avoid;
        }

        .group-section:not(:first-child) {
            page-break-before: always;
            page-break-inside: avoid;
        }

        .group-section:first-child {
            page-break-before: avoid;
            page-break-inside: avoid;
            margin-top: 0;
        }

        /* Manter header e tabela sempre juntos */
        .group-header {
            page-break-after: avoid;
        }

        .group-table {
            page-break-before: avoid;
        }

        /* Forçar que a primeira tabela comece na primeira página */
        .group-section:first-child table {
            page-break-before: avoid;
        }

        .group-section:first-child thead {
            page-break-after: avoid;
        }
    </style>
</head>

<body>
    <h1>Relatório de Requerimentos</h1>
    <div class="header-info">
        <p>Relatório agrupado por {{ $titulo_agrupamento }} • Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Grupos de Requerimentos -->
    @foreach($grupos as $grupo)
        <div class="group-section">
            <div class="group-header">
                <h2 class="group-title">{{ $grupo['titulo'] }} <span class="group-subtitle">• {{ $grupo['total'] }} {{ $grupo['total'] === 1 ? 'requerimento' : 'requerimentos' }} encontrado{{ $grupo['total'] === 1 ? '' : 's' }}</span></h2>
            </div>

            <div class="group-table">
                <table>
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            @if($agrupamento === 'turma')
                                <th>Nível de Ensino</th>
                            @else
                                <th>Turma</th>
                            @endif
                            @if($agrupamento !== 'disciplina')
                                <th>Disciplina</th>
                            @endif
                            @if($agrupamento !== 'professor')
                                <th>Professor</th>
                            @endif
                            <th>Data</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupo['requerimentos'] as $req)
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
                                    @if($agrupamento === 'turma')
                                        {{ $nivelEnsino }}
                                    @elseif($agrupamento === 'nivel_ensino')
                                        {{ $anoSerie }} - Turma {{ $req->turma }}
                                    @else
                                        {{ $nivelEnsino }} | {{ $anoSerie }} {{ $req->turma }}
                                    @endif
                                </td>
                                @if($agrupamento !== 'disciplina')
                                    <td>{{ $req->disciplina->nome ?? 'N/A' }}</td>
                                @endif
                                @if($agrupamento !== 'professor')
                                    <td>{{ $req->professor->nome ?? 'N/A' }}</td>
                                @endif
                                <td>{{ \Carbon\Carbon::parse($req->data_requerimento)->format('d/m/Y') }}</td>

                                <td>{{ $req->motivo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @php
        // Verificar se há filtros realmente ativos ou agrupamento
        $hasActiveFilters = false;
        $activeFiltersList = [];
        
        // SEMPRE mostrar se há agrupamento (pois agrupamento é um filtro ativo)
        if (isset($titulo_agrupamento) && !empty($titulo_agrupamento)) {
            $hasActiveFilters = true;
            $activeFiltersList[] = 'Agrupamento';
        }
        
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
                @if(isset($titulo_agrupamento) && !empty($titulo_agrupamento))
                    <li><strong>Agrupamento:</strong> {{ $titulo_agrupamento }}</li>
                @endif
                
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
