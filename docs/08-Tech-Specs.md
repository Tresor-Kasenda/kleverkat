# Spécifications Techniques

## 1. Structure des Fichiers

```
app/
├── Actions/
│   ├── Fortify/           # Actions Fortify existantes
│   ├── CreateCompany.php
│   ├── CreateProduct.php
│   └── CalculateScore.php
├── Enums/
│   ├── QuestionType.php
│   ├── LeadStatus.php
│   └── UserRole.php
├── Http/
│   ├── Controllers/
│   │   ├── ComparisonController.php
│   │   ├── ProductController.php
│   │   └── PartnerController.php
│   └── Requests/
│       ├── ComparisonRequest.php
│       └── CompanyUpdateRequest.php
├── Livewire/
│   ├── Public/
│   │   ├── CompareWizard.php
│   │   ├── ProductCard.php
│   │   ├── CategoryGrid.php
│   │   └── SearchBar.php
│   └── Partner/
│       ├── Dashboard.php
│       ├── ProductList.php
│       ├── ProductEdit.php
│       ├── ScoringManager.php
│       ├── LeadList.php
│       ├── LeadDetail.php
│       └── CompanyProfile.php
├── Models/
│   ├── Sector.php
│   ├── Company.php
│   ├── ProductCategory.php
│   ├── Product.php
│   ├── ProductDetail.php
│   ├── Questionnaire.php
│   ├── Question.php
│   ├── ScoringCoefficient.php
│   ├── Lead.php
│   └── LeadAction.php
├── Policies/
│   ├── ProductPolicy.php
│   ├── LeadPolicy.php
│   └── CompanyPolicy.php
├── Providers/
│   └── AppServiceProvider.php
├── Rules/
│   └── ValidCoefficient.php
├── Services/
│   ├── ComparisonService.php
│   ├── ScoringService.php
│   └── LeadService.php
└── Filament/
    └── Resources/
        ├── SectorResource.php
        ├── CompanyResource.php
        ├── ProductCategoryResource.php
        ├── ProductResource.php
        ├── QuestionnaireResource.php
        ├── QuestionResource.php
        └── LeadResource.php
```

## 2. Services

### ScoringService

```php
class ScoringService
{
    // Calcule le score d'un produit pour des réponses données
    public function calculateForProduct(
        array $answers, 
        Product $product
    ): float;

    // Calcule et classe tous les produits d'une catégorie
    public function rankProducts(
        array $answers, 
        ProductCategory $category
    ): Collection;

    // Évalue une réponse individuelle
    protected function evaluateAnswer(
        Question $question, 
        mixed $answer
    ): float;
}
```

### ComparisonService

```php
class ComparisonService
{
    // Démarre une session de comparaison
    public function startComparison(
        ProductCategory $category
    ): ComparisonSession;

    // Traite les réponses et retourne les résultats
    public function processResponses(
        array $answers, 
        ProductCategory $category
    ): ComparisonResult;

    // Crée un lead à partir des résultats
    public function createLead(
        array $personalInfo,
        ComparisonResult $result
    ): Lead;
}
```

### LeadService

```php
class LeadService
{
    // Crée un nouveau lead
    public function create(
        array $data, 
        Product $product, 
        Company $company
    ): Lead;

    // Enregistre une action sur un lead
    public function logAction(
        Lead $lead, 
        string $type, 
        ?array $metadata = null
    ): LeadAction;

    // Notifie le partenaire
    public function notifyPartner(Lead $lead): void;
}
```

## 3. Filament Resources

Chaque ressource Filament doit inclure :
- **Forms** : Définition des champs de formulaire
- **Tables** : Définition des colonnes, filtres, actions
- **Relations** : Gestion des relations (ex: Product → ProductDetail)
- **Widgets** : Widgets optionnels pour le dashboard

### Exemple : ProductResource

```php
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('company_id')
                ->relationship('company', 'name')
                ->required(),
            Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            TextInput::make('name')->required(),
            RichEditor::make('description'),
            TextInput::make('price_range_min')->numeric(),
            TextInput::make('price_range_max')->numeric(),
            KeyValue::make('highlights'),
            Toggle::make('is_active'),
            // ...
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('company.name'),
            TextColumn::make('category.name'),
            IconColumn::make('is_active')->boolean(),
            // ...
        ]);
    }
}
```

## 4. Migrations — Ordre de Création

1. `create_sectors_table`
2. `create_product_categories_table`
3. `add_role_to_users_table`
4. `create_companies_table`
5. `create_products_table`
6. `create_product_details_table`
7. `create_questionnaires_table`
8. `create_questions_table`
9. `create_scoring_coefficients_table`
10. `create_leads_table`
11. `create_lead_actions_table`

## 5. Tests

### Structure des Tests

```
tests/
├── Feature/
│   ├── ComparisonTest.php        # Test du processus de comparaison
│   ├── ScoringServiceTest.php    # Test du calcul de score
│   ├── LeadCreationTest.php      # Test de création de lead
│   ├── PartnerAccessTest.php     # Test des permissions partenaire
│   └── Admin/Filament/
│       ├── SectorResourceTest.php
│       ├── CompanyResourceTest.php
│       └── ProductResourceTest.php
├── Unit/
│   ├── Models/
│   │   ├── SectorTest.php
│   │   ├── ProductTest.php
│   │   ├── QuestionTest.php
│   │   └── ScoringCoefficientTest.php
│   └── Services/
│       ├── ScoringServiceTest.php
│       └── ComparisonServiceTest.php
└── Pest.php
```

## 6. Commandes Artisan Utiles

```bash
# Création des modèles
php artisan make:model Sector -m
php artisan make:model Company -m
php artisan make:model ProductCategory -m
php artisan make:model Product -m
php artisan make:model ProductDetail -m
php artisan make:model Questionnaire -m
php artisan make:model Question -m
php artisan make:model ScoringCoefficient -m
php artisan make:model Lead -m
php artisan make:model LeadAction -m

# Création des Filament Resources
php artisan make:filament-resource Sector --generate
php artisan make:filament-resource Company --generate
# ...

# Création des Livewire Components
php artisan make:livewire CompareWizard
php artisan make:livewire Partner/Dashboard
# ...

# Création des tests
php artisan make:test ComparisonTest --pest
php artisan make:test ScoringServiceTest --unit --pest
```
