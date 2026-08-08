<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * The index method retrieves and displays a paginated list of submissions for a specific form. It allows optional searching through the submission data.
     * @param Request $request
     * @param Form $form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request, Form $form)
    {
        $search = $request->input('search');

        $submissions = Submission::where('form_id', $form->id)
            ->when($search, function ($query) use ($search) {
                $query->where('data', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('submissions.index', compact(
            'form',
            'submissions',
            'search'
        ));
    }
    /**
     * The export method generates a CSV file containing all submissions for a specific form.
     * @param Form $form
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Form $form)
    {
        $submissions = Submission::where('form_id', $form->id)
            ->latest()
            ->get();

        $filename = $form->slug . '-submissions.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($submissions, $form) {

            $handle = fopen('php://output', 'w');

            /*
             * Get field names from form schema
             */
            $fields = $form->schema['fields'] ?? [];

            $columns = [];

            foreach ($fields as $field) {

                if (($field['type'] ?? '') === 'section') {
                    continue;
                }

                $columns[] = $field['key'];
            }

            /*
             * CSV header
             */
            fputcsv(
                $handle,
                array_merge(['ID', 'Submitted At'], $columns)
            );

            /*
             * CSV rows
             */
            foreach ($submissions as $submission) {

                $row = [
                    $submission->id,
                    $submission->created_at,
                ];

                foreach ($columns as $column) {

                    $value = $submission->data[$column] ?? '';

                    /*
                     * Checkbox values are arrays
                     */
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $row[] = $value;
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
