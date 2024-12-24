<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserTargetCalorie;
use Illuminate\Http\Request;

class UserTargetCalorieController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'required_with:end_date', 'date'],
            'end_date'   => ['nullable', 'date', 'gte:start_date'],
        ]);

        $data = UserTargetCalorie::query()
            ->where('user_id', $request->user()->id)
            ->when($request->start_date && $request->end_date, fn($query) => $query->whereBetween('date', [$request->start_date, $request->end_date]))
            ->when($request->start_date && !$request->end_date, fn($query) => $query->where('date', $request->start_date))
            ->latest()
            ->get();

        return response()->json(compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'           => ['required', 'date'],
            'target_calorie' => ['required', 'numeric']
        ]);

        $data = UserTargetCalorie::updateOrCreate([
            'user_id' => $request->user()->id,
            'date'    => $request->date,
        ], [
            'target_calorie' => $request->target_calorie,
        ]);

        $message = 'Data created successfully';

        return response()->json(compact('data', 'message'), 201);
    }

    public function show(UserTargetCalorie $userTargetCalorie)
    {
        abort_if($userTargetCalorie->user_id != auth()->id(), 'You don\'t have permission to access this resource');

        return response()->json(['data' => $userTargetCalorie]);
    }

    public function update(Request $request, UserTargetCalorie $userTargetCalorie)
    {
        abort_if($userTargetCalorie->user_id != auth()->id(), 'You don\'t have permission to access this resource');

        $request->validate([
            'target_calorie' => ['required', 'numeric']
        ]);

        $userTargetCalorie->update(['target_calorie' => $request->target_calorie]);

        return response()->json(['data' => $userTargetCalorie, 'message' => 'Data updated successfully']);
    }

    public function destroy(UserTargetCalorie $userTargetCalorie)
    {
        abort_if($userTargetCalorie->user_id != auth()->id(), 'You don\'t have permission to access this resource');

        return response()->json(['status' => 'success', 'message' => 'Data deleted successfully']);
    }
}
