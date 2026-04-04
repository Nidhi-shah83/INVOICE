@extends('layouts.app')

@section('page-title', 'Reports & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Date Range Picker -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700">From Date</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}"
                       class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700">To Date</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}"
                       class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Report Type Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                @foreach(['gst' => 'GST Summary', 'revenue_by_client' => 'Revenue by Client', 'ar_aging' => 'AR Aging', 'ai_performance' => 'AI Performance', 'followup_insights' => 'Follow-up Insights'] as $type => $label)
                    <a href="{{ route('reports.index', ['report_type' => $type, 'from_date' => $fromDate, 'to_date' => $toDate]) }}"
                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $reportType === $type ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            @switch($reportType)
                @case('gst')
                    @include('reports.gst_summary')
                    @break
                @case('revenue_by_client')
                    @include('reports.revenue_by_client')
                    @break
                @case('ar_aging')
                    @include('reports.ar_aging')
                    @break
                @case('ai_performance')
                    @include('reports.ai_performance')
                    @break
                @case('followup_insights')
                    @include('reports.followup_insights')
                    @break
            @endswitch
        </div>
    </div>
</div>
@endsection