<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'employee') {
            abort(403, 'Unauthorized');
        }
        // List customers of authenticated user's company
        $companyId = Auth::user()->company_id;
        $customers = Customer::where('company_id', $companyId)->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:retail,wholesale,both',
            'cel_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cnic' => ['nullable', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'address' => 'nullable|string|max:500',
        ]);

        $validated['company_id'] = $companyId;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        // Ensure customer belongs to auth user's company
        if ($customer->company_id != Auth::user()->company_id) {
            abort(403);
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->company_id != Auth::user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:retail,wholesale,both',
            'cel_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cnic' => ['nullable', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->company_id != Auth::user()->company_id) {
            abort(403);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
