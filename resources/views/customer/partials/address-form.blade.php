{{-- Shared address form fields. $a = UserAddress|null --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Label</label>
        <select name="label" class="form-control">
            @foreach(['Home','Work','Other'] as $lbl)
                <option value="{{ $lbl }}" {{ old('label', $a?->label ?? 'Home') === $lbl ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        @error('label') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Recipient Name <span class="text-danger">*</span></label>
        <input type="text" name="recipient_name" class="form-control"
               value="{{ old('recipient_name', $a?->recipient_name ?? auth()->user()->name) }}"
               placeholder="Full name" required>
        @error('recipient_name') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone <span class="text-danger">*</span></label>
        <input type="tel" name="phone" class="form-control"
               value="{{ old('phone', $a?->phone ?? auth()->user()->phone) }}"
               placeholder="01XXXXXXXXX" maxlength="20" required>
        @error('phone') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label class="form-label">Full Address <span class="text-danger">*</span></label>
        <textarea name="address" class="form-control" rows="3"
                  placeholder="House no, road, area, district..." required>{{ old('address', $a?->address) }}</textarea>
        @error('address') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_default" id="isDefault{{ $a?->id }}"
                   {{ old('is_default', $a?->is_default) ? 'checked' : '' }}>
            <label class="form-check-label" for="isDefault{{ $a?->id }}" style="font-size:13px;">
                Set as default delivery address
            </label>
        </div>
    </div>
</div>
