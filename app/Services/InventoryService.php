<?php

namespace App\Services;
use App\AccessFeature;
use App\Role;

class InventoryService
{
  public function getProductInventories()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function getProductInventory()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function addProductInventory()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function updateProductInventory()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function deleteProductInventory()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }

    public function getInventoryBySearch()
    {
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [], "status" => 200];
    }
}