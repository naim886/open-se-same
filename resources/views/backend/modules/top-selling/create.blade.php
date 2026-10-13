@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-10">
        {{ html()->form('POST', route('top-selling.store'))->open() }}
        @csrf
        <fieldset>
            <legend>{{ $cms_content['page_title'] ?? 'Add Top Selling Products' }}</legend>
            @include('backend.modules.top-selling._form')
        </fieldset>
        {{ html()->form()->close() }}
    </div>
</div>
@endsection
