<?php

use App\Enums\LeadActionType;
use App\Enums\QuestionInputType;
use App\Models\Category;
use App\Models\Company;
use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Sector;
use App\Models\User;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function buildCatalogue(): array
{
    $category = Category::factory()->create(['name' => 'Assurance', 'slug' => 'assurance', 'is_active' => true]);
    $sector = Sector::factory()->for($category)->create(['name' => 'Voyage', 'slug' => 'voyage', 'is_active' => true]);
    $product = Product::factory()->for($sector)->create(['name' => 'Assurance Voyage', 'slug' => 'assurance-voyage', 'is_active' => true]);
    $questionnaire = Questionnaire::factory()->for($product)->create(['name' => 'Questionnaire Voyage', 'is_active' => true]);

    Question::factory()->for($questionnaire)->create([
        'field_key' => 'destination', 'label' => 'Destination', 'input_type' => QuestionInputType::Select,
        'options_json' => ['europe' => 'Europe', 'monde' => 'Monde entier'], 'step_key' => 'voyage', 'sort_order' => 1, 'is_required' => true, 'is_active' => true,
    ]);
    Question::factory()->for($questionnaire)->create([
        'field_key' => 'duree', 'label' => 'Durée (jours)', 'input_type' => QuestionInputType::Number,
        'options_json' => null, 'step_key' => 'voyage', 'sort_order' => 2, 'is_required' => true, 'is_active' => true,
    ]);
    Question::factory()->for($questionnaire)->create([
        'field_key' => 'nom', 'label' => 'Nom complet', 'input_type' => QuestionInputType::Text,
        'options_json' => null, 'step_key' => 'identite', 'sort_order' => 3, 'is_required' => true, 'is_active' => true,
    ]);

    $company1 = Company::factory()->for($category)->create(['name' => 'AXA Travel', 'is_active' => true]);
    $company2 = Company::factory()->for($category)->create(['name' => 'Chapka', 'is_active' => true]);

    $offer1 = Offer::factory()->for($company1)->for($product)->create(['name' => 'AXA Voyage Europe', 'base_price' => 22.00, 'is_active' => true]);
    $offer2 = Offer::factory()->for($company2)->for($product)->create(['name' => 'Chapka Cap Travel', 'base_price' => 13.90, 'is_active' => true]);

    return compact('category', 'sector', 'product', 'questionnaire', 'company1', 'company2', 'offer1', 'offer2');
}

// ─── CategoryList ─────────────────────────────────────────────────────────────

