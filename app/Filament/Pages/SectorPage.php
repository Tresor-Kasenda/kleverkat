<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Sector;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class SectorPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Gestion des secteurs';
    protected static string|null|UnitEnum $navigationGroup = 'Catalogue';
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';
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
                Sector::query()
                    ->withCount('products')
                    ->orderBy('sort_order')
                    ->orderBy('name')
            )
            ->modelLabel('secteur')
            ->pluralModelLabel('secteurs')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label('Produits')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Maj')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Créer un secteur')
                    ->icon('heroicon-s-plus')
                    ->model(Sector::class)
                    ->schema($this->getSectorFormSchema())
                    ->modalWidth('3xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->schema($this->getSectorFormSchema())
                    ->modalWidth('3xl'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->defaultSort('sort_order')
            ->emptyStateHeading('Aucun secteur')
            ->emptyStateDescription('Commence par créer un secteur pour organiser le catalogue.');
    }

    /**
     * @return array<int, Field>
     */
    protected function getSectorFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                    if (($get('slug') ?? '') !== Str::slug((string)$old)) {
                        return;
                    }

                    $set('slug', Str::slug((string)$state));
                }),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(Sector::class, 'slug', ignoreRecord: true),
            Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->label("Ordre d'affichage")
                ->required()
                ->numeric()
                ->default(0)
                ->minValue(0),
            Toggle::make('is_active')
                ->label('Actif')
                ->default(true),
        ];
    }
}
