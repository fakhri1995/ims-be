<?php

namespace App\Services;
use App\Services\GeneralService;
use App\Company;

class CompanyService
{
    public function __construct()
    {
        $this->client_role_id = 2;
        $this->branch_role_id = 3;
    }

    public function findCompany($id)
    {
        $company = Company::find($id);
        if($company) return $company->company_name;
        else return "Perusahaan Tidak Ditemukan";
    }

    public function findLowerCompanyList($companies, $id, $company_list)
    {
        foreach($companies as $company){
            if($company['parent_id'] === $id){
                $company_list = $this->findLowerCompanyList($companies, $company['company_id'], $company_list);
            }
        }
        $company_list[] = $id;
        return $company_list;
    }

    public function checkCompanyList($id)
    {
        $companies = Company::select('company_id', 'parent_id')->get()->toArray();
        return $this->findLowerCompanyList($companies, $id, []);
    }

    public function haveChild($company_user_id, $companies, $parent_id){
        foreach($companies as $company){
            if($company['company_id'] === $parent_id){
                if($company['parent_id'] === null) return false;
                if($company['parent_id'] === $company_user_id) return true;
                return $this->haveChild($company_user_id, $companies, $company['parent_id']);
            }
        }
    }

    public function checkPermission($target_id, $company_user_id){
        $companies = Company::select('company_id', 'parent_id')->get();
        $company = $companies->find($target_id);
        if($company->company_id === $company_user_id) return true;
        if($company->parent_id === $company_user_id) return true;
        $companies = $companies->toArray();
        return $this->haveChild($company_user_id, $companies, $company->parent_id);
    }

    public function getCompanyTreeChildren($companies, $sub_company){
        $company_children = [];
        foreach($companies as $company){
            if($company['parent_id'] === $sub_company['company_id']) $company_children[] = $company;
        }
        $members = [];
        if(count($company_children)){
            foreach($company_children as $company_child){
                $members[] = $this->getCompanyTreeChildren($companies, $company_child);
            }
        } 
        if(count($members)) $sub_company['members'] = $members;
        unset($sub_company["parent_id"]); 
        return $sub_company;
    }    

    public function getCompanyTree($id, $role_id = null){
        $companies = Company::select('company_id','company_name','address','phone_number','image_logo','role','parent_id')->get();
        $company = $companies->find($id);
        if($company === null) return ["success" => false, "message" => "Company Tidak Ditemukan", "code" => 400];
        if($role_id !== null) $companies = $companies->where('role', $role_id);
        $company_children = $companies->where('parent_id', $id);
        $companies_array = $companies->toArray();
        $members = [];
        if(count($company_children)){
            foreach($company_children as $company_child){
                $members[] = $this->getCompanyTreeChildren($companies_array, $company_child);
            }
        } 
        if(count($members)) $company['members'] = $members;
        unset($company["parent_id"]); 
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        
    }

    public function getCompanyTreeSelectChildren($companies, $sub_company, $parent_id){
        $company_children = [];
        foreach($companies as $company){
            if($company['parent_id'] === $sub_company['company_id']) $company_children[] = $company;
        }
        $children = [];
        if(count($company_children)){
            foreach($company_children as $company_child){
                $children[] = $this->getCompanyTreeSelectChildren($companies, $company_child, $sub_company['company_id']);
            }
        } 
        $new_company = [
            "id" => $sub_company['company_id'],
            "title" => $sub_company['company_name'],
            "key" => $sub_company['company_id'],
            "value" => $sub_company['company_id'],
            "id_parent" => $parent_id
        ];
        if(count($children)) $new_company['children'] = $children;
        return $new_company;
    }

