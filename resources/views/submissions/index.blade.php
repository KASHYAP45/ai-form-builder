@extends('layouts.admin')

@section('content')

	<div class="app-content">

		<div class="side-app">

			{{-- Page Header --}}
			<div class="page-header d-flex justify-content-between align-items-center">

				<div>
					<h4 class="page-title">
						{{ $form->title }} - Submissions
					</h4>
				</div>

				<div>

					<a href="{{ route('submissions.export', $form->id) }}" class="btn btn-success">
						<i class="fa fa-download"></i>
						Export CSV
					</a>

				</div>

			</div>


			{{-- Success --}}
			@if(session('success'))

				<div class="alert alert-success">
					{{ session('success') }}
				</div>

			@endif


			<div class="card">

				<div class="card-header">

					<div class="row" style="width:100%">

						<div class="col-md-6">

							<h4 class="card-title mb-0">
								Submissions
							</h4>

						</div>

						<div class="col-md-6">

							{{-- Search --}}
							<form method="GET" action="{{ route('submissions.index', $form->id) }}">

								<div class="input-group">

									<input type="text" name="search" value="{{ $search }}" class="form-control"
										placeholder="Search submissions...">

									<button type="submit" class="btn btn-primary">
										Search
									</button>

									@if($search)

										<a href="{{ route('submissions.index', $form->id) }}" class="btn btn-secondary">
											Clear
										</a>

									@endif

								</div>

							</form>

						</div>

					</div>

				</div>


				<div class="card-body">

					<div class="table-responsive">

						<table class="table table-bordered table-striped">

							<thead>

								<tr>

									<th>
										#
									</th>


									@foreach($form->schema['fields'] ?? [] as $field)

										@if($field['type'] !== 'section')

											<th>
												{{ $field['label'] }}
											</th>

										@endif

									@endforeach

									<th>
										Submitted At
									</th>
								</tr>

							</thead>


							<tbody>

								@forelse($submissions as $submission)

									<tr>

										<td>
											{{ $loop->iteration }}
										</td>

										@foreach($form->schema['fields'] ?? [] as $field)

											@if($field['type'] !== 'section')

												@php

													$value = $submission->data[$field['key']] ?? '';

												@endphp

												<td>

													@if(is_array($value))

														{{ implode(', ', $value) }}

													@else

														{{ $value }}

													@endif

												</td>

											@endif

										@endforeach


										<td>
											{{ $submission->created_at->format('d M Y H:i') }}
										</td>

									</tr>

								@empty

									<tr>

										<td colspan="100" class="text-center py-4">

											@if($search)

												No submissions found for
												"<strong>{{ $search }}</strong>"

											@else

												No submissions found.

											@endif

										</td>

									</tr>

								@endforelse

							</tbody>

						</table>

					</div>


					{{-- Pagination --}}

					@if($submissions->hasPages())

						<div class="d-flex justify-content-between align-items-center mt-3">

							<div>

								Showing
								{{ $submissions->firstItem() }}
								to
								{{ $submissions->lastItem() }}
								of
								{{ $submissions->total() }}
								submissions

							</div>

							<div>

								{{ $submissions->links() }}

							</div>

						</div>

					@endif

				</div>

			</div>

		</div>

	</div>

@endsection