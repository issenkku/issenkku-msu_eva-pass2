{{-- กล่องความคิดเห็นเพิ่มเติม --}}
<div class="info-card">
    <div class="card-header">
        <h3>ความคิดเห็นเพิ่มเติม</h3>
    </div>
    <div class="card-body">
        <textarea id="summernote" name="comment" style="min-height: 150px;">{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
    </div>
</div>
