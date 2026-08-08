<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicFormController extends Controller
{
    public function show($slug)
    {
        $form = Form::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('livewire.public-form', [
            'form' => $form,
            'title' => $form->title,
        ]);
    }
    /**
     * The submit method handles the submission of a public form identified by its slug. It validates the incoming request data based on the form's schema and creates a new submission record in the database.
     * @param Request $request
     * @param mixed $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $fields = $form->schema['fields'] ?? [];

        $rules = [];

        foreach ($fields as $field) {

            if ($field['type'] === 'section') {
                continue;
            }

            $key = $field['key'];

            $fieldRules = [];

            // Required
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type validation
            switch ($field['type']) {

                case 'email':
                    $fieldRules[] = 'email';
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'phone':
                    $fieldRules[] = 'string';
                    break;

                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;

                case 'select':
                case 'radio':

                    $options = $field['options'] ?? [];

                    if (!empty($options)) {
                        $fieldRules[] = Rule::in($options);
                    }

                    break;

                case 'checkbox':

                    $fieldRules[] = 'array';

                    break;

                case 'file':

                    $fieldRules[] = 'file';

                    break;
            }
            
            $validation = $field['validation'] ?? [];

            if (!empty($validation['min'])) {
                $fieldRules[] = 'min:' . $validation['min'];
            }

            if (!empty($validation['max'])) {
                $fieldRules[] = 'max:' . $validation['max'];
            }

            if (!empty($validation['min_length'])) {
                $fieldRules[] = 'min:' . $validation['min_length'];
            }

            if (!empty($validation['max_length'])) {
                $fieldRules[] = 'max:' . $validation['max_length'];
            }

            if (!empty($validation['url'])) {
                $fieldRules[] = 'url';
            }

            if (!empty($validation['regex'])) {
                $fieldRules[] = 'regex:' . $validation['regex'];
            }

            $rules[$key] = $fieldRules;
        }

        $validated = $request->validate($rules);

        Submission::create([
            'submitted_ip' => $request->ip(),
            'form_id' => $form->id,
            'data' => $validated,
        ]);

        return redirect()
            ->route('forms.public', $form->slug)
            ->with('success', 'Form submitted successfully.');
    }
}
