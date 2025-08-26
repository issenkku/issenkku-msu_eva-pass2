@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลผู้บริหาร"/>

    <x-manager-table  
        :evaluations="$evaluations"  
        :statusCounts="$statusCounts" />
</div>

<!-- Mobile-friendly spacing -->
<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
    
    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}
</style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Simple fade in animation
            $('.table-card, .sidebar-card').css('opacity', '0').animate({
                opacity: 1
            }, 200);

            // Simple hover effect for table rows
            $('.evaluation-table tbody tr').hover(
                function() {
                    $(this).addClass('hover-row');
                },
                function() {
                    $(this).removeClass('hover-row');
                }
            );

            // Auto refresh every 5 minutes
            setInterval(function() {
                console.log('Auto refreshing data...');
            }, 300000);
        });
    </script>
@endpush
