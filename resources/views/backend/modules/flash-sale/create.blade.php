@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-10">
        {{ html()->form('POST', route('flash-sale.store'))->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] ?? 'Create Flash Sale' }}</legend>
            @include('backend.modules.flash-sale._form')
        </fieldset>
        {{ html()->form()->close() }}
    </div>
</div>
@endsection
