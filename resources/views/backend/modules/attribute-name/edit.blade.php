@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->modelForm($attributeName, 'PATCH', route('attribute-name.update', $attributeName->id))->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>
                <div class="row">
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Attribute Name', 'name')}}
                        <x-required/>
                        {{html()->text('name')->placeholder('Ex. Color, Size, Material')->class('form-control')}}
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
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-edit"></i> Update Attribute Name</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->closeModelForm()}}
        </div>
    </div>
@endsection

