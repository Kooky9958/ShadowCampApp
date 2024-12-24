<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Country;

class RegionController extends Controller
{
    protected $countryModel;
    protected $regionModel;

    public function __construct(Country $countryModel, Region $regionModel)
    {
        $this->countryModel = $countryModel;
        $this->regionModel = $regionModel;
    }

    public function getRegionsByCountryId(Request $request)
    {
        $countryIdOrName = $request->input('id'); // This could be an ID or name
        $country = $this->countryModel->where('id', $countryIdOrName)
                                      ->orWhere('name', $countryIdOrName)
                                      ->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $regions = $this->regionModel->where('country_id', $country->id)->get();
        // dd($regions);
        return response()->json($regions);
    }
}