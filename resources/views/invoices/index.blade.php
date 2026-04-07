@extends('layouts.app')

@section('page-title', 'Invoices')

@section('primary-action')
    <a
        href="{{ route('invoices.create') }}"
        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition js-create-invoice"
        data-invoice-action="create"
    >
        + Create invoice
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoices</p>
                    <h1 class="text-2xl font-semibold text-slate-900">All invoices</h1>
                </div>
            </div>
            <div
                class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                x-data="invoiceTypeaheadSearch({
                    initialTerm: @js($search),
                    suggestionUrl: @js(route('invoices.searchSuggestions')),
                })"
                @click.outside="close()"
            >
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                    @foreach ($statusTabs as $tab)
                        @php
                            $isActive = $activeStatus === $tab || ($tab === 'all' && !$activeStatus);
                            $tabLabel = ucfirst($tab);
                            $count = $counts[$tab] ?? 0;
                        @endphp
                        <a
                            href="{{ route('invoices.index', ['status' => $tab === 'all' ? null : $tab, 'search' => $search ?: null]) }}"
                            class="inline-flex items-center gap-2 rounded-full border px-4 py-2 {{ $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        >
                            {{ $tabLabel }}
                            <span class="text-xs text-slate-400">{{ number_format($count) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="relative w-full lg:w-auto">
                    <form method="GET" action="{{ route('invoices.index') }}" x-ref="searchForm" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center" @submit="close()">
                        @if($activeStatus && $activeStatus !== 'all')
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <div class="relative w-full">
                            <input
                                type="search"
                                name="search"
                                x-model="term"
                                placeholder="Search invoice or client..."
                                autocomplete="off"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-2 pr-10 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 lg:w-96"
                                @input="onTermInput"
                                @focus="openFromFocus"
                                @keydown.down.prevent="move(1)"
                                @keydown.up.prevent="move(-1)"
                                @keydown.enter.prevent="handleEnter"
                            >
                            <svg x-show="loading" class="absolute right-3 top-2.5 h-5 w-5 animate-spin text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                                <path d="M22 12a10 10 0 0 0-10-10"></path>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                                Search
                            </button>
                            @if($search !== '')
                                <a href="{{ route('invoices.index', ['status' => $activeStatus !== 'all' ? $activeStatus : null]) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 hover:border-slate-300">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>

                    <div x-cloak x-show="open" class="absolute right-0 z-20 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl lg:w-96">
                        <template x-if="items.length === 0 && !loading">
                            <div class="px-4 py-3 text-sm text-slate-500">No matching invoices found.</div>
                        </template>
                        <template x-for="(item, index) in items" :key="item.id">
                            <button
                                type="button"
                                class="flex w-full items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50"
                                :class="{ 'bg-slate-50': index === highlighted }"
                                @mouseenter="highlighted = index"
                                @click="select(item)"
                            >
                                <div>
                                    <p class="text-sm font-semibold text-slate-900" x-text="item.invoice_number"></p>
                                    <p class="text-xs text-slate-500">
                                        <span x-text="item.client_name"></span>
                                        <span x-show="item.client_email"> - <span x-text="item.client_email"></span></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em]"
                                          :class="item.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : (item.payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')"
                                          x-text="item.payment_status"></span>
                                    <p class="mt-1 text-[10px] text-slate-500" x-text="'Due: Rs ' + Number(item.amount_due).toFixed(2)"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto sm:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Invoice #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-right font-semibold">Grand Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount Due</th>
                            <th class="px-4 py-3 text-left font-semibold">Due Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($invoices as $invoice)
                            @php
                                $isOverdue = $invoice->is_overdue;
                                $rowClass = $isOverdue
                                    ? 'bg-rose-50 hover:bg-rose-100'
                                    : ($invoice->payment_status === 'partial' ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-50');
                                $currencySymbol = setting('currency_symbol', 'Rs ');
                                $secondaryStatus = match (true) {
                                    $invoice->payment_status === 'paid' => null,
                                    $invoice->status === 'draft' => 'draft',
                                    $invoice->status === 'cancelled' => 'cancelled',
                                    $invoice->is_overdue => 'overdue',
                                    default => 'sent',
                                };
                            @endphp
                            <tr class="transition {{ $rowClass }}">
                                <td class="px-4 py-3 text-slate-900">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 hover:text-emerald-600">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $invoice->client->name ?? 'Unknown client' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ $currencySymbol }}{{ number_format($invoice->grand_total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">
                                    {{ $currencySymbol }}{{ number_format($invoice->amount_due, 2) }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $invoice->due_date?->format('d M, Y') ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $invoice->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($invoice->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                        {{ strtoupper($invoice->payment_status) }}
                                    </span>
                                    @if($secondaryStatus)
                                        <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-slate-400">{{ strtoupper($secondaryStatus) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-center">
                                        <div class="relative group inline-flex">
                                            <a
                                                href="{{ route('invoices.download', $invoice) }}"
                                                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 js-download-invoice"
                                                title="Download invoice {{ $invoice->invoice_number }} as PDF"
                                                data-invoice-number="{{ $invoice->invoice_number }}"
                                                aria-label="Download invoice {{ $invoice->invoice_number }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M9 4h6v4l4 4v6H7z" />
                                                </svg>
                                            </a>
                                            <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                PDF
                                            </span>
                                        </div>
                                        <div class="relative group inline-flex">
                                            <a
                                                href="{{ route('invoices.show', ['invoice' => $invoice, 'view' => '1']) }}"
                                                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600 hover:border-slate-300 hover:text-slate-900"
                                                title="View invoice {{ $invoice->invoice_number }}"
                                                aria-label="View invoice {{ $invoice->invoice_number }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                View
                                            </span>
                                        </div>
                                        <div class="relative group inline-flex">
                                            <form
                                                action="{{ route('invoices.send', $invoice) }}"
                                                method="POST"
                                                class="js-send-invoice"
                                                data-invoice-number="{{ $invoice->invoice_number }}"
                                                data-client-name="{{ $invoice->client->name ?? 'client' }}"
                                            >
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900"
                                                    title="Email invoice {{ $invoice->invoice_number }} to client"
                                                    aria-label="Send invoice {{ $invoice->invoice_number }}"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9 6 9-6M4 6h16v12H4z" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                Send
                                            </span>
                                        </div>
                                        <div class="relative group inline-flex">
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700 hover:bg-blue-100 hover:text-blue-900 js-view-calls"
                                                data-invoice-number="{{ $invoice->invoice_number }}"
                                                data-call-logs-url="{{ route('invoices.callLogs', ['invoice_number' => $invoice->invoice_number]) }}"
                                                title="View call history for {{ $invoice->invoice_number }}"
                                                aria-label="View calls for {{ $invoice->invoice_number }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8a3 3 0 00-3-3M9 5a3 3 0 00-3 3m12 6a3 3 0 01-3 3M9 19a3 3 0 01-3-3m5-3a5 5 0 015-5" />
                                                </svg>
                                            </button>
                                            <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                Calls
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-slate-400" colspan="7">
                                    No invoices yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <div id="invoiceCallLogsModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div id="invoiceCallLogsBackdrop" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 id="invoiceCallLogsModalLabel" class="text-lg font-semibold text-slate-900">Call History</h3>
                    <button
                        type="button"
                        id="invoiceCallLogsCloseButton"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Close"
                    >
                        ×
                    </button>
                </div>
                <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
                    <div id="invoiceCallLogsLoading" class="text-sm text-slate-500">Loading call history...</div>
                    <div id="invoiceCallLogsError" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>
                    <div id="invoiceCallLogsEmpty" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">No call logs found for this invoice.</div>
                    <div id="invoiceCallLogsList" class="space-y-3"></div>
                </div>
                <div class="flex justify-end border-t border-slate-200 px-6 py-3">
                    <button
                        type="button"
                        id="invoiceCallLogsFooterCloseButton"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceTypeaheadSearch({ initialTerm, suggestionUrl }) {
            return {
                term: initialTerm || '',
                suggestionUrl,
                items: [],
                loading: false,
                open: false,
                highlighted: -1,
                requestToken: 0,
                searchTimer: null,
                suggestTimer: null,
                lastSubmittedTerm: String(initialTerm || '').trim(),
                onTermInput() {
                    clearTimeout(this.searchTimer);
                    clearTimeout(this.suggestTimer);

                    this.suggestTimer = setTimeout(() => {
                        this.fetchSuggestions();
                    }, 170);

                    this.searchTimer = setTimeout(() => {
                        this.submitSearchForm();
                    }, 420);
                },
                submitSearchForm(force = false) {
                    const normalized = this.term.trim();
                    if (!force && normalized === this.lastSubmittedTerm) {
                        return;
                    }

                    this.lastSubmittedTerm = normalized;
                    this.close();
                    this.$refs.searchForm.requestSubmit();
                },
                openFromFocus() {
                    if (this.items.length > 0 && this.term.trim().length >= 2) {
                        this.open = true;
                    }
                },
                close() {
                    this.open = false;
                    this.highlighted = -1;
                },
                async fetchSuggestions() {
                    const query = this.term.trim();
                    if (query.length < 2) {
                        this.items = [];
                        this.close();
                        return;
                    }

                    this.loading = true;
                    const token = ++this.requestToken;

                    try {
                        const response = await window.axios.get(this.suggestionUrl, {
                            params: { q: query },
                            headers: { Accept: 'application/json' },
                        });

                        if (token !== this.requestToken) {
                            return;
                        }

                        this.items = response?.data?.suggestions || [];
                        this.open = true;
                        this.highlighted = this.items.length > 0 ? 0 : -1;
                    } catch (error) {
                        if (token === this.requestToken) {
                            this.items = [];
                            this.open = false;
                        }
                    } finally {
                        if (token === this.requestToken) {
                            this.loading = false;
                        }
                    }
                },
                move(step) {
                    if (!this.open || this.items.length === 0) {
                        return;
                    }

                    const next = this.highlighted + step;
                    if (next < 0) {
                        this.highlighted = this.items.length - 1;
                        return;
                    }

                    if (next >= this.items.length) {
                        this.highlighted = 0;
                        return;
                    }

                    this.highlighted = next;
                },
                handleEnter() {
                    if (this.open && this.highlighted >= 0 && this.items[this.highlighted]) {
                        this.select(this.items[this.highlighted]);
                        return;
                    }

                    this.submitSearchForm(true);
                },
                select(item) {
                    window.location.href = item.url;
                },
            };
        }
    </script>
    <script>
        const invoiceCallLogsModal = {
            root: null,
            backdrop: null,
            closeButton: null,
            footerCloseButton: null,
            title: null,
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-send-invoice').forEach(form => {
                form.addEventListener('submit', event => handleInvoiceSend(event, form));
            });

            document.querySelectorAll('.js-download-invoice').forEach(link => {
                link.addEventListener('click', event => handleInvoiceDownload(event, link));
            });

            const createLink = document.querySelector('.js-create-invoice');
            if (createLink) {
                createLink.addEventListener('click', event => handleInvoiceCreate(event, createLink));
            }

            initializeCallLogsModal();

            document.querySelectorAll('.js-view-calls').forEach(button => {
                button.addEventListener('click', () => handleViewCalls(button));
            });
        });

        function initializeCallLogsModal() {
            invoiceCallLogsModal.root = document.getElementById('invoiceCallLogsModal');
            invoiceCallLogsModal.backdrop = document.getElementById('invoiceCallLogsBackdrop');
            invoiceCallLogsModal.closeButton = document.getElementById('invoiceCallLogsCloseButton');
            invoiceCallLogsModal.footerCloseButton = document.getElementById('invoiceCallLogsFooterCloseButton');
            invoiceCallLogsModal.title = document.getElementById('invoiceCallLogsModalLabel');

            if (!invoiceCallLogsModal.root) {
                return;
            }

            const closeModal = () => hideCallLogsModal();

            invoiceCallLogsModal.backdrop?.addEventListener('click', closeModal);
            invoiceCallLogsModal.closeButton?.addEventListener('click', closeModal);
            invoiceCallLogsModal.footerCloseButton?.addEventListener('click', closeModal);

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !invoiceCallLogsModal.root.classList.contains('hidden')) {
                    closeModal();
                }
            });
        }

        function showCallLogsModal() {
            if (!invoiceCallLogsModal.root) {
                return;
            }

            invoiceCallLogsModal.root.classList.remove('hidden');
            invoiceCallLogsModal.root.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-y-hidden');
        }

        function hideCallLogsModal() {
            if (!invoiceCallLogsModal.root) {
                return;
            }

            invoiceCallLogsModal.root.classList.add('hidden');
            invoiceCallLogsModal.root.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-y-hidden');
        }

        async function handleViewCalls(button) {
            if (!invoiceCallLogsModal.root || !window.axios) {
                return;
            }

            const invoiceNumber = button.dataset.invoiceNumber || 'invoice';
            const callLogsUrl = button.dataset.callLogsUrl || '';

            if (invoiceCallLogsModal.title) {
                invoiceCallLogsModal.title.textContent = `Call History - ${invoiceNumber}`;
            }

            toggleCallLogsState({
                loading: true,
                error: '',
                hasLogs: false,
            });

            showCallLogsModal();

            try {
                const response = await window.axios.get(callLogsUrl, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const callLogs = Array.isArray(response?.data?.call_logs) ? response.data.call_logs : [];
                renderCallLogs(callLogs);
            } catch (error) {
                toggleCallLogsState({
                    loading: false,
                    error: 'Unable to load call history right now.',
                    hasLogs: false,
                });
            }
        }

        function renderCallLogs(callLogs) {
            const listElement = document.getElementById('invoiceCallLogsList');
            if (!listElement) {
                return;
            }

            listElement.innerHTML = '';

            if (!Array.isArray(callLogs) || callLogs.length === 0) {
                toggleCallLogsState({
                    loading: false,
                    error: '',
                    hasLogs: false,
                });

                return;
            }

            callLogs.forEach(callLog => {
                const card = document.createElement('div');
                card.className = 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm';
                card.innerHTML = `
                    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h6 class="mb-1 text-sm font-semibold text-slate-800">Call Date/Time</h6>
                            <p class="mb-0 text-sm text-slate-600">${escapeHtml(formatDateTime(callLog.call_started_at))}${callLog.call_ended_at ? ` - ${escapeHtml(formatDateTime(callLog.call_ended_at))}` : ''}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <h6 class="mb-1 text-sm font-semibold text-slate-800">Promised Payment Date</h6>
                            <p class="mb-0 text-sm text-slate-600">${escapeHtml(formatDate(callLog.promised_payment_date))}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="mb-1 text-sm font-semibold text-slate-800">Notes</h6>
                        <p class="mb-0 text-sm text-slate-600">${escapeHtml(callLog.notes || 'N/A')}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="mb-1 text-sm font-semibold text-slate-800">Confidence</h6>
                        <p class="mb-0 text-sm text-slate-600">${escapeHtml(callLog.confidence || 'N/A')}</p>
                    </div>
                    <div>
                        <h6 class="mb-2 text-sm font-semibold text-slate-800">Conversation</h6>
                        <pre class="mb-0 rounded-md border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700" style="white-space: pre-wrap;">${escapeHtml(callLog.conversation || 'N/A')}</pre>
                    </div>
                `;

                listElement.appendChild(card);
            });

            toggleCallLogsState({
                loading: false,
                error: '',
                hasLogs: true,
            });
        }

        function toggleCallLogsState({ loading, error, hasLogs }) {
            const loadingElement = document.getElementById('invoiceCallLogsLoading');
            const errorElement = document.getElementById('invoiceCallLogsError');
            const emptyElement = document.getElementById('invoiceCallLogsEmpty');

            if (loadingElement) {
                loadingElement.classList.toggle('hidden', !loading);
            }

            if (errorElement) {
                errorElement.textContent = error || '';
                errorElement.classList.toggle('hidden', !error);
            }

            if (emptyElement) {
                const shouldShowEmpty = !loading && !error && !hasLogs;
                emptyElement.classList.toggle('hidden', !shouldShowEmpty);
            }
        }

        function formatDateTime(value) {
            if (!value) {
                return 'N/A';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return 'N/A';
            }

            return new Intl.DateTimeFormat('en-IN', {
                dateStyle: 'medium',
                timeStyle: 'short',
            }).format(date);
        }

        function formatDate(value) {
            if (!value) {
                return 'N/A';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return new Intl.DateTimeFormat('en-IN', {
                dateStyle: 'medium',
            }).format(date);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function handleInvoiceSend(event, form) {
            event.preventDefault();

            if (!window.Swal) {
                form.submit();
                return;
            }

            const invoiceNumber = form.dataset.invoiceNumber || 'invoice';
            const clientName = form.dataset.clientName || 'client';

            Swal.fire({
                title: `Send ${invoiceNumber}?`,
                text: `Email this invoice to ${clientName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Send invoice',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function handleInvoiceDownload(event, link) {
            event.preventDefault();

            if (!window.Swal) {
                window.location.href = link.href;
                return;
            }

            const invoiceNumber = link.dataset.invoiceNumber || 'invoice';

            Swal.fire({
                title: `Download ${invoiceNumber}?`,
                text: 'The PDF will download after you confirm.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Download',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = link.href;
                }
            });
        }

        function handleInvoiceCreate(event, link) {
            event.preventDefault();

            if (!window.Swal) {
                window.location.href = link.href;
                return;
            }

            Swal.fire({
                title: 'Create a new invoice?',
                text: 'You will be redirected to the invoice builder.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Create invoice',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = link.href;
                }
            });
        }
    </script>
@endsection