test('compare categories page lists active categories', function () {
    $active = Category::factory()->create(['is_active' => true, 'slug' => 'active-cat']);
    $inactive = Category::factory()->create(['is_active' => false, 'slug' => 'inactive-cat']);

    $this->get(route('compare.categories'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Compare/CategoryList')
            ->has('categories')
            ->where('categories', fn ($cats) => $cats->contains('slug', $active->slug)
                && ! $cats->contains('slug', $inactive->slug))
        );
});

test('compare categories page is accessible without authentication', function () {
    $this->get(route('compare.categories'))->assertStatus(200);
});

// ─── SectorList ──────────────────────────────────────────────────────────────

test('sector list shows sectors for a category', function () {
    $category = Category::factory()->create(['is_active' => true, 'slug' => 'assurance-test']);
    $sector = Sector::factory()->for($category)->create(['is_active' => true]);
    $hidden = Sector::factory()->for($category)->create(['is_active' => false]);

    $this->get(route('compare.sectors', $category->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Compare/SectorList')
            ->where('sectors', fn ($sectors) => $sectors->contains('id', $sector->id)
                && ! $sectors->contains('id', $hidden->id))
        );
});

test('sector list returns 404 for inactive category', function () {
    $inactive = Category::factory()->create(['is_active' => false, 'slug' => 'inactive-cat']);

    $this->get(route('compare.sectors', $inactive->slug))->assertNotFound();
});

// ─── ProductList ─────────────────────────────────────────────────────────────

test('product list shows active products for a sector', function () {
    $category = Category::factory()->create(['is_active' => true, 'slug' => 'cat-prod-test']);
    $sector = Sector::factory()->for($category)->create(['is_active' => true, 'slug' => 'sect-prod-test']);
    $active = Product::factory()->for($sector)->create(['is_active' => true]);
    $inactive = Product::factory()->for($sector)->create(['is_active' => false]);

    $this->get(route('compare.products', [$category->slug, $sector->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Compare/ProductList')
            ->where('products', fn ($products) => $products->contains('id', $active->id)
                && ! $products->contains('id', $inactive->id))
        );
});

test('product list returns 404 if sector does not belong to category', function () {
    $category = Category::factory()->create(['is_active' => true, 'slug' => 'cat-mismatch']);
    $otherCategory = Category::factory()->create(['is_active' => true, 'slug' => 'other-cat-mismatch']);
    $sector = Sector::factory()->for($otherCategory)->create(['is_active' => true, 'slug' => 'mismatch-sect']);

    $this->get(route('compare.products', [$category->slug, $sector->slug]))->assertNotFound();
});

// ─── Wizard ──────────────────────────────────────────────────────────────────

describe('Wizard — questionnaire multi-étapes', function () {

    beforeEach(function () {
        $d = buildCatalogue();
        $this->category = $d['category'];
        $this->sector = $d['sector'];
        $this->product = $d['product'];
        $this->questionnaire = $d['questionnaire'];
    });

    it('affiche le questionnaire avec les étapes', function () {
        $this->get(route('compare.wizard', [$this->category->slug, $this->sector->slug, $this->product->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Compare/Wizard')
                ->has('steps')
                ->has('stepKeys')
                ->where('product.slug', $this->product->slug)
            );
    });

    it('crée une ComparisonSession et redirige vers les résultats à la soumission finale', function () {
        $this->post(route('compare.wizard.store', [$this->category->slug, $this->sector->slug, $this->product->slug]), [
            'answers' => ['destination' => 'europe', 'duree' => '14', 'nom' => 'Marie Dupont'],
        ])->assertRedirectContains('/comparer/resultats/');

        $session = ComparisonSession::where('product_id', $this->product->id)->firstOrFail();
        expect($session->answers_json['destination'])->toBe('europe')
            ->and($session->isCompleted())->toBeTrue();

        expect(ComparisonResult::where('comparison_session_id', $session->id)->count())->toBe(2);
    });

    it('retourne 404 si le produit n\'a pas de questionnaire actif', function () {
        $product = Product::factory()->for($this->sector)->create(['is_active' => true, 'slug' => 'prod-no-questionnaire']);

        $this->get(route('compare.wizard', [$this->category->slug, $this->sector->slug, $product->slug]))->assertNotFound();
    });
});

// ─── Results ─────────────────────────────────────────────────────────────────

describe('Results — affichage et creation de lead', function () {

    beforeEach(function () {
        $d = buildCatalogue();
        $this->category = $d['category'];
        $this->sector = $d['sector'];
        $this->product = $d['product'];
        $this->questionnaire = $d['questionnaire'];
        $this->offer1 = $d['offer1'];
        $this->offer2 = $d['offer2'];

        $this->session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['destination' => 'europe', 'duree' => '14', 'nom' => 'Test User'],
            'completed_at' => now(),
        ]);

        $this->result1 = ComparisonResult::factory()->for($this->session, 'session')->for($this->offer1)->create([
            'company_id' => $this->offer1->company_id,
            'is_eligible' => true,
            'calculated_price' => 22.00,
            'rank_position' => 2,
        ]);
        $this->result2 = ComparisonResult::factory()->for($this->session, 'session')->for($this->offer2)->create([
            'company_id' => $this->offer2->company_id,
            'is_eligible' => true,
            'calculated_price' => 13.90,
            'rank_position' => 1,
        ]);
    });

    it('affiche les résultats classés du moins cher au plus cher', function () {
        $this->get(route('compare.results', $this->session))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Compare/Results')
                ->has('results', 2)
                ->where('results.0.offer.company.name', 'Chapka')
                ->where('results.1.offer.company.name', 'AXA Travel')
            );
    });

    it('retourne 404 pour une session non complétée', function () {
        $incomplete = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [],
            'completed_at' => null,
        ]);

        $this->get(route('compare.results', $incomplete->id))->assertNotFound();
    });

    it('crée un lead via l\'API JSON', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('compare.leads.create', $this->result2), [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'phone' => '0612345678',
                'action_type' => LeadActionType::QuoteRequest->value,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $lead = Lead::where('comparison_result_id', $this->result2->id)->first();
        expect($lead)->not->toBeNull()
            ->and($lead->contact_email)->toBe('jean.dupont@example.com')
            ->and($lead->action_type)->toBe(LeadActionType::QuoteRequest);
    });

    it('valide les champs obligatoires du formulaire de contact', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('compare.leads.create', $this->result2), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'action_type']);
    });
});
