<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $viewerType = $request->attributes->get('viewer_type');
        $user = auth()->user();

        if ($viewerType === 'admin') {

            $tab = $request->input('tab', 'pending');

            $query = AttendanceRequest::query()
                ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
                ->with(['user', 'attendance'])
                ->orderBy('attendances.work_date', 'asc')
                ->select('attendance_requests.*');

            if ($tab === 'approved') {
                $query->where('attendance_requests.status', AttendanceRequest::STATUS_APPROVED);
            } else {
                $query->where('attendance_requests.status', AttendanceRequest::STATUS_PENDING);
            }

            $requests = $query->get();

            return view('admin.request.list', compact('requests'));
        }

        $tab = $request->input('tab', 'pending');

        $query = AttendanceRequest::query()
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->with(['user', 'attendance'])
            ->where('attendance_requests.user_id', auth()->id())
            ->orderBy('attendances.work_date', 'asc')
            ->select('attendance_requests.*');

        if ($tab === 'approved') {
            $query->where('attendance_requests.status', AttendanceRequest::STATUS_APPROVED);
        } else {
            $query->where('attendance_requests.status', AttendanceRequest::STATUS_PENDING);
        }

        $requests = $query->get();

        return view('attendance.request.list', compact('requests'));
    }
}
