<?php

use App\Filament\Pages\QuestionnairesPage;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\User;
use Livewire\Livewire;

test('admin can access questionnaires page', function () {
    $admin = User::factory()->admin()->create();
    $questionnaires = Questionnaire::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(QuestionnairesPage::getUrl())->assertSuccessful();

    Livewire::test(QuestionnairesPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($questionnaires);
});

test('non admin cannot access questionnaires page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(QuestionnairesPage::getUrl())
        ->assertForbidden();
});

test('admin can create a questionnaire', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.product_id' => $product->id,
            'mountedActions.0.data.name' => 'Questionnaire Auto',
            'mountedActions.0.data.version' => 1,
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Questionnaire::class, [
        'product_id' => $product->id,
        'name' => 'Questionnaire Auto',
        'version' => 1,
        'is_active' => true,
    ]);
});

test('admin can edit a questionnaire name', function () {
    $admin = User::factory()->admin()->create();
    $questionnaire = Questionnaire::factory()->create(['name' => 'Questionnaire Initial']);

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->mountTableAction('edit', $questionnaire)
        ->set([
            'mountedActions.0.data.product_id' => $questionnaire->product_id,
            'mountedActions.0.data.name' => 'Questionnaire Modifié',
            'mountedActions.0.data.version' => $questionnaire->version,
            'mountedActions.0.data.is_active' => true,
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(Questionnaire::class, [
        'id' => $questionnaire->id,
        'name' => 'Questionnaire Modifié',
    ]);
});

test('admin can delete a questionnaire', function () {
    $admin = User::factory()->admin()->create();
    $questionnaire = Questionnaire::factory()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->callTableAction('delete', $questionnaire);

    $this->assertDatabaseMissing(Questionnaire::class, ['id' => $questionnaire->id]);
});

test('deleting a questionnaire cascades to its questions', function () {
    $admin = User::factory()->admin()->create();
    $questionnaire = Questionnaire::factory()->create();
    $question = Question::factory()->for($questionnaire)->text()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->callTableAction('delete', $questionnaire);

    $this->assertDatabaseMissing(Question::class, ['id' => $question->id]);
});

test('questionnaire table shows question count', function () {
    $admin = User::factory()->admin()->create();
    $questionnaire = Questionnaire::factory()->create(['name' => 'Q Comptage']);
    Question::factory()->for($questionnaire)->count(3)->text()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->assertCanSeeTableRecords([$questionnaire]);

    $this->assertSame(3, $questionnaire->questions()->count());
});

test('questionnaire requires a product', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionnairesPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.product_id' => null,
            'mountedActions.0.data.name' => 'Sans produit',
            'mountedActions.0.data.version' => 1,
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasFormErrors(['product_id']);
});
