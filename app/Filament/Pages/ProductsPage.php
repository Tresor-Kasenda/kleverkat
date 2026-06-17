<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ProductBillingFrequency;
use App\Enums\ProductCategory;
use App\Enums\ProductPriceType;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ProductsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'products';

    protected static ?string $title = 'Gestion des produits';

    protected static ?string $navigationLabel = 'Produits';

    protected static string|null|UnitEnum $navigationGroup = 'Catalogue';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 2;

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
                Product::query()
                    ->with('sector')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            )
            ->modelLabel('produit')
            ->pluralModelLabel('produits')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sector.name')
                    ->label('Secteur')
                    ->badge()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn (ProductCategory $state): string => $state->label())
                    ->toggleable(),
                TextColumn::make('price_type')
                    ->label('Tarif')
                    ->formatStateUsing(fn (ProductPriceType $state): string => $state->label())
                    ->badge()
                    ->color(fn (ProductPriceType $state): string => match ($state) {
                        ProductPriceType::Fixed => 'success',
                        ProductPriceType::Variable => 'warning',
                        ProductPriceType::OnQuote => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('base_price')
                    ->label('Prime de base')
                    ->money(fn (Product $record): string => $record->currency)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('duration_months')
                    ->label('Durée (mois)')
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
                SelectFilter::make('sector')
                    ->label('Secteur')
                    ->relationship('sector', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(ProductCategory::options()),
                SelectFilter::make('price_type')
                    ->label('Type de tarif')
                    ->options(ProductPriceType::options()),
                TernaryFilter::make('is_active')
                    ->label('Actif'),
                TernaryFilter::make('is_featured')
                    ->label('Mis en avant'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(Product::class)
                    ->schema($this->getProductFormSchema())
                    ->modalWidth('5xl'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema($this->getProductViewSchema())
                    ->modalWidth('5xl'),
                EditAction::make()
                    ->schema($this->getProductFormSchema())
                    ->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order')
            ->emptyStateHeading('Aucun produit')
            ->emptyStateDescription('Crée un produit et rattache-le à un secteur.');
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    protected function getProductViewSchema(): array
    {
        return [
            Section::make('Identification')
                ->icon('heroicon-o-information-circle')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nom du produit')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                        ->columnSpan(2),
                    TextEntry::make('code')
                        ->label('Code interne')
                        ->badge()
                        ->color('gray')
                        ->placeholder('—'),
                    TextEntry::make('sector.name')
                        ->label('Secteur')
                        ->badge(),
                    TextEntry::make('category')
                        ->label('Catégorie')
                        ->badge()
                        ->formatStateUsing(fn (?ProductCategory $state): string => $state?->label() ?? '—'),
                    TextEntry::make('slug')
                        ->label('Slug')
                        ->color('gray'),
                    TextEntry::make('short_description')
                        ->label('Description courte')
                        ->columnSpanFull()
                        ->placeholder('—'),
                    TextEntry::make('description')
                        ->label('Description complète')
                        ->columnSpanFull()
                        ->prose()
                        ->placeholder('—'),
                ]),

            Section::make('Tarification')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    TextEntry::make('price_type')
                        ->label('Type de tarif')
                        ->badge()
                        ->formatStateUsing(fn (?ProductPriceType $state): string => $state?->label() ?? '—')
                        ->color(fn (?ProductPriceType $state): string => match ($state) {
                            ProductPriceType::Fixed => 'success',
                            ProductPriceType::Variable => 'warning',
                            ProductPriceType::OnQuote => 'gray',
                            null => 'gray',
                        }),
                    TextEntry::make('base_price')
                        ->label('Prime de base')
                        ->money(fn (Product $record): string => $record->currency)
                        ->placeholder('Sur devis'),
                    TextEntry::make('currency')
                        ->label('Devise'),
                    TextEntry::make('billing_frequency')
                        ->label('Fréquence de facturation')
                        ->formatStateUsing(fn (?ProductBillingFrequency $state): string => $state?->label() ?? '—')
                        ->placeholder('—'),
                ]),

            Section::make("Conditions d'éligibilité")
                ->icon('heroicon-o-shield-check')
                ->columns(3)
                ->schema([
                    TextEntry::make('min_age')
                        ->label('Âge minimum')
                        ->suffix(' ans')
                        ->placeholder('Non défini'),
                    TextEntry::make('max_age')
                        ->label('Âge maximum')
                        ->suffix(' ans')
                        ->placeholder('Non défini'),
                    TextEntry::make('duration_months')
                        ->label('Durée du contrat')
                        ->suffix(' mois')
                        ->placeholder('Non défini'),
                    TextEntry::make('min_insured_amount')
                        ->label('Montant minimum assuré')
                        ->money(fn (Product $record): string => $record->currency)
                        ->placeholder('Non défini'),
                    TextEntry::make('max_insured_amount')
                        ->label('Montant maximum assuré')
                        ->money(fn (Product $record): string => $record->currency)
                        ->placeholder('Non défini'),
                    TextEntry::make('waiting_period_days')
                        ->label('Délai de carence')
                        ->suffix(' jours')
                        ->placeholder('Aucun'),
                ]),

            Grid::make(2)->schema([
                Section::make('Garanties incluses')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        RepeatableEntry::make('features')
                            ->label('')
                            ->schema([
                                TextEntry::make('label')
                                    ->label('')
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->contained(false)
                            ->placeholder('Aucune garantie renseignée.'),
                    ]),

                Section::make('Exclusions')
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        RepeatableEntry::make('exclusions')
                            ->label('')
                            ->schema([
                                TextEntry::make('label')
                                    ->label('')
                                    ->icon('heroicon-o-x-circle')
                                    ->iconColor('danger'),
                            ])
                            ->contained(false)
                            ->placeholder('Aucune exclusion renseignée.'),
                    ]),
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
                    TextEntry::make('available_from')
                        ->label('Disponible à partir du')
                        ->date('d/m/Y')
                        ->placeholder('—'),
                    TextEntry::make('available_until')
                        ->label('Disponible jusqu\'au')
                        ->date('d/m/Y')
                        ->placeholder('—'),
                    TextEntry::make('sort_order')
                        ->label("Ordre d'affichage"),
                    TextEntry::make('updated_at')
                        ->label('Dernière mise à jour')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ];
    }

    /**
     * @return array<int, Field>
     */
    protected function getProductFormSchema(): array
    {
        return [
            Tabs::make('Produit')
                ->tabs([
                    Tab::make('Général')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('sector_id')
                                    ->label('Secteur')
                                    ->relationship('sector', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('category')
                                    ->label('Catégorie')
                                    ->options(ProductCategory::options())
                                    ->searchable(),
                            ]),
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
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                            ]),
                            TextInput::make('code')
                                ->label('Code interne')
                                ->maxLength(50)
                                ->unique(Product::class, 'code', ignoreRecord: true)
                                ->placeholder('ex : ASS-VIE-001'),
                            TextInput::make('short_description')
                                ->label('Description courte')
                                ->maxLength(500)
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->label('Description complète')
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Tarification')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('price_type')
                                    ->label('Type de tarif')
                                    ->options(ProductPriceType::options())
                                    ->required()
                                    ->default(ProductPriceType::OnQuote->value)
                                    ->live(),
                                TextInput::make('base_price')
                                    ->label('Prime de base')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix(fn (Get $get): string => $get('currency') ?: 'USD')
                                    ->visible(fn (Get $get): bool => $get('price_type') === ProductPriceType::Fixed->value),
                                Select::make('currency')
                                    ->label('Devise')
                                    ->options([
                                        'USD' => 'USD — Dollar américain',
                                        'CDF' => 'CDF — Franc congolais',
                                        'EUR' => 'EUR — Euro',
                                    ])
                                    ->default('USD')
                                    ->required(),
                            ]),
                            Select::make('billing_frequency')
                                ->label('Fréquence de facturation')
                                ->options(ProductBillingFrequency::options())
                                ->searchable(),
                        ]),

                    Tab::make('Conditions')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('min_age')
                                    ->label('Âge minimum')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->suffix('ans'),
                                TextInput::make('max_age')
                                    ->label('Âge maximum')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->suffix('ans'),
                                TextInput::make('min_insured_amount')
                                    ->label('Montant minimum assuré')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix(fn (Get $get): string => $get('currency') ?: 'USD'),
                                TextInput::make('max_insured_amount')
                                    ->label('Montant maximum assuré')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix(fn (Get $get): string => $get('currency') ?: 'USD'),
                                TextInput::make('duration_months')
                                    ->label('Durée du contrat')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('mois'),
                                TextInput::make('waiting_period_days')
                                    ->label('Délai de carence')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('jours'),
                            ]),
                        ]),

                    Tab::make('Garanties')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Repeater::make('features')
                                ->label('Garanties incluses')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('Garantie')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->addActionLabel('Ajouter une garantie')
                                ->collapsible()
                                ->defaultItems(0)
                                ->columnSpanFull(),
                            Repeater::make('exclusions')
                                ->label('Exclusions')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('Exclusion')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->addActionLabel('Ajouter une exclusion')
                                ->collapsible()
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
                                    ->label('Mis en avant dans le catalogue')
                                    ->default(false),
                            ]),
                            Grid::make(2)->schema([
                                DatePicker::make('available_from')
                                    ->label('Disponible à partir du')
                                    ->native(false),
                                DatePicker::make('available_until')
                                    ->label('Disponible jusqu\'au')
                                    ->native(false)
                                    ->after('available_from'),
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
}
