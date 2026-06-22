<?php

use App\Enums\QuestionInputType;
use App\Models\Category;
use App\Models\ComparisonSession;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Sector;
use App\Services\Comparison\ComparisonService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Category $category;
    public Sector $sector;
    public Product $product;
    public Questionnaire $questionnaire;

    /** @var array<string, list<Question>> */
    public array $stepsQuestions = [];

    /** @var list<string> */
    public array $steps = [];

    public int $stepIndex = 0;

    /** @var array<string, mixed> */
    public array $answers = [];

    public function mount(Category $category, Sector $sector, Product $product): void
    {
        abort_if(! $category->is_active, 404);
        abort_if(! $sector->is_active || $sector->category_id !== $category->id, 404);
        abort_if(! $product->is_active || $product->sector_id !== $sector->id, 404);

        $questionnaire = $product->questionnaires()
            ->where('is_active', true)
            ->with(['questions' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();

        abort_if($questionnaire === null, 404);

        $this->category = $category;
        $this->sector = $sector;
        $this->product = $product;
        $this->questionnaire = $questionnaire;

        // Group questions by step_key, preserving step order from first question appearance
        $grouped = collect($questionnaire->questions)
            ->groupBy('step_key')
            ->map(fn ($qs) => $qs->values()->all());

        $this->stepsQuestions = $grouped->all();
        $this->steps = $grouped->keys()->all();

        // Init answers: empty string for scalars, empty array for checkboxes
        foreach ($questionnaire->questions as $question) {
            $this->answers[$question->field_key] = $question->input_type === QuestionInputType::Checkbox ? [] : '';
        }
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->stepIndex < count($this->steps) - 1) {
            $this->stepIndex++;
        } else {
            $this->submit();
        }
    }

    public function previousStep(): void
    {
        if ($this->stepIndex > 0) {
            $this->stepIndex--;
        }
    }

    private function validateCurrentStep(): void
    {
        $step = $this->steps[$this->stepIndex];
        $questions = $this->stepsQuestions[$step] ?? [];
        $rules = [];

        foreach ($questions as $question) {
            if (! $question->is_required) {
                continue;
            }

            $key = 'answers.' . $question->field_key;

            $rules[$key] = $question->input_type === QuestionInputType::Checkbox
                ? ['required', 'array', 'min:1']
                : ['required', 'string', 'max:1000'];
        }

        if (! empty($rules)) {
            $this->validate($rules);
        }
    }

    private function submit(): void
    {
        $session = ComparisonSession::create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => $this->answers,
        ]);

        app(ComparisonService::class)->compare($session);

        $this->redirect(route('compare.results', $session->id), navigate: true);
    }

    public function currentStepQuestions(): array
    {
        $step = $this->steps[$this->stepIndex] ?? null;

        return $step ? ($this->stepsQuestions[$step] ?? []) : [];
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.compare')
            ->title($this->product->name . ' — Questionnaire — ' . config('app.name'));
    }
};
?>

<div>
    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('compare.categories') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Catégories</a>
        <flux:icon.chevron-right class="size-4" />
        <a href="{{ route('compare.sectors', $category->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">{{ $category->name }}</a>
        <flux:icon.chevron-right class="size-4" />
        <a href="{{ route('compare.products', [$category->slug, $sector->slug]) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">{{ $sector->name }}</a>
        <flux:icon.chevron-right class="size-4" />
        <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="mx-auto max-w-2xl">
        {{-- Progress bar --}}
        <div class="mb-8">
            <div class="mb-2 flex items-center justify-between text-sm">
                <flux:text class="font-medium">{{ $product->name }}</flux:text>
                <flux:text class="text-zinc-500">Étape {{ $stepIndex + 1 }} / {{ count($steps) }}</flux:text>
            </div>
            <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div
                    class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                    style="width: {{ round((($stepIndex + 1) / max(count($steps), 1)) * 100) }}%"
                ></div>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-8 shadow-sm">
            <flux:heading size="xl" class="mb-6">
                {{ ucfirst(str_replace('-', ' ', $steps[$stepIndex] ?? 'Informations')) }}
            </flux:heading>

            <div class="space-y-6">
                @foreach ($this->currentStepQuestions() as $question)
                    <div wire:key="question-{{ $question->id }}">
                        @php $key = 'answers.' . $question->field_key; @endphp

                        @if ($question->input_type === \App\Enums\QuestionInputType::Text)
                            <flux:input
                                wire:model="answers.{{ $question->field_key }}"
                                :label="$question->label"
                                :required="$question->is_required"
                            />

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Number)
                            <flux:input
                                wire:model="answers.{{ $question->field_key }}"
                                type="number"
                                :label="$question->label"
                                :required="$question->is_required"
                            />

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Date)
                            <flux:input
                                wire:model="answers.{{ $question->field_key }}"
                                type="date"
                                :label="$question->label"
                                :required="$question->is_required"
                            />

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Textarea)
                            <flux:textarea
                                wire:model="answers.{{ $question->field_key }}"
                                :label="$question->label"
                                :required="$question->is_required"
                                rows="3"
                            />

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Boolean)
                            <div>
                                <flux:label :required="$question->is_required">{{ $question->label }}</flux:label>
                                <flux:radio.group wire:model="answers.{{ $question->field_key }}" class="mt-2">
                                    <flux:radio value="oui" label="Oui" />
                                    <flux:radio value="non" label="Non" />
                                </flux:radio.group>
                            </div>

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Radio)
                            <div>
                                <flux:label :required="$question->is_required">{{ $question->label }}</flux:label>
                                <flux:radio.group wire:model="answers.{{ $question->field_key }}" class="mt-2">
                                    @foreach (($question->options_json ?? []) as $value => $label)
                                        <flux:radio :value="$value" :label="$label" wire:key="radio-{{ $question->id }}-{{ $value }}" />
                                    @endforeach
                                </flux:radio.group>
                            </div>

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Select)
                            <flux:select
                                wire:model="answers.{{ $question->field_key }}"
                                :label="$question->label"
                                :required="$question->is_required"
                                placeholder="Sélectionnez une option"
                            >
                                @foreach (($question->options_json ?? []) as $value => $label)
                                    <flux:select.option :value="$value" wire:key="opt-{{ $question->id }}-{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>

                        @elseif ($question->input_type === \App\Enums\QuestionInputType::Checkbox)
                            <div>
                                <flux:label :required="$question->is_required">{{ $question->label }}</flux:label>
                                <div class="mt-2 space-y-2">
                                    @foreach (($question->options_json ?? []) as $value => $label)
                                        <flux:checkbox
                                            wire:model="answers.{{ $question->field_key }}"
                                            :value="$value"
                                            :label="$label"
                                            wire:key="chk-{{ $question->id }}-{{ $value }}"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @error('answers.' . $question->field_key)
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex items-center justify-between">
                @if ($stepIndex > 0)
                    <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                        Précédent
                    </flux:button>
                @else
                    <div></div>
                @endif

                @if ($stepIndex < count($steps) - 1)
                    <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right">
                        Suivant
                    </flux:button>
                @else
                    <flux:button wire:click="nextStep" variant="primary" icon-trailing="magnifying-glass">
                        Lancer la comparaison
                    </flux:button>
                @endif
            </div>
        </div>
    </div>
</div>
