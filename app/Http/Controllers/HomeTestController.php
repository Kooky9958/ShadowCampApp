<?php

namespace App\Http\Controllers;

use App\Helpers\TerraApi;
use Illuminate\Http\Request;

class HomeTestController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $terraApi = (new TerraApi);

        $data = [
            'user_id' => 'faecf830-7a26-4739-b937-d384e81c134e',
            'to_webhook' => false,
            'start_date' => date('Y-m-d'),
            'with_samples' => true,
        ];

        $terraResponse = $terraApi->get('nutrition', $data);

        return response()->json($terraResponse);
    }
}
