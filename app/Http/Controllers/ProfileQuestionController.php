<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfileQuestion;
use Illuminate\Support\Facades\Response;

class ProfileQuestionController extends Controller
{

    public function update(Request $request)
    {
        $request->validate([
            'goals' => 'array',
            'mental_health_issues' => 'array',
        ]);

        $profile = auth()->user()->profile ?? new \App\Models\ProfileQuestion();
        
        $profile->user_id = auth()->id();

        $profile->goals = json_encode($request->input('goals'));
        $profile->mental_health_issues = json_encode($request->input('mental_health_issues'));
        $profile->hair_loss = $request->input('hair_loss');
        $profile->birth_control = $request->input('birth_control');
        $profile->reproductive_disorder = $request->input('reproductive_disorder');
        $profile->weight_change = $request->input('weight_change');
        $profile->coffee_consumption = $request->input('coffee_consumption');
        $profile->alcohol_consumption = $request->input('alcohol_consumption');
        
        if ($request->has('other_goal')) {
            $profile->other_goal = $request->input('other_goal');
        }

        $profile->save();

        return redirect()->back()->with('status', 'Profile updated!');
    }
}