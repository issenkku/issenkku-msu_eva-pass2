<section
    id="evaluation-list"
    data-evaluation-list
    aria-labelledby="evaluation-list-heading"
    class="overflow-hidden rounded-xl bg-white shadow-lg">
    @include('dashboard.partials.index-list-header')
    @include('dashboard.partials.index-status-filters')

    <div class="overflow-x-auto">
        <table id="userParticipant" class="min-w-full divide-y divide-gray-200">
            @include('dashboard.partials.index-table-head')
            <tbody id="userTableBody" class="divide-y divide-gray-200 bg-white">
                @forelse($evaluations as $evaluation)
                    @include('dashboard.partials.index-table-row', [
                        'evaluation' => $evaluation,
                        'rowNumber' => ($evaluations->firstItem() ?? 1) + $loop->index,
                    ])
                @empty
                    @include('dashboard.partials.index-table-empty-row')
                @endforelse
            </tbody>
        </table>
    </div>

    @include('dashboard.partials.index-loading-state')
    @include('dashboard.partials.index-empty-state')
    @include('dashboard.partials.index-pagination')
</section>
