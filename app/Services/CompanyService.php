<?php

namespace App\Services;
use Exception;
use App\Company;
use App\Inventory;
use App\Services\LogService;
use App\Services\FileService;
use App\RelationshipInventory;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalService;
use Illuminate\Support\Facades\Validator;

class CompanyService
{
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->client_role_id = 2;
        $this->branch_role_id = 3;
        $this->sub_role_id = 4;
    }

    public function checkNoSubCompanyList($id)
    {
        $company = Company::find($id);
        $list_company = $company->getAllNoSubChildrenList()->pluck('id');
        $list_company[] = $company->id;
        return $list_company;
    }

    public function checkSubCompanyList($company)
    {
        $list_company = $company->getAllSubChildrenList()->pluck('id');
        $list_company[] = $company->id;
        return $list_company;
    }

    public function checkCompanyList($company)
    {
        $list_company = $company->getAllChildrenList()->pluck('id');
        $list_company[] = $company->id;
        return $list_company;
    }

    public function leveling($company)
    {
        if(count($company->subChildren)){
            foreach($company->subChildren as $child){
                $child = $this->leveling($child);
                $child->level = $child->level(0);
                $child->parent_name = $child->parent->name;
                $child->makeHidden('parent');
            }
        }
        return $company;
    }

    public function checkPermission($target_id, $company_user_id){
        if($target_id == $company_user_id) return true;
        $company = Company::with('parent')->select('id', 'parent_id')->find($target_id);
        if(isset($company->parent->id)){
            if($company->parent->id === $company_user_id) return true;
            return $this->checkPermission($company->parent->id, $company_user_id);
        } else return false;
    }

    private function childrenAttributeConvert($company, $children, $count = false, $children_count = false)
    {
        if($count){
            $company->children_count = $company->$children_count;
            unset($company[$children_count]);
        }
        if(!$count){
            if(isset($company[$children_count])) unset($company[$children_count]);
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
            
            // Change Camel Case 
            $count_attribute = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $type));
            $count_attribute = $count_attribute.'_count';
            $company = $this->childrenAttributeConvert($company, $type_children, false, $count_attribute);
        }
        
        return $company;
    }

    public function getSubLocations($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('company_id');
        if($id === null) $id = auth()->user()->company_id;
        if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
        $company =  $this->getCompanyTreeSelect($id, 'subChild');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
    }

    public function getLocations($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $id = $request->get('company_id');
        if($id === null) $id = auth()->user()->company_id;
        if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
        $company =  $this->getLocationTrees($id, 'noSubChild', true);
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        
    }

    public function getMainLocations($route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = auth()->user()->company_id;
        if($id === 1) $company = $this->getCompanyTreeSelect($id, 'branchChild', true);
        else $company = $this->getLocationTrees($id, 'noSubChild', true);
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
    }

    public function getLocationTrees($id = null){
        if($id === null) $id = auth()->user()->company_id;
        return $this->getCompanyTreeSelect($id, 'noSubChild', true);
    }

    public function getAllCompanyList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        if(auth()->user()->role == 2) $companies = $this->getCompanyTreeSelect(auth()->user()->company_id, 'noSubChild');
        else $companies = $this->getCompanyTreeSelect(1, 'noSubChild');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }

    public function getBranchCompanyList($route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $companies = $this->getCompanyTreeSelect(1, 'branchChild');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }

    public function getClientCompanyList($route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;
        
        $companies = $this->getCompanyTreeSelect(1, 'clientChild');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }

    public function getCompanyClientList($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $with_mig = $request->get('with_mig', false);
        if($with_mig) $companies = Company::with('companyLogo')->select('id','name','address','phone_number','role','parent_id', 'is_enabled')->where('parent_id', 1)->where('role', 2)->orWhere('id', 1)->get();
        else $companies = Company::with('companyLogo')->select('id','name','address','phone_number','role','parent_id', 'is_enabled')->where('parent_id', 1)->where('role', 2)->get();
        $companies->makeHidden('parent_id');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }    
    
    public function getCompanyDetail($request, $route_name){
        try{
            $access = $this->globalService->checkRoute($route_name);
            if($access["success"] === false) return $access;

            $id = $request->get('id', null);
            if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $company = Company::with(['companyLogo', 'noSubChild.noSubChild.noSubChild', 'banks'])->find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            $list_company = $this->checkCompanyList($company);

            $company->relationship_inventories = RelationshipInventory::with('relationship:id,relationship_type,inverse_relationship_type')
            ->whereIn('connected_id', $list_company)->where('type_id',-3)->select('relationship_id', 'is_inverse', DB::raw('count(*) as relationship_total'))
            ->groupBy('relationship_id', 'is_inverse')->get();

            foreach($company->relationship_inventories as $relationship_inventory){
                $relationship_inventory->relationship_name = $relationship_inventory->is_inverse ? $relationship_inventory->relationship->inverse_relationship_type : $relationship_inventory->relationship->relationship_type;
                $relationship_inventory->makeHidden('relationship', 'relationship_id', 'is_inverse');
            }

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
            $company->makeHidden('deleted_at','parent_id', 'noSubChild', 'child');
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getSubCompanyDetail($request, $route_name){
        try{
            $access = $this->globalService->checkRoute($route_name);
            if($access["success"] === false) return $access;
            $id = $request->get('id', null);
            if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $company = Company::with(['companyLogo', 'parent:id,name,parent_id,role', 'subChildren', 'subChild.subChild', 'noSubChild.noSubChild.noSubChild'])->find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            $company->makeVisible('top_parent_id');
            $company->sub_location_level_1_count = count($company->subChild);
            $company->sub_location_level_2_count = 0;
            if($company->sub_location_level_1_count){
                foreach($company->subChild as $child){
                    $company->sub_location_level_2_count += count($child->subChild);
                }
            }

            $company->induk_level_1_count = count($company->noSubChild);
            $company->induk_level_2_count = 0;
            $company->induk_level_3_count = 0;
            if($company->induk_level_1_count){
                foreach($company->noSubChild as $child){
                    $temp_count = count($child->noSubChild);
                    $company->induk_level_2_count += count($child->noSubChild);
                    if($temp_count){
                        foreach($child->noSubChild as $child_child){
                            $company->induk_level_3_count += count($child_child->noSubChild);
                        }
                    }
                }
            }
            $company->level = $company->level(0);
            $company->makeHidden('deleted_at','parent_id', 'subChild', 'noSubChild');
            $company = $this->leveling($company);
            $company->parent->makeHidden(['parent', 'role']);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function getSubCompanyProfile($request, $route_name){
        try{
            $access = $this->globalService->checkRoute($route_name);
            if($access["success"] === false) return $access;
            $id = $request->get('id', null);
            if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $company = Company::with('parent', 'companyLogo')->select('id', 'name', 'phone_number', 'parent_id')->find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            $company_list = $this->checkSubCompanyList($company);
            $inventory_count = Inventory::whereIn('location', $company_list)->count();
            $good_inventory_count = Inventory::whereIn('location', $company_list)->where('status_condition', 1)->count();
            $company->inventory_count = $inventory_count;
            $company->good_inventory_count = $good_inventory_count;
            $company->good_inventory_percentage = $inventory_count ? floor($good_inventory_count/$inventory_count * 100) : 0;
            $company->asset_cluster = DB::table('inventories')
            ->select(DB::raw('count(*) as asset_count, assets.name, assets.code'))
            ->whereIn('location', $company_list)
            ->join('model_inventories', 'inventories.model_id', '=', 'model_inventories.id')
            ->join('assets', 'model_inventories.asset_id', '=', 'assets.id')
            ->groupBy('assets.id')
            ->get();

            foreach($company->asset_cluster as $inventory){
                if(strlen($inventory->code) > 3){
                    $parent_model = substr($inventory->code, 0, 3);
                    $parent = DB::table('assets')->where('code', $parent_model)->first();
                    $parent_name = $parent === null ? "Asset Not Found" : $parent->name;
                    $inventory->name = $parent_name . " / " . $inventory->name;
                }
            }
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
    
    public function addCompany($request, $role_id, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "check_in_time" => "date_format:H:i:s"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        if($role_id === 4) $parent_id = $request->get('parent_id', null);
        else $parent_id = $request->get('parent_id', auth()->user()->company->parent_id);
        
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
            $company->role = $role_id;
            $company->created_time = date("Y-m-d H:i:s");
            $company->is_enabled = false;
            
            $company->singkatan = $request->get('singkatan', '-');
            $company->tanggal_pkp = $request->get('tanggal_pkp', null);
            $company->penanggung_jawab = $request->get('penanggung_jawab', '-');
            $company->npwp = $request->get('npwp', '-');
            $company->fax = $request->get('fax', '-');
            $company->email = $request->get('email', '-');
            $company->website = $request->get('website', '-');
            $company->check_in_time = $request->get('check_in_time','08:00:00');
            $company->save();

            if($request->hasFile('company_logo')) {
                $fileService = new FileService;
                $file = $request->file('company_logo');
                $table = 'App\Company';
                $description = 'company_logo';
                $folder_detail = 'Companies';
                $add_file_response = $fileService->addFile($company->id, $file, $table, $description, $folder_detail);
            }

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
        return $this->addCompany($request, $this->branch_role_id, $route_name);
    }

    public function addCompanyClient($request, $route_name){
        return $this->addCompany($request, $this->client_role_id, $route_name);
    }

    public function addCompanySub($request, $route_name){
        return $this->addCompany($request, $this->sub_role_id, $route_name);
    }

    public function updateCompany($request, $route_name, $main = false){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "check_in_time" => "date_format:H:i:s"
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        if($main) $company = Company::find(auth()->user()->company_id);
        else {
            $id = $request->get('id');
            if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $company = Company::find($id);
        }
        if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        try{
            
            $company->name = $request->get('name');
            $company->address = $request->get('address');
            $company->phone_number = $request->get('phone_number');
            
            $company->singkatan = $request->get('singkatan', '-');
            $company->tanggal_pkp = $request->get('tanggal_pkp', null);
            $company->penanggung_jawab = $request->get('penanggung_jawab', '-');
            $company->npwp = $request->get('npwp', '-');
            $company->fax = $request->get('fax', '-');
            $company->email = $request->get('email', '-');
            $company->website = $request->get('website', '-');
            $company->check_in_time = $request->get('check_in_time', '08:00:00');
            $company->save();

            if($request->hasFile('company_logo')) {
                $fileService = new FileService;
                $file = $request->file('company_logo');
                $table = 'App\Company';
                $description = 'company_logo';
                $folder_detail = 'Companies';
                if($company->companyLogo->id){
                    $delete_file_response = $fileService->deleteForceFile($company->companyLogo->id);
                }
                $add_file_response = $fileService->addFile($company->id, $file, $table, $description, $folder_detail);
            }

            $logService = new LogService;
            if($company->role !== 4) $logService->updateCompany($company->id, $company->id);
            else $logService->updateCompany($company->top_parent_id, $company->id, true);

            return ["success" => true, "message" => "Detail Company Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateMainCompany($request, $route_name){
        return $this->updateCompany($request, $route_name, true);
    }

    public function updateSpecificCompany($request, $route_name){
        return $this->updateCompany($request, $route_name);
    }

    public function companyActivation($request, $route_name){
        $access = $this->globalService->checkRoute($route_name);
        if($access["success"] === false) return $access;

        $id = $request->get('company_id', null);
        if(!$this->checkPermission($id, auth()->user()->company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
        $company = Company::find($id);
        if($company === null || $company->role === 4) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        try{
            $company->is_enabled = $request->get('is_enabled', null);
            $company->save();
            if($company->role === 2) return ["success" => true, "message" => "Status Aktivasi Client Telah Diperbarui", "status" => 200];
            else if($company->role === 4) return ["success" => true, "message" => "Status Aktivasi Sub Company Telah Diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Status Aktivasi Branch Telah Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    private function deleteChildLocations($companies){
        foreach($companies as $company){
            if(count($company->child)){
                $this->deleteChildLocations($company->child);
            }
            $company->delete();
        }
    }

    public function deleteCompany($request, $route_name){
        try{
            $access = $this->globalService->checkRoute($route_name);
            if($access["success"] === false) return $access;

            $id = $request->get('id', null);
            $company_id = auth()->user()->company_id;
            if($id == $company_id) return ["success" => false, "message" => "Tidak Dapat Menghapus Perusahaan User", "status" => 403];
            if($id == 1) return ["success" => false, "message" => "Tidak Dapat Menghapus Perusahaan Mitramas Infosys Global", "status" => 400];
            if(!$this->checkPermission($id, $company_id)) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 403];
            $company = Company::withCount('noSubChild', 'subChild')->find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            $new_parent = $request->get('new_parent', null);
            $list_company = $this->checkSubCompanyList($company);
            if($company->role !== 4){
                if($company->no_sub_child_count > 0) return ["success" => false, "message" => "Masih Teradapat Lokasi Pada Perusahaan Ini, Proses Delete Perusahaan Tidak Dapat Dilakukan", "status" => 400];
                
                if($new_parent !== null){
                    if($this->checkPermission($new_parent, $id)) return ["success" => false, "message" => "New Parent Tidak Bisa Dari Lokasi Bawahannya", "status" => 400];
                    $companies = Company::with('child')->where('parent_id', $id)->where('role', 4)->get();
                    $this->deleteChildLocations($companies);
                    $inventories = Inventory::whereIn('location', $list_company)->update(['location' => $new_parent]);
                    // $companies = Company::where('parent_id', $company->id)->where('role', '<>', 4)->update(['parent_id' => $new_parent]);
                } else {
                    // $list_company = $this->checkCompanyList($company);
                    $inventories = Inventory::with('modelInventory:id,name','locationInventory:id,name,parent_id,role')->select('id', 'model_id', 'location')->whereIn('location', $list_company)->get();
                    if(count($inventories)){
                        foreach($inventories as $inventory) $inventory->full_name = $inventory->locationInventory->fullSubNameWParent();
                        $inventories->makeHidden('locationInventory');
                        return ["success" => false, "message" => 'Masih terdapat inventori yang terhubung ke lokasi :', "inventories" => $inventories, "status" => 200];
                    }
                    $companies = Company::with('child')->where('parent_id', $company->id)->get();
                    if(count($companies)) $this->deleteChildLocations($companies);
                }
            } else {
                if($company->sub_child_count > 0) {
                    if($new_parent !== null){
                        if($this->checkPermission($new_parent, $id)) return ["success" => false, "message" => "New Parent Tidak Bisa Dari Sublokasi Bawahannya", "status" => 400];
                        $companies = Company::where('parent_id', $id)->where('role', 4)->update(['parent_id' => $new_parent]);
                        $inventories = Inventory::whereIn('location', $list_company)->update(['location' => $new_parent]);
                    } else {
                        $companies = Company::with('child')->where('parent_id', $id)->where('role', 4)->get();
                        $this->deleteChildLocations($companies);
                        $inventories = Inventory::whereIn('location', $list_company)->update(['location' => $company->parent_id]);
                    }
                } else {
                    if($new_parent !== null) $inventories = Inventory::where('location', $id)->update(['location' => $new_parent]);
                    else $inventories = Inventory::where('location', $id)->update(['location' => $company->parent_id]);
                }
            }
            $company->delete();

            $logService = new LogService;
            if($company->role !== 4) $logService->deleteCompany($company->parent_id, $company->id);
            else $logService->deleteCompany($company->top_parent_id, $company->id, true);
            return ["success" => true, "message" => "Company Berhasil Dihapus", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}