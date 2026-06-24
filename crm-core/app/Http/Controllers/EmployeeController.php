<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    private function requireAdminOrManager(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('team', 'manage')) {
            abort(403, 'Unauthorized access to employee management.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->requireAdminOrManager();

        // Fetch all users except the current admin
        $employees = User::where('id', '!=', auth()->id())->get();

        // Sort in PHP to ensure compatibility with both SQLite (Dev) and MySQL (Prod)
        $employees = $employees->sortBy(function ($user) {
            return match ($user->role) {
                'Manager' => 1,
                'SBA' => 2,
                'BA' => 3,
                default => 4,
            };
        });

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireAdminOrManager();
        // Get Managers/SBAs for "Report To" dropdown
        $supervisors = User::whereIn('role', ['Manager', 'SBA'])->get();
        return view('employees.create', compact('supervisors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireAdminOrManager();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:Manager,Team Leader,SBA,BA'],
            'parent_id' => ['nullable', 'exists:users,id'],
            'whatsapp_phone_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'parent_id' => $request->parent_id,
            'whatsapp_phone_id' => $request->whatsapp_phone_id,
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $employee)
    {
        $this->requireAdminOrManager();
        // Get Managers/SBAs for "Report To" dropdown
        $supervisors = User::whereIn('role', ['Manager', 'SBA'])
                           ->where('id', '!=', $employee->id) // Cannot report to self
                           ->get();
        return view('employees.edit', compact('employee', 'supervisors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        $this->requireAdminOrManager();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:Manager,Team Leader,SBA,BA'],
            'parent_id' => ['nullable', 'exists:users,id'],
            'whatsapp_phone_id' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'parent_id' => $request->parent_id,
            'whatsapp_phone_id' => $request->whatsapp_phone_id,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        $this->requireAdminOrManager();
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        // Logical delete or actual delete? For now, actual delete.
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee removed.');
    }
}
