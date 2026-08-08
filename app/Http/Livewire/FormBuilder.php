<?php

namespace App\Http\Livewire;

use App\Models\Form;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\AiGeneration;
use App\Jobs\GenerateFormWithAI;

class FormBuilder extends Component
{
    /**
     * The FormBuilder class is a Livewire component that provides functionality for building and managing dynamic forms. It allows users to add, remove, duplicate, and reorder fields and sections, as well as manage form metadata such as title and description. The component also handles validation and synchronization between the form's canvas representation and its underlying JSON schema.
     */
    public $formTitle = '';
    public $formDescription = '';

    public $fields = [];

    public $sections = [];

    public $schemaJson = '';
    // AI
    public $aiPrompt = '';

    public $aiLoading = false;

    public $aiStatus = '';

    public $publicUrl = '';
    public $aiGenerationId = null;
    /**
     * The $rules property defines the validation rules for the form builder component. It specifies that the form title is required and must be between 3 and 255 characters, the form description is optional but cannot exceed 1000 characters, and at least one field must be present in the fields array.
     */
    protected $rules = [
        'formTitle' => 'required|min:3|max:255',
        'formDescription' => 'nullable|max:1000',
        'fields' => 'required|array|min:1',
        'aiPrompt' => 'nullable|min:10|max:2000',
    ];

