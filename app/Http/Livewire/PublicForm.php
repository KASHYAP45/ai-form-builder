<?php

namespace App\Http\Livewire;

use App\Models\Form;
use Livewire\Component;

class PublicForm extends Component
{
    public $form;

    /**
     * The mount method initializes the component with the form data based on its slug.
     * @param mixed $slug
     * @return void
     */
    public function mount($slug)
    {
        $this->form = Form::where('slug', $slug)
            ->where('status', 'draft')
            ->firstOrFail();
    }
    public function render()
    {
        return view('livewire.public-form');
    }
}