<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Accounts Receivable Aging</h3>
        <button onclick="exportToCSV('ar_aging')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            Export CSV
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bucket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Count</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $colors = [
                        'current' => 'bg-green-50 text-green-800',
                        '1-30' => 'bg-yellow-50 text-yellow-800',
                        '31-60' => 'bg-orange-50 text-orange-800',
                        '61-90' => 'bg-red-50 text-red-800',
                        '90+' => 'bg-red-100 text-red-900'
                    ];
                    $bucketLabels = [
                        'current' => 'Current',
                        '1-30' => '1-30 Days',
                        '31-60' => '31-60 Days',
                        '61-90' => '61-90 Days',
                        '90+' => '90+ Days'
                    ];
                @endphp
                @foreach($data as $bucket => $info)
                    <tr class="{{ $colors[$bucket] ?? '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            {{ $bucketLabels[$bucket] ?? $bucket }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            ₹{{ number_format($info['amount'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $info['count'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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