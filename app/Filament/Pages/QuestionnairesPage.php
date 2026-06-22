<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\QuestionInputType;
use App\Models\Product;
use App\Models\Questionnaire;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class QuestionnairesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'questionnaires';

    protected static ?string $title = 'Gestion des questionnaires';

    protected static ?string $navigationLabel = 'Questionnaires';

    protected static string|null|UnitEnum $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 3;

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
                Questionnaire::query()
                    ->with('product.sector.category')
                    ->withCount('questions')
                    ->orderBy('name'),
            )
            ->modelLabel('questionnaire')
            ->pluralModelLabel('questionnaires')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sector.name')
                    ->label('Secteur')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('warning')
                    ->prefix('v')
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->badge()
                    ->color('success')
                    ->sortable(),
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
                SelectFilter::make('product')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema($this->getQuestionnaireFormSchema())
                    ->modalWidth('6xl'),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun questionnaire')
            ->emptyStateDescription('Crée un questionnaire et associe-le à un produit pour lancer le tunnel de comparaison.');
    }

    /**
     * @return array<int, Component>
     */
    protected function getQuestionnaireFormSchema(): array
    {
        return [
            Tabs::make('Questionnaire')
                ->tabs([
                    Tab::make('Général')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Select::make('product_id')
                                ->label('Produit')
                                ->options(fn (): array => Product::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                            Grid::make(3)->schema([
                                TextInput::make('name')
                                    ->label('Nom du questionnaire')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                TextInput::make('version')
                                    ->label('Version')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->prefix('v'),
                            ]),
                            Toggle::make('is_active')
                                ->label('Actif')
                                ->default(true),
                        ]),

                    Tab::make('Questions')
                        ->icon('heroicon-o-question-mark-circle')
                        ->badge(fn (?Questionnaire $record): ?int => $record?->questions_count)
                        ->schema([
                            Repeater::make('questions')
                                ->relationship('questions')
                                ->label('')
                                ->schema($this->getQuestionFields())
                                ->addActionLabel('Ajouter une question')
                                ->collapsible()
                                ->collapsed(fn (?array $state): bool => count((array) $state) > 3)
                                ->itemLabel(fn (array $state): string => ($state['step_key'] ?? '?').' › '.($state['label'] ?? 'Nouvelle question'))
                                ->defaultItems(0)
                                ->orderColumn('sort_order')
                                ->reorderable()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function getQuestionFields(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('step_key')
                    ->label('Étape (step_key)')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('ex: vehicule, conducteur'),
                TextInput::make('field_key')
                    ->label('Clé du champ (field_key)')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('ex: has_vehicle, driver_age'),
                Select::make('input_type')
                    ->label('Type de champ')
                    ->options(QuestionInputType::options())
                    ->required()
                    ->live(),
            ]),

            TextInput::make('label')
                ->label('Libellé de la question')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Grid::make(2)->schema([
                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->maxLength(255)
                    ->placeholder('ex: Entrez votre âge...'),
                TextInput::make('helper_text')
                    ->label('Texte d\'aide')
                    ->maxLength(500),
            ]),

            KeyValue::make('options_json')
                ->label('Options de réponse')
                ->keyLabel('Valeur (ex: oui)')
                ->valueLabel('Libellé affiché (ex: Oui)')
                ->addActionLabel('Ajouter une option')
                ->visible(fn (Get $get): bool => QuestionInputType::tryFrom((string) $get('input_type'))?->hasOptions() ?? false)
                ->columnSpanFull(),

            TagsInput::make('validation_rules_json')
                ->label('Règles de validation')
                ->placeholder('required, numeric, min:18, ...')
                ->helperText('Tapez une règle et appuyez sur Entrée. Exemples : required, numeric, min:0, max:120, date, before:today')
                ->columnSpanFull(),

            Section::make("Condition d'affichage")
                ->description('Laisser vide si la question est toujours visible.')
                ->icon('heroicon-o-eye')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('display_conditions_json.depends_on')
                            ->label('Dépend du champ (field_key)')
                            ->placeholder('ex: has_vehicle'),
                        TextInput::make('display_conditions_json.equals')
                            ->label('Si la valeur est')
                            ->placeholder('ex: oui'),
                    ]),
                ]),

            Grid::make(3)->schema([
                Toggle::make('is_required')
                    ->label('Obligatoire')
                    ->default(true),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un questionnaire')
                ->icon(Heroicon::OutlinedPlus)
                ->model(Questionnaire::class)
                ->schema($this->getQuestionnaireFormSchema())
                ->modalWidth('6xl'),
        ];
    }
}
