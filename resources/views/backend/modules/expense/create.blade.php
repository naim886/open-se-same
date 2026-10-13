@extends('backend.layouts.app')
@section('title', 'Add Expense')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->form('POST', route('expense.store'))->acceptsFiles()->open()}}
            <fieldset>
                <legend>Add Expense</legend>
                <div class="row justify-content-center">
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Purpose', 'purpose')}}
                        <x-required/>
                        {{html()->text('purpose')->placeholder('Ex. Product Buy')->class('form-control')}}
                        @error('purpose')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Date', 'date')}}
                        <x-required/>
                        {{html()->date('date')->class('form-control')}}
                        @error('date')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Amount (৳)', 'amount')}}
                        <x-required/>
                        {{html()->text('amount')->placeholder('Ex. 250.30')->class('form-control')}}
                        @error('amount')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Status', 'status')}}
                        <x-required/>
                        {{html()->select('status', \App\Models\Expense::STATUS_LIST)->placeholder('Select status')->class('form-select')}}
                        @error('status')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category', 'category')}}
                        {{html()->select('category', \App\Models\Expense::CATEGORY_LIST)->placeholder('Select category')->class('form-select')}}
                        @error('category')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Attachment', 'attachment')}}
                        {{html()->file('attachment')->class('form-control')}}
                        @error('attachment')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Attachment Title', 'attachment_title')}}
                        {{html()->text('attachment_title')->placeholder('Ex. Invoice')->class('form-control')}}
                        @error('attachment_title')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        <div id="preview" class="text-center"></div>
                    </div>
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Note', 'note')}}
                        {{html()->textarea('note')->placeholder('Additional notes...')->class('form-control')->rows(3)}}
                        @error('note')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-4">
                        <div class="d-grid">
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Add Expense</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->form()->close()}}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#attachment').on('change', function (e) {
            const file = e.target.files[0];
            const preview = $('#preview');
            preview.empty();

            if (!file) return;

            const fileType = file.type;
            const fileName = file.name.toLowerCase();
            const reader = new FileReader();

            // Image preview
            if (fileType.startsWith('image/')) {
                reader.onload = function (e) {
                    const img = $('<img>').attr('src', e.target.result);
                    preview.append(img);
                };
                reader.readAsDataURL(file);
            } else {
                // Show icon based on extension
                let icon = '';

                if (fileName.endsWith('.pdf')) {
                    icon = '<i class="fa-solid fa-file-pdf"></i>';
                } else if (fileName.endsWith('.doc') || fileName.endsWith('.docx')) {
                    icon = '<i class="fa-solid fa-file-word"></i>';
                } else if (fileName.endsWith('.xls') || fileName.endsWith('.xlsx')) {
                    icon = '<i class="fa-solid fa-file-excel"></i>';
                } else {
                    icon = '<i class="fa-solid fa-file"></i>';
                }
                const iconEl = $('<div class="file-icon">').html(icon);
                preview.append(iconEl);
            }
        });
    </script>
@endpush
@push('styles')
    <style>
        #preview img{
            width: auto;
            height: 100px;
            object-fit: cover;
        }
        .file-icon {
            font-size: 72px;
            color: #555;
            text-align: center;
        }
    </style>
@endpush
