{{-- Product Q&A Section -- included in both create and edit forms --}}
<div class="col-lg-12 mb-4">
    <div class="card bg-transparent border-secondary">
        <div class="card-header bg-transparent border-secondary">
            <h6 class="mb-0"><i class="fa fa-question-circle me-1"></i> Product Q&amp;A <small class="text-muted fw-normal">(Questions &amp; Answers)</small></h6>
        </div>
        <div class="card-body bg-transparent" id="qaContainer">
            @if(!empty($product) && $product->qas->count() > 0)
                @foreach($product->qas as $qaIdx => $existingQa)
                <div class="qa-row border border-secondary rounded p-3 mb-3" data-qa-index="{{ $qaIdx }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary">Q&amp;A #{{ $qaIdx + 1 }}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-qa"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Question <span class="text-danger">*</span></label>
                        <input type="text" name="qas[{{ $qaIdx }}][question]" value="{{ $existingQa->question }}"
                               placeholder="E.g. What is the fabric material?" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Answer</label>
                        <textarea name="qas[{{ $qaIdx }}][answer]" rows="2"
                                  placeholder="E.g. This product is made of 100% cotton."
                                  class="form-control">{{ $existingQa->answer }}</textarea>
                    </div>
                </div>
                @endforeach
            @else
                <div class="qa-row border border-secondary rounded p-3 mb-3" data-qa-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary">Q&amp;A #1</span>
                        <button type="button" class="btn btn-sm btn-danger remove-qa"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Question <span class="text-danger">*</span></label>
                        <input type="text" name="qas[0][question]" placeholder="E.g. What is the fabric material?" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Answer</label>
                        <textarea name="qas[0][answer]" rows="2"
                                  placeholder="E.g. This product is made of 100% cotton."
                                  class="form-control"></textarea>
                    </div>
                </div>
            @endif
        </div>
        <div class="card-footer bg-transparent border-secondary">
            <button type="button" class="btn btn-sm btn-primary" id="addQa">
                <i class="fa fa-plus"></i> Add Q&amp;A
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var qaIndex = document.querySelectorAll('#qaContainer .qa-row').length;

    document.getElementById('addQa').addEventListener('click', function () {
        var html = `<div class="qa-row border border-secondary rounded p-3 mb-3" data-qa-index="${qaIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary">Q&A #${qaIndex + 1}</span>
                <button type="button" class="btn btn-sm btn-danger remove-qa"><i class="fa fa-trash"></i></button>
            </div>
            <div class="mb-2">
                <label class="form-label">Question <span class="text-danger">*</span></label>
                <input type="text" name="qas[${qaIndex}][question]" placeholder="E.g. What is the fabric material?" class="form-control">
            </div>
            <div>
                <label class="form-label">Answer</label>
                <textarea name="qas[${qaIndex}][answer]" rows="2"
                          placeholder="E.g. This product is made of 100% cotton."
                          class="form-control"></textarea>
            </div>
        </div>`;
        document.getElementById('qaContainer').insertAdjacentHTML('beforeend', html);
        qaIndex++;
    });

    document.getElementById('qaContainer').addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-qa');
        if (btn) {
            var rows = document.querySelectorAll('#qaContainer .qa-row');
            if (rows.length > 1) {
                btn.closest('.qa-row').remove();
            }
        }
    });
})();
</script>
