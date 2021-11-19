<?php

namespace App\Services;
use Exception;
use App\Company;
use App\Services\LogService;
use App\Services\CheckRouteService;

class CompanyService
{
    public function __construct()
    {
        $this->checkRouteService = new CheckRouteService;
        $this->client_role_id = 2;
        $this->branch_role_id = 3;
        $this->sub_role_id = 4;
    }

    public function checkCompanyList($id)
    {
        $company = Company::find($id);
        $list_company = $company->getAllChildrenList()->pluck('id');
        $list_company[] = $company->id;
        return $list_company;
    }

    public function checkPermission($target_id, $company_user_id){
        if($target_id === $company_user_id) return true;
        $company = Company::with('parent')->select('id', 'parent_id')->find($target_id);
        if(isset($company->parent->id)){
            if($company->parent->id === $company_user_id) return true;
            return $this->checkPermission($company->parent->id, $company_user_id);
        } else return false;
    }

    public function getTopCompany($companies, $company_id){
        $search = array_search($company_id, array_column($companies, 'id'));
        $company = $companies[$search];
        if($company['parent_id'] === 1 || $company['parent_id'] === null){
            return $company['name'];
        }
        return $this->getTopCompany($companies, $company['parent_id']);
    }

    public function getCompanyTreeChildren($companies, $sub_company){
        $company_children = [];
        foreach($companies as $company){
            if($company['parent_id'] === $sub_company['id']) $company_children[] = $company;
        }
        $members = [];
        if(count($company_children)){
            foreach($company_children as $company_child){
                $members[] = $this->getCompanyTreeChildren($companies, $company_child);
            }
        } 
        if(count($members)) $sub_company['members'] = $members;
        if($sub_company['parent_id'] !== 1 && $sub_company['parent_id'] !== null){
            $parent_company_name = $this->getTopCompany($companies, $sub_company["parent_id"]);
            $sub_company['name'] = $parent_company_name.' / '.$sub_company['name'];
        } 
        
        unset($sub_company["parent_id"]); 
        return $sub_company;
    }  

    private function childrenAttributeConvert($company, $children, $count = false, $children_count = false)
    {
        if($count){
            $company->children_count = $company->$children_count;
            unset($company[$children_count]);
        }
        if(count($company->$children)){
            foreach($company->$children as $child){
                $child = $this->childrenAttributeConvert($child, $children, $count, $children_count);
            }
            $company->children = $company->$children;
        } 
        unset($company[$children]);
        
        return $company;
    }

