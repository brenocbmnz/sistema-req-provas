<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AprovarUsuarios extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static string $view = 'filament.pages.aprovar-usuarios';
    protected static ?string $title = 'Aprovar Usuários';
    protected static ?string $navigationLabel = 'Aprovar Usuários';
    
    // Esconder da navegação principal
    protected static bool $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->pendingApproval())
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar Usuário')
                    ->modalDescription(fn (User $record) => "Tem certeza que deseja aprovar o usuário {$record->name}?")
                    ->modalSubmitActionLabel('Aprovar')
                    ->action(function (User $record) {
                        $record->approve(Auth::user());
                        
                        Notification::make()
                            ->title('Usuário aprovado com sucesso!')
                            ->success()
                            ->send();
                    })
            ])
            ->emptyStateHeading('Nenhum usuário pendente')
            ->emptyStateDescription('Não há usuários aguardando aprovação no momento.')
            ->emptyStateIcon('heroicon-o-users');
    }

    // public function getHeading(): string
    // {
    //     $pendingCount = User::pendingApproval()->count();
    //     return "Aprovar Usuários" . ($pendingCount > 0 ? " ({$pendingCount} pendente" . ($pendingCount > 1 ? 's' : '') . ")" : '');
    // }

    // public function getSubheading(): ?string
    // {
    //     return 'Usuários que se registraram e aguardam aprovação para acessar o sistema';
    // }
}
