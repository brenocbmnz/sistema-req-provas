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
use App\Models\Requerimento;
use App\Models\Trimestre;
use App\Models\Disciplina;
use App\Models\Professor;
use App\Enums\NivelEnsino;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class RelatorioRequerimentos extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.relatorio-requerimentos';
    protected static ?string $navigationGroup = 'Operacional';
    protected static ?string $title = 'Gerador de Relatórios';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filtros')
                ->label('Filtros')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->form([
                    Section::make('Filtros Adicionais')
                        ->description('Marque os filtros adicionais que deseja utilizar no relatório.')
                        ->schema([
                            Checkbox::make('filtrar_nivel_ensino')
                                ->label('Filtrar por Nível de Ensino')
                                ->live(),
                            Checkbox::make('filtrar_ano')
                                ->label('Filtrar por Ano/Série')
                                ->live(),
                            Checkbox::make('filtrar_turma')
                                ->label('Filtrar por Turma')
                                ->live(),
                            Checkbox::make('filtrar_disciplina')
                                ->label('Filtrar por Disciplina')
                                ->live(),
                            Checkbox::make('filtrar_professor')
                                ->label('Filtrar por Professor')
                                ->live(),
                        ])->columns(2)
                ])
                ->action(function (array $data): void {
                    // Atualiza o estado do formulário com as checkboxes
                    $currentData = $this->form->getState();
                    $this->form->fill(array_merge($currentData, $data));
                })
                ->modalSubmitActionLabel('Aplicar Filtros')
                ->modalCancelActionLabel('Cancelar')
        ];
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
}
