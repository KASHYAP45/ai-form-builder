@push('styles')
<style>
    .ai-conatiner-style {
        color: #fcfdff !important;
        background-color: #f7f7fa !important;
        border-color: #d7d7d7 !important; 
    }
</style>
@endpush
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">AI Form Builder</h3>
    </div>

    <div class="card-body">
@if($aiLoading)

    <div wire:poll.2s="checkAIGenerationStatus">
        <div class="alert alert-info">
            {{ $aiStatus }}
        </div>
    </div>

@endif
@if($publicUrl)

    <div class="alert alert-success mt-4 ai-conatiner-style" >

        <h5 class="mb-2">
            Form saved successfully! You can access the public form using the URL below:
        </h5>
        <div class="input-group">

            <input
                type="text"
                class="form-control"
                value="{{ $publicUrl }}"
                readonly
                id="publicFormUrl"
            >

            <button
                type="button"
                class="btn btn-primary"
                onclick="navigator.clipboard.writeText(document.getElementById('publicFormUrl').value)">
                Copy URL
            </button>

            <a
                href="{{ $publicUrl }}"
                target="_blank"
                class="btn btn-dark">
                Open Form
            </a>

        </div>

    </div>

@endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Form Title
                </label>

                <input
                    type="text"
                    class="form-control @error('formTitle') is-invalid @enderror"
                    wire:model.live="formTitle"
                    placeholder="Enter form title"
                >

                @error('formTitle')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Description
                </label>

                <textarea
                    rows="2"
                    class="form-control @error('formDescription') is-invalid @enderror"
                    wire:model.live="formDescription"
                    placeholder="Enter form description"
                ></textarea>

                @error('formDescription')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>


        <hr>
<hr>

<h5 class="mb-3">AI Form Generator</h5>

<div class="mb-3">

    <label class="form-label">
        Describe the form you want
    </label>

    <textarea
        class="form-control"
        rows="4"
        wire:model="aiPrompt"
        placeholder="Example: Internship application with personal details, education history, skills and resume upload"
    ></textarea>

</div>

<button
    type="button"
    class="btn btn-dark"
    wire:click="generateWithAI"
    wire:loading.attr="disabled"
>

    <span wire:loading.remove wire:target="generateWithAI">
        Generate Form with AI
    </span>

    <span wire:loading wire:target="generateWithAI">
        Generating...
    </span>

</button>

@if($aiStatus)

    <div class="alert alert-info mt-3">
        {{ $aiStatus }}
    </div>

