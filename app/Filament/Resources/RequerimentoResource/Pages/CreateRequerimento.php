<?php

namespace App\Filament\Resources\RequerimentoResource\Pages;

use App\Filament\Resources\RequerimentoResource;
use App\Filament\Pages\Base\BaseCreatePage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateRequerimento extends BaseCreatePage
{
    protected static string $resource = RequerimentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Armazenar os dados do aluno na sessão antes de criar
        session([
            'ultimo_requerimento_aluno' => [
                'nome_completo' => $data['nome_completo'] ?? null,
                'nivel_ensino' => $data['nivel_ensino'] ?? null,
                'ano' => $data['ano'] ?? null,
                'turma' => $data['turma'] ?? null,
            ]
        ]);

        return $data;
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label('Salvar e Criar outro')
            ->action('createAnother')
            ->keyBindings(['mod+shift+s'])
            ->color('gray');
    }

    public function createAnother(): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->record)->saveRelationships();

            $this->callHook('afterCreate');

            $this->commitDatabaseTransaction();
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->getCreatedNotification()?->send();

        // Redirecionar para nova criação mantendo os dados do aluno
        $this->redirect($this->getResource()::getUrl('create', [
            'preservar_aluno' => '1'
        ]));
    }

    public function mount(): void
    {
        parent::mount();

        // Se deve preservar os dados do aluno, preencher o formulário
        if (request()->has('preservar_aluno') && session()->has('ultimo_requerimento_aluno')) {
            $dadosAluno = session('ultimo_requerimento_aluno');
            
            $this->form->fill([
                'nome_completo' => $dadosAluno['nome_completo'],
                'nivel_ensino' => $dadosAluno['nivel_ensino'],
                'ano' => $dadosAluno['ano'],
                'turma' => $dadosAluno['turma'],
                // Limpar os dados do requerimento para novo registro
                'trimestre_id' => null,
                'disciplina_id' => null,
                'professor_id' => null,
                'data_requerimento' => now(),
                'motivo' => null,
                'observacao' => null,
                'status' => 'Aprovado',
            ]);
        }
    }
}