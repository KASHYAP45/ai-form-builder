@extends('layouts.admin')

@section('content')

    <form method="POST" action="{{ route('forms.submit', $form->slug) }}" enctype="multipart/form-data">
        @csrf

        <div class="app-content">
            <div class="side-app">

                <div class="page-header">
                    <h4 class="page-title">
                        {{ $form->title }}
                    </h4>
                </div>

                <div class="card">
                    <div class="card-body">

                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please fix the following errors:</strong>

                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <h3>{{ $form->title }}</h3>

                        @if($form->description)
                            <p class="text-muted">
                                {{ $form->description }}
                            </p>
                        @endif

                        <hr>

                        @foreach($form->schema['fields'] ?? [] as $field)

                            @if($field['type'] === 'section')

                                <h4 class="mt-4 mb-3">
                                    {{ $field['label'] }}
                                </h4>

                                @continue

                            @endif

                            <div class="mb-3">

                                <label class="form-label">
                                    {{ $field['label'] }}

                                    @if(!empty($field['required']))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if($field['type'] === 'text')

                                    <input type="text" name="{{ $field['key'] }}" class="form-control"
                                        placeholder="{{ $field['placeholder'] ?? '' }}">

                                @elseif($field['type'] === 'email')

                                    <input type="email" name="{{ $field['key'] }}" class="form-control"
                                        placeholder="{{ $field['placeholder'] ?? '' }}">

                                @elseif($field['type'] === 'number')

                                    <input type="number" name="{{ $field['key'] }}" class="form-control">

                                @elseif($field['type'] === 'textarea')

                                    <textarea name="{{ $field['key'] }}" class="form-control"></textarea>

                                @elseif($field['type'] === 'date')

                                    <input type="date" name="{{ $field['key'] }}" class="form-control">

                                @elseif($field['type'] === 'phone')

                                    <input type="tel" name="{{ $field['key'] }}" class="form-control">

                                @elseif($field['type'] === 'select')

                                    <select name="{{ $field['key'] }}" class="form-select">
                                        <option value="">
                                            Select an option
                                        </option>

                                        @foreach($field['options'] ?? [] as $option)
                                            <option value="{{ $option }}">
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($field['type'] === 'radio')

                                    @foreach($field['options'] ?? [] as $option)
                                        <div class="form-check">

                                            <input type="radio" name="{{ $field['key'] }}" value="{{ $option }}"
                                                class="form-check-input">

                                            <label class="form-check-label">
                                                {{ $option }}
                                            </label>

                                        </div>
                                    @endforeach

                                @elseif($field['type'] === 'checkbox')

                                    @foreach($field['options'] ?? [] as $option)
                                        <div class="form-check">

                                            <input type="checkbox" name="{{ $field['key'] }}[]" value="{{ $option }}"
                                                class="form-check-input">

                                            <label class="form-check-label">
                                                {{ $option }}
                                            </label>

                                        </div>
                                    @endforeach

                                @elseif($field['type'] === 'file')

                                    <input type="file" name="{{ $field['key'] }}" class="form-control">

                                @elseif($field['type'] === 'rating')

                                    <select name="{{ $field['key'] }}" class="form-select">
                                        <option value="">
                                            Select Rating
                                        </option>

                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>

                                @endif

                                @if(!empty($field['help_text']))
                                    <small class="text-muted">
                                        {{ $field['help_text'] }}
                                    </small>
                                @endif

                            </div>

                        @endforeach

                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>

                    </div>
                </div>

            </div>
        </div>

    </form>

@endsection