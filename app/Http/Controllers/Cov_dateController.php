<?php

namespace App\Http\Controllers;

use App\Models\Cov_date;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Cov_dateController extends Controller
{
    public function index()
    {
        try {
            if (!Auth::user()->hasPermission('access-coverage-date')) {
                abort(403, 'Unauthorized action.');
            }
            
            $coverage_dates = Cov_date::orderBy('coverage_date_from', 'desc')->get();
            $user = Auth::user();
            $userRole = Role::where('name', $user->role)->first();
            
            return view('biu_genset.coverage_date', [
                'coverage_dates' => $coverage_dates,
                'userRole' => $userRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading coverage dates: ' . $e->getMessage());
            return view('biu_genset.coverage_date', [
                'coverage_dates' => collect([]),
                'userRole' => null
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->hasPermission('add-coverage-date')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
            }
            
            $validated = $request->validate([
                'coverage_date_from' => 'required|date',
                'coverage_date_to' => 'required|date|after:coverage_date_from',
                'reading_date' => 'required|date',
                'due_date' => 'required|date|after:reading_date',
                'status' => 'required|in:Open,Close'
            ]);

            if ($this->hasDuplicateDates($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All dates (Coverage From, Coverage To, Reading Date, Due Date) must be different'
                ], 422);
            }

            if (strtotime($validated['coverage_date_from']) > strtotime($validated['reading_date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading Date must be after Coverage Date From'
                ], 422);
            }

            if ($validated['status'] === 'Open') {
                Cov_date::where('status', 'Open')->update(['status' => 'Close']);
            }

            $coverage_date = Cov_date::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coverage date created successfully',
                'data' => $coverage_date
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $message = reset($errors)[0] ?? 'Validation failed';
            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the coverage date. Please try again.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $coverage_date = Cov_date::findOrFail($id);
            return response()->json($coverage_date);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coverage date not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->hasPermission('edit-coverage-date')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
            }

            $validated = $request->validate([
                'coverage_date_from' => 'required|date',
                'coverage_date_to' => 'required|date|after:coverage_date_from',
                'reading_date' => 'required|date',
                'due_date' => 'required|date|after:reading_date',
                'status' => 'required|in:Open,Close'
            ]);

            $coverage_date = Cov_date::findOrFail($id);
            
            if ($validated['status'] === 'Open' && $coverage_date->status === 'Close') {
                $currentActive = Cov_date::where('status', 'Open')->first();
                if ($currentActive) {
                    $currentActive->update(['status' => 'Close']);
                }
            }

            $coverage_date->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coverage date updated successfully',
                'data' => $coverage_date
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating coverage date',
                'type' => 'warning'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->hasPermission('delete-coverage-date')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
            }

            $coverage_date = Cov_date::findOrFail($id);
            
            if ($coverage_date->status === 'Open') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the active coverage date. Please set another coverage date as active first.',
                    'type' => 'warning'
                ], 422);
            }

            $coverage_date->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coverage date deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting coverage date',
                'type' => 'warning'
            ], 500);
        }
    }

    private function hasDuplicateDates($data)
    {
        $dates = [
            $data['coverage_date_from'],
            $data['coverage_date_to'],
            $data['reading_date'],
            $data['due_date']
        ];
        return count(array_unique($dates)) !== count($dates);
    }

    private function hasOpenStatus($excludeId = null)
    {
        $query = Cov_date::where('status', 'Open');
        if ($excludeId) {
            $query->where('covdate_id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