    public function getCompanyTreeSelect($id){
        $companies = Company::select('company_id','company_name','parent_id')->get();
        $company = $companies->find($id);
        if($company === null) return ["success" => false, "message" => "Company Tidak Ditemukan", "data" => [], "code" => 400];
        else {
            $company_children = $companies->where('parent_id', $id);
            $companies_array = $companies->toArray();
            $children = [];
            if(count($company_children)){
                foreach($company_children as $company_child){
                    $children[] = $this->getCompanyTreeSelectChildren($companies_array, $company_child, $company->company_id);
                }
            } 
            $new_company = [
                "id" => $company->company_id,
                "title" => $company->company_name,
                "key" => $company->company_id,
                "value" => $company->company_id,
                "id_parent" => $company->company_id
            ];
            if(count($children)) $new_company['children'] = $children;
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $new_company, "status" => 200];
        }
    }

    public function getLocations($id = null, $bypass = false){
        if($bypass) return $this->getCompanyTreeSelect($id);
        if($id === null) $id = auth()->user()->company_id;
        if($this->checkPermission($id, auth()->user()->company_id)) return $this->getCompanyTreeSelect($id);
        return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
    }

    public function getBranchCompanyList(){
        return $this->getCompanyTreeSelect(auth()->user()->company_id);
    }

    public function getClientCompanyList(){
        return $this->getCompanyTree(auth()->user()->company_id, $this->client_role_id);
    }

    public function getCompanyClientList(){
        $companies = Company::select('company_id','company_name','address','phone_number','image_logo','role','parent_id')->where('parent_id', 1)->where('role', 2)->get();
        $companies->makeHidden('parent_id');
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $companies, "status" => 200];
    }

    public function getCompanyDetail($id, $role_id){
        try{
            $company = Company::find($id);
            if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
            if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
            $company->makeHidden('deleted_at','parent_id');
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $company, "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getMainCompanyDetail(){
        return $this->getCompanyDetail(1, 1);
    }

    public function getCompanyBranchDetail($id){
        return $this->getCompanyDetail($id, $this->branch_role_id);
    }

    public function getCompanyClientDetail($id){
        return $this->getCompanyDetail($id, $this->client_role_id);
    }

    public function addCompany($data, $role_id){
        $generalService = new GeneralService;
        try{
            $company = new Company;
            $company->company_name = $data['name'];
            $company->parent_id = $data['parent_id'];
            $company->address = $data['address'];
            $company->phone_number = $data['phone_number'];
            $company->image_logo = $data['image_logo'];
            $company->role = $role_id;
            $company->created_time = $generalService->getTimeNow();
            $company->is_enabled = false;
            
            $company->singkatan = '-';
            $company->tanggal_pkp = null;
            $company->penanggung_jawab = '-';
            $company->npwp = '-';
            $company->fax = '-';
            $company->email = '-';
            $company->website = '-';
            $company->save();
            if($role_id === 2) return ["success" => true, "message" => "Client Company Berhasil Dibentuk", "status" => 200];
            else return ["success" => true, "message" => "Branch Company Berhasil Dibentuk", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addCompanyBranch($data){
        return $this->addCompany($data, $this->branch_role_id);
    }

    public function addCompanyClient($data){
        return $this->addCompany($data, $this->client_role_id);
    }

    public function updateCompany($data, $role_id){
        $company = Company::find($data['id']);
        if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
        try{
            $company->company_name = $data['company_name'];
            $company->address = $data['address'];
            $company->phone_number = $data['phone_number'];
            $company->image_logo = $data['image_logo'];
            
            $company->singkatan = $data['singkatan'];
            $company->tanggal_pkp = $data['tanggal_pkp'];
            $company->penanggung_jawab = $data['penanggung_jawab'];
            $company->npwp = $data['npwp'];
            $company->fax = $data['fax'];
            $company->email = $data['email'];
            $company->website = $data['website'];
            $company->save();
            if($role_id === 2) return ["success" => true, "message" => "Client Company Berhasil Diperbarui", "status" => 200];
            else if($role_id === 3) return ["success" => true, "message" => "Branch Company Berhasil Diperbarui", "status" => 200];
            else return ["success" => true, "message" => "MIG Company Berhasil Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateMainCompany($data){
        if(auth()->user()->company_id !== 1) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Melakukan Proses Ini", "status" => 401];
        return $this->updateCompany($data, 1);
    }

    public function updateCompanyBranch($data){
        return $this->updateCompany($data, $this->branch_role_id);
    }

    public function updateCompanyClient($data){
        return $this->updateCompany($data, $this->client_role_id);
    }

    public function companyActivation($data, $role_id){
        $company = Company::find($data['id']);
        if($company === null) return ["success" => false, "message" => "Id Company Tidak Ditemukan", "status" => 400];
        if($company->role !== $role_id) return ["success" => false, "message" => "Anda Tidak Memiliki Akses Untuk Company Ini", "status" => 401];
        try{
            $company->is_enabled = $data['is_enabled'];
            $company->save();
            if($role_id === 2) return ["success" => true, "message" => "Status Aktivasi Client Telah Diperbarui", "status" => 200];
            else return ["success" => true, "message" => "Status Aktivasi Branch Telah Diperbarui", "status" => 200];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function companyBranchActivation($data){
        return $this->companyActivation($data, $this->branch_role_id);
    }

    public function companyClientActivation($data){
        return $this->companyActivation($data, $this->client_role_id);
    }
}