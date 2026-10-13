@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->form('POST', route('attribute-value.store'))->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>
                <div class="row">
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Attribute Name', 'attribute_name_id')}}
                        <x-required/>
                        {{html()->select('attribute_name_id', $attributeNames)->placeholder('Select Attribute Name')->class('form-select')}}
                        @error('attribute_name_id')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Attribute Value', 'name')}}
                        <x-required/>
                        {{html()->text('name')->placeholder('Ex. Red, Large, Cotton')->class('form-control')}}
                        @error('name')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Status', 'status')}}
                        <x-required/>
                        {{html()->select('status', ['1' => 'Active', '2' => 'Inactive'])->placeholder('Select status')->class('form-select')}}
                        @error('status')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-4">
                        <div class="d-grid">
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Add Attribute Value</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->form()->close()}}
        </div>
    </div>
@endsection

