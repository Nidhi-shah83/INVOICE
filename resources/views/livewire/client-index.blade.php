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
                                    <div class="relative group inline-flex">
                                        <a
                                            href="{{ route('clients.show', $client) }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                                            aria-label="View client {{ $client->name }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A5.002 5.002 0 0111 15h2a5 5 0 104 7.778" />
                                                <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                        </a>
                                        <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                            View
                                        </span>
                                    </div>
                                    <div class="relative group inline-flex">
                                        <a
                                            href="{{ route('clients.edit', $client) }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                                            aria-label="Edit client {{ $client->name }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4h6m-3 0v6M4 17v3h3l9-9-3-3-9 9z" />
                                            </svg>
                                        </a>
                                        <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                            Edit
                                        </span>
                                    </div>
                                    <div class="relative group inline-flex">
                                        <form
                                            method="POST"
                                            action="{{ route('clients.destroy', $client) }}"
                                            data-swal-confirm
                                            data-swal-title="Discard {{ $client->name }}?"
                                            data-swal-text="This will permanently remove the client."
                                            data-swal-confirm-button="Delete client"
                                            data-swal-cancel-button="Cancel"
                                            data-swal-icon="warning"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-full border border-rose-100 bg-rose-50 px-3 py-1 text-rose-600 hover:border-rose-200 hover:bg-rose-100"
                                                aria-label="Delete client {{ $client->name }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M10 11v6m4-6v6M9 7V5h6v2" />
                                                </svg>
                                            </button>
                                        </form>
                                        <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                            Delete
                                        </span>
                                    </div>
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
