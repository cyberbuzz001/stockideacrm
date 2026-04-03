<x-app-layout>
    <div class="min-h-screen bg-[var(--ag-page-bg)] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            @php
                $subView = match($role) {
                    'Admin' => 'dashboards.admin',
                    'Manager' => 'dashboards.manager',
                    'SBA' => 'dashboards.sba',
                    'BA' => 'dashboards.ba',
                    default => 'dashboards.ba'
                };
            @endphp

            @include($subView)
        </div>
    </div>

    <!-- Dashboard Auto-Refresh Logic -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Heartbeat animation for indicators
            console.log('AntiGravity Dashboard Initialized...');
            
            // Auto-refresh leaderboard data every 60s
            setInterval(() => {
                // Here we would typically fetch new data and update the DOM
                // For now, we rely on the premium CSS animations to keep it feeling alive
            }, 60000);
        });
    </script>
    @endpush
</x-app-layout>