@endif
<hr>
        <h5 class="mb-3">
            Available Fields
        </h5>

        <div class="d-flex flex-wrap gap-2 mb-4">

            <button
                type="button"
                class="btn btn-primary btn-sm"
                wire:click="addField('text')"
            >
                Text
            </button>

            <button
                type="button"
                class="btn btn-secondary btn-sm"
                wire:click="addField('textarea')"
            >
                Textarea
            </button>

            <button
                type="button"
                class="btn btn-warning btn-sm"
                wire:click="addField('number')"
            >
                Number
            </button>

            <button
                type="button"
                class="btn btn-success btn-sm"
                wire:click="addField('email')"
            >
                Email
            </button>

            <button
                type="button"
                class="btn btn-info btn-sm"
                wire:click="addField('phone')"
            >
                Phone
            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm"
                wire:click="addField('date')"
            >
                Date
            </button>

            <button
                type="button"
                class="btn btn-dark btn-sm"
                wire:click="addField('select')"
            >
                Dropdown
            </button>

            <button
                type="button"
                class="btn btn-primary btn-sm"
                wire:click="addField('radio')"
            >
                Radio
            </button>

            <button
                type="button"
                class="btn btn-success btn-sm"
                wire:click="addField('checkbox')"
            >
                Checkbox
            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm"
                wire:click="addField('file')"
            >
                File Upload
            </button>

            <button
                type="button"
                class="btn btn-secondary btn-sm"
                wire:click="addField('section')"
            >
                Section Heading
            </button>

            <button
                type="button"
                class="btn btn-warning btn-sm"
                wire:click="addField('rating')"
            >
                Rating
            </button>

            {{-- Actual form section --}}
            <button
                type="button"
                class="btn btn-dark btn-sm"
                wire:click="addSection"
            >
                + Add Section
            </button>

        </div>

        <hr>

        <h5 class="mb-3">
            Sections / Steps
        </h5>

        @forelse ($sections as $sectionIndex => $section)

            <div
                class="card mb-3"
                wire:key="section-{{ $section['id'] }}"
            >

                <div class="card-body">

                    <div class="row align-items-center">

                        <div class="col-md-10">

                            <label class="form-label">
                                Section Title
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                wire:model.live="sections.{{ $sectionIndex }}.title"
                                placeholder="Section / Step title"
                            >

                        </div>


                        <div class="col-md-2">

                            <label class="form-label d-block">
                                &nbsp;
                            </label>

                            <button
                                type="button"
                                class="btn btn-danger btn-sm w-100"
                                wire:click="removeSection('{{ $section['id'] }}')"
                            >
                                Delete
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        @empty

            <div class="alert alert-light border">
                No sections created yet.
            </div>

        @endforelse

        @error('fields')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror


        <hr>

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                Form Fields
            </h5>

            <small class="text-muted">
                Drag fields using ☰ to reorder
            </small>

        </div>


        <div
            id="form-fields"
            wire:ignore.self
        >

            @forelse ($fields as $index => $field)

                <div
                    class="card shadow-sm mb-3 form-field"
                    data-id="{{ $field['id'] }}"
                    wire:key="field-{{ $field['id'] }}"
                >

                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >

                        <div>

                            <span
                                class="drag-handle me-2"
                                style="cursor: grab;"
                                title="Drag to reorder"
                            >
                                ☰
                            </span>

                            <strong>
                                {{ ucfirst($field['type']) }}
                            </strong>

                            <span class="badge bg-secondary ms-2">
                                {{ $field['type'] }}
                            </span>

                        </div>


                        <div class="d-flex gap-1">

                            {{-- Duplicate --}}
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                wire:click="duplicateField({{ $index }})"
                            >
                                Duplicate
                            </button>


                            {{-- Delete --}}
                            <button
                                type="button"
                                class="btn btn-danger btn-sm"
                                wire:click="removeField({{ $index }})"
                            >
                                Delete
                            </button>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="row">

                            {{-- Label --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Label
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.label"
                                    placeholder="Field label"
                                >

                            </div>


                            {{-- Key --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Field Key
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.key"
                                    placeholder="field_key"
                                >

                            </div>

                            {{-- Placeholder --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Placeholder
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.placeholder"
                                    placeholder="Enter placeholder"
                                >

                            </div>

                            {{-- Default --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Default Value
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.default"
                                    placeholder="Default value"
                                >

                            </div>


                            {{-- Help Text --}}
                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Help Text
                                </label>

                                <textarea
                                    class="form-control"
                                    rows="2"
                                    wire:model.live="fields.{{ $index }}.help_text"
                                    placeholder="Additional help text"
                                ></textarea>

                            </div>

                            {{-- Section --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Section / Step
                                </label>

                                <select
                                    class="form-select"
                                    wire:model.live="fields.{{ $index }}.section_id"
                                >

                                    <option value="">
                                        No Section
                                    </option>

                                    @foreach ($sections as $section)

                                        <option value="{{ $section['id'] }}">
                                            {{ $section['title'] }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            {{-- Required --}}
                            <div class="col-md-6 mb-3">

                                <div class="form-check mt-4">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="required-{{ $field['id'] }}"
                                        wire:model.live="fields.{{ $index }}.required"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="required-{{ $field['id'] }}"
                                    >
                                        Required field
                                    </label>

                                </div>

                            </div>

                        </div>

                        @if (in_array($field['type'], ['select', 'radio', 'checkbox']))

                            <hr>

                            <h6>
                                Options
                            </h6>

                            @foreach ($field['options'] ?? [] as $optionIndex => $option)

                                <div
                                    class="input-group mb-2"
                                    wire:key="option-{{ $field['id'] }}-{{ $optionIndex }}"
                                >

                                    <input
                                        type="text"
                                        class="form-control"
                                        wire:model.live="fields.{{ $index }}.options.{{ $optionIndex }}"
                                        placeholder="Option {{ $optionIndex + 1 }}"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-danger"
                                        wire:click="removeOption({{ $index }}, {{ $optionIndex }})"
                                    >
                                        Delete
                                    </button>

                                </div>

                            @endforeach


                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                wire:click="addOption({{ $index }})"
                            >
                                + Add Option
                            </button>

                        @endif

                        <hr>

                        <h6>
                            Validation Rules
                        </h6>

                        <div class="row">

                            {{-- Minimum --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Minimum Value
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.validation.min"
                                    placeholder="Example: 18"
                                >

                            </div>


                            {{-- Maximum --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Maximum Value
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.validation.max"
                                    placeholder="Example: 60"
                                >

                            </div>


                            {{-- Minimum Length --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Minimum Length
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.validation.min_length"
                                    placeholder="Example: 3"
                                >

                            </div>


                            {{-- Maximum Length --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Maximum Length
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.validation.max_length"
                                    placeholder="Example: 100"
                                >

                            </div>


                            {{-- Numeric --}}
                            <div class="col-md-4 mb-3">

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="numeric-{{ $field['id'] }}"
                                        wire:model.live="fields.{{ $index }}.validation.numeric"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="numeric-{{ $field['id'] }}"
                                    >
                                        Numeric
                                    </label>

                                </div>

                            </div>


                            {{-- Email --}}
                            <div class="col-md-4 mb-3">

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="email-{{ $field['id'] }}"
                                        wire:model.live="fields.{{ $index }}.validation.email"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="email-{{ $field['id'] }}"
                                    >
                                        Email
                                    </label>

                                </div>

                            </div>


                            {{-- URL --}}
                            <div class="col-md-4 mb-3">

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="url-{{ $field['id'] }}"
                                        wire:model.live="fields.{{ $index }}.validation.url"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="url-{{ $field['id'] }}"
                                    >
                                        URL
                                    </label>

                                </div>

                            </div>


                            {{-- Regex --}}
                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Regex Pattern
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.live="fields.{{ $index }}.validation.regex"
                                    placeholder="/^[A-Za-z]+$/"
                                >

                            </div>


                            {{-- File Types --}}
                            @if ($field['type'] === 'file')

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Allowed File Types
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        wire:model.live="fields.{{ $index }}.validation.file_types"
                                        placeholder="pdf,doc,docx"
                                    >

                                </div>


                                {{-- File Size --}}
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Maximum File Size (KB)
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        wire:model.live="fields.{{ $index }}.validation.file_size"
                                        placeholder="2048"
                                    >

                                </div>

                            @endif

                        </div>

                        <hr>

                        <h6>
                            Live Preview
                        </h6>

                        <div class="mb-3">

                            @if ($field['type'] !== 'section')

                                <label class="form-label">

                                    {{ $field['label'] }}

                                    @if ($field['required'])
                                        <span class="text-danger">*</span>
                                    @endif

                                </label>

                            @endif


                            @switch($field['type'])

                                {{-- TEXT --}}
                                @case('text')

                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="{{ $field['placeholder'] }}"
                                        value="{{ $field['default'] }}"
                                        disabled
                                    >

                                    @break

                                {{-- TEXTAREA --}}
                                @case('textarea')

                                    <textarea
                                        class="form-control"
                                        placeholder="{{ $field['placeholder'] }}"
                                        rows="3"
                                        disabled
                                    >{{ $field['default'] }}</textarea>

                                    @break


                                {{-- NUMBER --}}
                                @case('number')

                                    <input
                                        type="number"
                                        class="form-control"
                                        placeholder="{{ $field['placeholder'] }}"
                                        value="{{ $field['default'] }}"
                                        disabled
                                    >

                                    @break


                                {{-- EMAIL --}}
                                @case('email')

                                    <input
                                        type="email"
                                        class="form-control"
                                        placeholder="{{ $field['placeholder'] }}"
                                        value="{{ $field['default'] }}"
                                        disabled
                                    >

                                    @break


                                {{-- PHONE --}}
                                @case('phone')

                                    <input
                                        type="tel"
                                        class="form-control"
                                        placeholder="{{ $field['placeholder'] }}"
                                        value="{{ $field['default'] }}"
                                        disabled
                                    >

                                    @break


                                {{-- DATE --}}
                                @case('date')

                                    <input
                                        type="date"
                                        class="form-control"
                                        value="{{ $field['default'] }}"
                                        disabled
                                    >

                                    @break


                                {{-- FILE --}}
                                @case('file')

                                    <input
                                        type="file"
                                        class="form-control"
                                        disabled
                                    >

                                    @break


                                {{-- SELECT --}}
                                @case('select')

                                    <select
                                        class="form-select"
                                        disabled
                                    >

                                        <option value="">
                                            Select an option
                                        </option>

                                        @foreach ($field['options'] ?? [] as $option)

                                            <option value="{{ $option }}">
                                                {{ $option }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @break


                                {{-- RADIO --}}
                                @case('radio')

                                    @foreach ($field['options'] ?? [] as $option)

                                        <div class="form-check">

                                            <input
                                                type="radio"
                                                class="form-check-input"
                                                name="radio_{{ $field['id'] }}"
                                                disabled
                                            >

                                            <label class="form-check-label">
                                                {{ $option }}
                                            </label>

                                        </div>

                                    @endforeach

                                    @break


                                {{-- CHECKBOX --}}
                                @case('checkbox')

                                    @foreach ($field['options'] ?? [] as $option)

                                        <div class="form-check">

                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                disabled
                                            >

                                            <label class="form-check-label">
                                                {{ $option }}
                                            </label>

                                        </div>

                                    @endforeach

                                    @break


                                {{-- SECTION --}}
                                @case('section')

                                    <h5 class="border-bottom pb-2">
                                        {{ $field['label'] }}
                                    </h5>

                                    @break


                                {{-- RATING --}}
                                @case('rating')

                                    <div class="fs-4">
                                        ★ ★ ★ ★ ★
                                    </div>

                                    @break

                            @endswitch


                            @if (!empty($field['help_text']))

                                <small class="text-muted">
                                    {{ $field['help_text'] }}
                                </small>

                            @endif

                        </div>

                    </div>

                </div>

            @empty

                <div class="alert alert-info">
                    No Fields Added
                </div>

            @endforelse

        </div>


        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">

            <h5 class="mb-0">
                JSON Schema
            </h5>

            <button
                type="button"
                class="btn btn-secondary btn-sm"
                wire:click="syncSchemaToCanvas"
            >
                Apply JSON
            </button>

        </div>

        <p class="text-muted small">
            The JSON schema is the single source of truth for the form.
            Changes can be applied back to the visual builder.
        </p>


        <textarea
            class="form-control font-monospace"
            rows="20"
            wire:model.live.debounce.500ms="schemaJson"
            spellcheck="false"
            placeholder='{
    "title": "Contact Form",
    "description": "Example form",
    "sections": [],
    "fields": []
}'
        ></textarea>


        @error('schemaJson')
            <div class="text-danger mt-2">
                {{ $message }}
            </div>
        @enderror


        <div class="mt-4 d-flex gap-2">

            <button
                type="button"
                class="btn btn-success"
                wire:click="saveForm"
                wire:loading.attr="disabled"
            >

                <span wire:loading.remove wire:target="saveForm">
                    Save Form
                </span>

                <span wire:loading wire:target="saveForm">
                    Saving...
                </span>

            </button>

        </div>

    </div>

</div>


@push('scripts')


<script>

    document.addEventListener('livewire:init', () => {

        let sortable = null;

        function initializeSortable() {

            const container = document.getElementById('form-fields');

            if (!container) {
                return;
            }

            if (typeof Sortable === 'undefined') {
                console.warn('SortableJS is not loaded.');
                return;
            }

            if (sortable) {
                sortable.destroy();
            }

            sortable = new Sortable(container, {

                animation: 150,

                handle: '.drag-handle',

                ghostClass: 'bg-light',

                onEnd: function () {

                    const ids = Array.from(
                        container.querySelectorAll('.form-field')
                    ).map(element => element.dataset.id);

                    $wire.reorderFields(ids);
                }

            });

        }

        initializeSortable();


        Livewire.hook('morph.updated', () => {
            initializeSortable();
        });

    });

</script>

<script>

function initFormSortable() {

    const container = document.getElementById('form-fields');

    if (!container) {
        console.log('❌ form-fields not found');
        return;
    }

    if (typeof Sortable === 'undefined') {
        console.log('❌ SortableJS not loaded');
        return;
    }

    console.log('✅ Initializing Sortable');

    if (container._sortable) {
        container._sortable.destroy();
    }

    container._sortable = new Sortable(container, {

        animation: 200,

        handle: '.drag-handle',

        draggable: '.form-field',

        ghostClass: 'sortable-ghost',

        onEnd: function () {

            const orderedIds = [];

            container
                .querySelectorAll('.form-field')
                .forEach(function (element) {

                    orderedIds.push(
                        element.getAttribute('data-id')
                    );

                });

            console.log('Order fields:', orderedIds);

            @this.call('reorderFields', orderedIds);
        }

    });
}


document.addEventListener(
    'livewire:load',
    function () {
        initFormSortable();
    }
);

</script>
@endpush