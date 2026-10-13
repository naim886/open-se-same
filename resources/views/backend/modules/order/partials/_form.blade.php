<div class="row justify-content-center my-5">
    <div class="col-lg-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <fieldset>
            <legend>Order Details</legend>
            <div class="row">
                <div class="col-12 mb-4">
                    {{html()->label('Products', 'product_ids')}}
                    <x-required/>
                    <div id="product-rows" class="mb-3">
                        {{-- initial row will be inserted by JS --}}
                    </div>
                    <button type="button" id="add-product-btn" class="btn btn-sm btn-outline-primary">Add Product</button>
                    @error('product_ids')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                    @error('product_ids.*')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                    @error('quantities.*')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Customer Name', 'customer_name')}}
                    <x-required/>
                    {{html()->text('customer_name')->placeholder('Enter Customer Name')->class('form-control')}}
                    @error('customer_name')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Customer Phone', 'customer_phone')}}
                    <x-required/>
                    {{html()->text('customer_phone')->placeholder('Enter Customer Phone')->class('form-control')}}
                    @error('customer_phone')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Customer Address', 'customer_address')}}
                    <x-required/>
                    {{html()->textarea('customer_address')->placeholder('Enter Customer Phone')->class('form-control')}}
                    @error('customer_address')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Select Status', 'status')}}
                    <x-required/>
                    {{html()->select('status', \App\Models\Order::STATUS_LIST)->placeholder('Select Status')->class('form-select')}}
                    @error('status')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Order Note', 'note')}}
                    <textarea name="note" id="note" rows="3" class="form-control" placeholder="Internal order note...">{{ old('note', $order?->notes ?? '') }}</textarea>
                    @error('note')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-6 mb-4 position-relative">
                    {{html()->label('Order Source', 'source')}}
                    <select name="source" id="source" class="form-select">
                        @php
                            $sources = \App\Http\Controllers\Backend\CustomerController::SOURCE_LIST;
                            $currentSource = old('source', $order?->source ?? 'facebook');
                        @endphp
                        @foreach($sources as $val => $lbl)
                            <option value="{{ $val }}" {{ $currentSource === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('source')
                    <p class="validation-message">{{$message}}</p>
                    @enderror
                </div>
                <div class="col-lg-3 mb-4 position-relative">
                    <div id="image-preview"></div>
                </div>
            </div>
        </fieldset>




    </div>
    <div class="col-lg-4">
        <fieldset>
            <legend>Shipping & Pricing</legend>
            <div class="mb-4 position-relative">
                {{html()->label('Delivery Location', 'delivery_location')}}
                <x-required/>
                {{html()->select('delivery_location', \App\Models\Order::DELIVERY_LIST)->id('delivery_location')->placeholder('Select Delivery Location')->class('form-select')}}
                @error('delivery_location')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                {{html()->label('Products Total', 'product_total_price')}}
                <x-required/>
                {{html()->number('product_price')->id('product_total_price')->placeholder('Products Total')->class('form-control')}}
                @error('product_price')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                {{html()->label('Delivery Charge', 'delivery_charge')}}
                <x-required/>
                {{html()->number('delivery_charge')->id('delivery_charge')->placeholder('Delivery Charge')->class('form-control')}}
                @error('delivery_charge')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                {{html()->label('Paid Amount', 'paid_amount')}}
                {{html()->number('paid_amount')->id('paid_amount')->placeholder('Paid Amount')->class('form-control')}}
                @error('paid_amount')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                {{html()->label('COD Amount', 'cod_amount')}}
                <small class="text-white-50">(auto-calc, editable)</small>
                {{html()->number('cod_amount')->id('cod_amount')->placeholder('COD Amount')->class('form-control')}}
                @error('cod_amount')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                {{html()->label('Order Total', 'order_total')}}
                <small class="text-white-50">(auto-calc, editable)</small>
                {{html()->number('order_total')->id('order_total')->placeholder('Order Total')->class('form-control')}}
                @error('order_total')
                <p class="validation-message">{{$message}}</p>
                @enderror
            </div>
        </fieldset>
    </div>
    <div class="col-lg-12">
        {{-- Add save / cancel controls inside the form --}}
        <div class="d-flex justify-content-center gap-2 mt-3">
            <button type="submit" id="save-order-btn" class="btn btn-primary">Save Order</button>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</div>
