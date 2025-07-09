<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Get;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use App\Models\Requerimento;
use App\Models\Trimestre;
use App\Models\Disciplina;
use App\Models\Professor;
use App\Enums\NivelEnsino;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class RelatorioRequerimentos extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.relatorio-requerimentos';
    protected static ?string $navigationGroup = 'Operacional';
    protected static ?string $title = 'Gerador de Relatórios';

    public ?array $data = [];
    public ?array $dataGeral = [];

    protected function getForms(): array
    {
        return [
            'form',
            'formGeral',
        ];
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->formGeral->fill();
    }

    public function filtrosAction(): Action
    {
        return Action::make('filtros')
            ->label('Filtros Avançados')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color('gray')
            ->size('sm')
            ->form([
                Section::make('Filtros Adicionais')
                    ->description('Marque os filtros adicionais que deseja utilizar no relatório.')
                    ->schema([
                        Checkbox::make('filtrar_nivel_ensino')
                            ->label('Filtrar por Nível de Ensino')
                            ->default(fn () => $this->data['filtrar_nivel_ensino'] ?? false),
                        Checkbox::make('filtrar_ano')
                            ->label('Filtrar por Ano/Série')
                            ->default(fn () => $this->data['filtrar_ano'] ?? false),
                        Checkbox::make('filtrar_turma')
                            ->label('Filtrar por Turma')
                            ->default(fn () => $this->data['filtrar_turma'] ?? false),
                        Checkbox::make('filtrar_disciplina')
                            ->label('Filtrar por Disciplina')
                            ->default(fn () => $this->data['filtrar_disciplina'] ?? false),
                        Checkbox::make('filtrar_professor')
                            ->label('Filtrar por Professor')
                            ->default(fn () => $this->data['filtrar_professor'] ?? false),
                    ])->columns(2)
            ])
            ->action(function (array $data): void {
                // Atualiza o estado do formulário com as checkboxes
                $this->data = array_merge($this->data ?? [], $data);
                $this->form->fill($this->data);
            })
            ->modalSubmitActionLabel('Aplicar Filtros')
            ->modalCancelActionLabel('Cancelar');
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Filtros do Relatório')
                    ->description('Configure os filtros para gerar seu relatório personalizado.')
                    ->schema([
                        // Filtros principais sempre visíveis
                        Group::make([
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Pendente' => 'Pendente',
                                    'Aprovado' => 'Aprovado',
                                    'Reprovado' => 'Reprovado',
                                    'Concluído' => 'Concluído',
                                ])
                                ->placeholder('Todos os status'),
                            Select::make('trimestre_id')
                                ->label('Trimestre')
                                ->options(Trimestre::all()->pluck('nome', 'id'))
                                ->placeholder('Todos os trimestres')
                                ->searchable(),
                        ])->columns(2),

                        Group::make([
                            DatePicker::make('data_inicial')
                                ->label('Data Inicial'),
                            DatePicker::make('data_final')
                                ->label('Data Final'),
                        ])->columns(2),

                        // Filtros opcionais (condicionais)
                        Select::make('nivel_ensino')
                            ->label('Nível de Ensino')
                            ->options(NivelEnsino::class)
                            ->placeholder('Todos os níveis')
                            ->visible(fn (Get $get): bool => (bool) $get('filtrar_nivel_ensino')),

                        Group::make([
                            Select::make('ano')
                                ->label('Ano/Série')
                                ->options(array_combine(range(1, 9), range(1, 9)))
                                ->placeholder('Todos os anos'),
                            Select::make('turma')
                                ->label('Turma')
                                ->options(array_combine(['A', 'B', 'C', 'D', 'E'], ['A', 'B', 'C', 'D', 'E']))
                                ->placeholder('Todas as turmas'),
                        ])->columns(2)
                            ->visible(fn (Get $get): bool => (bool) $get('filtrar_ano') || (bool) $get('filtrar_turma')),

                        Select::make('disciplina_id')
                            ->label('Disciplina')
                            ->options(Disciplina::all()->pluck('nome', 'id'))
                            ->placeholder('Todas as disciplinas')
                            ->searchable()
                            ->visible(fn (Get $get): bool => (bool) $get('filtrar_disciplina')),

                        Select::make('professor_id')
                            ->label('Professor')
                            ->options(Professor::all()->pluck('nome', 'id'))
                            ->placeholder('Todos os professores')
                            ->searchable()
                            ->visible(fn (Get $get): bool => (bool) $get('filtrar_professor')),
                    ])
            ]);
    }

    public function formGeral(Form $form): Form
    {
        return $form
            ->statePath('dataGeral')
            ->schema([
                Group::make([
                    DatePicker::make('data_inicial')
                        ->label('Data Inicial')
                        ->required()
                        ->default(now()->startOfMonth()),
                    DatePicker::make('data_final')
                        ->label('Data Final')
                        ->required()
                        ->default(now()->endOfMonth()),
                ])->columns(2),
                
                Section::make('Opções de Ordenação')
                    ->description('Escolha como deseja organizar os dados no relatório geral.')
                    ->schema([
                        Select::make('ordenacao')
                            ->label('Ordenar por')
                            ->options([
                                'nivel' => 'Nível de Ensino',
                                'turma' => 'Turma',
                                'disciplina' => 'Disciplina',
                                'professor' => 'Professor',
                                'aluno' => 'Nome do Aluno',
                                'data' => 'Data do Requerimento',
                            ])
                            ->default('nivel')
                            ->required()
                            ->helperText('Selecione o campo principal para ordenação dos dados no relatório.'),
                        
                        Select::make('direcao_ordenacao')
                            ->label('Direção da Ordenação')
                            ->options([
                                'asc' => 'Crescente (A-Z / 1-9)',
                                'desc' => 'Decrescente (Z-A / 9-1)',
                            ])
                            ->default('asc')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function generateReport()
    {
        $formData = $this->form->getState();

        $query = Requerimento::query()
            ->when(isset($formData['status']) && $formData['status'], 
                fn ($q) => $q->where('status', $formData['status']))
            ->when(isset($formData['trimestre_id']) && $formData['trimestre_id'], 
                fn ($q) => $q->where('trimestre_id', $formData['trimestre_id']))
            ->when(isset($formData['nivel_ensino']) && $formData['nivel_ensino'] && isset($formData['filtrar_nivel_ensino']) && $formData['filtrar_nivel_ensino'], 
                fn ($q) => $q->where('nivel_ensino', $formData['nivel_ensino']))
            ->when(isset($formData['ano']) && $formData['ano'] && isset($formData['filtrar_ano']) && $formData['filtrar_ano'], 
                fn ($q) => $q->where('ano', $formData['ano']))
            ->when(isset($formData['turma']) && $formData['turma'] && isset($formData['filtrar_turma']) && $formData['filtrar_turma'], 
                fn ($q) => $q->where('turma', $formData['turma']))
            ->when(isset($formData['disciplina_id']) && $formData['disciplina_id'] && isset($formData['filtrar_disciplina']) && $formData['filtrar_disciplina'], 
                fn ($q) => $q->where('disciplina_id', $formData['disciplina_id']))
            ->when(isset($formData['professor_id']) && $formData['professor_id'] && isset($formData['filtrar_professor']) && $formData['filtrar_professor'], 
                fn ($q) => $q->where('professor_id', $formData['professor_id']))
            ->when(isset($formData['data_inicial']) && $formData['data_inicial'], 
                fn ($q) => $q->where('data_requerimento', '>=', $formData['data_inicial']))
            ->when(isset($formData['data_final']) && $formData['data_final'], 
                fn ($q) => $q->where('data_requerimento', '<=', $formData['data_final']));

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            // Exibir notificação quando não há dados para o relatório
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos para os filtros selecionados.')
                ->warning()
                ->send();
            return;
        }

        $pdf = Pdf::loadView('pdf.requerimentos-relatorio', [
            'requerimentos' => $requerimentos,
            'filters' => $formData
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'relatorio-requerimentos-' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    public function generateRelatorioPorSerie()
    {
        $formDataGeral = $this->formGeral->getState();

        // Validação das datas
        if (!isset($formDataGeral['data_inicial']) || !isset($formDataGeral['data_final'])) {
            Notification::make()
                ->title('Erro de validação')
                ->body('Por favor, selecione a data inicial e final.')
                ->danger()
                ->send();
            return;
        }

        // Query base com filtro de data e aplicação da ordenação
        $query = Requerimento::query()
            ->with(['disciplina', 'professor', 'trimestre'])
            ->where('data_requerimento', '>=', $formDataGeral['data_inicial'])
            ->where('data_requerimento', '<=', $formDataGeral['data_final']);

        // Aplicar ordenação baseada na seleção do usuário
        $ordenacao = $formDataGeral['ordenacao'] ?? 'nivel';
        $direcao = $formDataGeral['direcao_ordenacao'] ?? 'asc';

        $query = $this->aplicarOrdenacao($query, $ordenacao, $direcao);

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos no período selecionado.')
                ->warning()
                ->send();
            return;
        }

        // Agrupa por série, disciplina e professor, mantendo a ordenação
        $dadosAgrupados = $requerimentos->groupBy(function ($item) {
            return $item->nivel_ensino . '|' . $item->ano . '|' . $item->disciplina->nome . '|' . $item->professor->nome;
        })->map(function ($group) {
            $primeiro = $group->first();
            $nivelEnsino = match($primeiro->nivel_ensino) {
                'Fundamental I' => 'Ensino Fundamental I',
                'Fundamental II' => 'Ensino Fundamental II', 
                'Ensino Médio' => 'Ensino Médio',
                default => $primeiro->nivel_ensino
            };
            return [
                'nivel_ensino' => $nivelEnsino,
                'serie' => $primeiro->nivel_ensino === 'Ensino Médio' 
                    ? $primeiro->ano . 'ª Série' 
                    : $primeiro->ano . 'º Ano',
                'disciplina' => $primeiro->disciplina->nome,
                'professor' => $primeiro->professor->nome,
                'total_alunos' => $group->count(),
                'requerimentos' => $group // Adiciona os requerimentos para ordenação detalhada
            ];
        });

        // Aplicar ordenação final nos dados agrupados
        switch ($ordenacao) {
            case 'nivel':
                // Ordenação customizada por nível de ensino: Fundamental I, Fundamental II, Ensino Médio
                $dadosAgrupados = $dadosAgrupados->sort(function ($a, $b) use ($direcao) {
                    $nivelPrioridade = [
                        'Fundamental I' => 1,
                        'Fundamental II' => 2,
                        'Ensino Médio' => 3
                    ];
                    
                    $nivelA = $a['requerimentos']->first()->nivel_ensino;
                    $nivelB = $b['requerimentos']->first()->nivel_ensino;
                    
                    $prioridadeA = $nivelPrioridade[$nivelA] ?? 4;
                    $prioridadeB = $nivelPrioridade[$nivelB] ?? 4;
                    
                    // Primeiro compara por nível de ensino
                    $comparison = $prioridadeA <=> $prioridadeB;
                    if ($comparison !== 0) {
                        return $direcao === 'asc' ? $comparison : -$comparison;
                    }
                    
                    // Depois compara por série/ano
                    $anoA = $a['requerimentos']->first()->ano;
                    $anoB = $b['requerimentos']->first()->ano;
                    if ($anoA !== $anoB) {
                        $anoComparison = $anoA <=> $anoB;
                        return $direcao === 'asc' ? $anoComparison : -$anoComparison;
                    }
                    
                    // Por último compara por disciplina
                    return strcmp($a['disciplina'], $b['disciplina']);
                });
                break;
            case 'disciplina':
                $dadosAgrupados = $direcao === 'asc' 
                    ? $dadosAgrupados->sortBy('disciplina') 
                    : $dadosAgrupados->sortByDesc('disciplina');
                break;
            case 'professor':
                $dadosAgrupados = $direcao === 'asc' 
                    ? $dadosAgrupados->sortBy('professor') 
                    : $dadosAgrupados->sortByDesc('professor');
                break;
            case 'turma':
                $dadosAgrupados = $dadosAgrupados->sort(function ($a, $b) use ($direcao) {
                    $comparison = strcmp($a['requerimentos']->first()->turma, $b['requerimentos']->first()->turma);
                    return $direcao === 'asc' ? $comparison : -$comparison;
                });
                break;
            default:
                // Ordenação customizada que coloca Ensino Médio por último
                $dadosAgrupados = $dadosAgrupados->sort(function ($a, $b) {
                    // Definir prioridade para níveis de ensino
                    $nivelPrioridade = [
                        'Fundamental I' => 1,
                        'Fundamental II' => 2,
                        'Ensino Médio' => 3
                    ];
                    
                    $nivelA = $a['requerimentos']->first()->nivel_ensino;
                    $nivelB = $b['requerimentos']->first()->nivel_ensino;
                    
                    $prioridadeA = $nivelPrioridade[$nivelA] ?? 4;
                    $prioridadeB = $nivelPrioridade[$nivelB] ?? 4;
                    
                    // Primeiro compara por nível de ensino
                    if ($prioridadeA !== $prioridadeB) {
                        return $prioridadeA <=> $prioridadeB;
                    }
                    
                    // Depois compara por série/ano
                    $anoA = $a['requerimentos']->first()->ano;
                    $anoB = $b['requerimentos']->first()->ano;
                    if ($anoA !== $anoB) {
                        return $anoA <=> $anoB;
                    }
                    
                    // Por último compara por disciplina
                    return strcmp($a['disciplina'], $b['disciplina']);
                });
        }

        $pdf = Pdf::loadView('pdf.relatorio-por-serie', [
            'dados' => $dadosAgrupados,
            'filtros' => $formDataGeral,
            'total_geral' => $requerimentos->count(),
            'ordenacao_info' => [
                'campo' => $ordenacao,
                'direcao' => $direcao,
                'campo_nome' => $this->obterNomeCampoOrdenacao($ordenacao)
            ]
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'relatorio-geral-ordenado-' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    public function generateRelatorioCompleto()
    {
        $formDataGeral = $this->formGeral->getState();

        // Validação das datas
        if (!isset($formDataGeral['data_inicial']) || !isset($formDataGeral['data_final'])) {
            Notification::make()
                ->title('Erro de validação')
                ->body('Por favor, selecione a data inicial e final.')
                ->danger()
                ->send();
            return;
        }

        // Query base com filtro de data
        $query = Requerimento::query()
            ->with(['disciplina', 'professor', 'trimestre'])
            ->where('data_requerimento', '>=', $formDataGeral['data_inicial'])
            ->where('data_requerimento', '<=', $formDataGeral['data_final']);

        // Aplicar ordenação baseada na seleção do usuário
        $ordenacao = $formDataGeral['ordenacao'] ?? 'nivel';
        $direcao = $formDataGeral['direcao_ordenacao'] ?? 'asc';

        $query = $this->aplicarOrdenacao($query, $ordenacao, $direcao);

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos no período selecionado.')
                ->warning()
                ->send();
            return;
        }

        $pdf = Pdf::loadView('pdf.relatorio-completo', [
            'requerimentos' => $requerimentos,
            'filtros' => $formDataGeral,
            'ordenacao_info' => [
                'campo' => $ordenacao,
                'direcao' => $direcao,
                'campo_nome' => $this->obterNomeCampoOrdenacao($ordenacao),
                'direcao_nome' => $direcao === 'asc' ? 'Crescente' : 'Decrescente'
            ]
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'relatorio-completo-ordenado-' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    private function aplicarOrdenacao($query, $ordenacao, $direcao)
    {
        switch ($ordenacao) {
            case 'nivel':
                // Ordenação customizada para nível de ensino: Fundamental I, Fundamental II, Ensino Médio
                $ordenacaoNivel = $direcao === 'asc' ? 'ASC' : 'DESC';
                return $query->orderByRaw("
                    CASE nivel_ensino 
                        WHEN 'Fundamental I' THEN 1
                        WHEN 'Fundamental II' THEN 2
                        WHEN 'Ensino Médio' THEN 3
                        ELSE 4
                    END {$ordenacaoNivel}
                ")
                ->orderBy('ano', $direcao)
                ->orderBy('turma', $direcao)
                ->orderBy('nome_completo', 'asc');
                
            case 'turma':
                return $query->orderBy('turma', $direcao)
                            ->orderBy('ano', $direcao)
                            ->orderByRaw("
                                CASE nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    ELSE 4
                                END ASC
                            ")
                            ->orderBy('nome_completo', 'asc');
                
            case 'disciplina':
                return $query->join('disciplinas', 'requerimentos.disciplina_id', '=', 'disciplinas.id')
                            ->orderBy('disciplinas.nome', $direcao)
                            ->orderByRaw("
                                CASE nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    ELSE 4
                                END ASC
                            ")
                            ->orderBy('ano', $direcao)
                            ->orderBy('nome_completo', 'asc')
                            ->select('requerimentos.*');
                
            case 'professor':
                return $query->join('professors', 'requerimentos.professor_id', '=', 'professors.id')
                            ->orderBy('professors.nome', $direcao)
                            ->orderByRaw("
                                CASE nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    ELSE 4
                                END ASC
                            ")
                            ->orderBy('ano', $direcao)
                            ->orderBy('nome_completo', 'asc')
                            ->select('requerimentos.*');
                
            case 'aluno':
                return $query->orderBy('nome_completo', $direcao)
                            ->orderByRaw("
                                CASE nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    ELSE 4
                                END ASC
                            ")
                            ->orderBy('ano', 'asc');
                
            case 'data':
                return $query->orderBy('data_requerimento', $direcao)
                            ->orderBy('nome_completo', 'asc');
                
            default:
                return $query->orderByRaw("
                    CASE nivel_ensino 
                        WHEN 'Fundamental I' THEN 1
                        WHEN 'Fundamental II' THEN 2
                        WHEN 'Ensino Médio' THEN 3
                        ELSE 4
                    END ASC
                ")
                ->orderBy('ano', 'asc')
                ->orderBy('turma', 'asc')
                ->orderBy('nome_completo', 'asc');
        }
    }

    private function obterNomeCampoOrdenacao($ordenacao)
    {
        return match($ordenacao) {
            'nivel' => 'Nível de Ensino',
            'turma' => 'Turma',
            'disciplina' => 'Disciplina',
            'professor' => 'Professor',
            'aluno' => 'Nome do Aluno',
            'data' => 'Data do Requerimento',
            default => 'Nível de Ensino'
        };
    }
}
