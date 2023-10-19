<?php

namespace App\Http\Controllers;

use App\Services\TalentPoolService;
use Illuminate\Http\Request;

class TalentPoolController extends Controller
{
    protected $talentPoolService;
    /**
     * Create a new controller instance.
     *
     * @return void
    */

    public function __construct()
    {
        $this->talentPoolService = new TalentPoolService;
    }

    public function getTalentPools(Request $request){
        $route_name = "TALENT_POOLS_GET";

        $response = $this->talentPoolService->getTalentPools($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTalentPool(Request $request){
        $route_name = "TALENT_POOL_GET";

        $response = $this->talentPoolService->getTalentPool($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTalentPool(Request $request){
        $route_name = "TALENT_POOLS_ADD";

        $response = $this->talentPoolService->addTalentPool($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTalentPoolCandidates(Request $request){
        $route_name = "TALENT_POOLS_ADD";

        $response = $this->talentPoolService->getTalentPoolCandidates($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // TALENT POOL CATEGORIES
    public function getTalentPoolCategories(Request $request){
        $route_name = "TALENT_POOL_CATEGORIES_GET";

        $response = $this->talentPoolService->getTalentPoolCategories($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTalentPoolCategory(Request $request){
        $route_name = "TALENT_POOL_CATEGORY_ADD";

        $response = $this->talentPoolService->addTalentPoolCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTalentPoolCategory(Request $request){
        $route_name = "TALENT_POOLS_DELETE";

        $response = $this->talentPoolService->deleteTalentPoolCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    // TALENT POOL FILTERS
    public function getTalentPoolFilters(Request $request){
        $route_name = "TALENT_POOLS_GET";

        $response = $this->talentPoolService->getTalentPoolFilters($request, $route_name);
        return response()->json($response, $response['status']);
    }

}