    protected $messages = [
        'formTitle.required' => 'Form title is required.',
        'fields.required' => 'Please add at least one field.',
    ];
    protected $listeners = [
        'reorderFields'
    ];
    /**
     * The mount method initializes the component's state, setting up default values for the form title, description, fields, sections, and schema JSON. This ensures that the form builder starts with a clean slate when the component is first rendered.
     */
    public function mount()
    {
        $this->fields = [];

        $this->sections = [];

        $this->schemaJson = json_encode([
            'version' => '1.0',
            'title' => '',
            'description' => '',
            'sections' => [],
            'fields' => [],
        ], JSON_PRETTY_PRINT);
    }
    /**
     * The function is used to add the dynamic fieild on the form basis of the type
     * @param mixed $type
     * @return void
     */
    public function addField($type)
    {
        $field = [
            'id' => uniqid(),

            'type' => $type,

            'label' => ucfirst($type) . ' Field',

            'key' => Str::snake($type . '_' . uniqid()),

            'placeholder' => '',

            'help_text' => '',

            'default' => '',

            'required' => false,

            'options' => [],

            'section_id' => null,

            'validation' => [
                'min' => '',
                'max' => '',
                'min_length' => '',
                'max_length' => '',
                'numeric' => false,
                'email' => false,
                'url' => false,
                'regex' => '',
                'file_types' => '',
                'file_size' => '',
            ],
        ];


        if (in_array($type, ['select', 'radio', 'checkbox'])) {

            $field['options'] = [
                'Option 1',
                'Option 2',
            ];
        }

        if ($type === 'file') {

            $field['validation']['file_types'] = 'pdf,doc,docx';

            $field['validation']['file_size'] = 2048;
        }

        $this->fields[] = $field;

        $this->syncCanvasToSchema();
    }
    /**
     * The removeField method removes a field from the form based on its index.
     * @param mixed $index
     * @return void
     */
    public function removeField($index)
    {
        unset($this->fields[$index]);

        $this->fields = array_values($this->fields);

        $this->syncCanvasToSchema();
    }
    /**
     * The render method returns the view for the form builder component.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.form-builder');
    }
    /**
     * This function is used to save the form data with the fields into the database with bydefult published as status
     * @return void
     */
    public function saveForm()
    {
        $this->resetValidation();

        // Normal validation
        $this->validate([
            'formTitle' => 'required|min:3|max:255',
            'formDescription' => 'nullable|max:1000',
            'fields' => 'required|array|min:1',
        ]);

        foreach ($this->fields as $index => $field) {

            if ($field['type'] !== 'file') {
                continue;
            }

            $fileTypes = strtolower(
                trim($field['validation']['file_types'] ?? '')
            );

            $allowedTypes = array_filter(
                array_map('trim', explode(',', $fileTypes))
            );

            $allowedExtensions = [
                'pdf',
                'doc',
                'docx',
            ];

            $invalidTypes = array_diff(
                $allowedTypes,
                $allowedExtensions
            );

            if (!empty($invalidTypes)) {

                $this->addError(
                    "fields.$index.validation.file_types",
                    'Only PDF, DOC and DOCX files are allowed.'
                );

                return;
            }
        }

        $form = Form::create([
            'title' => $this->formTitle,
            'description' => $this->formDescription,
            'slug' => Str::slug($this->formTitle),
            'schema' => [
                'fields' => $this->fields,
            ],
            'status' => 'published',
        ]);
        // Generate public form URL
        $this->publicUrl = route('forms.public', [
            'form_id' => $form->id,
        ]);
        session()->flash('success', 'Form saved successfully.');
    }
    /**
     * The addOption method adds a new option to a specific field in the form.
     * @param mixed $index
     * @return void
     */
    public function addOption($index)
    {
        $this->fields[$index]['options'][] = 'New Option';
        $this->syncCanvasToSchema();
    }
    /**
     * The syncCanvasToSchema method updates the schema JSON based on the current state of the canvas fields and sections.
     * @return void
     */
    public function syncCanvasToSchema()
    {
        $this->schemaJson = json_encode(
            [
                'version' => '1.0',

                'title' => $this->formTitle,

                'description' => $this->formDescription,

                'sections' => $this->sections,

                'fields' => $this->fields,
            ],
            JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
        );
    }
    /**
     * The removeOption method removes an option from a specific field in the form.
     * @param mixed $fieldIndex
     * @param mixed $optionIndex
     * @return void
     */
    public function removeOption($fieldIndex, $optionIndex)
    {
        unset($this->fields[$fieldIndex]['options'][$optionIndex]);

        $this->fields[$fieldIndex]['options'] =
            array_values($this->fields[$fieldIndex]['options']);
        $this->syncCanvasToSchema();
    }
    /**
     * The reorderFields method reorders the fields in the form based on the provided IDs.
     * @param mixed $orderedIds
     * @return void
     */
    public function reorderFields($orderedIds)
    {
        $newFields = [];

        foreach ($orderedIds as $id) {
            foreach ($this->fields as $field) {
                if ($field['id'] == $id) {
                    $newFields[] = $field;
                    break;
                }
            }
        }

        $this->fields = $newFields;

        $this->syncCanvasToSchema();
    }
    /**
     * The duplicateField method creates a copy of a field in the form based on its index.
     * @param mixed $index
     * @return void
     */
    public function duplicateField($index)
    {
        if (!isset($this->fields[$index])) {
            return;
        }

        // Copy the field
        $field = $this->fields[$index];

        // Generate new unique ID
        $field['id'] = uniqid();

        // Generate a new unique key
        $field['key'] = Str::snake(
            $field['type'] . '_' . uniqid()
        );

        // Add "Copy" to the label
        $field['label'] = $field['label'] . ' Copy';

        // Insert after original field
        array_splice(
            $this->fields,
            $index + 1,
            0,
            [$field]
        );

        // Re-index array
        $this->fields = array_values($this->fields);

        $this->syncCanvasToSchema();
    }
    /**
     * The removeSection method removes a section from the form and updates any fields that belong to that section.
     * @param mixed $sectionId
     * @return void
     */
    public function removeSection($sectionId)
    {
        foreach ($this->fields as &$field) {

            if (($field['section_id'] ?? null) === $sectionId) {
                $field['section_id'] = null;
            }
        }

        unset($field);

        $this->sections = array_values(
            array_filter(
                $this->sections,
                fn($section) => $section['id'] !== $sectionId
            )
        );
    }
    /**
     * The addSection method adds a new section to the form with a unique ID and default title.
     * @return void
     */
    public function addSection()
    {
        $this->sections[] = [
            'id' => uniqid('section_'),
            'title' => 'New Section',
        ];
        $this->syncCanvasToSchema();
    }
    /**
     * The syncSchemaToCanvas method updates the canvas fields and sections based on the schema JSON.
     * @throws \Exception
     * @return void
     */
    public function syncSchemaToCanvas()
    {
        try {
            $schema = json_decode($this->schemaJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(
                    'Invalid JSON: ' . json_last_error_msg()
                );
            }

            if (!is_array($schema)) {
                throw new \Exception('JSON schema must be an object.');
            }


            if (!isset($schema['fields']) || !is_array($schema['fields'])) {
                throw new \Exception(
                    'JSON schema must contain a "fields" array.'
                );
            }

            if (
                isset($schema['sections']) &&
                !is_array($schema['sections'])
            ) {
                throw new \Exception(
                    '"sections" must be an array.'
                );
            }

            $this->formTitle = $schema['title'] ?? '';

            $this->formDescription = $schema['description'] ?? '';

            $this->sections = [];

            foreach ($schema['sections'] ?? [] as $section) {

                if (!isset($section['id'])) {
                    $section['id'] = 'section_' . uniqid();
                }

                $section['title'] = $section['title'] ?? 'Untitled Section';

                $this->sections[] = [
                    'id' => $section['id'],
                    'title' => $section['title'],
                ];
            }


            $this->fields = [];

            foreach ($schema['fields'] as $field) {

                if (!is_array($field)) {
                    continue;
                }

                $field['id'] = $field['id']
                    ?? 'field_' . uniqid();

                $field['type'] = $field['type']
                    ?? 'text';

                $field['label'] = $field['label']
                    ?? ucfirst($field['type']);

                $field['key'] = $field['key']
                    ?? \Illuminate\Support\Str::slug(
                        $field['label'],
                        '_'
                    );

                $field['placeholder'] =
                    $field['placeholder'] ?? '';

                $field['help_text'] =
                    $field['help_text'] ?? '';

                $field['default'] =
                    $field['default'] ?? '';

                $field['required'] =
                    (bool) ($field['required'] ?? false);

                $field['section_id'] =
                    $field['section_id'] ?? null;

                $field['options'] =
                    is_array($field['options'] ?? null)
                    ? $field['options']
                    : [];

                $validation = $field['validation'] ?? [];

                $field['validation'] = [
                    'min' => $validation['min'] ?? null,

                    'max' => $validation['max'] ?? null,

                    'min_length' =>
                    $validation['min_length'] ?? null,

                    'max_length' =>
                    $validation['max_length'] ?? null,

                    'numeric' =>
                    (bool) ($validation['numeric'] ?? false),

                    'email' =>
                    (bool) ($validation['email'] ?? false),

                    'url' =>
                    (bool) ($validation['url'] ?? false),

                    'regex' =>
                    $validation['regex'] ?? null,

                    'file_types' =>
                    $validation['file_types'] ?? null,

                    'file_size' =>
                    $validation['file_size'] ?? null,
                ];


                $this->fields[] = $field;
            }

            $this->schemaJson = json_encode(
                [
                    'version' => $schema['version'] ?? '1.0',

                    'title' => $this->formTitle,

                    'description' => $this->formDescription,

                    'sections' => $this->sections,

                    'fields' => $this->fields,
                ],
                JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE
            );


            session()->flash(
                'success',
                'JSON schema successfully applied to the form builder.'
            );
        } catch (\Throwable $e) {

            $this->addError(
                'schemaJson',
                $e->getMessage()
            );
        }
    }
    public function checkAIGenerationStatus()
    {
        if (!$this->aiGenerationId) {
            return;
        }

        $generation = AiGeneration::find($this->aiGenerationId);

        if (!$generation) {
            $this->aiLoading = false;
            $this->aiStatus = 'Generation record not found.';
            return;
        }

        if ($generation->status === 'pending') {
            $this->aiStatus = 'AI generation is waiting in the queue...';
            return;
        }

        if ($generation->status === 'processing') {
            $this->aiStatus = 'AI is generating your form...';
            return;
        }

        if ($generation->status === 'failed') {

            $this->aiLoading = false;

            $this->aiStatus =
                'AI generation failed: ' .
                ($generation->error ?? 'Unknown error.');

            return;
        }

        if ($generation->status === 'completed') {

            /*
         * Get the AI result from database.
         */
            $response = $generation->result;

            /*
         * Make sure result is an array.
         */
            if (is_string($response)) {
                $response = json_decode($response, true);
            }

            /*
         * Make sure we actually received fields.
         */
            if (
                !is_array($response) ||
                !isset($response['fields']) ||
                !is_array($response['fields'])
            ) {
                $this->aiLoading = false;

                $this->aiStatus =
                    'AI returned an invalid form schema.';

                return;
            }

            /*
         * Update form details.
         */
            $this->formTitle =
                $response['title'] ?? 'AI Generated Form';

            $this->formDescription =
                $response['description'] ?? '';

            /*
         * Update builder fields.
         */
            $this->fields = $response['fields'];

            /*
         * Normalize fields so they match
         * our existing Form Builder structure.
         */
            foreach ($this->fields as $index => $field) {

                $this->fields[$index]['id'] =
                    $field['id'] ?? uniqid();

                $this->fields[$index]['type'] =
                    $field['type'] ?? 'text';

                $this->fields[$index]['label'] =
                    $field['label'] ?? 'Untitled Field';

                $this->fields[$index]['key'] =
                    $field['key'] ?? 'field_' . uniqid();

                $this->fields[$index]['placeholder'] =
                    $field['placeholder'] ?? '';

                $this->fields[$index]['help_text'] =
                    $field['help_text'] ?? '';

                $this->fields[$index]['default'] =
                    $field['default'] ?? '';

                $this->fields[$index]['required'] =
                    $field['required'] ?? false;

                $this->fields[$index]['options'] =
                    $field['options'] ?? [];

                $this->fields[$index]['validation'] =
                    $field['validation'] ?? [];

                $this->fields[$index]['section_id'] =
                    $field['section_id'] ?? null;
            }

            /*
         * Stop polling.
         */
            $this->aiLoading = false;

            $this->aiStatus =
                'AI form generated successfully.';

            session()->flash(
                'success',
                'AI form generated successfully. You can edit it below.'
            );
        }
    }
    public function generateWithAI()
    {
        $this->validate([
            'aiPrompt' => 'required|min:10|max:2000',
        ]);

        $generation = AiGeneration::create([
            'prompt' => $this->aiPrompt,
            'status' => 'pending',
        ]);

        $this->aiGenerationId = $generation->id;

        $this->aiLoading = true;

        $this->aiStatus = 'AI generation has been queued...';

        GenerateFormWithAI::dispatch($generation->id);
    }
}
