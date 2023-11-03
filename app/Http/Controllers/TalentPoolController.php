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
        $route_name = "TALENT_POOL_ADD";

        $response = $this->talentPoolService->addTalentPool($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTalentPoolCandidates(Request $request){
        $route_name = "TALENT_POOL_GET";

        $response = $this->talentPoolService->getTalentPoolCandidates($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTalentPool(Request $request){
        $route_name = "TALENT_POOL_DELETE";

        $response = $this->talentPoolService->deleteTalentPool($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //* TALENT POOL CATEGORIES
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
        $route_name = "TALENT_POOL_CATEGORY_DELETE";

        $response = $this->talentPoolService->deleteTalentPoolCategory($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //* TALENT POOL FILTERS
    public function getTalentPoolFilters(Request $request){
        $route_name = "TALENT_POOLS_GET";

        $response = $this->talentPoolService->getTalentPoolFilters($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //* TALENT POOL SHARE
    public function getTalentPoolShares(Request $request){
        $route_name = "TALENT_POOL_SHARES_GET";

        $response = $this->talentPoolService->getTalentPoolShares($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function addTalentPoolShare(Request $request){
        $route_name = "TALENT_POOL_SHARE_ADD";

        $response = $this->talentPoolService->addTalentPoolShare($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function deleteTalentPoolShare(Request $request){
        $route_name = "TALENT_POOL_SHARE_DELETE";

        $response = $this->talentPoolService->deleteTalentPoolShare($request, $route_name);
        return response()->json($response, $response['status']);
    }

    //* TALENT POOL SHARE PUBLIC
    public function getTalentPoolSharePublics(Request $request){
        $route_name = "NONE";

        $response = $this->talentPoolService->getTalentPoolSharePublics($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function getTalentPoolSharePublic(Request $request){
        $route_name = "NONE";

        $response = $this->talentPoolService->getTalentPoolSharePublic($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function markTalentPoolSharePublic(Request $request){
        $route_name = "NONE";

        $response = $this->talentPoolService->markTalentPoolSharePublic($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function eliminateTalentPoolSharePublic(Request $request){
        $route_name = "NONE";

        $response = $this->talentPoolService->eliminateTalentPoolSharePublic($request, $route_name);
        return response()->json($response, $response['status']);
    }

    public function cancelTalentPoolSharePublic(Request $request){
        $route_name = "NONE";

        $response = $this->talentPoolService->cancelTalentPoolSharePublic($request, $route_name);
        return response()->json($response, $response['status']);
    }

}
