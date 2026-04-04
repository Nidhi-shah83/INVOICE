<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">AI Collections Performance</h3>
        <button onclick="exportToCSV('ai_performance')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            Export CSV
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Calls Made</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $data['total_calls'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Promises Made</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $data['fulfilled_promises'] + $data['broken_promises'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-emerald-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Promises Kept</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $data['fulfilled_promises'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Success Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                @if(($data['fulfilled_promises'] + $data['broken_promises']) > 0)
                                    {{ round(($data['fulfilled_promises'] / ($data['fulfilled_promises'] + $data['broken_promises'])) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Additional Metrics</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Invoices with Follow-ups</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ $data['invoices_with_followups'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Avg Promised Payment Delay (Days)</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ $data['avg_promised_delay'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">High/Low Confidence Ratio</dt>
                <dd class="text-2xl font-semibold text-gray-900">
                    {{ is_numeric($data['confidence_ratio']) ? round($data['confidence_ratio'], 2) : $data['confidence_ratio'] }}
                </dd>
            </div>
        </div>
    </div>
</div>

<script>
function exportToCSV(type) {
    const url = new URL('{{ route("reports.export") }}', window.location.origin);
    url.searchParams.set('report_type', type);
    url.searchParams.set('from_date', '{{ $fromDate }}');
    url.searchParams.set('to_date', '{{ $toDate }}');
    window.location.href = url.toString();
}
</script>