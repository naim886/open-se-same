@extends('backend.layouts.app')
@section('title', 'Edit Expense')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->modelForm($expense, 'PUT', route('expense.update', $expense->id))->acceptsFiles()->open()}}
            <fieldset>
                <legend>Edit Expense</legend>
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

                    {{-- Existing attachment --}}
                    <div class="col-lg-6 mb-4">
                        @if($expense->attachment)
                            <label class="form-label">Current Attachment</label>
                            @php $ext = pathinfo($expense->attachment, PATHINFO_EXTENSION); @endphp
                            @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif']))
                                <div class="mb-2">
                                    <a href="{{ Storage::url($expense->attachment) }}" target="_blank">
                                        <img src="{{ Storage::url($expense->attachment) }}"
                                             style="max-height:80px;border-radius:4px;" alt="attachment">
                                    </a>
                                </div>
                            @else
                                <div class="mb-2">
                                    <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file"></i> {{ $expense->attachment_title ?: 'Download File' }}
                                    </a>
                                </div>
                            @endif
                            <small class="text-muted">Upload a new file below to replace it.</small>
                        @endif
                    </div>

                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label($expense->attachment ? 'Replace Attachment' : 'Attachment', 'attachment')}}
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
                    <div class="col-lg-6 d-flex gap-2 justify-content-center">
                        <a href="{{ route('expense.show', $expense->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn theme-button-outline">
                            <i class="fas fa-save"></i> Update Expense
                        </button>
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
            if (fileType.startsWith('image/')) {
                reader.onload = function (e) {
                    preview.append($('<img>').attr('src', e.target.result).css({height:'100px','border-radius':'4px'}));
                };
                reader.readAsDataURL(file);
            } else {
                let icon = '<i class="fas fa-file"></i>';
                if (fileName.endsWith('.pdf')) icon = '<i class="fas fa-file-pdf"></i>';
                else if (fileName.endsWith('.doc') || fileName.endsWith('.docx')) icon = '<i class="fas fa-file-word"></i>';
                else if (fileName.endsWith('.xls') || fileName.endsWith('.xlsx')) icon = '<i class="fas fa-file-excel"></i>';
                preview.html('<div style="font-size:48px;">' + icon + '</div>');
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        fieldset { padding: 24px; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; }
        legend { font-size: 1.1rem; font-weight: 600; padding: 0 8px; width: auto; }
        .text-muted { color: rgba(255,255,255,0.5) !important; }
        .form-control, .form-select {
            background: rgba(255,255,255,0.07) !important;
            color: inherit !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.35) !important; }
    </style>
@endpush
