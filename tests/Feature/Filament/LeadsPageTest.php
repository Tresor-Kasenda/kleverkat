<?php

use App\Actions\Leads\CreateLead;
use App\Enums\LeadActionType;
use App\Enums\LeadStatus;
use App\Filament\Pages\LeadsPage;
use App\Models\Company;
use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\User;
use Livewire\Livewire;

test('admin can access leads page', function () {
    $admin = User::factory()->admin()->create();
    Lead::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(LeadsPage::getUrl())->assertSuccessful();

    Livewire::test(LeadsPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Lead::all());
});

test('non admin cannot access leads page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(LeadsPage::getUrl())
        ->assertForbidden();
});

test('admin can view lead detail modal', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'contact_first_name' => 'Jean',
        'contact_last_name' => 'Dupont',
    ]);

    $this->actingAs($admin);

    Livewire::test(LeadsPage::class)
        ->callTableAction('view', $lead)
        ->assertSuccessful()
        ->assertHasNoErrors();
});

test('admin can update lead status', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create(['status' => LeadStatus::New->value]);

    $this->actingAs($admin);

    Livewire::test(LeadsPage::class)
        ->callTableAction('edit', $lead, data: [
            'status' => LeadStatus::Contacted->value,
            'action_type' => $lead->action_type->value,
        ])
        ->assertHasNoTableActionErrors();

    expect($lead->fresh()->status)->toEqual(LeadStatus::Contacted);
});

test('admin can delete a lead', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($admin);

    Livewire::test(LeadsPage::class)
        ->callTableAction('delete', $lead);

    $this->assertDatabaseMissing(Lead::class, ['id' => $lead->id]);
});

// CreateLead action tests

test('CreateLead creates a lead from a comparison result', function () {
    $product = Product::factory()->create();
    $questionnaire = Questionnaire::factory()->for($product)->create();
    $session = ComparisonSession::factory()->for($product)->for($questionnaire)->create();
    $offer = Offer::factory()->for($product)->create();
    $result = ComparisonResult::factory()->for($session, 'session')->for($offer)->create();

    $lead = app(CreateLead::class)->handle(
        $result,
        ['first_name' => 'Marie', 'last_name' => 'Martin', 'email' => 'marie@example.com', 'phone' => '0612345678'],
        LeadActionType::QuoteRequest,
    );

    expect($lead->comparison_result_id)->toBe($result->id)
        ->and($lead->company_id)->toBe($result->company_id)
        ->and($lead->offer_id)->toBe($result->offer_id)
        ->and($lead->action_type)->toBe(LeadActionType::QuoteRequest)
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->contact_first_name)->toBe('Marie')
        ->and($lead->contact_email)->toBe('marie@example.com')
        ->and($lead->contact_phone)->toBe('0612345678');
});

test('CreateLead sets status to new by default', function () {
    $product = Product::factory()->create();
    $questionnaire = Questionnaire::factory()->for($product)->create();
    $session = ComparisonSession::factory()->for($product)->for($questionnaire)->create();
    $offer = Offer::factory()->for($product)->create();
    $result = ComparisonResult::factory()->for($session, 'session')->for($offer)->create();

    $lead = app(CreateLead::class)->handle(
        $result,
        ['first_name' => 'Paul', 'last_name' => 'Durand', 'email' => 'paul@example.com'],
        LeadActionType::Callback,
    );

    expect($lead->status)->toBe(LeadStatus::New)
        ->and($lead->sent_at)->toBeNull();
});

test('CreateLead works with phone null', function () {
    $product = Product::factory()->create();
    $questionnaire = Questionnaire::factory()->for($product)->create();
    $session = ComparisonSession::factory()->for($product)->for($questionnaire)->create();
    $offer = Offer::factory()->for($product)->create();
    $result = ComparisonResult::factory()->for($session, 'session')->for($offer)->create();

    $lead = app(CreateLead::class)->handle(
        $result,
        ['first_name' => 'Alice', 'last_name' => 'Lebrun', 'email' => 'alice@example.com'],
        LeadActionType::PartnerRedirect,
    );

    expect($lead->contact_phone)->toBeNull();
});

// Model relation tests

test('lead belongs to company', function () {
    $company = Company::factory()->create();
    $lead = Lead::factory()->for($company)->create();

    expect($lead->company)->toBeInstanceOf(Company::class)
        ->and($lead->company->id)->toBe($company->id);
});

test('lead belongs to offer', function () {
    $offer = Offer::factory()->create();
    $lead = Lead::factory()->for($offer)->create();

    expect($lead->offer)->toBeInstanceOf(Offer::class)
        ->and($lead->offer->id)->toBe($offer->id);
});

test('company has many leads', function () {
    $company = Company::factory()->create();
    Lead::factory()->for($company)->count(3)->create();

    expect($company->leads)->toHaveCount(3);
});

test('offer has many leads', function () {
    $offer = Offer::factory()->create();
    Lead::factory()->for($offer)->count(2)->create();

    expect($offer->leads)->toHaveCount(2);
});

test('lead nullifies comparison_result_id when result is deleted', function () {
    $product = Product::factory()->create();
    $questionnaire = Questionnaire::factory()->for($product)->create();
    $session = ComparisonSession::factory()->for($product)->for($questionnaire)->create();
    $offer = Offer::factory()->for($product)->create();
    $result = ComparisonResult::factory()->for($session, 'session')->for($offer)->create();

    $lead = Lead::factory()->for($result, 'result')->for($result->company)->for($offer)->create();

    $result->delete();

    expect($lead->fresh()->comparison_result_id)->toBeNull();
});

test('lead is deleted when company is deleted', function () {
    $company = Company::factory()->create();
    $lead = Lead::factory()->for($company)->create();

    $company->delete();

    $this->assertDatabaseMissing(Lead::class, ['id' => $lead->id]);
});

// Factory state tests

test('lead factory states set correct action type', function () {
    $quoteRequest = Lead::factory()->quoteRequest()->make();
    $callback = Lead::factory()->callback()->make();
    $redirect = Lead::factory()->partnerRedirect()->make();

    expect($quoteRequest->action_type)->toEqual(LeadActionType::QuoteRequest)
        ->and($callback->action_type)->toEqual(LeadActionType::Callback)
        ->and($redirect->action_type)->toEqual(LeadActionType::PartnerRedirect);
});

test('lead factory status states work correctly', function () {
    $contacted = Lead::factory()->contacted()->make();
    $converted = Lead::factory()->converted()->make();
    $lost = Lead::factory()->lost()->make();

    expect($contacted->status)->toEqual(LeadStatus::Contacted)
        ->and($converted->status)->toEqual(LeadStatus::Converted)
        ->and($lost->status)->toEqual(LeadStatus::Lost);
});
