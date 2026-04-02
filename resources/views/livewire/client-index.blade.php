<div>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Clients</h2>
                <p class="text-sm text-slate-500">Track GST eligibility and engagement per customer.</p>
            </div>
            <div class="w-full max-w-sm">
                <input
                    wire:model.debounce.500ms="search"
                    type="search"
                    placeholder="Search by name, company or GSTIN"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Name</th>
                        <th class="px-4 py-3 text-left font-semibold">Company name</th>
                        <th class="px-4 py-3 text-left font-semibold">GSTIN</th>
                        <th class="px-4 py-3 text-left font-semibold">State</th>
                        <th class="px-4 py-3 text-left font-semibold">GST type</th>
                        <th class="px-4 py-3 text-left font-semibold">Client type</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clients as $client)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $client->name }}</p>
                                <p class="text-xs text-slate-500">{{ $client->email ?? 'No email' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $client->company_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $client->gstin ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $client->state }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $client->gst_type === 'intra' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">
                                    {{ $client->gst_type === 'intra' ? 'Intra' : 'Inter' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ ucfirst($client->client_type) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-500">View</a>
                                    <a href="{{ route('clients.edit', $client) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Edit</a>
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Discard this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-500">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                No clients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $clients->links() }}
        </div>
    </div>
</div>
