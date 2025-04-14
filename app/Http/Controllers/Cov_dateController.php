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

            $active_coverage = Cov_date::where('status', 'Open')->first();
         
            $closed_coverage_dates = Cov_date::where('status', 'Close')
                ->orderBy('coverage_date_from', 'desc')
                ->paginate(10);
            
            $user = Auth::user();
            $userRole = Role::where('name', $user->role)->first();
            
            return view('biu_genset.coverage_date', [
                'active_coverage' => $active_coverage,
                'coverage_dates' => $closed_coverage_dates,
                'userRole' => $userRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading coverage dates: ' . $e->getMessage());
            return view('biu_genset.coverage_date', [
                'active_coverage' => null,
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
                'due_date' => 'required|date',
                'status' => 'required|in:Open,Close'
            ]);

            $dateValidationResult = $this->validateDates($validated);
            if (!$dateValidationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $dateValidationResult['message']
                ], 422);
            }

            if ($this->hasOverlappingDates($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The coverage dates overlap with an existing coverage period'
                ], 422);
            }

            if ($validated['status'] === 'Open') {
                $activeCount = Cov_date::where('status', 'Open')->count();
                if ($activeCount > 0) {
                    Cov_date::where('status', 'Open')->update(['status' => 'Close']);
                }
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
                'due_date' => 'required|date',
                'status' => 'required|in:Open,Close'
            ]);

            $coverage_date = Cov_date::findOrFail($id);

            $dateValidationResult = $this->validateDates($validated, $id);
            if (!$dateValidationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $dateValidationResult['message']
                ], 422);
            }

            if ($this->hasOverlappingDates($validated, $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The coverage dates overlap with an existing coverage period'
                ], 422);
            }

            if ($validated['status'] === 'Open' && $coverage_date->status === 'Close') {
                Cov_date::where('status', 'Open')->update(['status' => 'Close']);
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

    public function validateOverlap(Request $request)
    {
        $data = $request->validate([
            'coverage_date_from' => 'required|date',
            'coverage_date_to' => 'required|date',
            'reading_date' => 'required|date',
            'due_date' => 'required|date'
        ]);

        $overlapping = $this->hasOverlappingDates($data);

        return response()->json([
            'overlapping' => $overlapping
        ]);
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

    private function validateDates(array $data, $excludeId = null): array
    {
        $coverageFrom = strtotime($data['coverage_date_from']);
        $coverageTo = strtotime($data['coverage_date_to']);
        $dueDate = strtotime($data['due_date']);

        if ($coverageTo <= $coverageFrom) {
            return [
                'valid' => false,
                'message' => 'Coverage Date To must be after Coverage Date From'
            ];
        }

        if ($dueDate <= $coverageTo) {
            return [
                'valid' => false,
                'message' => 'Due Date must be after Coverage Date To'
            ];
        }

        if ($dueDate <= $coverageFrom) {
            return [
                'valid' => false,
                'message' => 'Due Date must be after Coverage Date From'
            ];
        }

        return ['valid' => true];
    }

    private function hasOverlappingDates(array $data, $excludeId = null): bool
    {
        $start = $data['coverage_date_from'];
        $end = $data['coverage_date_to'];

        $query = Cov_date::where(function ($q) use ($start, $end) {
            $q->where(function ($inner) use ($start, $end) {
                $inner->where('coverage_date_from', '<', $end)
                      ->where('coverage_date_to', '>', $start);
            });
        });

        if ($excludeId) {
            $query->where('covdate_id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
