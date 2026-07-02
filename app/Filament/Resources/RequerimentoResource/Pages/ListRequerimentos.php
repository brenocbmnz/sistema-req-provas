<?php

namespace App\Filament\Resources\RequerimentoResource\Pages;

use App\Filament\Resources\RequerimentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ListRequerimentos extends ListRecords
{
    protected static string $resource = RequerimentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('atualizar_status_concluido')
                ->label('Atualizar Status para Concluído')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->tooltip('Atualiza automaticamente os requerimentos aprovados cujo trimestre já terminou')
                ->requiresConfirmation()
                ->modalHeading('Atualizar Status dos Requerimentos')
                ->modalDescription('Esta ação irá verificar todos os requerimentos aprovados e atualizar para "Concluído" aqueles cujo trimestre já terminou.')
                ->modalSubmitActionLabel('Atualizar')
                ->action(function () {
                    try {
                        // Executa o comando artisan
                        $exitCode = Artisan::call('requerimentos:atualizar-status');
                        $output = Artisan::output();
                        
                        if ($exitCode === 0) {
                            // Extrai o número de requerimentos atualizados da saída
                            if (strpos($output, 'Nenhum requerimento') !== false) {
                                Notification::make()
                                    ->title('Nenhuma atualização necessária')
                                    ->body('Todos os requerimentos já estão com o status correto.')
                                    ->info()
                                    ->send();
                            } else {
                                // Busca por números na saída para contar quantos foram atualizados
                                preg_match('/(\d+) requerimento\(s\) atualizado\(s\)/', $output, $matches);
                                $count = $matches[1] ?? '0';
                                
                                Notification::make()
                                    ->title('Status atualizados com sucesso!')
                                    ->body($count . ' requerimento(s) foram marcados como "Concluído".')
                                    ->success()
                                    ->send();
                            }
                            
                            // Recarrega a página para mostrar as mudanças
                            $this->redirect(request()->header('Referer'));
                        } else {
                            throw new \Exception('Erro na execução do comando');
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao atualizar status')
                            ->body('Ocorreu um erro durante a atualização: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
