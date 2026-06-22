<?php

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use App\Filament\Pages\OffersPage;
use App\Models\Offer;
use App\Models\OfferRule;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\User;
use Livewire\Livewire;

test('offer rules cascade delete when offer is deleted', function () {
    $offer = Offer::factory()->create();
    $rule = OfferRule::factory()->eligibility()->for($offer)->create();

    $offer->delete();

    $this->assertDatabaseMissing(OfferRule::class, ['id' => $rule->id]);
});

test('offer rules cascade delete when question is deleted', function () {
    $offer = Offer::factory()->create();
    $question = Question::factory()->for(Questionnaire::factory()->state(['product_id' => $offer->product_id]))->create();
    $rule = OfferRule::factory()->eligibility()->for($offer)->for($question)->create();

    $question->delete();

    $this->assertDatabaseMissing(OfferRule::class, ['id' => $rule->id]);
});

test('offer has many rules', function () {
    $offer = Offer::factory()->create();
    OfferRule::factory()->eligibility()->for($offer)->count(2)->create();
    OfferRule::factory()->scoring()->for($offer)->count(1)->create();

    $this->assertSame(3, $offer->rules()->count());
});

test('offers page renders correctly when offer has rules', function () {
    $admin = User::factory()->admin()->create();
    $offer = Offer::factory()->create();
    OfferRule::factory()->eligibility()->for($offer)->create();
    OfferRule::factory()->scoring()->for($offer)->create();

    $this->actingAs($admin);

    Livewire::test(OffersPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$offer]);
});

test('eligibility rule is stored with correct attributes', function () {
    $offer = Offer::factory()->create();
    $question = Question::factory()->for(
        Questionnaire::factory()->state(['product_id' => $offer->product_id])
    )->number()->create();

    $rule = OfferRule::factory()
        ->eligibility()
        ->for($offer)
        ->for($question)
        ->create([
            'operator' => OfferRuleOperator::Lt,
            'expected_value' => '21',
            'priority' => 1,
        ]);

    $this->assertDatabaseHas(OfferRule::class, [
        'id' => $rule->id,
        'offer_id' => $offer->id,
        'question_id' => $question->id,
        'rule_type' => OfferRuleType::Eligibility->value,
        'operator' => OfferRuleOperator::Lt->value,
        'expected_value' => '21',
        'score_delta' => null,
        'price_delta' => null,
        'price_multiplier' => null,
        'priority' => 1,
        'is_active' => true,
    ]);
});

test('scoring rule is stored with score delta and weight', function () {
    $offer = Offer::factory()->create();
    $question = Question::factory()->for(
        Questionnaire::factory()->state(['product_id' => $offer->product_id])
    )->radio()->create();

    $rule = OfferRule::factory()
        ->scoring()
        ->for($offer)
        ->for($question)
        ->create([
            'operator' => OfferRuleOperator::Eq,
            'expected_value' => 'professionnel',
            'score_delta' => 15.0,
            'weight' => 1.5,
        ]);

    $this->assertDatabaseHas(OfferRule::class, [
        'id' => $rule->id,
        'rule_type' => OfferRuleType::Scoring->value,
        'expected_value' => 'professionnel',
        'price_delta' => null,
        'price_multiplier' => null,
    ]);

    $this->assertEquals(15.0, (float) $rule->fresh()->score_delta);
    $this->assertEquals(1.5, (float) $rule->fresh()->weight);
});

test('pricing rule is stored with price multiplier', function () {
    $offer = Offer::factory()->create();
    $question = Question::factory()->for(
        Questionnaire::factory()->state(['product_id' => $offer->product_id])
    )->text()->create();

    $rule = OfferRule::factory()
        ->pricing()
        ->for($offer)
        ->for($question)
        ->create([
            'operator' => OfferRuleOperator::Eq,
            'expected_value' => 'paris',
            'price_multiplier' => 1.15,
        ]);

    $this->assertDatabaseHas(OfferRule::class, [
        'id' => $rule->id,
        'rule_type' => OfferRuleType::Pricing->value,
        'expected_value' => 'paris',
        'score_delta' => null,
        'weight' => null,
    ]);

    $this->assertEquals(1.15, (float) $rule->fresh()->price_multiplier);
});

test('rules are ordered by priority then rule type', function () {
    $offer = Offer::factory()->create();
    $r2 = OfferRule::factory()->eligibility()->for($offer)->create(['priority' => 2]);
    $r0 = OfferRule::factory()->scoring()->for($offer)->create(['priority' => 0]);
    $r1 = OfferRule::factory()->pricing()->for($offer)->create(['priority' => 1]);

    $ids = $offer->rules()->pluck('id')->all();

    $this->assertSame([$r0->id, $r1->id, $r2->id], $ids);
});
