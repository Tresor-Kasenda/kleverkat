<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Question;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
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
use UnitEnum;

class OffersPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'offers';

    protected static ?string $title = 'Gestion des offres';

    protected static ?string $navigationLabel = 'Offres';

    protected static string|null|UnitEnum $navigationGroup = 'Offres';

    protected static ?int $navigationSort = 0;

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
                Offer::query()
                    ->with(['company.category', 'product.sector.category'])
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            )
            ->modelLabel('offre')
            ->pluralModelLabel('offres')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Entreprise')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('base_price')
                    ->label('Prix de base')
                    ->money('EUR')
                    ->placeholder('Sur devis')
                    ->toggleable(),
                TextColumn::make('price_note')
                    ->label('Note de prix')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->label('Mis en avant')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Maj')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(fn (): array => Category::query()
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $q, $categoryId): Builder => $q->whereHas(
                            'company',
                            fn (Builder $companyQuery): Builder => $companyQuery->where('category_id', $categoryId),
                        ),
                    )),
                SelectFilter::make('company')
                    ->label('Entreprise')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('product')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Actif'),
                TernaryFilter::make('is_featured')
                    ->label('Mis en avant'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema($this->getOfferViewSchema())
                    ->modalWidth('5xl'),
                EditAction::make()
                    ->schema($this->getOfferFormSchema())
                    ->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order')
            ->emptyStateHeading('Aucune offre')
            ->emptyStateDescription('Crée une offre partenaire et rattache-la à une entreprise et un produit.');
    }

    /**
     * @return array<int, Component>
     */
    protected function getOfferViewSchema(): array
    {
        return [
            Section::make('Identification')
                ->icon('heroicon-o-information-circle')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label("Nom de l'offre")
                        ->weight(FontWeight::Bold)
                        ->columnSpan(2),
                    TextEntry::make('slug')
                        ->label('Slug')
                        ->color('gray'),
                    TextEntry::make('company.name')
                        ->label('Entreprise')
                        ->badge(),
                    TextEntry::make('product.name')
                        ->label('Produit')
                        ->badge(),
                    TextEntry::make('short_description')
                        ->label('Description courte')
                        ->columnSpanFull()
                        ->placeholder('—'),
                    TextEntry::make('long_description')
                        ->label('Description complète')
                        ->columnSpanFull()
                        ->prose()
                        ->placeholder('—'),
                ]),

            Section::make('Tarification')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    TextEntry::make('base_price')
                        ->label('Prix de base')
                        ->money('EUR')
                        ->placeholder('Sur devis'),
                    TextEntry::make('price_note')
                        ->label('Note sur le prix')
                        ->placeholder('—'),
                ]),

            Section::make('Caractéristiques')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    RepeatableEntry::make('features')
                        ->label('')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('label')
                                    ->label('Libellé')
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                                TextEntry::make('value')
                                    ->label('Valeur')
                                    ->placeholder('—'),
                            ]),
                        ])
                        ->contained(false)
                        ->placeholder('Aucune caractéristique renseignée.'),
                ]),

            Section::make('Publication')
                ->icon('heroicon-o-eye')
                ->columns(4)
                ->schema([
                    IconEntry::make('is_active')
                        ->label('Actif')
                        ->boolean(),
                    IconEntry::make('is_featured')
                        ->label('Mis en avant')
                        ->boolean(),
                    TextEntry::make('sort_order')
                        ->label("Ordre d'affichage"),
                    TextEntry::make('updated_at')
                        ->label('Dernière mise à jour')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function getOfferFormSchema(): array
    {
        return [
            Tabs::make('Offre')
                ->tabs([
                    Tab::make('Général')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Select::make('company_id')
                                ->label('Entreprise')
                                ->relationship('company', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->columnSpanFull(),
                            Select::make('product_id')
                                ->label('Produit')
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                            Grid::make(2)->schema([
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
                                    ->unique(Offer::class, 'slug', ignoreRecord: true),
                            ]),
                            TextInput::make('short_description')
                                ->label('Description courte')
                                ->maxLength(500)
                                ->columnSpanFull(),
                            Textarea::make('long_description')
                                ->label('Description complète')
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Tarification')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('base_price')
                                    ->label('Prix de base')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('EUR')
                                    ->placeholder('Laisser vide si sur devis'),
                                TextInput::make('price_note')
                                    ->label('Note de prix')
                                    ->maxLength(255)
                                    ->placeholder('ex: À partir de 15€/mois'),
                            ]),
                        ]),

                    Tab::make('Caractéristiques')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Repeater::make('features')
                                ->label('Caractéristiques et garanties')
                                ->relationship('features')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('label')
                                            ->label('Libellé')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('value')
                                            ->label('Valeur')
                                            ->maxLength(255)
                                            ->placeholder('ex: Inclus, 5000€, etc.'),
                                    ]),
                                    Grid::make(2)->schema([
                                        Toggle::make('is_highlight')
                                            ->label('Mis en avant')
                                            ->default(false),
                                        TextInput::make('sort_order')
                                            ->label('Ordre')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ]),
                                ])
                                ->addActionLabel('Ajouter une caractéristique')
                                ->collapsible()
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Règles')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->badge(fn (?Offer $record): ?int => $record?->rules()->count() ?: null)
                        ->schema([
                            Repeater::make('rules')
                                ->relationship('rules')
                                ->label('')
                                ->schema($this->getOfferRuleFields())
                                ->addActionLabel('Ajouter une règle')
                                ->collapsible()
                                ->itemLabel(function (array $state): string {
                                    $ruleType = OfferRuleType::tryFrom((string) ($state['rule_type'] ?? ''));
                                    $type = $ruleType?->label() ?? '?';
                                    $ruleOperator = OfferRuleOperator::tryFrom((string) ($state['operator'] ?? ''));
                                    $op = $ruleOperator->value ?? '?';

                                    return $type.' — '.$op.' '.(string) ($state['expected_value'] ?? '');
                                })
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Publication')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Grid::make(2)->schema([
                                Toggle::make('is_active')
                                    ->label('Actif')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Mis en avant')
                                    ->default(false),
                            ]),
                            TextInput::make('sort_order')
                                ->label("Ordre d'affichage")
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function getOfferRuleFields(): array
    {
        return [
            Grid::make(3)->schema([
                Select::make('rule_type')
                    ->label('Type de règle')
                    ->options(OfferRuleType::options())
                    ->required()
                    ->live(),
                Select::make('operator')
                    ->label('Opérateur')
                    ->options(OfferRuleOperator::options())
                    ->required(),
                TextInput::make('expected_value')
                    ->label('Valeur attendue')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('ex: 25, oui, paris'),
            ]),

            Select::make('question_id')
                ->label('Question liée')
                ->options(function (Get $get): array {
                    $productId = $get('../product_id');

                    return Question::query()
                        ->whereHas('questionnaire', fn ($q) => $q->where('product_id', $productId))
                        ->with('questionnaire')
                        ->get()
                        ->mapWithKeys(fn (Question $q): array => [
                            $q->id => $q->questionnaire->name.' › '.$q->label,
                        ])
                        ->all();
                })
                ->required()
                ->searchable()
                ->helperText('Seules les questions du produit sélectionné sont affichées.')
                ->columnSpanFull(),

            Grid::make(4)->schema([
                TextInput::make('score_delta')
                    ->label('Delta score')
                    ->numeric()
                    ->placeholder('ex: 15 ou -10')
                    ->helperText('Points ajoutés au score si la règle est satisfaite.')
                    ->visible(fn (Get $get): bool => $get('rule_type') === OfferRuleType::Scoring->value),
                TextInput::make('weight')
                    ->label('Poids')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('ex: 1.5')
                    ->helperText('Multiplicateur appliqué au delta score.')
                    ->visible(fn (Get $get): bool => $get('rule_type') === OfferRuleType::Scoring->value),
                TextInput::make('price_delta')
                    ->label('Delta prix (€)')
                    ->numeric()
                    ->placeholder('ex: 50 ou -20')
                    ->helperText('Montant ajouté au prix calculé.')
                    ->visible(fn (Get $get): bool => $get('rule_type') === OfferRuleType::Pricing->value),
                TextInput::make('price_multiplier')
                    ->label('Multiplicateur prix')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('ex: 1.15 = +15%')
                    ->helperText('Multiplié par le prix de base.')
                    ->visible(fn (Get $get): bool => $get('rule_type') === OfferRuleType::Pricing->value),
            ]),

            Grid::make(2)->schema([
                TextInput::make('priority')
                    ->label("Priorité d'évaluation")
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Les règles sont évaluées par ordre croissant de priorité.'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une offre')
                ->icon(Heroicon::OutlinedPlus)
                ->model(Offer::class)
                ->schema($this->getOfferFormSchema())
                ->modalWidth('5xl'),
        ];
    }
}
