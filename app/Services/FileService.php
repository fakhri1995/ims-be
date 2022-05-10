<?php

namespace App\Services;
use App\File;
use App\Services\DOCdnService;
use Illuminate\Support\Facades\Storage;
use Exception;

class FileService
{
    private function purgeLink($link)
    {
        $cdnService = new DOCdnService;
        $service_response = $cdnService->purge($link);
        if(isset($service_response['message'])) return false;
        return true;
    }

    public function addFile($id, $file, $table, $description, $folder_detail, $detail = false)
    {
        try{
            $new_file = new File;
            $file_name = str_replace(" ","-",$file->getClientOriginalName());
            $filename = pathinfo($file_name, PATHINFO_FILENAME);
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $name = $filename.'_'.time().'.'.$extension;
            if($detail){
                $year_month = date("Y m");
                $year_month_split = explode(' ', $year_month);
                $folder = env('APP_ENV').'/'.$folder_detail.'/'.$year_month_split[0].'/'.$year_month_split[1];
                $upload = Storage::disk('do')->putFileAs($folder, $file, $name);
            } else {
                $folder = env('APP_ENV').'/'.$folder_detail;
                $upload = Storage::disk('do')->putFileAs($folder, $file, $name);
            }
            $new_file->link = $folder.'/'.$name;
            $new_file->fileable_id = $id;
            $new_file->fileable_type = $table;
            $new_file->uploaded_by = auth()->user()->id;
            $new_file->description = $description;
            $new_file->save();
            return ["success" => true, "id" => $new_file->id];
        } catch(Exception $err){
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    // Storage::disk('do')->setVisibility($file->link, 'public');
    // Storage::disk('do')->getVisibility($file->link);

    public function deleteFile($id)
    {
        $file = File::find($id);
        if($file === null) return ["success" => false, "message" => "File Tidak Ditemukan"];
        $set_private = Storage::disk('do')->setVisibility($file->link, 'private');
        if(!$set_private) return ["success" => false, "message" => "File Gagal Didelete dari Space"];
        return ["success" => true];
        $file->delete();
        $purge_response = $this->purgeLink($file->link);
        if(!$purge_response) return ["success" => false, "message" => "Gagal Purge Data"];
        return ["success" => true];
    }

    public function deleteForceFile($id)
    {
        $file = File::find($id);
        if($file === null) return ["success" => false, "message" => "File Tidak Ditemukan"];
        $delete_file = Storage::disk('do')->delete($file->link);
        if(!$delete_file) return ["success" => false, "message" => "File Gagal Didelete dari Space"];
        $file->forceDelete();
        $purge_response = $this->purgeLink($file->link);
        if(!$purge_response) return ["success" => false, "message" => "Gagal Purge Data"];
        return ["success" => true];
    }
}



