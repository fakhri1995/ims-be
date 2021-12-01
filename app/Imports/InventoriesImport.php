<?php

namespace App\Imports;

use App\Inventory;
use App\InventoryValue;
use App\ModelInventoryColumn;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InventoriesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return Inventory|null
     */
    public function model(array $row)
    {
        $model_id = $row['model_id'];
        $inventory = Inventory::create([
            'model_id'     => $model_id,
            'vendor_id'    => $row['vendor_id'],
            'status_condition'     => $row['status_condition'],
            'status_usage'    => $row['status_usage'],
            'location' => $row['location'],
            'deskripsi'    => $row['deskripsi'],
            'manufacturer_id' => $row['manufacturer_id'],
            'mig_id'     => $row['mig_id'],
            'serial_number'    => $row['serial_number']
        ]);

        $model_inventory_columns = ModelInventoryColumn::where('model_id', $model_id)->pluck('id');

        if(count($model_inventory_columns)){
            foreach($model_inventory_columns as $model_inventory_column){
                $inventory->additionalAttributes()->attach($model_inventory_column, ['value' => '-']);
            }
        }
    }

    public function rules(): array
    {
        return [
            'model_id' => 'integer',
            'vendor_id' => 'integer|nullable',
            'status_condition'     => 'integer|min:1|max:3',
            'status_usage'    => 'integer|min:1|max:3',
            'location' => 'integer|nullable',
            'deskripsi'    => 'string|max:255|nullable',
            'manufacturer_id' => 'integer|nullable',
            'mig_id'     => 'required|max:255',
            'serial_number'    => 'string|max:255|nullable'
        ];
    }
}