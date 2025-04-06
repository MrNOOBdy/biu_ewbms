<?php

namespace App\Http\Controllers;

use App\Models\ManageNotice;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        try {
            $notifications = ManageNotice::orderBy('created_at', 'desc')
                ->paginate(20);

            $user = Auth::user();
            $userRole = Role::where('name', $user->role)->first();

            return view('biu_genset.manage_not', compact('notifications', 'userRole'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Unable to fetch notifications. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $exists = ManageNotice::where('type', $request->type)
                ->where('announcement', $request->announcement)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This notice already exists.',
                    'field' => 'type'
                ]);
            }

            $notice = ManageNotice::create([
                'type' => $request->type,
                'announcement' => $request->announcement
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notice added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add notice: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($noticeId)
    {
        try {
            $notice = ManageNotice::findOrFail($noticeId);
            return response()->json([
                'success' => true,
                'notice' => $notice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notice details'
            ]);
        }
    }

    public function update(Request $request, $noticeId)
    {
        try {
            $exists = ManageNotice::where('type', $request->type)
                ->where('announcement', $request->announcement)
                ->where('notice_id', '!=', $noticeId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This notice already exists.',
                    'field' => 'type'
                ]);
            }

            $notice = ManageNotice::findOrFail($noticeId);
            $notice->update([
                'type' => $request->type,
                'announcement' => $request->announcement
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notice updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notice: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($noticeId)
    {
        try {
            $notice = ManageNotice::findOrFail($noticeId);
            $notice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notice deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notice: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            
            $notifications = ManageNotice::where('type', 'LIKE', "%{$query}%")
                ->orWhere('announcement', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications->map(function($notice) {
                    return [
                        'notice_id' => $notice->notice_id,
                        'type' => $notice->type,
                        'announcement' => $notice->announcement,
                        'created_at' => $notice->created_at->format('M d, Y'),
                        'updated_at' => $notice->updated_at->format('M d, Y')
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search notifications: ' . $e->getMessage()
            ]);
        }
    }
}