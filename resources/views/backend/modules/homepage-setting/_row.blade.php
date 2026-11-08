@php
    $section = $section ?? null;
    $needsCategory = in_array($section->type ?? '', \App\Models\HomepageSection::CATEGORY_TYPES, true);
    $needsTreasures = ($section->type ?? null) === \App\Models\HomepageSection::TYPE_TREASURES;
    $treasureItems = \App\Models\HomepageSection::treasureItems($section->settings ?? null);
@endphp
<div class="card mb-3 homepage-block" data-index="{{ $index }}">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-2">
                <label class="form-label d-block">Order</label>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary block-up" title="Move up">
                        <i class="fa fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary block-down" title="Move down">
                        <i class="fa fa-arrow-down"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Block Type</label>
                <x-required/>
                <select name="sections[{{ $index }}][type]" class="form-select block-type">
                    @foreach(\App\Models\HomepageSection::TYPE_LIST as $type => $label)
                        <option value="{{ $type }}" {{ ($section->type ?? null) === $type ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 block-category-wrap" style="{{ $needsCategory ? '' : 'display:none' }}">
                <label class="form-label">Category</label>
                <x-required/>
                <select name="sections[{{ $index }}][category_id]" class="form-select">
                    <option value="">Select Category</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $id }}" {{ (int) ($section->category_id ?? 0) === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Title Override</label>
                <input type="text" name="sections[{{ $index }}][title]" value="{{ $section->title ?? '' }}"
                       class="form-control" placeholder="Optional heading">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Status</label>
                <select name="sections[{{ $index }}][status]" class="form-select">
                    <option value="1" {{ (int) ($section->status ?? \App\Models\HomepageSection::STATUS_ACTIVE) === 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ (int) ($section->status ?? \App\Models\HomepageSection::STATUS_ACTIVE) === 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12 block-treasures-wrap" style="{{ $needsTreasures ? '' : 'display:none' }}">
                <label class="form-label">Treasure Links</label>
                <p class="text-muted mb-2" style="font-size:12px;">
                    The five icons are fixed in this order: Pen, Car, Ramen, Gum, Others.
                    Set a label and destination link for each one.
                </p>
                @foreach($treasureItems as $treasure)
                    <div class="row g-2 align-items-center mb-2 treasure-admin-row">
                        <div class="col-auto">
                            <img src="{{ asset($treasure['icon']) }}" alt="{{ $treasure['label'] }}"
                                 style="width:40px;height:40px;object-fit:contain;">
                        </div>
                        <div class="col-sm-3">
                            <input type="text"
                                   name="sections[{{ $index }}][settings][items][{{ $loop->index }}][label]"
                                   value="{{ $treasure['label'] }}"
                                   class="form-control"
                                   placeholder="{{ $treasure['label'] }}">
                        </div>
                        <div class="col">
                            <input type="text"
                                   name="sections[{{ $index }}][settings][items][{{ $loop->index }}][link]"
                                   value="{{ $treasure['link'] }}"
                                   class="form-control"
                                   placeholder="https://example.com/page or /products">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-lg-12 text-end">
                <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id ?? '' }}">
                <button type="button" class="btn btn-sm btn-danger remove-block">
                    <i class="fa fa-trash"></i> Remove Block
                </button>
            </div>
        </div>
    </div>
</div>
