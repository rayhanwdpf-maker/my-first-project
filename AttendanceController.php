<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('student')->latest('date');

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->class) {
            $query->where('class', $request->class);
        }

        $attendances = $query->paginate(15)->withQueryString();

        $summary = [
            'present' => Attendance::where('status', 'present')->whereDate('date', today())->count(),
            'absent'  => Attendance::where('status', 'absent')->whereDate('date', today())->count(),
            'late'    => Attendance::where('status', 'late')->whereDate('date', today())->count(),
            'leave'   => Attendance::where('status', 'leave')->whereDate('date', today())->count(),
        ];

        return Inertia::render('Attendance/Index', compact('attendances', 'summary'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('name')->get();
        return Inertia::render('Attendance/Create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date',
            'status'     => 'required|in:present,absent,late,leave',
            'class'      => 'nullable|string',
            'section'    => 'nullable|string',
            'note'       => 'nullable|string',
        ]);

        Attendance::create($request->all());

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance recorded successfully!');
    }

    public function edit(Attendance $attendance)
    {
        $students = Student::where('status', 'active')->orderBy('name')->get();
        return Inertia::render('Attendance/Edit', compact('attendance', 'students'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date',
            'status'     => 'required|in:present,absent,late,leave',
            'class'      => 'nullable|string',
            'section'    => 'nullable|string',
            'note'       => 'nullable|string',
        ]);

        $attendance->update($request->all());

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance updated successfully!');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')
            ->with('success', 'Attendance deleted successfully!');
    }
}