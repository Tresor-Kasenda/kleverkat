<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\LeadActionType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class LeadsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'leads';

    protected static ?string $title = 'Gestion des leads';

    protected static ?string $navigationLabel = 'Leads';

    protected static string|null|UnitEnum $navigationGroup = 'Offres';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->with(['company', 'offer', 'result'])
                    ->latest(),
            )
            ->modelLabel('lead')
            ->pluralModelLabel('leads')
            ->columns([
                TextColumn::make('company.name')
                    ->label('Entreprise')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('offer.name')
                    ->label('Offre')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('action_type')
                    ->label('Action')
                    ->formatStateUsing(fn (LeadActionType $state): string => $state->label())
                    ->badge()
                    ->color(fn (LeadActionType $state): string => $state->color()),
                TextColumn::make('contact_first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('contact_last_name')
                    ->label('Nom')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('contact_phone')
                    ->label('Téléphone')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                    ->color(fn (LeadStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Envoyé le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(LeadStatus::options()),
                SelectFilter::make('action_type')
                    ->label('Action')
                    ->options(LeadActionType::options()),
                SelectFilter::make('company')
                    ->label('Entreprise')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema($this->getLeadViewSchema()),
                EditAction::make()
                    ->schema($this->getLeadEditSchema()),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun lead')
            ->emptyStateDescription('Les leads sont créés lorsqu\'un utilisateur déclenche une action commerciale depuis les résultats de comparaison.');
    }

    /**
     * @return array<int, Component>
     */
    protected function getLeadViewSchema(): array
    {
        return [
            Section::make('Contact')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextEntry::make('contact_first_name')->label('Prénom'),
                    TextEntry::make('contact_last_name')->label('Nom'),
                    TextEntry::make('contact_email')->label('Email')->copyable(),
                    TextEntry::make('contact_phone')->label('Téléphone')->placeholder('—'),
                ]),

            Section::make('Offre')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->schema([
                    TextEntry::make('company.name')->label('Entreprise')->badge(),
                    TextEntry::make('offer.name')->label('Offre')->badge()->color('info'),
                    TextEntry::make('action_type')
                        ->label('Action déclenchée')
                        ->formatStateUsing(fn (LeadActionType $state): string => $state->label()),
                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge()
                        ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                        ->color(fn (LeadStatus $state): string => $state->color()),
                ]),

            Section::make('Suivi')
                ->icon('heroicon-o-clock')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    TextEntry::make('sent_at')->label('Envoyé au partenaire le')->dateTime('d/m/Y H:i')->placeholder('Non transmis'),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function getLeadEditSchema(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('status')
                    ->label('Statut')
                    ->options(LeadStatus::options())
                    ->required(),
                Select::make('action_type')
                    ->label('Type d\'action')
                    ->options(LeadActionType::options())
                    ->required(),
            ]),
        ];
    }
}
