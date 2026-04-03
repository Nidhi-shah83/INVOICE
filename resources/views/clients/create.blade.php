@extends('layouts.app')

@section('page-title', 'New Client')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('clients.store') }}" class="space-y-6" data-swal data-swal-message="Save this client?">
                @csrf

                @include('clients.partials.form', ['client' => null])

                <div class="flex items-center justify-end">
                    <a href="{{ route('clients.index') }}" class="text-sm text-slate-500 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="ml-4 inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition">
                        Save client
                    </button>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[data-swal]');
            if (!form) return;
            const promptMessage = form.dataset.swalMessage || 'Are you sure?';

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                Swal.fire({
                    title: 'Confirm',
                    text: promptMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save',
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
@endsection