    public function getCompanyTreeSelect($id, $type, $withCount = false){
        $type_children = $type.'ren';
        if($withCount){
            $company = Company::select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->withCount($type)->with($type_children)->find($id);
            
            // Change Camel Case 
            $count_attribute = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $type));
            $count_attribute = $count_attribute.'_count';
            $company = $this->childrenAttributeConvert($company, $type_children, true, $count_attribute);
        } else {
            $company = Company::select('id', 'name as title', 'id as key', 'id as value', 'parent_id')->with($type_children)->find($id);
            $company = $this->childrenAttributeConvert($company, $type_children);
        }
        
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
    }

    public function getLocations($id = null, $bypass = false){
        if($bypass) return $this->getCompanyTreeSelect($id, 'noSubChild');
        if($id === null) $id = auth()->user()->company_id;
        if($this->checkPermission($id, auth()->user()->company_id)) return $this->getCompanyTreeSelect($id, 'noSubChild');
        return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
    }

    public function getBranchCompanyList($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        return $this->getCompanyTreeSelect(auth()->user()->company_id, 'branchChild', true);
    }

    public function getClientCompanyList($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $companies = Company::select('id','name','address','phone_number','image_logo','role','parent_id', 'is_enabled')->where('parent_id', 1)->where('role', 2)->get();
        $companies->makeHidden('parent_id');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }

    // public function getCompanyClientList($route_name){
    //     $access = $this->checkRouteService->checkRoute($route_name);
    //     if($access["success"] === false) return $access;
        
    //     $companies = Company::select('id','name','address','phone_number','image_logo','role','parent_id', 'is_enabled')->where('parent_id', 1)->where('role', 2)->get();
    //     $companies->makeHidden('parent_id');
    //     return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    // }

    public function getCompanyDetail($id, $role_id){
        try{
            $company = Company::with(['noSubChild.noSubChild.noSubChild'])->find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
            $company->induk_level_1_count = $company->noSubChild->count();
            $company->induk_level_2_count = 0;
            $company->induk_level_3_count = 0;
            if($company->induk_level_1_count){
                foreach($company->noSubChild as $child){
                    $temp_count = $child->noSubChild->count();
                    $company->induk_level_2_count += $child->noSubChild->count();
                    if($temp_count){
                        foreach($child->noSubChild as $child_child){
                            $company->induk_level_3_count += $child_child->noSubChild->count();
                        }
                    }
                }
            }
            
            // if($company->relation){
            //     foreach($company->relation as $relationship){
            //         $is_inverse_inventory_relationship = $relationship->relationshipAsset->is_inverse === $relationship->is_inverse ? true : false;
            //         $relationship->name = $is_inverse_inventory_relationship ? $relationship->relationshipAsset->relationship->inverse_relationship_type : $relationship->relationshipAsset->relationship->relationship_type;
            //         unset($relationship['relationshipAsset']);
            //     }
            // }
            $company->makeHidden('deleted_at','parent_id', 'noSubChild');
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getMainCompanyDetail($route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $company_id = auth()->user()->company_id;
        $company_role = auth()->user()->company->role;
        return $this->getCompanyDetail($company_id, $company_role);
    }

    public function getCompanyBranchDetail($id, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getCompanyDetail($id, $this->branch_role_id);
    }

    public function getCompanyClientDetail($id, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->getCompanyDetail($id, $this->client_role_id);
    }

    public function addCompany($request, $role_id){
        $parent_id = $request->get('parent_id', auth()->user()->company->parent_id);
        if($parent_id === null) return ["success" => false, "message" => "Parent Id Tidak Boleh Null", "status" => 400];
        try{
            $parent_company = Company::find($parent_id);
            if($parent_company === null) return ["success" => false, "message" => "Parent Company Tidak Ditemukan", "status" => 400];
            
            $company = new Company;
            if($role_id === 4){
                if($parent_company->role !== 4) $top_parent_id = $parent_id;
                else $top_parent_id = $parent_company->top_parent_id;
                $address_same = $request->get('address_same', false);
                if($address_same) $company->address = $parent_company->address;
                else $company->address = $request->get('address',null);
            } else{
                $top_parent_id = $parent_company->getTopParent()->id ?? null;  
                $company->address = $request->get('address',null);
            } 

            $company->name = $request->get('name',null);
            $company->parent_id = $request->get('parent_id',null);
            $company->top_parent_id = $top_parent_id;
            $company->phone_number = $request->get('phone_number',null);
            $company->image_logo = $request->get('image_logo',null);
            $company->role = $role_id;
            $company->created_time = date("Y-m-d H:i:s");
            $company->is_enabled = false;
            
            $company->singkatan = '-';
            $company->tanggal_pkp = $request->get('tanggal_pkp', null);
            $company->penanggung_jawab = $request->get('penanggung_jawab', '-');
            $company->npwp = $request->get('npwp', '-');
            $company->fax = $request->get('fax', '-');
            $company->email = $request->get('email', '-');
            $company->website = $request->get('website', '-');
            $company->save();

            $logService = new LogService;
            if($role_id !== 4) $logService->createCompany($company->parent_id, $company->id);
            else $logService->createCompany($company->top_parent_id, $company->id, true);

            if($role_id === 2) return ["success" => true, "message" => "Client Company Berhasil Dibentuk", "id" => $company->id, "status" => 200];
            else if($role_id === 4) return ["success" => true, "message" => "Sub Company Berhasil Dibentuk", "id" => $company->id, "status" => 200];
            else return ["success" => true, "message" => "Branch Company Berhasil Dibentuk", "id" => $company->id, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCompanyBranch($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addCompany($request, $this->branch_role_id);
    }

    public function addCompanyClient($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addCompany($request, $this->client_role_id);
    }

    public function addCompanySub($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->addCompany($request, $this->sub_role_id);
    }

    public function updateCompany($request, $role_id){
        $company = Company::find($request->get('id'));
        if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
        try{
            $company->name = $request->get('name');
            $company->address = $request->get('address');
            $company->phone_number = $request->get('phone_number');
            $company->image_logo = $request->get('image_logo');
            
            $company->singkatan = $request->get('singkatan', '-');
            $company->tanggal_pkp = $request->get('tanggal_pkp', null);
            $company->penanggung_jawab = $request->get('penanggung_jawab', '-');
            $company->npwp = $request->get('npwp', '-');
            $company->fax = $request->get('fax', '-');
            $company->email = $request->get('email', '-');
            $company->website = $request->get('website', '-');
            $company->save();

            $logService = new LogService;
            if($role_id !== 4) $logService->updateCompany($company->id, $company->id);
            else $logService->updateCompany($company->top_parent_id, $company->id, true);

            return ["success" => true, "message" => "Detail Company Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateMainCompany($request){
        $company_role = auth()->user()->company->role;
        return $this->updateCompany($request, $company_role);
    }

    public function updateCompanyBranch($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateCompany($request, $this->branch_role_id);
    }

    public function updateCompanyClient($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateCompany($request, $this->client_role_id);
    }

    public function updateCompanySub($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->updateCompany($request, $this->sub_role_id);
    }

    public function companyActivation($data, $role_id){
        $company = Company::find($data['id']);
        if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
        try{
            $company->is_enabled = $data['is_enabled'];
            $company->save();
            if($role_id === 2) return ["success" => true, "message" => "Status Aktivasi Client Telah Diperbarui", "status" => 200];
            else if($role_id === 4) return ["success" => true, "message" => "Status Aktivasi Sub Company Telah Diperbarui", "id" => $company->id, "status" => 200];
            else return ["success" => true, "message" => "Status Aktivasi Branch Telah Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function companyBranchActivation($data, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->companyActivation($data, $this->branch_role_id);
    }

    public function companyClientActivation($data, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->companyActivation($data, $this->client_role_id);
    }

    private function deleteChildLocations($companies){
        foreach($companies as $company){
            if(count($company->child)){
                $this->deleteChildLocations($company->child);
            }
            $company->delete();
        }
    }

    public function deleteCompany($request, $role_id){
        try{
            $id = $request->get('id', null);
            $company = Company::find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
            $company->delete();
            $new_parent = $request->get('new_parent', null);
            if($new_parent !== null){
                $companies = Company::with('child')->where('parent_id', $company->id)->where('role', 4)->get();
                $this->deleteChildLocations($companies);
                $companies = Company::where('parent_id', $company->id)->where('role', '<>', 4)->get();
                if(count($companies)) {
                    foreach($companies as $temp_company)
                    {
                        $temp_company->parent_id = $new_parent;
                        $temp_company->save();
                    }
                }
            } else {
                $companies = Company::with('child')->where('parent_id', $company->id)->get();
                if(count($companies)) $this->deleteChildLocations($companies);
            }

            $logService = new LogService;
            if($role_id !== 4) $logService->deleteCompany($company->parent_id, $company->id);
            else $logService->deleteCompany($company->parent_id, $company->id, true);
            return ["success" => true, "message" => "Company Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteCompanyBranch($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteCompany($request, $this->branch_role_id);
    }

    public function deleteCompanyClient($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteCompany($request, $this->client_role_id);
    }

    public function deleteCompanySub($request, $route_name){
        $access = $this->checkRouteService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        return $this->deleteCompany($request, $this->sub_role_id);
    }
}