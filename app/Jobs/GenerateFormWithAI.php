<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFormWithAI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     * @param int $generationId
     */
    public function __construct(
        public int $generationId
    ) {}

    /**
     * Execute the job.
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $generation = AiGeneration::findOrFail(
            $this->generationId
        );

        $generation->update([
            'status' => 'processing',
        ]);

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

            try {

                $response = $this->generateSchemaFromAI(
                    $generation->prompt
                );

                $this->validateFormSchema($response);

                $response = $this->normalizeSchema(
                    $response
                );

                $generation->update([
                    'status' => 'completed',
                    'result' => $response,
                ]);

                return;
            } catch (\Throwable $e) {

                if ($attempt === $maxAttempts) {

                    $generation->update([
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]);

                    throw $e;
                }

                // Try AI generation again.
                sleep(1);
            }
        }
    }
    /**
     * The normalizeSchema method takes a form schema array as input and ensures that each field in the schema has all the required properties. It assigns default values to missing properties, such as generating unique IDs for fields, setting placeholders, help texts, default values, required flags, options, section IDs, and validation rules. The method returns the normalized schema array.
     * @param array $schema The form schema to be normalized.
     * @return array The normalized form schema.
     */
    private function normalizeSchema(array $schema): array
    {
        foreach ($schema['fields'] as $index => $field) {

            $schema['fields'][$index]['id'] =
                $field['id'] ?? uniqid();

            $schema['fields'][$index]['placeholder'] =
                $field['placeholder'] ?? '';

            $schema['fields'][$index]['help_text'] =
                $field['help_text'] ?? '';

            $schema['fields'][$index]['default'] =
                $field['default'] ?? '';

            $schema['fields'][$index]['required'] =
                $field['required'] ?? false;

            $schema['fields'][$index]['options'] =
                $field['options'] ?? [];

            $schema['fields'][$index]['section_id'] =
                $field['section_id'] ?? null;

            $schema['fields'][$index]['validation'] =
                $field['validation'] ?? [];
        }

        return $schema;
    }
    private function validateFormSchema(array $schema): void
    {
        if (!isset($schema['title']) || !is_string($schema['title'])) {
            throw new \Exception('Invalid or missing form title.');
        }

        if (
            isset($schema['description']) &&
            !is_string($schema['description'])
        ) {
            throw new \Exception('Invalid form description.');
        }

        if (
            !isset($schema['fields']) ||
            !is_array($schema['fields']) ||
            count($schema['fields']) === 0
        ) {
            throw new \Exception('Form must contain at least one field.');
        }

        $allowedTypes = [
            'text',
            'textarea',
            'email',
            'phone',
            'number',
            'date',
            'select',
            'radio',
            'checkbox',
            'file',
            'section',
            'rating',
        ];

        foreach ($schema['fields'] as $index => $field) {

            if (!is_array($field)) {
                throw new \Exception(
                    "Field {$index} is invalid."
                );
            }

            if (
                !isset($field['type']) ||
                !in_array($field['type'], $allowedTypes, true)
            ) {
                throw new \Exception(
                    "Field {$index} has an unsupported type."
                );
            }

            if (
                !isset($field['label']) ||
                !is_string($field['label'])
            ) {
                throw new \Exception(
                    "Field {$index} is missing a valid label."
                );
            }

            if (
                !isset($field['key']) ||
                !is_string($field['key'])
            ) {
                throw new \Exception(
                    "Field {$index} is missing a valid key."
                );
            }

            if (
                isset($field['options']) &&
                !is_array($field['options'])
            ) {
                throw new \Exception(
                    "Options for field {$index} must be an array."
                );
            }

            if (
                isset($field['required']) &&
                !is_bool($field['required'])
            ) {
                throw new \Exception(
                    "Required value for field {$index} must be boolean."
                );
            }

            if (
                isset($field['validation']) &&
                !is_array($field['validation'])
            ) {
                throw new \Exception(
                    "Validation rules for field {$index} must be an array."
                );
            }
        }
    }
    private function generateSchemaFromAI(string $prompt): array
    {
        $systemPrompt = <<<PROMPT
You are an expert form builder.

Convert the user's request into a valid JSON form schema.

Return ONLY valid JSON.
Do not return markdown.
Do not return ```json.
Do not add explanations.

Allowed field types:
text, textarea, email, phone, number, date, select, radio, checkbox, file, section, rating.

The JSON must have this structure:

{
    "title": "string",
    "description": "string",
    "fields": [
        {
            "type": "text",
            "label": "string",
            "key": "string",
            "placeholder": "string",
            "help_text": "string",
            "default": "",
            "required": true,
            "options": [],
            "validation": {
                "min": "",
                "max": "",
                "min_length": "",
                "max_length": "",
                "numeric": false,
                "email": false,
                "url": false,
                "regex": "",
                "file_types": "",
                "file_size": ""
            }
        }
    ]
}

Rules:

1. Use only the allowed field types.
2. Generate sensible field keys using snake_case.
3. Use options for select, radio and checkbox.
4. Use file_types and file_size for file fields.
5. Use email validation for email fields.
6. Do not invent unsupported field types.
7. Every field must contain all required properties.
PROMPT;

        $response = \Illuminate\Support\Facades\Http::withToken(
            config('services.huggingface.token')
        )
            ->timeout(60)
            ->post('https://router.huggingface.co/v1/chat/completions', [
                'model' => 'openai/gpt-oss-120b:fastest',

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],

                'temperature' => 0.2,

                'stream' => false,
            ]);

        if ($response->failed()) {
            throw new \Exception(
                'AI API request failed: ' . $response->body()
            );
        }

        $content = $response->json(
            'choices.0.message.content'
        );

        if (!$content) {
            throw new \Exception(
                'AI returned an empty response.'
            );
        }

        // Remove accidental markdown fences
        $content = trim($content);

        $content = preg_replace(
            '/^```json\s*/',
            '',
            $content
        );

        $content = preg_replace(
            '/\s*```$/',
            '',
            $content
        );

        $data = json_decode(
            $content,
            true
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'AI returned invalid JSON: ' .
                    json_last_error_msg()
            );
        }

        return $data;
    }
}
