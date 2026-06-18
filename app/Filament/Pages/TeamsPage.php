<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Teams\CreateTeamWithMembers;
use App\Enums\TeamRole;
use App\Models\Team;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use UnitEnum;

class TeamsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'teams';

    protected static ?string $title = 'Gestion des équipes';

    protected static ?string $navigationLabel = 'Équipes';

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 4;

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
                Team::query()
                    ->whereIn('id', $this->getCachedTeamIds())
                    ->withCount('members')
                    ->with('company')
                    ->orderBy('name')
            )
            ->modelLabel('équipe')
            ->pluralModelLabel('équipes')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner')
                    ->label('Propriétaire')
                    ->state(fn (Team $record): ?string => $record->owner()?->name)
                    ->placeholder('Non désigné'),
                TextColumn::make('members_count')
                    ->label('Membres')
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Entreprise')
                    ->placeholder('Non liée')
                    ->toggleable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Maj')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->schema($this->getTeamEditSchema())
                    ->modalWidth('2xl'),
                DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->emptyStateHeading('Aucune équipe')
            ->emptyStateDescription('Crée une équipe partenaire et ses utilisateurs.');
    }

    /**
     * Edit schema: team identity only (members are managed at creation).
     *
     * @return array<int, Component>
     */
    protected function getTeamEditSchema(): array
    {
        return $this->getTeamIdentityFields();
    }

    /**
     * @return array<int, Component>
     */
    protected function getTeamIdentityFields(): array
    {
        return [
            Section::make('Équipe')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom de l’équipe')
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
                        ->unique(Team::class, 'slug', ignoreRecord: true),
                ]),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getCachedTeamIds(): array
    {
        return Cache::remember('teams:ids', 3600, function () {
            return Team::query()
                ->where('is_personal', false)
                ->pluck('id')
                ->toArray();
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Créer une équipe')
                ->icon(Heroicon::OutlinedPlus)
                ->model(Team::class)
                ->schema($this->getTeamCreateSchema())
                ->using(fn (array $data): Team => app(CreateTeamWithMembers::class)->handle($data))
                ->modalWidth('3xl'),
        ];
    }

    /**
     * Create schema: team identity + the member accounts to provision.
     *
     * @return array<int, Component>
     */
    protected function getTeamCreateSchema(): array
    {
        return [
            ...$this->getTeamIdentityFields(),
            Repeater::make('members')
                ->label('Membres')
                ->helperText('Désigne au moins un propriétaire (Owner) : il sera le gestionnaire de l’entreprise.')
                ->addActionLabel('Ajouter un membre')
                ->minItems(1)
                ->defaultItems(1)
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->placeholder('Votre nom')
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->placeholder('Votre email')
                        ->maxLength(255)
                        ->unique(table: 'users', column: 'email'),
                    Select::make('role')
                        ->label('Rôle')
                        ->placeholder('Choisissez le role')
                        ->native(false)
                        ->options($this->getRoleOptions())
                        ->default(TeamRole::Member->value)
                        ->required(),
                    TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->placeholder('*****************')
                        ->revealable()
                        ->required(),
                ]),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getRoleOptions(): array
    {
        return collect(TeamRole::cases())
            ->mapWithKeys(fn (TeamRole $role): array => [$role->value => $role->label()])
            ->all();
    }
}
