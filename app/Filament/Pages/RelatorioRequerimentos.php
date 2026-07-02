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
use Illuminate\Support\Facades\Session;

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
    
    // Usar sessão para manter estado dos filtros
    protected function getFiltrosAtivos(): array
    {
        return session('relatorio_filtros_ativos', []);
    }
    
    protected function setFiltrosAtivos(array $filtros): void
    {
        session(['relatorio_filtros_ativos' => $filtros]);
    }

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

    public function atualizarFiltros(array $filtros): void
    {
        // Salvar na sessão
        $this->setFiltrosAtivos($filtros);
        
        // Atualizar o formulário
        $currentData = $this->form->getState();
        $newData = array_merge($currentData, $filtros);
        $this->form->fill($newData);
        
        // Forçar re-render
        $this->dispatch('$refresh');
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
                                ->placeholder('Todos os status')
                                ->multiple(),
                            Select::make('trimestre_id')
                                ->label('Trimestre')
                                ->options(Trimestre::all()->pluck('nome', 'id'))
                                ->placeholder('Todos os trimestres')
                                ->multiple()
                                ->searchable(),
                        ])->columns(2),

                        Group::make([
                            DatePicker::make('data_inicial')
                                ->label('Data Inicial'),
                            DatePicker::make('data_final')
                                ->label('Data Final'),
                        ])->columns(2),

                        // Campos ocultos para controlar checkboxes
                        Group::make([
                            Checkbox::make('filtrar_nivel_ensino')->hiddenLabel()->hidden()->live(),
                            Checkbox::make('filtrar_ano')->hiddenLabel()->hidden()->live(),
                            Checkbox::make('filtrar_turma')->hiddenLabel()->hidden()->live(),
                            Checkbox::make('filtrar_disciplina')->hiddenLabel()->hidden()->live(),
                            Checkbox::make('filtrar_professor')->hiddenLabel()->hidden()->live(),
                        ]),

                        // Campos de filtro condicionais visíveis
                        Select::make('nivel_ensino')
                            ->label('Nível de Ensino')
                            ->options(NivelEnsino::class)
                            ->placeholder('Selecione os níveis')
                            ->multiple()
                            ->searchable()
                            ->visible(fn (): bool => $this->getFiltrosAtivos()['filtrar_nivel_ensino'] ?? false),

                        Group::make([
                            Select::make('ano')
                                ->label('Ano/Série')
                                ->options(array_combine(range(1, 9), range(1, 9)))
                                ->placeholder('Selecione os anos')
                                ->multiple()
                                ->visible(fn (): bool => $this->getFiltrosAtivos()['filtrar_ano'] ?? false),
                            Select::make('turma')
                                ->label('Turma')
                                ->options(array_combine(['A', 'B', 'C', 'D', 'E'], ['A', 'B', 'C', 'D', 'E']))
                                ->placeholder('Selecione as turmas')
                                ->multiple()
                                ->visible(fn (): bool => $this->getFiltrosAtivos()['filtrar_turma'] ?? false),
                        ])->columns(2)
                        ->visible(fn (): bool => ($this->getFiltrosAtivos()['filtrar_ano'] ?? false) || ($this->getFiltrosAtivos()['filtrar_turma'] ?? false)),

                        Select::make('disciplina_id')
                            ->label('Disciplina')
                            ->options(function(){
                                return Disciplina::query()
                                    ->orderBy('nome', 'asc')
                                    ->pluck('nome', 'id');
                            })
                            ->placeholder('Selecione as disciplinas')
                            ->multiple()
                            ->searchable()
                            ->visible(fn (): bool => $this->getFiltrosAtivos()['filtrar_disciplina'] ?? false),

                        Select::make('professor_id')
                            ->label('Professor')
                            ->options(function(){
                                return Professor::query()
                                    ->whereHas('disciplinas')
                                    ->orderBy('nome', 'asc')
                                    ->pluck('nome', 'id');
                            })
                            ->placeholder('Selecione os professores')
                            ->multiple()
                            ->searchable()
                            ->visible(fn (): bool => $this->getFiltrosAtivos()['filtrar_professor'] ?? false),
                    ]),
                
                Section::make('Organização do Relatório')
                    ->description('Configure como deseja organizar os dados no relatório final.')
                    ->schema([
                        Select::make('agrupar_por')
                            ->label('Agrupar Relatório Por')
                            ->options([
                                '' => 'Sem agrupamento (lista única)',
                                'professor' => 'Professor',
                                'disciplina' => 'Disciplina', 
                                'nivel_ensino' => 'Nível de Ensino',
                                'turma' => 'Turma',
                                'status' => 'Status',
                                'trimestre' => 'Trimestre',
                            ])
                            ->placeholder('Selecione como agrupar')
                            ->helperText('Quando selecionado, o relatório será dividido em tabelas separadas para cada grupo.')
                            ->extraFieldWrapperAttributes(['data-field' => 'agrupar_por']),
                    ]),
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
                
                Section::make('Filtro por Nível de Ensino')
                    ->description('Selecione quais níveis de ensino deseja incluir no relatório.')
                    ->schema([
                        Group::make([
                            Checkbox::make('incluir_fundamental_1')
                                ->label('Fundamental I')
                                ->default(true),
                            Checkbox::make('incluir_fundamental_2')
                                ->label('Fundamental II')
                                ->default(true),
                            Checkbox::make('incluir_ensino_medio')
                                ->label('Ensino Médio')
                                ->default(true),
                        Checkbox::make('incluir_terceirao')
                                ->label('Terceirão')
                                ->default(true),
                        ])->columns(4),
                        ]),
                    
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
                            ->live()
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
                
                                    Section::make('Filtro por Disciplina')
                    ->description('Selecione quais disciplinas deseja incluir no relatório.')
                    ->schema([
                        Group::make(function () {
                            $disciplinas = Disciplina::query()
                                ->orderBy('nome', 'asc')
                                ->get();
                                
                            $checkboxes = [];
                            
                            foreach ($disciplinas as $disciplina) {
                                $checkboxes[] = Checkbox::make('disciplina_' . $disciplina->id)
                                    ->label($disciplina->nome)
                                    ->default(true);
                            }
                            
                            return $checkboxes;
                        })->columns(3),
                    ])
                    ->visible(fn (Get $get): bool => $get('ordenacao') === 'disciplina'),
                
                Section::make('Filtro por Professor')
                    ->description('Selecione quais professores deseja incluir no relatório.')
                    ->schema([
                        Select::make('professores_selecionados')
                            ->label('Professores')
                            ->options(function(){
                                return Professor::query()
                                    ->whereHas('disciplinas')
                                    ->orderBy('nome', 'asc')
                                    ->pluck('nome', 'id');
                            })
                            ->placeholder('Selecione os professores')
                            ->multiple()
                            ->searchable()
                            ->helperText('Selecione os professores que deseja incluir no relatório.'),
                    ])
                    ->visible(fn (Get $get): bool => $get('ordenacao') === 'professor'),
                
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function filtrosAction(): Action
    {
        return Action::make('filtros')
            ->label('Filtros Avançados')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color(function () {
                $filtros = $this->getFiltrosAtivos();
                $hasActiveFilters = !empty($filtros['filtrar_nivel_ensino']) || 
                                   !empty($filtros['filtrar_ano']) || 
                                   !empty($filtros['filtrar_turma']) || 
                                   !empty($filtros['filtrar_disciplina']) || 
                                   !empty($filtros['filtrar_professor']);
                return $hasActiveFilters ? 'primary' : 'gray';
            })
            ->badge(function () {
                $filtros = $this->getFiltrosAtivos();
                $count = 0;
                if (!empty($filtros['filtrar_nivel_ensino'])) $count++;
                if (!empty($filtros['filtrar_ano'])) $count++;
                if (!empty($filtros['filtrar_turma'])) $count++;
                if (!empty($filtros['filtrar_disciplina'])) $count++;
                if (!empty($filtros['filtrar_professor'])) $count++;
                return $count > 0 ? $count : null;
            })
            ->badgeColor('primary')
            ->fillForm(function (): array {
                $filtros = $this->getFiltrosAtivos();
                return [
                    'filtrar_nivel_ensino' => $filtros['filtrar_nivel_ensino'] ?? false,
                    'filtrar_ano' => $filtros['filtrar_ano'] ?? false,
                    'filtrar_turma' => $filtros['filtrar_turma'] ?? false,
                    'filtrar_disciplina' => $filtros['filtrar_disciplina'] ?? false,
                    'filtrar_professor' => $filtros['filtrar_professor'] ?? false,
                ];
            })
            ->form([
                Section::make('Selecionar Filtros Avançados')
                    ->description('Marque os filtros que deseja utilizar. Eles aparecerão no formulário principal para preenchimento.')
                    ->schema([
                        Group::make([
                            Checkbox::make('filtrar_nivel_ensino')
                                ->label('Filtrar por Nível de Ensino')
                                ->helperText('Fundamental I, Fundamental II, Ensino Médio, Terceirão'),
                            Checkbox::make('filtrar_ano')
                                ->label('Filtrar por Ano/Série')
                                ->helperText('1º ao 9º ano, 1ª à 3ª série'),
                        ])->columns(2),

                        Group::make([
                            Checkbox::make('filtrar_turma')
                                ->label('Filtrar por Turma')
                                ->helperText('Turmas A, B, C, D, E'),
                            Checkbox::make('filtrar_disciplina')
                                ->label('Filtrar por Disciplina')
                                ->helperText('Matemática, Português, etc.'),
                        ])->columns(2),

                        Group::make([
                            Checkbox::make('filtrar_professor')
                                ->label('Filtrar por Professor')
                                ->helperText('Docentes cadastrados no sistema'),
                        ])->columns(1),
                    ]),
                

            ])
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Aplicar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (array $data) {
                // Atualizar os filtros ativos
                $this->atualizarFiltros([
                    'filtrar_nivel_ensino' => $data['filtrar_nivel_ensino'] ?? false,
                    'filtrar_ano' => $data['filtrar_ano'] ?? false,
                    'filtrar_turma' => $data['filtrar_turma'] ?? false,
                    'filtrar_disciplina' => $data['filtrar_disciplina'] ?? false,
                    'filtrar_professor' => $data['filtrar_professor'] ?? false,
                ]);
                
                // Limpar campos que foram desativados
                $currentData = $this->form->getState();
                $newData = $currentData;
                
                if (!($data['filtrar_nivel_ensino'] ?? false)) {
                    $newData['nivel_ensino'] = null;
                }
                if (!($data['filtrar_ano'] ?? false)) {
                    $newData['ano'] = null;
                }
                if (!($data['filtrar_turma'] ?? false)) {
                    $newData['turma'] = null;
                }
                if (!($data['filtrar_disciplina'] ?? false)) {
                    $newData['disciplina_id'] = null;
                }
                if (!($data['filtrar_professor'] ?? false)) {
                    $newData['professor_id'] = null;
                }
                
                $this->form->fill($newData);
                
                $filtrosAtivos = array_filter([
                    $data['filtrar_nivel_ensino'] ?? false ? 'Nível de Ensino' : null,
                    $data['filtrar_ano'] ?? false ? 'Ano/Série' : null,
                    $data['filtrar_turma'] ?? false ? 'Turma' : null,
                    $data['filtrar_disciplina'] ?? false ? 'Disciplina' : null,
                    $data['filtrar_professor'] ?? false ? 'Professor' : null,
                ]);
                
                if (empty($filtrosAtivos)) {
                    Notification::make()
                        ->title('Filtros limpos!')
                        ->body('Todos os filtros avançados foram desativados.')
                        ->warning()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Filtros configurados!')
                        ->body('Filtros ativos: ' . implode(', ', $filtrosAtivos))
                        ->success()
                        ->send();
                }
            });
    }

    public function generateReport()
    {
        $formData = $this->form->getState();
        $filtrosAtivos = $this->getFiltrosAtivos();

        $query = Requerimento::query()
            ->with(['disciplina', 'professor', 'trimestre'])
            ->when(isset($formData['status']) && !empty($formData['status']), 
                fn ($q) => is_array($formData['status']) 
                    ? $q->whereIn('status', $formData['status'])
                    : $q->where('status', $formData['status']))
            ->when(isset($formData['trimestre_id']) && !empty($formData['trimestre_id']), 
                fn ($q) => is_array($formData['trimestre_id']) 
                    ? $q->whereIn('trimestre_id', $formData['trimestre_id'])
                    : $q->where('trimestre_id', $formData['trimestre_id']))
            ->when(
                isset($filtrosAtivos['filtrar_nivel_ensino']) && 
                $filtrosAtivos['filtrar_nivel_ensino'] && 
                isset($formData['nivel_ensino']) && 
                !empty($formData['nivel_ensino']), 
                fn ($q) => is_array($formData['nivel_ensino']) 
                    ? $q->whereIn('nivel_ensino', $formData['nivel_ensino'])
                    : $q->where('nivel_ensino', $formData['nivel_ensino']))
            ->when(
                isset($filtrosAtivos['filtrar_ano']) && 
                $filtrosAtivos['filtrar_ano'] && 
                isset($formData['ano']) && 
                !empty($formData['ano']), 
                fn ($q) => is_array($formData['ano']) 
                    ? $q->whereIn('ano', $formData['ano'])
                    : $q->where('ano', $formData['ano']))
            ->when(
                isset($filtrosAtivos['filtrar_turma']) && 
                $filtrosAtivos['filtrar_turma'] && 
                isset($formData['turma']) && 
                !empty($formData['turma']), 
                fn ($q) => is_array($formData['turma']) 
                    ? $q->whereIn('turma', $formData['turma'])
                    : $q->where('turma', $formData['turma']))
            ->when(
                isset($filtrosAtivos['filtrar_disciplina']) && 
                $filtrosAtivos['filtrar_disciplina'] && 
                isset($formData['disciplina_id']) && 
                !empty($formData['disciplina_id']), 
                fn ($q) => is_array($formData['disciplina_id']) 
                    ? $q->whereIn('disciplina_id', $formData['disciplina_id'])
                    : $q->where('disciplina_id', $formData['disciplina_id']))
            ->when(
                isset($filtrosAtivos['filtrar_professor']) && 
                $filtrosAtivos['filtrar_professor'] && 
                isset($formData['professor_id']) && 
                !empty($formData['professor_id']), 
                fn ($q) => is_array($formData['professor_id']) 
                    ? $q->whereIn('professor_id', $formData['professor_id'])
                    : $q->where('professor_id', $formData['professor_id']))
            ->when(isset($formData['data_inicial']) && !empty($formData['data_inicial']), 
                fn ($q) => $q->where('data_requerimento', '>=', $formData['data_inicial']))
            ->when(isset($formData['data_final']) && !empty($formData['data_final']), 
                fn ($q) => $q->where('data_requerimento', '<=', $formData['data_final']));

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos para os filtros selecionados.')
                ->warning()
                ->send();
            return;
        }

        // Mesclar dados do formulário com dados da sessão para o PDF
        $filtersForPdf = array_merge($formData, $filtrosAtivos);

        // Verificar se deve agrupar
        $agruparPor = $formData['agrupar_por'] ?? '';
        
        $viewName = '';
        $viewData = [];

        if (empty($agruparPor)) {
            // Relatório normal sem agrupamento
            $viewName = 'pdf.requerimentos-relatorio';
            $viewData = [
                'requerimentos' => $requerimentos,
                'filters' => $filtersForPdf
            ];
        } else {
            // Relatório agrupado
            $dadosAgrupados = $this->agruparRequerimentos($requerimentos, $agruparPor);
            
            $viewName = 'pdf.requerimentos-relatorio-agrupado';
            $viewData = [
                'grupos' => $dadosAgrupados,
                'filters' => $filtersForPdf,
                'agrupamento' => $agruparPor,
                'titulo_agrupamento' => $this->obterTituloAgrupamento($agruparPor)
            ];
        }

        $tipoRelatorio = empty($agruparPor) ? 'personalizado' : 'agrupado-' . $agruparPor;
        $filename = "relatorio-{$tipoRelatorio}-" . now()->format('Y-m-d_H-i') . '.pdf';

        // Armazenar dados na sessão
        Session::put('pdf_report_data', [
            'view' => $viewName,
            'data' => $viewData,
            'filename' => $filename,
        ]);

        // Abrir nova aba para visualização
        $this->dispatch('open-pdf-in-new-tab', url: request()->getSchemeAndHttpHost() . '/report/view');
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

        // Validação dos níveis de ensino selecionados
        $niveisIncluir = [];
        if ($formDataGeral['incluir_fundamental_1'] ?? true) {
            $niveisIncluir[] = 'Fundamental I';
        }
        if ($formDataGeral['incluir_fundamental_2'] ?? true) {
            $niveisIncluir[] = 'Fundamental II';
        }
        if ($formDataGeral['incluir_ensino_medio'] ?? true) {
            $niveisIncluir[] = 'Ensino Médio';
        }
        if ($formDataGeral['incluir_terceirao'] ?? true) {
            $niveisIncluir[] = 'Terceirão';
        }

        if (empty($niveisIncluir)) {
            Notification::make()
                ->title('Erro de validação')
                ->body('Selecione pelo menos um nível de ensino.')
                ->danger()
                ->send();
            return;
        }

        // Processar filtros de disciplina se a ordenação for por disciplina
        $disciplinasIncluir = [];
        if (($formDataGeral['ordenacao'] ?? '') === 'disciplina') {
            $todasDisciplinas = Disciplina::all();
            foreach ($todasDisciplinas as $disciplina) {
                if ($formDataGeral['disciplina_' . $disciplina->id] ?? true) {
                    $disciplinasIncluir[] = $disciplina->id;
                }
            }
            
            if (empty($disciplinasIncluir)) {
                Notification::make()
                    ->title('Erro de validação')
                    ->body('Selecione pelo menos uma disciplina.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Processar filtros de professor se a ordenação for por professor
        $professoresIncluir = [];
        if (($formDataGeral['ordenacao'] ?? '') === 'professor') {
            $professoresIncluir = $formDataGeral['professores_selecionados'] ?? [];
            
            if (empty($professoresIncluir)) {
                Notification::make()
                    ->title('Erro de validação')
                    ->body('Selecione pelo menos um professor.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Query base com filtro de data e níveis de ensino selecionados
        $query = Requerimento::query()
            ->with(['disciplina', 'professor', 'trimestre'])
            ->where('data_requerimento', '>=', $formDataGeral['data_inicial'])
            ->where('data_requerimento', '<=', $formDataGeral['data_final'])
            ->whereIn('requerimentos.nivel_ensino', $niveisIncluir);
        
        // Aplicar filtro de disciplina se necessário
        if (!empty($disciplinasIncluir)) {
            $query->whereIn('disciplina_id', $disciplinasIncluir);
        }
        
        // Aplicar filtro de professor se necessário
        if (!empty($professoresIncluir)) {
            $query->whereIn('professor_id', $professoresIncluir);
        }

        // Aplicar ordenação baseada na seleção do usuário
        $ordenacao = $formDataGeral['ordenacao'] ?? 'nivel';
        $direcao = $formDataGeral['direcao_ordenacao'] ?? 'asc';

        $query = $this->aplicarOrdenacao($query, $ordenacao, $direcao);

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos no período selecionado com os níveis de ensino escolhidos.')
                ->warning()
                ->send();
            return;
        }

        // Agrupa por série, disciplina e professor, mantendo a ordenação
        $dadosAgrupados = $requerimentos->groupBy(function ($item) {
            return $item->nivel_ensino . '|' . $item->ano . '|' . ($item->disciplina?->nome ?? 'Sem Disciplina') . '|' . ($item->professor?->nome ?? 'Sem Professor');
        })->map(function ($group) {
            $primeiro = $group->first();
            $nivelEnsino = match($primeiro->nivel_ensino) {
                'Fundamental I' => 'Ensino Fundamental I',
                'Fundamental II' => 'Ensino Fundamental II', 
                'Ensino Médio' => 'Ensino Médio',
                'Terceirão' => 'Terceirão',
                default => $primeiro->nivel_ensino
            };
            return [
                'nivel_ensino' => $nivelEnsino,
                'serie' => ($primeiro->nivel_ensino === 'Ensino Médio' || $primeiro->nivel_ensino === 'Terceirão')
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
                // Ordenação customizada por nível de ensino: Fundamental I, Fundamental II, Ensino Médio, Terceirão
                $dadosAgrupados = $dadosAgrupados->sort(function ($a, $b) use ($direcao) {
                    $reqA = $a['requerimentos']->first();
                    $reqB = $b['requerimentos']->first();
                    
                    $nivelPrioridade = [
                        'Fundamental I' => 1,
                        'Fundamental II' => 2,
                        'Ensino Médio' => 3,
                        'Terceirão' => 4
                    ];
                    
                    $prioridadeNivelA = $nivelPrioridade[$reqA->nivel_ensino] ?? 4;
                    $prioridadeNivelB = $nivelPrioridade[$reqB->nivel_ensino] ?? 4;
                    
                    // Primeiro compara por nível de ensino
                    $comparison = $prioridadeNivelA <=> $prioridadeNivelB;
                    if ($comparison !== 0) {
                        return $direcao === 'asc' ? $comparison : -$comparison;
                    }
                    
                    // Depois compara por prioridade de ano/série usando a nova função
                    $prioridadeAnoA = $this->obterPrioridadeAnoSerie($reqA->nivel_ensino, $reqA->ano);
                    $prioridadeAnoB = $this->obterPrioridadeAnoSerie($reqB->nivel_ensino, $reqB->ano);
                    
                    if ($prioridadeAnoA !== $prioridadeAnoB) {
                        $anoComparison = $prioridadeAnoA <=> $prioridadeAnoB;
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
                    $reqA = $a['requerimentos']->first();
                    $reqB = $b['requerimentos']->first();
                    
                    // Primeiro ordena por prioridade de ano/série
                    $prioridadeA = $this->obterPrioridadeAnoSerie($reqA->nivel_ensino, $reqA->ano);
                    $prioridadeB = $this->obterPrioridadeAnoSerie($reqB->nivel_ensino, $reqB->ano);
                    
                    if ($prioridadeA !== $prioridadeB) {
                        $comparison = $prioridadeA <=> $prioridadeB;
                        return $direcao === 'asc' ? $comparison : -$comparison;
                    }
                    
                    // Se mesmo ano/série, ordena por turma (A, B, C, etc)
                    $comparison = strcmp($reqA->turma, $reqB->turma);
                    return $direcao === 'asc' ? $comparison : -$comparison;
                });
                break;
            default:
                // Ordenação customizada que segue a ordem escolar
                $dadosAgrupados = $dadosAgrupados->sort(function ($a, $b) {
                    $reqA = $a['requerimentos']->first();
                    $reqB = $b['requerimentos']->first();
                    
                    // Definir prioridade para níveis de ensino
                    $nivelPrioridade = [
                        'Fundamental I' => 1,
                        'Fundamental II' => 2,
                        'Ensino Médio' => 3,
                        'Terceirão' => 4
                    ];
                    
                    $prioridadeNivelA = $nivelPrioridade[$reqA->nivel_ensino] ?? 4;
                    $prioridadeNivelB = $nivelPrioridade[$reqB->nivel_ensino] ?? 4;
                    
                    // Primeiro compara por nível de ensino
                    if ($prioridadeNivelA !== $prioridadeNivelB) {
                        return $prioridadeNivelA <=> $prioridadeNivelB;
                    }
                    
                    // Depois compara por prioridade de ano/série usando a nova função
                    $prioridadeAnoA = $this->obterPrioridadeAnoSerie($reqA->nivel_ensino, $reqA->ano);
                    $prioridadeAnoB = $this->obterPrioridadeAnoSerie($reqB->nivel_ensino, $reqB->ano);
                    
                    if ($prioridadeAnoA !== $prioridadeAnoB) {
                        return $prioridadeAnoA <=> $prioridadeAnoB;
                    }
                    
                    // Por último compara por disciplina
                    return strcmp($a['disciplina'], $b['disciplina']);
                });
        }

        $viewData = [
            'dados' => $dadosAgrupados,
            'filtros' => array_merge($formDataGeral, [
                'niveis_incluidos' => $niveisIncluir
            ]),
            'total_geral' => $requerimentos->count(),
            'ordenacao_info' => [
                'campo' => $ordenacao,
                'direcao' => $direcao,
                'campo_nome' => $this->obterNomeCampoOrdenacao($ordenacao)
            ]
        ];

        $filename = 'relatorio-geral-ordenado-' . now()->format('Y-m-d_H-i') . '.pdf';

        // Armazenar dados na sessão
        Session::put('pdf_report_data', [
            'view' => 'pdf.relatorio-geral',
            'data' => $viewData,
            'filename' => $filename,
        ]);

        // Abrir nova aba para visualização
        $this->dispatch('open-pdf-in-new-tab', url: request()->getSchemeAndHttpHost() . '/report/view');
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

        // Validação dos níveis de ensino selecionados
        $niveisIncluir = [];
        if ($formDataGeral['incluir_fundamental_1'] ?? true) {
            $niveisIncluir[] = 'Fundamental I';
        }
        if ($formDataGeral['incluir_fundamental_2'] ?? true) {
            $niveisIncluir[] = 'Fundamental II';
        }
        if ($formDataGeral['incluir_ensino_medio'] ?? true) {
            $niveisIncluir[] = 'Ensino Médio';
        }
        if ($formDataGeral['incluir_terceirao'] ?? true) {
            $niveisIncluir[] = 'Terceirão';
        }

        if (empty($niveisIncluir)) {
            Notification::make()
                ->title('Erro de validação')
                ->body('Selecione pelo menos um nível de ensino.')
                ->danger()
                ->send();
            return;
        }

        // Processar filtros de disciplina se a ordenação for por disciplina
        $disciplinasIncluir = [];
        if (($formDataGeral['ordenacao'] ?? '') === 'disciplina') {
            $todasDisciplinas = Disciplina::all();
            foreach ($todasDisciplinas as $disciplina) {
                if ($formDataGeral['disciplina_' . $disciplina->id] ?? true) {
                    $disciplinasIncluir[] = $disciplina->id;
                }
            }
            
            if (empty($disciplinasIncluir)) {
                Notification::make()
                    ->title('Erro de validação')
                    ->body('Selecione pelo menos uma disciplina.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Processar filtros de professor se a ordenação for por professor
        $professoresIncluir = [];
        if (($formDataGeral['ordenacao'] ?? '') === 'professor') {
            $professoresIncluir = $formDataGeral['professores_selecionados'] ?? [];
            
            if (empty($professoresIncluir)) {
                Notification::make()
                    ->title('Erro de validação')
                    ->body('Selecione pelo menos um professor.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Query base com filtro de data e níveis de ensino selecionados
        $query = Requerimento::query()
            ->with(['disciplina', 'professor', 'trimestre'])
            ->where('data_requerimento', '>=', $formDataGeral['data_inicial'])
            ->where('data_requerimento', '<=', $formDataGeral['data_final'])
            ->whereIn('requerimentos.nivel_ensino', $niveisIncluir);
        
        // Aplicar filtro de disciplina se necessário
        if (!empty($disciplinasIncluir)) {
            $query->whereIn('disciplina_id', $disciplinasIncluir);
        }
        
        // Aplicar filtro de professor se necessário
        if (!empty($professoresIncluir)) {
            $query->whereIn('professor_id', $professoresIncluir);
        }

        // Aplicar ordenação baseada na seleção do usuário
        $ordenacao = $formDataGeral['ordenacao'] ?? 'nivel';
        $direcao = $formDataGeral['direcao_ordenacao'] ?? 'asc';

        $query = $this->aplicarOrdenacao($query, $ordenacao, $direcao);

        $requerimentos = $query->get();

        if ($requerimentos->isEmpty()) {
            Notification::make()
                ->title('Nenhum requerimento encontrado')
                ->body('Não foram encontrados requerimentos no período selecionado com os níveis de ensino escolhidos.')
                ->warning()
                ->send();
            return;
        }

        $viewData = [
            'requerimentos' => $requerimentos,
            'filtros' => array_merge($formDataGeral, [
                'niveis_incluidos' => $niveisIncluir
            ]),
            'ordenacao_info' => [
                'campo' => $ordenacao,
                'direcao' => $direcao,
                'campo_nome' => $this->obterNomeCampoOrdenacao($ordenacao),
                'direcao_nome' => $direcao === 'asc' ? 'Crescente' : 'Decrescente'
            ]
        ];

        $filename = 'relatorio-completo-ordenado-' . now()->format('Y-m-d_H-i') . '.pdf';

        // Armazenar dados na sessão
        Session::put('pdf_report_data', [
            'view' => 'pdf.relatorio-completo',
            'data' => $viewData,
            'filename' => $filename,
        ]);

        // Abrir nova aba para visualização
        $this->dispatch('open-pdf-in-new-tab', url: request()->getSchemeAndHttpHost() . '/report/view');
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
                        WHEN 'Terceirão' THEN 4
                        ELSE 5
                    END {$ordenacaoNivel}
                ")
                ->orderByRaw("
                    CASE 
                        WHEN nivel_ensino IN ('Fundamental I', 'Fundamental II') THEN ano
                        WHEN nivel_ensino IN ('Ensino Médio', 'Terceirão') THEN ano + 8
                        ELSE 99
                    END {$direcao}
                ")
                ->orderBy('turma', $direcao)
                ->orderBy('nome_completo', 'asc');
                
            case 'turma':
                return $query->orderByRaw("
                    CASE 
                        WHEN nivel_ensino IN ('Fundamental I', 'Fundamental II') THEN ano
                        WHEN nivel_ensino IN ('Ensino Médio', 'Terceirão') THEN ano + 8
                        ELSE 99
                    END {$direcao}
                ")
                ->orderBy('turma', $direcao)
                ->orderBy('nome_completo', 'asc');
                
            case 'disciplina':
                return $query->join('disciplinas', 'requerimentos.disciplina_id', '=', 'disciplinas.id')
                            ->orderBy('disciplinas.nome', $direcao)
                            ->orderByRaw("
                                CASE requerimentos.nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    WHEN 'Terceirão' THEN 4
                                    ELSE 5
                                END ASC
                            ")
                            ->orderBy('ano', $direcao)
                            ->orderBy('nome_completo', 'asc')
                            ->select('requerimentos.*');
                
            case 'professor':
                return $query->join('professors', 'requerimentos.professor_id', '=', 'professors.id')
                            ->orderBy('professors.nome', $direcao)
                            ->orderByRaw("
                                CASE requerimentos.nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    WHEN 'Terceirão' THEN 4
                                    ELSE 5
                                END ASC
                            ")
                            ->orderBy('ano', $direcao)
                            ->orderBy('nome_completo', 'asc')
                            ->select('requerimentos.*');
                
            case 'aluno':
                return $query->orderBy('nome_completo', $direcao)
                            ->orderByRaw("
                                CASE requerimentos.nivel_ensino 
                                    WHEN 'Fundamental I' THEN 1
                                    WHEN 'Fundamental II' THEN 2
                                    WHEN 'Ensino Médio' THEN 3
                                    WHEN 'Terceirão' THEN 4
                                    ELSE 5
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
                        WHEN 'Terceirão' THEN 4
                        ELSE 5
                    END ASC
                ")
                ->orderByRaw("
                    CASE 
                        WHEN nivel_ensino IN ('Fundamental I', 'Fundamental II') THEN ano
                        WHEN nivel_ensino IN ('Ensino Médio', 'Terceirão') THEN ano + 8
                        ELSE 99
                    END ASC
                ")
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

    /**
     * Retorna a prioridade de ordenação para ano/série seguindo a ordem escolar:
     * 1º ano ao 8º ano, depois 1ª série à 3ª série
     */
    private function obterPrioridadeAnoSerie($nivelEnsino, $ano)
    {
        // Para Fundamental I e II: anos 1-8 têm prioridade 1-8
        if (in_array($nivelEnsino, ['Fundamental I', 'Fundamental II'])) {
            return (int) $ano;
        }
        
        // Para Ensino Médio e Terceirão: séries 1-3 têm prioridade 9-11
        if (in_array($nivelEnsino, ['Ensino Médio', 'Terceirão'])) {
            return 8 + (int) $ano;
        }
        
        // Fallback
        return 99;
    }

    private function agruparRequerimentos($requerimentos, $agruparPor)
    {
        $grupos = $requerimentos->groupBy(function ($req) use ($agruparPor) {
            return match($agruparPor) {
                'professor' => $req->professor->nome ?? 'Sem Professor',
                'disciplina' => $req->disciplina->nome ?? 'Sem Disciplina',
                'nivel_ensino' => $req->nivel_ensino,
                'turma' => $req->nivel_ensino . ' - ' . $req->ano . (($req->nivel_ensino === 'Ensino Médio' || $req->nivel_ensino === 'Terceirão') ? 'ª Série' : 'º Ano') . ' - Turma ' . $req->turma,
                'status' => $req->status,
                'trimestre' => $req->trimestre->nome ?? 'Sem Trimestre',
                default => 'Outros'
            };
        })->map(function ($grupo, $chave) use ($agruparPor) {
            return [
                'titulo' => $chave,
                'total' => $grupo->count(),
                'requerimentos' => $grupo->sortBy('nome_completo')
            ];
        });

        // Aplicar ordenação especial para agrupamento por turma
        if ($agruparPor === 'turma') {
            return $grupos->sort(function ($a, $b) {
                // Extrair informações do primeiro requerimento de cada grupo
                $reqA = $a['requerimentos']->first();
                $reqB = $b['requerimentos']->first();
                
                // Usar a função de prioridade para ordenar
                $prioridadeA = $this->obterPrioridadeAnoSerie($reqA->nivel_ensino, $reqA->ano);
                $prioridadeB = $this->obterPrioridadeAnoSerie($reqB->nivel_ensino, $reqB->ano);
                
                if ($prioridadeA !== $prioridadeB) {
                    return $prioridadeA <=> $prioridadeB;
                }
                
                // Se mesmo ano/série, ordena por turma
                return strcmp($reqA->turma, $reqB->turma);
            });
        }

        return $grupos->sortBy('titulo');
    }

    private function obterTituloAgrupamento($agruparPor)
    {
        return match($agruparPor) {
            'professor' => 'Professor',
            'disciplina' => 'Disciplina',
            'nivel_ensino' => 'Nível de Ensino',
            'turma' => 'Turma',
            'status' => 'Status',
            'trimestre' => 'Trimestre',
            default => 'Grupo'
        };
    }
}
