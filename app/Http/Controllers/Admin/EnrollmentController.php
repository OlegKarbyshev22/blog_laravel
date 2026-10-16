<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = Enrollment::query()
            ->with(['user', 'post'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.enrollments.index', compact('enrollments'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'cancelled'])],
        ]);

        DB::transaction(function () use ($data, $enrollment) {
            $enrollment->post()->lockForUpdate()->firstOrFail();

            if ($data['status'] === 'confirmed' && $enrollment->status !== 'confirmed') {
                $occupied = Enrollment::query()
                    ->where('post_id', $enrollment->post_id)
                    ->where('status', 'confirmed')
                    ->count();

                if ($occupied >= Enrollment::MAX_PER_POST) {
                    throw ValidationException::withMessages([
                        'status' => 'Нельзя подтвердить запись: на тему уже записаны 3 человека.',
                    ]);
                }
            }

            $enrollment->update($data);
        }, 3);

        return back()->with('success', 'Статус записи изменён.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('success', 'Запись удалена.');
    }
}
