<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TerraActivity;
use App\Models\TerraBody;
use App\Models\TerraDaily;
use App\Models\TerraMenstruation;
use App\Models\TerraNutrition;
use App\Models\TerraSleep;
use App\Models\TerraUserProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TerraAPIWebHookController extends Controller
{

    function __invoke(Request $request)
    {
        if ($this->checkTerraSignature($request) == false) {
            activity('TERRA_WEB_HOOK')
                ->event($request->input('type'))
                ->withProperties($request->all('user', 'data'))
                ->log('Terra Signature was not valid');

            return response()->json(['success' => false,], 403);
        }

        $type = $request->type;
        switch ($type) {
            case 'auth':
                $this->handleAuthWebHookEvent($request);
                break;

            case 'deauth':
                $this->handleDeauthWebHookEvent($request);
                break;

            case 'reauth':
                $this->handleReauthWebHookEvent($request);
                break;

            case 'activity':
                $this->handleActivityWebHookEvent($request);
                break;

            case 'body':
                $this->handleBodyWebHookEvent($request);
                break;

            case 'sleep':
                $this->handleSleepWebHookEvent($request);
                break;

            case 'daily':
                $this->handleDailyWebHookEvent($request);
                break;

            case 'menstruation':
                $this->handleMenstruationWebHookEvent($request);
                break;

            case 'nutrition':
                $this->handleNutritionWebHookEvent($request);
                break;

            default:

                break;
        }

        $terraUserProvider = $this->getTerraUserProvider($request);

        activity('TERRA_WEB_HOOK')
            ->event($request->input('type'))
            ->performedOn($terraUserProvider ?? new TerraUserProvider())
            ->withProperties($request->getContent())
            ->log($request->input('message', 'Terra Web Hook Received'));

        return response()->json(['success' => true]);
    }

    private function getTerraUserProvider(Request $request)
    {
        $userPayload = $request->input('user');
        if (is_null($userPayload)) {
            return null;
        }
        return TerraUserProvider::where('user_id', $userPayload['reference_id'])->where('provider', $userPayload['provider'])->first();
    }

    private function handleAuthWebHookEvent(Request $request)
    {
        $isSuccess = $request->status == 'success';

        if (!$isSuccess) {
            Log::error(
                "TERRA_WEBHOOK: Auth unsucessfull"
            );
            return;
        }

        $userPayload = $request->input('user');
        $user = User::where('id', $userPayload['reference_id'])->first();

        if (!$user) {
            Log::error(
                "TERRA_WEBHOOK: Unable to get user data"
            );
            return;
        }

        TerraUserProvider::updateOrCreate(
            [
                'user_id'       => $user->id,
                'provider'      => $userPayload['provider'],
            ],
            [
                'terra_user_id'       => $userPayload['user_id'],
                'active'              => $userPayload['active'],
                'scopes'              => $userPayload['scopes'],
                'last_webhook_update' => $userPayload['last_webhook_update'],
                'widget_session_id'   => $request->widget_session_id,
            ]
        );
    }

    private function handleDeauthWebHookEvent(Request $request)
    {
        $isSuccess = $request->status == 'success';

        if (!$isSuccess) {
            Log::error(
                "TERRA_WEBHOOK: Deauth unsuccessfull"
            );
            return;
        }

        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if (is_null($terraUserProvider)) {
            Log::error("TERRA_WEBHOOK: Unable to get terra user provider data");
            return;
        }

        $terraUserProvider->terra_user_id     = null;
        $terraUserProvider->scopes            = null;
        $terraUserProvider->active            = false;
        $terraUserProvider->widget_session_id = null;
        $terraUserProvider->save();
    }

    function handleReauthWebHookEvent(Request $request)
    {
        //TODO
    }

    private function handleActivityWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            Log::info('Terra User Provider ID is null');
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            Log::info($payloadData['work_data']['work_kilojoules']);

            TerraActivity::updateOrCreate([
                'terra_user_provider_id' => $terraUserProvider->id,
                'start_time'             => $metadata['start_time'],
                'end_time'               => $metadata['end_time'],
                'activity_type'          => $metadata['type'],
            ], [
                'raw_data'          => $payloadData,
                'metadata'          => $payloadData['metadata'],
                'work_kilojoules'   => $payloadData['work_data']['work_kilojoules'],
                'stress_score'      => $payloadData['data_enrichment']['stress_score'],
                'met_data'          => $payloadData['MET_data'],
                'laps_data'         => $payloadData['lap_data'],
                'calories_data'     => $payloadData['calories_data'],
                'activity_duration' => $payloadData['active_durations_data'],
                'oxygen_data'       => $payloadData['oxygen_data'],
                'energy_data'       => $payloadData['energy_data'],
                'tss_samples_data'  => $payloadData['TSS_data'],
                'device_data'       => $payloadData['device_data'],
                'distance_data'     => $payloadData['distance_data'],
                'polyline_map_data' => $payloadData['polyline_map_data'],
                'heart_rate_data'   => $payloadData['heart_rate_data'],
                'movement_data'     => $payloadData['movement_data'],
                'strain_data'       => $payloadData['strain_data'],
                'power_data'        => $payloadData['power_data'],
                'cheat_detection'   => $payloadData['cheat_detection'],
                'position_data'     => $payloadData['position_data'],
            ]);
        }
    }

    private function handleBodyWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            TerraBody::updateOrCreate(
                [
                    'terra_user_provider_id' => $terraUserProvider->id,
                    'start_time'             => $metadata['start_time'],
                    'end_time'               => $metadata['end_time'],
                ],
                [
                    'raw_data'            => $payloadData,
                    'metadata'            => $metadata,
                    'blood_pressure_data' => $payloadData['blood_pressure_data'],
                    'device_data'         => $payloadData['device_data'],
                    'glucose_data'        => $payloadData['glucose_data'],
                    'heart_data'          => $payloadData['heart_data'],
                    'hydration_data'      => $payloadData['hydration_data'],
                    'ketone_data'         => $payloadData['ketone_data'],
                    'measurements_data'   => $payloadData['measurements_data'],
                    'oxygen_data'         => $payloadData['oxygen_data'],
                    'temperature_data'    => $payloadData['temperature_data'],
                ]
            );
        }
    }

    private function handleSleepWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            TerraSleep::updateOrCreate(
                [
                    'terra_user_provider_id' => $terraUserProvider->id,
                    'start_time'             => $metadata['start_time'],
                    'end_time'               => $metadata['end_time'],
                ],
                [
                    'raw_data'             => $payloadData,
                    'metadata'             => $metadata,
                    'device_data'          => $payloadData['device_data'],
                    'heart_rate_data'      => $payloadData['heart_rate_data'],
                    'readiness_data'       => $payloadData['readiness_data'],
                    'respiration_data'     => $payloadData['respiration_data'],
                    'sleep_durations_data' => $payloadData['sleep_durations_data'],
                    'temperature_data'     => $payloadData['temperature_data'],
                ]
            );
        }
    }

    private function handleDailyWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            TerraDaily::updateOrCreate(
                [
                    'terra_user_provider_id' => $terraUserProvider->id,
                    'start_time'             => $metadata['start_time'],
                    'end_time'               => $metadata['end_time'],
                ],
                [
                    'raw_data'              => $payloadData,
                    'metadata'              => $metadata,
                    'calories_data'         => $payloadData['calories_data'],
                    'device_data'           => $payloadData['device_data'],
                    'met_data'              => $payloadData['MET_data'],
                    'oxygen_data'           => $payloadData['oxygen_data'],
                    'heart_rate_data'       => $payloadData['heart_rate_data'],
                    'distance_data'         => $payloadData['distance_data'],
                    'stress_data'           => $payloadData['stress_data'],
                    'tag_data'              => $payloadData['tag_data'],
                    'scores'                => $payloadData['scores'],
                    'strain_data'           => $payloadData['strain_data'],
                    'active_durations_data' => $payloadData['active_durations_data'],
                ]
            );
        }
    }

    private function handleMenstruationWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            TerraMenstruation::updateOrCreate([
                'terra_user_provider_id' => $terraUserProvider->id,
                'start_time'             => $metadata['start_time'],
                'end_time'               => $metadata['end_time'],
            ], [
                'raw_data'          => $payloadData,
                'metadata'          => $metadata,
                'menstruation_data' => $payloadData['menstruation_data'],
            ]);
        }
    }

    private function handleNutritionWebHookEvent(Request $request)
    {
        $userPayload = $request->input('user');
        $terraUserProvider = TerraUserProvider::query()
            ->where('user_id', $userPayload['reference_id'])
            ->where('provider', $userPayload['provider'])
            ->first();

        if ($terraUserProvider == null) {
            return;
        }

        $terraPayloadData = $request->input('data', []);

        foreach ($terraPayloadData as $key => $payloadData) {
            $metadata = $payloadData['metadata'];

            TerraNutrition::updateOrCreate([
                'terra_user_provider_id' => $terraUserProvider->id,
                'start_time'             => $metadata['start_time'],
                'end_time'               => $metadata['end_time'],
            ], [
                'raw_data'      => $payloadData,
                'meals'         => $payloadData['meals'],
                'summary'       => $payloadData['summary'],
                'drink_samples' => $payloadData['drink_samples'],
            ]);
        }
    }

    private function checkTerraSignature(Request $request): bool
    {
        $signingKey = config('terra.signing_key');

        if (is_null($signingKey)) {
            Log::error(
                "TERRA_WEBHOOK: SIGNING KEY WAS NOT PROVIDED YET."
            );
            return false;
        }

        $terraSignature = $request->header('terra-signature');
        if (is_null($terraSignature)) {
            Log::error(
                "TERRA_WEBHOOK: Terra-Signature Header was not found."
            );
            return false;
        }

        $s = explode(",", $terraSignature);
        $t = explode("=", $s[0])[1];
        $v1 = explode("=", $s[1])[1];

        $payload = $request->getContent();

        $generatedSignature = hash_hmac('sha256', $t . '.' . $payload, $signingKey);

        $isValid = $v1 == $generatedSignature;

        Log::info('TERRA_WEBHOOK: CHECK SIGNATURE: ' . $isValid);

        return $isValid;
    }
}
