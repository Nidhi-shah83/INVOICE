<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('clients.index');
    }

    public function create()
    {
        return view('clients.create', [
            'states' => $this->indianStates(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateClient($request);

        $request->user()->clients()->create($data);

        return redirect()->route('clients.index')->with('status', 'Client created.');
    }

    public function show(Client $client)
    {
        $this->ensureOwnership($client);

        $client->load(['quotes', 'orders', 'invoices']);

        return view('clients.show', [
            'client' => $client,
            'states' => $this->indianStates(),
        ]);
    }

    public function edit(Client $client)
    {
        $this->ensureOwnership($client);

        return view('clients.edit', [
            'client' => $client,
            'states' => $this->indianStates(),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $this->ensureOwnership($client);

        $client->update($this->validateClient($request));

        return redirect()->route('clients.index')->with('status', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->ensureOwnership($client);

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client removed.');
    }

    protected function ensureOwnership(Client $client): void
    {
        if ($client->user_id !== auth()->id()) {
            abort(403);
        }
    }

    protected function validateClient(Request $request): array
    {
        $stateRule = ['required', 'string'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'size:15', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'state' => $stateRule,
            'address' => ['nullable', 'string'],
        ]);
    }

    protected function indianStates(): array
    {
        return [
            'Andhra Pradesh',
            'Arunachal Pradesh',
            'Assam',
            'Bihar',
            'Chhattisgarh',
            'Goa',
            'Gujarat',
            'Haryana',
            'Himachal Pradesh',
            'Jharkhand',
            'Karnataka',
            'Kerala',
            'Madhya Pradesh',
            'Maharashtra',
            'Manipur',
            'Meghalaya',
            'Mizoram',
            'Nagaland',
            'Odisha',
            'Punjab',
            'Rajasthan',
            'Sikkim',
            'Tamil Nadu',
            'Telangana',
            'Tripura',
            'Uttar Pradesh',
            'Uttarakhand',
            'West Bengal',
            'Andaman and Nicobar Islands',
            'Chandigarh',
            'Dadra and Nagar Haveli and Daman and Diu',
            'Delhi',
            'Jammu and Kashmir',
            'Ladakh',
            'Lakshadweep',
            'Puducherry',
        ];
    }
}
