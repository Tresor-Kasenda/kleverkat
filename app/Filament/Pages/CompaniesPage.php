<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\TeamRole;
use App\Models\Company;
use App\Models\User;
use App\Notifications\Companies\CompanyAssignedManager;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnitEnum;

class CompaniesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'companies';

    protected static ?string $title = 'Gestion des entreprises';

    protected static ?string $navigationLabel = 'Entreprises';

    protected static string|UnitEnum|null $navigationGroup = 'Partenaires';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->with(['category', 'team', 'manager'])
                    ->orderBy('name')
            )
            ->modelLabel('entreprise')
            ->pluralModelLabel('entreprises')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Secteur')
                    ->badge()
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Équipe')
                    ->placeholder('Non liée')
                    ->toggleable(),
                TextColumn::make('manager.name')
                    ->label('Gestionnaire')
                    ->placeholder('Non assigné')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('support_email')
                    ->label('Email support')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('website_url')
                    ->label('Site web')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Maj')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->schema($this->getCompanyFormSchema())
                    ->modalWidth('4xl')
                    ->after(function (Company $record): void {
                        if ($record->wasChanged('manager_id')) {
                            $record->load('manager');
                            $record->manager?->notify(new CompanyAssignedManager($record, isNew: false));
                        }
                    }),
                DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->emptyStateHeading('Aucune entreprise')
            ->emptyStateDescription('Crée une entreprise partenaire et rattache-la à une catégorie.');
    }

    /**
     * Company form schema shared by the create and edit actions: a company is
     * linked to a category, a partner team and the team owner (its manager).
     *
     * @return array<int, Component>
     */
    protected function getCompanyFormSchema(): array
    {
        return [
            Section::make('Rattachement')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->columns(2)
                ->schema([
                    Select::make('category_id')
                        ->label('Secteur d\'activité')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('team_id')
                        ->label('Équipe partenaire')
                        ->relationship('team', 'name', fn (Builder $query) => $query->where('is_personal', false))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->unique(Company::class, 'team_id', ignoreRecord: true)
                        ->live()
                        ->afterStateUpdated(fn (Set $set): null => $set('manager_id', null))
                        ->helperText('Une équipe ne peut être liée qu’à une seule entreprise.'),
                    Select::make('manager_id')
                        ->label('Gestionnaire (propriétaire de l’équipe)')
                        ->options(fn (Get $get): array => $this->getTeamOwnerOptions($get('team_id')))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn (Get $get): bool => blank($get('team_id')))
                        ->rule(fn (Get $get) => Rule::exists('team_members', 'user_id')
                            ->where('team_id', $get('team_id') ?? 0)
                            ->where('role', TeamRole::Owner->value))
                        ->helperText('Le gestionnaire est le propriétaire (Owner) de l’équipe partenaire.')
                        ->columnSpanFull(),
                ]),
            ...$this->getProfileSections(),
        ];
    }

    /**
     * Owners (managers) of the given partner team.
     *
     * @return array<int, string>
     */
    protected function getTeamOwnerOptions(int|string|null $teamId): array
    {
        if (blank($teamId)) {
            return [];
        }

        return User::query()
            ->whereHas('teamMemberships', fn ($query) => $query
                ->where('team_id', $teamId)
                ->where('role', TeamRole::Owner->value))
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email'])
            ->mapWithKeys(fn (User $user): array => [
                $user->id => "{$user->name} ({$user->email})",
            ])
            ->all();
    }

    /**
     * Identity, contact, address and status sections.
     *
     * @return array<int, Component>
     */
    protected function getProfileSections(): array
    {
        return [
            Section::make('Identité')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                            if (($get('slug') ?? '') !== Str::slug((string) $old)) {
                                return;
                            }

                            $set('slug', Str::slug((string) $state));
                        }),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Company::class, 'slug', ignoreRecord: true),
                    TextInput::make('logo_path')
                        ->label('Chemin du logo')
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),
            Section::make('Coordonnées')
                ->icon(Heroicon::OutlinedPhone)
                ->columns(2)
                ->schema([
                    TextInput::make('website_url')
                        ->label('Site web')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('support_email')
                        ->label('Email support')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('support_phone')
                        ->label('Téléphone support')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('contact_name')
                        ->label('Contact principal')
                        ->maxLength(255),
                ]),
            Section::make('Adresse')
                ->icon(Heroicon::OutlinedMapPin)
                ->columns(2)
                ->schema([
                    TextInput::make('address_line_1')
                        ->label('Adresse')
                        ->maxLength(255),
                    TextInput::make('address_line_2')
                        ->label("Complément d'adresse")
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label('Ville')
                        ->maxLength(255),
                    TextInput::make('postal_code')
                        ->label('Code postal')
                        ->maxLength(100),
                    TextInput::make('country')
                        ->label('Pays')
                        ->maxLength(255),
                ]),
            Section::make('Statut')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Créer une entreprise')
                ->icon(Heroicon::OutlinedPlus)
                ->model(Company::class)
                ->schema($this->getCompanyFormSchema())
                ->modalWidth('4xl')
                ->after(function (Company $record): void {
                    $record->manager->notify(new CompanyAssignedManager($record, isNew: true));
                }),
        ];
    }
}
