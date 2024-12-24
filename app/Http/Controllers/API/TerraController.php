<?php

namespace App\Http\Controllers\API;

use App\Helpers\TerraApi;
use App\Http\Controllers\Controller;
use App\Models\TerraActivity;
use App\Models\TerraMenstruation;
use App\Models\TerraNutrition;
use App\Models\TerraUserProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TerraController extends Controller
{
    private $supportedProviders = [];

    function __construct(private TerraApi $terraApi)
    {
        $this->supportedProviders = config('terra.available_providers');
    }

    function activeConnections(Request $request)
    {
        $user = $request->user();

        $activeConnections = TerraUserProvider::query()
            ->where('user_id', $user->id)
            ->where('active', true)
            ->get();

        return response()->json(['data' => $activeConnections]);
    }

    function connectWidget(Request $request)
    {
        $request->validate([
            'redirect_url' => ['required', 'url'],
        ]);

        $user = $request->user();

        $data = [
            'reference_id'              => $user->id,
            'auth_success_redirect_url' => $request->redirect_url,
            'show_disconnect'           => true,
        ];

        $terraResponse = $this->terraApi->post('auth/generateWidgetSession', $data);

        return response()->json($terraResponse, $terraResponse['success'] ? 200 : 500);
    }

    function connect(Request $request)
    {
        $request->validate([
            'provider'     => ['required', Rule::in($this->supportedProviders)],
            'redirect_url' => ['required', 'url'],
        ]);

        $user = $request->user();

        $data = [
            'reference_id'              => $user->id,
            'auth_success_redirect_url' => $request->redirect_url,
        ];

        $terraResponse = $this->terraApi->post('auth/authenticateUser?resource=' . $request->provider, $data);

        return response()->json($terraResponse, $terraResponse['success'] ? 200 : 422);
    }

    function disconnect(Request $request)
    {
        $request->validate([
            'provider'     => ['required', Rule::in($this->supportedProviders)],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->firstOrFail();

        if ($terraProvider->active == false) {
            //already disconnected or inactive
            return response()->json(['message' => 'Provider Connection Already Inactive'], 422);
        }

        $terraApiResponse = $this->terraApi->delete('auth/deauthenticateUser', ['user_id' => $terraProvider->terra_user_id]);

        if ($terraApiResponse['success'] == false) {
            return response()->json($terraApiResponse);
        }

        return response()->json($terraApiResponse);
    }

    function activity(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->firstOrFail();

        $data = TerraActivity::where('terra_user_provider_id', $terraProvider->id)
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->get();

        return response()->json(compact('data'));
    }

    function bodyData(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->firstOrFail();

        $data = $terraProvider->bodyRecords()
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->latest()
            ->first();

        return response()->json(compact('data'));
    }

    function dailyData(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->firstOrFail();

        $data = $terraProvider->dailyRecords()
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->latest()
            ->first();

        return response()->json(compact('data'));
    }

    function nutrition(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->where('active', true)->firstOrFail();

        $data = TerraNutrition::where('terra_user_provider_id', $terraProvider->id)
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->latest()
            ->first();

        return response()->json(compact('data'));
    }

    function nutritions(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->where('active', true)->firstOrFail();

        $data = TerraNutrition::where('terra_user_provider_id', $terraProvider->id)
            ->whereBetween(DB::raw('DATE(start_time)'), [$request->start_date, $request->end_date])
            ->get();

        return response()->json(compact('data'));
    }

    function menstruation(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->where('active', true)->firstOrFail();

        $data = TerraMenstruation::where('terra_user_provider_id', $terraProvider->id)
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->latest()
            ->first();

        return response()->json(compact('data'));
    }

    function sleepData(Request $request)
    {
        $request->validate([
            'provider'   => ['required', Rule::in($this->supportedProviders)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $terraProvider = $user->terraProviders()->where('provider', $request->provider)->firstOrFail();

        $data = $terraProvider->sleeps()
            ->where(DB::raw('DATE(start_time)'), $request->start_date)
            ->when($request->end_date != null, fn($query) => $query->where(DB::raw('DATE(end_time)'), $request->end_date))
            ->latest()
            ->first();

        return response()->json(compact('data'));
    }
}
