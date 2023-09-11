<?php

namespace App\Services;

use App\Contract;
use App\ContractHistory;
use App\ContractInvoice;
use App\ContractInvoiceProduct;
use App\ContractInvoiceTemplate;
use App\ContractProduct;
use App\ContractProductTemplate;
use App\ContractProductTemplateValue;
use App\Product;
use Exception;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpParser\ErrorHandler\Throwing;
use Throwable;

class ContractService extends BaseService
{
    public function __construct()
    {
        $this->globalService = new GlobalService;
        $this->logService = new LogService;
    }


    public function getContracts(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $rules = [
            "page" => "numeric",
            "rows" => "numeric|max:50",
            "sort_by" => "in:contract_number,title,start_date,duration,status|nullable",
            "sort_type" => "in:asc,desc",
            "duration" => "numeric|nullable"
        ];

        if ($request->to && $request->from) {
            $rules["from"] = "date|before:to";
            $rules["to"] = "date|after:from";
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $fieldStatusQuery = "
            CASE
                WHEN contracts.is_posted = 0 THEN 'draft'
                WHEN CURDATE() > contracts.end_date THEN 'selesai'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) < 14 THEN 'segeraberakhir'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) > 14 THEN 'berlangsung'
            END AS status,
            CASE
                WHEN contracts.is_posted = 0 THEN '2'
                WHEN CURDATE() > contracts.end_date THEN '4'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) < 14 THEN '1'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) > 14 THEN '3'
            END AS status_order
            ";
        // $contracts = Contract::select()->addSelect(DB::raw(
        //     "$fieldStatusQuery,
        //     DATEDIFF(contracts.end_date, CURDATE()) as duration
        //     "
        // ))->with(["client","requester"]);
        $contracts = Contract::fromSub(function ($query) use ($fieldStatusQuery) {
            $query->from('contracts')
                ->select('*', DB::raw(
                    "$fieldStatusQuery,
                        DATEDIFF(contracts.end_date, CURDATE()) as duration"
                ));
        }, 'contracts')->with(["client", "requester"]);
        $rows = $request->rows ?? 5;
        $keyword = $request->keyword;
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? 'asc';
        $status_types = $request->status_types ? explode(",", $request->status_types) : NULL;
        $client_ids = $request->client_ids ? explode(",", $request->client_ids) : NULL;
        $duration = $request->duration ?? NULL;

        if ($keyword) $contracts = $contracts->where("contract_number", "LIKE", "%$keyword%")->orWhere("title", "LIKE", "%$keyword%");
        if ($status_types) $contracts = $contracts->whereIn('status', $status_types);
        if ($client_ids) $contracts = $contracts->whereIn("client_id", $client_ids);
        if ($duration) $contracts = $contracts->where('duration', '<', $duration)->where('duration', '>', 0);

        if (in_array($sort_by, ["contract_number", "title", "start_date", "duration"])) $contracts = $contracts->orderBy($sort_by, $sort_type);
        if ($sort_by == "status") $contracts = $contracts->orderBy("status_order", $sort_type);

        $contracts = $contracts->paginate($rows);
        return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contracts, "status" => 200];
        try {
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getContract(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $id = $request->id;
            $fieldStatusQuery = "CASE
                WHEN contracts.is_posted = 0 THEN 'draft'
                WHEN CURDATE() > contracts.end_date THEN 'selesai'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) < 14 THEN 'segeraberakhir'
                WHEN DATEDIFF(contracts.end_date, CURDATE()) > 14 THEN 'berlangsung'
            END AS status";
            $contract = Contract::select()->addSelect(DB::raw(
                "$fieldStatusQuery,
                DATEDIFF(contracts.end_date, CURDATE()) as duration
                "
            ))->with(["client", "requester", "services", "services.product", "invoice_template"])->find($id);

            if (!$contract) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contract, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addContract(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $is_posted_rules = !$request->is_posted ? "" : "required|";

        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            "code_number" => $is_posted_rules . "nullable",
            "title" => $is_posted_rules . "nullable",
            "client_id" => $is_posted_rules . "numeric|nullable",
            "requester_id" => $is_posted_rules . "numeric|nullable",
            "initial_date" => $is_posted_rules . "date|nullable",
            "start_date" => $is_posted_rules . "date|nullable",
            "end_date" => $is_posted_rules . "date|nullable",
            "is_posted" => $is_posted_rules . "boolean",
            "extras" => "array|nullable",
            "extras.*.type" => "in:1,2,3|required_with:extras",
            "extras.*.name" => "required_with:extras",
            "extras.*.value" => [
                "required_with:extras",
                function ($attribute, $value, $fail) use ($requestData) {
                    $index = explode(".", $attribute)[1];
                    $type = $requestData["extras"][$index]["type"];
                    if ($type == 1 && !is_string($value)) {
                        $fail("The " . $attribute . " must be an string if type is 1.");
                    }
                    if ($type == 2 && !is_array($value)) {
                        $fail("The " . $attribute . " must be an array if type is 2.");
                    }
                    if ($type == 3 && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())) {
                        $fail("The " . $attribute . " must be a valid file if type is 3.");
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            DB::beginTransaction();
            $contract = new Contract();
            $contract->code_number = $request->code_number ?? NULL;
            $contract->title = $request->title ?? NULL;
            $contract->client_id = $request->client_id ?? NULL;
            $contract->requester_id = $request->requester_id ?? NULL;
            $contract->initial_date = $request->initial_date ?? NULL;
            $contract->start_date = $request->start_date ?? NULL;
            $contract->end_date = $request->end_date ?? NULL;
            $contract->is_posted = $request->is_posted ?? 0;
            $current_time = date('Y-m-d H:i:s');
            $contract->created_at = $current_time;
            $contract->created_by = auth()->user()->id;
            $contract->updated_at = $current_time;
            $contract->extras = $request->extras ?? [];
            $contract->save();

            $extras_arr = $request->extras ?? [];
            $extras = [];
            $fileService = new FileService;
            foreach ($extras_arr as $e) {
                $extra = [];
                $extra["key"] = Str::uuid()->toString();
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];
                if ($e['type'] == 3) {
                    $file = $e['value'];
                    $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                    if ($add_file_response["success"]) {
                        $extra['value'] = $add_file_response["new_data"];
                    }
                } else {
                    $extra["value"] = $e['value'];
                }
                $extras[] = $extra;
            }

            $contract->extras = $extras;
            $contract->save();


            $history = new ContractHistory();
            $history->category = 'initial';
            $history->contract_id = $contract->id;
            $history->code_number = $request->code_number ?? NULL;
            $history->title = $request->title ?? NULL;
            $history->client_id = $request->client_id ?? NULL;
            $history->requester_id = $request->requester_id ?? NULL;
            $history->initial_date = $request->initial_date ?? NULL;
            $history->start_date = $request->start_date ?? NULL;
            $history->end_date = $request->end_date ?? NULL;
            $history->extras = $contract->extras;
            $history->is_posted = 1;
            $history->created_at = $current_time;
            $history->created_by = auth()->user()->id;
            $history->updated_at = $current_time;
            $history->save();

            $contract->contract_history_id_active = $history->id;
            $contract->save();

            $logDataNew = clone $contract;
            $logDataNew->services();

            $logProperties = [
                "log_type" => "created_contract",
                "old" => null,
                "new" => $logDataNew
            ];
            $this->logService->addLogContract($contract->id, auth()->user()->id, "Created", $logProperties, null);
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $contract, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateContract(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $id = $request->id;
        $contract = Contract::with("services")->find($id);
        if (!$contract) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
        $history = ContractHistory::query()->where('contract_id', $id)->where('category', 'initial')->with('services')->first();
        if (!$history) return ["success" => false, "message" => "Data Initial tidak ditemukan.", "status" => 400];
        $logDataOld = clone $contract;
        $old_extras = $contract->extras;
        $old_extras_remap = [];
        $old_extra_key_array = [];
        foreach ($old_extras as $oe) {
            $old_extra_key_array[] = $oe['key'];
            $old_extras_remap[$oe['key']] = $oe;
        }

        $is_posted_rules = !$request->is_posted ? "" : "required|";
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            "code_number" => $is_posted_rules . "nullable",
            "title" => $is_posted_rules . "nullable",
            "client_id" => $is_posted_rules . "numeric|nullable",
            "requester_id" => $is_posted_rules . "numeric|nullable",
            "initial_date" => $is_posted_rules . "date|nullable",
            "start_date" => $is_posted_rules . "date|nullable",
            "end_date" => $is_posted_rules . "date|nullable",
            "is_posted" => $is_posted_rules . "boolean",
            "extras" => "array|nullable",
            "extras.*.key" => "nullable|in:" . implode(",", $old_extra_key_array),
            "extras.*.type" => [
                "in:1,2,3",
                "required_with:extras",
                function ($attribute, $value, $fail) use ($requestData, $old_extras_remap) {
                    $index = explode(".", $attribute)[1];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($issetInOld && ($value != $old_extras_remap[$key]['type'])) {
                        $fail("The " . $attribute . " can be change.");
                    }
                }
            ],
            "extras.*.name" => "required_with:extras",
            "extras.*.value" => [
                "required_without:extras.*.key",
                function ($attribute, $value, $fail) use ($requestData) {
                    $index = explode(".", $attribute)[1];
                    $type = $requestData["extras"][$index]["type"];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($type == 1 && !is_string($value)) {
                        $fail("The " . $attribute . " must be an string if type is 1.");
                    }
                    if ($type == 2 && !is_array($value)) {
                        $fail("The " . $attribute . " must be an array if type is 2.");
                    }
                    if ($type == 3) {
                        if (($key == NULL && !$issetInOld) && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())) {
                            $fail("The " . $attribute . " must be a valid file if type is 3 and key not exist in old data.");
                        }
                    }
                }
            ],
            "extras.*.is_deleted" => "boolean|nullable",
            "services" => "array|nullable",
            "services.*.product_id" => "required_with:services|numeric",
            "services.*.pax" => "required_with:services",
            "services.*.price" => "required_with:services",
            "services.*.unit" => "required_with:services:in:jam,hari,bulan,tahun"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $contract->code_number = $request->code_number ?? NULL;
            $contract->title = $request->title ?? NULL;
            $contract->client_id = (int)$request->client_id ?? NULL;
            $contract->requester_id = (int)$request->requester_id ?? NULL;
            $contract->initial_date = $request->initial_date ?? NULL;
            $contract->start_date = $request->start_date ?? NULL;
            $contract->end_date = $request->end_date ?? NULL;
            $contract->extras = $request->extras ?? [];
            $contract->is_posted = (int)$request->is_posted ?? NULL;
            $current_time = date('Y-m-d H:i:s');
            $contract->updated_at = $current_time;

            // Extra Section
            $fileService = new FileService;
            $extras_arr = $request->extras ?? [];
            $extras = [];
            $extra_key_array = [];
            foreach ($extras_arr as $k => $e) {
                $extra = [];
                // $extra["key"] = Str::uuid()->toString();
                $isset_key = isset($e["key"]);
                $is_deleted = isset($e["is_deleted"]) ? $e["is_deleted"] : false;
                $extra["key"] = $key = $isset_key ? $e["key"] : Str::uuid()->toString();
                $extra_key_array[] = $key;
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];

                if ($isset_key) {
                    if ($is_deleted) { // for_delete
                        if ($e["type"] == 3) {
                            $fileService->deleteForceFile($old_extras_remap[$key]["value"]["id"]);
                        }
                        unset($old_extras_remap[$key]);
                        unset($extras_arr[$k]);
                        continue;
                    } else {
                        if ($e["type"] == 3) { //for_update
                            if (is_a($e["value"], "Illuminate\Http\UploadedFile")) {
                                try {
                                    $fileService->deleteForceFile($old_extras_remap[$key]["value"]['id']);
                                } catch (Exception $err) {
                                    echo $err;
                                }
                                $file = $e["value"];
                                $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                                if ($add_file_response["success"]) {
                                    $extra["value"] = $add_file_response["new_data"];
                                }
                            } else {
                                try {
                                    $extra["value"] = $old_extras_remap[$key]["value"];
                                } catch (Exception $err) {
                                    echo $err;
                                }
                            }
                        } else {
                            $extra["value"] = $e["value"];
                        }
                    }
                } else {
                    // skip if new but is_deleted true
                    if ($is_deleted) continue;
                    // new data
                    if ($e['type'] == 3) {
                        $file = $e['value'];
                        $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                        if ($add_file_response["success"]) {
                            $extra['value'] = $add_file_response["new_data"];
                        }
                    } else {
                        $extra["value"] = $e['value'];
                    }
                }
                $extras[] = $extra;
            }

            $diff_extra = array_diff($old_extra_key_array, $extra_key_array);
            $old_extras = [];
            foreach ($diff_extra as $d) {
                $old_extras[] = $old_extras_remap[$d];
            }

            $extras = array_merge($old_extras, $extras);
            $contract->extras = $extras;

            // SERVICES
            ContractProduct::where("contract_id", $contract->id)
                ->where('contract_history_id', $history->id)->delete();
            $services = $request->services ?? [];
            $serviceData = [];
            foreach ($services as $s) {
                $s = (object)$s;
                $serviceData[] = [
                    "product_id" => $s->product_id,
                    "contract_id" => $contract->id,
                    "contract_history_id" => $history->id,
                    "pax" => $s->pax,
                    "price" => $s->price,
                    "unit" => $s->unit,
                    "created_at" => $current_time,
                    "updated_at" => $current_time,
                ];
            };
            $contract->save();

            $history->code_number = $request->code_number ?? NULL;
            $history->title = $request->title ?? NULL;
            $history->client_id = (int)$request->client_id ?? NULL;
            $history->requester_id = (int)$request->requester_id ?? NULL;
            $history->initial_date = $request->initial_date ?? NULL;
            $history->start_date = $request->start_date ?? NULL;
            $history->end_date = $request->end_date ?? NULL;
            $history->extras = $extras;
            $history->is_posted = 1;
            $history->updated_at = $current_time;
            $history->save();

            $services = ContractProduct::insert($serviceData);
            $contract->services();

            $logDataNew = clone $contract;

            $logProperties = [
                "log_type" => "updated_contract",
                "old" => $logDataOld,
                "new" => $logDataNew
            ];

            unset($logDataOld->updated_at);
            unset($logDataNew->updated_at);

            //if the data is not same, then the write the log
            if (json_encode($logDataOld) != json_encode($logDataNew)) {
                $this->logService->addLogContract($contract->id, auth()->user()->id, "Updated", $logProperties, null);
            }

            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $contract, "status" => 200];
        } catch (Throwable $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteContract(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        try {
            $validator = Validator::make($request->all(), [
                "id" => "required|numeric",
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }

            $id = $request->id;
            $contract = Contract::find($id);
            $logDataOld = clone $contract;
            if (!$contract)  return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
            $contract->delete();
            $logProperties = [
                "log_type" => "deleted_contract",
                "old" => $logDataOld,
                "new" => null
            ];

            $this->logService->addLogContract($contract->id, auth()->user()->id, "Deleted", $logProperties, null);
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $contract, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getContractActiveCount($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $contract = Contract::where("is_posted", 1)->count();

        $data = [
            "total" => $contract,
        ];

        try {
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $data, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //NOTES
    public function addContractLogNotes($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "contract_id" => "required|numeric",
            "notes" => "required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $contract_id = $request->contract_id ?? NULL;
        $notes = $request->notes ?? NULL;

        $description = "Menambahkan sebuah catatan pada kontrak.";


        $contract = Contract::find($contract_id);
        if (!$contract) return ["success" => false, "message" => "Contract tidak ditemukan.", "status" => 400];

        if ($this->logService->addLogContractFunction($contract_id, auth()->user()->id, "Notes", NULL, $notes, $description)) {
            return ["success" => true, "message" => "Notes berhasil ditambahkan.", "status" => 200];
        };
        return ["success" => false, "message" => "Gagal menambahkan notes.", "status" => 400];
    }

    public function deleteContractLogNotes($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $logId = $request->id;

        return $this->logService->deleteContractLogNotes($logId);
    }

    // Contract Template
    public function updateContractTemplate($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "contract_id" => "required|numeric",
            "contract_history_id" => "required|numeric",
            "invoice_template" => "array",
            "service_template" => "array",
            "service_template_values" => "array",
            "service_template_values.*.contract_service_id" => "integer|required_with:service_template_values.values",
            "service_template_values.*.details" => "array",
            "service_template_values.*.details.*" => "required_with:service_template.*",
            "invoice_period" => "min:1|max:31"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $contract_id = $request->contract_id;
            $contract_history_id = $request->contract_history_id;
            $contract = ContractHistory::query()->where('contract_id', $contract_id)->find($contract_history_id);
            if (!$contract) return ["success" => false, "message" => "Contract tidak ditemukan.", "status" => 400];

            $invoice_template = $request->invoice_template ?? [];
            $service_template = $request->service_template ?? [];
            $service_template_values = $request->service_template_values ?? [];
            $invoice_period = $request->invoice_period;
            $bank_id = $request->bank_id ?? NULL;

            $current_time = date("Y-m-d H:i:s");
            // ContractInvoiceTemplate
            $contractInvoiceTemplate = ContractInvoiceTemplate::where("contract_id", $contract_id)->where('contract_history_id', $contract_history_id)->first();
            if (!$contractInvoiceTemplate) $contractInvoiceTemplate = new ContractInvoiceTemplate();

            $contractInvoiceTemplate->contract_id = $contract_id;
            $contractInvoiceTemplate->contract_history_id = $contract_history_id;
            $contractInvoiceTemplate->details = $invoice_template;
            $contractInvoiceTemplate->created_at = $contractInvoiceTemplate->created_at ?? $current_time;
            $contractInvoiceTemplate->invoice_period = $invoice_period;
            $contractInvoiceTemplate->updated_at = $current_time;
            $contractInvoiceTemplate->bank_id = $bank_id;
            $contractInvoiceTemplate->updated_by = auth()->user()->id;
            $contractInvoiceTemplate->save();

            // ContractProductTemplate
            $contractServiceTemplate = ContractProductTemplate::where("contract_id", $contract_id)->where('contract_history_id', $contract_history_id)->first();
            if (!$contractServiceTemplate) $contractServiceTemplate = new ContractProductTemplate();

            $contractServiceTemplate->contract_id = $contract_id;
            $contractServiceTemplate->contract_history_id = $contract_history_id;
            $contractServiceTemplate->details = $service_template;
            $contractServiceTemplate->created_at = $contractServiceTemplate->created_at ?? $current_time;
            $contractServiceTemplate->updated_at = $current_time;
            $contractServiceTemplate->updated_by = auth()->user()->id;
            $contractServiceTemplate->save();


            // ContractProductTemplateValue
            $contract_service_ids_request = collect($service_template_values)->pluck("contract_service_id")->toArray();
            $contract_service_ids = $contract->services()->pluck("id")->toArray();
            // $contract_service_id_diff = array_diff($contract_service_ids_request, $contract_service_ids);
            // if(count($contract_service_ids_request) != count($contract_service_ids)) return ["success" => false, "message" => "Panjang array service_template_values harus sama dengan jumlah product", "status" => 400];
            // if(count($contract_service_id_diff) > 0) return ["success" => false, "message" => "contract_service_id [".implode(",",$contract_service_id_diff)."] tidak valid.", "status" => 400];

            $count_service_template = count($service_template);
            foreach ($service_template_values as $key => $val) {
                $val = (object)$val;
                if (count($val->details) != $count_service_template) return ["success" => false, "message" => "Panjang array service_template_values.details[$key] harus sama array service_template", "status" => 400];
                if (!in_array($val->contract_service_id, $contract_service_ids)) return ["success" => false, "message" => "service_template_values.contract_service_id[$key] tidak terdaftar dalam contract.", "status" => 400];
            }


            foreach ($service_template_values as $s) {
                $s = (object)$s;
                $contractServiceTemplateValue = ContractProductTemplateValue::where([
                    "contract_id" => $contract_id,
                    "contract_history_id" => $contract_history_id,
                    "contract_product_id" => $s->contract_service_id
                ])->first();
                if (!$contractServiceTemplateValue) $contractServiceTemplateValue = new ContractProductTemplateValue();

                $contractServiceTemplateValue->contract_id = $contract_id;
                $contractServiceTemplateValue->contract_product_id = $s->contract_service_id;
                $contractServiceTemplateValue->contract_history_id = $contract_history_id;
                $contractServiceTemplateValue->details = $s->details ?? [];
                $contractServiceTemplateValue->created_at = $contractServiceTemplateValue->created_at ?? $current_time;
                $contractServiceTemplateValue->updated_at = $current_time;
                $contractServiceTemplateValue->updated_by = auth()->user()->id;
                $contractServiceTemplateValue->save();
            }

            DB::commit();
            return ["success" => true, "message" => "Data berhasil diupdate", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            //throw $th;
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function getContractTemplate($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "contract_id" => "required|numeric",
            "contract_history_id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $contract_id = $request->contract_id;
        $contract_history_id = $request->contract_history_id;
        $contract = ContractHistory::select()->addSelect(DB::raw(
            "DATEDIFF(contract_histories.end_date, CURDATE()) as duration"
        ))->with("client", "requester", "invoice_template", "service_template", "invoice_template.bank", "services", "services.product", "services.service_template_value")->where('contract_id', $contract_id)->find($contract_history_id);
        if (!$contract) return ["success" => false, "message" => "Contract tidak ditemukan.", "status" => 400];

        return ["success" => true, "message" => "Data berhasil diambil", "data" => $contract, "status" => 200];
    }

    public function getContractInvoices($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $rules = [
            "page" => "numeric",
            "rows" => "numeric",
            "sort_type" => "in:asc,desc",
            "sort_by" => "in:invoice_raise_at",
            "total_max" => "numeric|nullable",
            "total_min" => "numeric|nullable",
            "is_posted" => "boolean|nullable",
        ];


        $total_min = $request->total_min ?? NULL;
        $total_max = $request->total_max ?? NULL;
        if ($total_min && $total_max) {
            $rules['total_min'] = "lte:total_max|numeric";
            $rules['total_max'] = "gte:total_min|numeric";
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $rows = $request->rows ?? 10;
        $is_posted = $request->is_posted;
        $client_ids = $request->client_ids ? explode(",", $request->client_ids) : NULL;
        $keyword = $request->keyword ?? NULL;
        $year = $request->year ?? NULL;
        $month = $request->month ?? NULL;
        $sort_by = $request->sort_by ?? NULL;
        $sort_type = $request->sort_type ?? "asc";
        $total_max = $request->total_max;
        $total_min = $request->total_min;


        $contractInvoice = ContractInvoice::with('contract_template', 'contract_template.invoice_template', 'contract_template.client', 'contract_template.requester');

        if ($is_posted != NULL) $contractInvoice = $contractInvoice->where("is_posted", $is_posted);
        if ($client_ids) $contractInvoice = $contractInvoice->whereHas("contract_template", function ($q) use ($client_ids) {
            $q->whereIn("client_id", $client_ids);
        });
        if ($keyword) $contractInvoice = $contractInvoice->where("invoice_name", "LIKE", "%$keyword%")->orWhere("invoice_number", "LIKE", "%$keyword%");
        if ($year) $contractInvoice = $contractInvoice->whereYear("invoice_raise_at", $year);
        if ($month) $contractInvoice = $contractInvoice->whereMonth("invoice_raise_at", $month);
        if ($total_max) $contractInvoice = $contractInvoice->where("invoice_total", "<=", $total_max);
        if ($total_min) $contractInvoice = $contractInvoice->where("invoice_total", ">=", $total_min);
        if ($sort_by) $contractInvoice = $contractInvoice->orderBy("invoice_raise_at", $sort_type);

        $contractInvoice = $contractInvoice->paginate($rows);

        return ["success" => true, "message" => "Data berhasil diambil", "data" => $contractInvoice, "status" => 200];
    }

    public function getContractInvoice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "numeric|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $contractInvoice = ContractInvoice::with("service_attribute_values", "service_attribute_values.product", "bank")->find($id);
        if (!$contractInvoice) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
        $history = ContractHistory::select()->addSelect(DB::raw(
            "DATEDIFF(contract_histories.end_date, CURDATE()) as duration"
        ))->with("client", "requester")->find($contractInvoice->contract_history_id);

        // $history->contract_invoice = $contractInvoice;

        $data = array_merge($history->toArray(), $contractInvoice->toArray());
        $data['invoice_services'] = $data['service_attribute_values'];
        unset($data['service_attribute_values']);

        foreach ($data['invoice_services'] as $key => $value) {
            $data['invoice_services'][$key]['invoice_service_value'] = array(
                "invoice_service_id" => $data['invoice_services'][$key]['id'],
                "details" => $data['invoice_services'][$key]['details'],
            );
            unset($data['invoice_services'][$key]['details']);
        }


        return ["success" => true, "message" => "Data berhasil diambil", "data" => $data, "status" => 200];
    }

    public function addContractInvoice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "contract_template_id" => "numeric|required",
            "contract_history_id" => "numeric|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $contractInvoice = new ContractInvoice;
            $contract_id = $request->contract_template_id;
            $contract_history_id = $request->contract_history_id;
            $contract = ContractHistory::with("invoice_template", "service_template", "services", "services.product", "services.service_template_value")
                ->where('contract_id', $contract_id)->find($contract_history_id);
            if (!$contract) return ["success" => false, "message" => "Data template tidak ditemukan.", "status" => 400];
            // dd($contract->services);
            $contractInvoice->contract_template_id = $contract_id;
            $contractInvoice->contract_history_id = $contract_history_id;
            $contractInvoice->invoice_name = $request->invoice_name ?? NULL;
            $contractInvoice->invoice_raise_at = $request->invoice_raise_at ?? NULL;
            $contractInvoice->bank_id = $contract->invoice_template->bank_id;
            $contractInvoice->invoice_attribute = $contract->invoice_template->details ?? [];
            $contractInvoice->service_attribute = $contract->service_template->details ?? [];
            $current_time = date("Y-m-d H:i:s");
            $contractInvoice->created_at = $current_time;
            $contractInvoice->updated_at = $current_time;
            $contractInvoice->save();


            // $contractInvoiceProduct->contract_invoice_id = $contractInvoice->id;

            $contractInvoiceProductData = [];
            $invoice_total = 0;
            foreach ($contract->services as $s) {
                $s = (object)$s;
                $contractInvoiceProductData[] = [
                    "contract_invoice_id" => $contractInvoice->id,
                    "product_id" => $s->product_id,
                    "pax" => $s->pax,
                    "price" => $s->price,
                    "unit" => $s->unit,
                    "details" => json_encode($s->service_template_value->details ?? []),
                    "created_at" => $current_time,
                    "updated_at" => $current_time
                ];
                $invoice_total += ($s->price * $s->pax);
            }

            ContractInvoiceProduct::insert($contractInvoiceProductData);
            $contractInvoice->invoice_total = $invoice_total;
            $contractInvoice->save();
            DB::commit();
            return ["success" => true, "message" => "Data berhasil diambil", "data" => $contractInvoice, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            //throw $th;
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateContractInvoice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            // "invoice_total" => "numeric|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $contract_invoice_id = $request->id;
            $contractInvoice = ContractInvoice::find($contract_invoice_id);
            if (!$contractInvoice) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
            // dd($contract->services);
            $invoice_total = $contractInvoice->invoice_total;
            $contractInvoice->invoice_name = $request->invoice_name ?? NULL;
            $contractInvoice->invoice_number = $request->invoice_number ?? NULL;
            $contractInvoice->invoice_raise_at = $request->invoice_raise_at ?? NULL;
            $contractInvoice->bank_id = $request->bank_id;
            $contractInvoice->invoice_attribute = $request->invoice_attribute ?? [];
            $contractInvoice->service_attribute = $request->service_attribute ?? [];
            $contractInvoice->invoice_total = $request->invoice_total ?? $invoice_total;
            if ($contractInvoice->is_posted) {
                return ["success" => false, "message" => "Data yang telah diterbitkan tidak dapat diubah", "status" => 400];
            }
            $contractInvoice->is_posted = $request->is_posted ?? 0;


            $current_time = date("Y-m-d H:i:s");
            $contractInvoice->updated_at = $current_time;
            // $contractInvoiceProduct->contract_invoice_id = $contractInvoice->id;
            $service_attribute_deleted = [];
            $service_attribute_values = $request->service_attribute_values ?? [];
            foreach ($service_attribute_values as $s) {
                $s = (object)$s;
                if (!$s->id) {
                    $contractInvoiceProduct = new ContractInvoiceProduct();
                } else {
                    if ($s->is_delete ?? false) $service_attribute_deleted[] = $s->id;
                    $contractInvoiceProduct = ContractInvoiceProduct::where(
                        ["id" => $s->id, "contract_invoice_id" => $contract_invoice_id]
                    )->first();
                    if (!$contractInvoiceProduct) continue;
                }

                $contractInvoiceProduct->contract_invoice_id = $contractInvoice->id;
                $contractInvoiceProduct->product_id = $s->product_id;
                $contractInvoiceProduct->pax = $s->pax;
                $contractInvoiceProduct->price = $s->price;
                $contractInvoiceProduct->unit = $s->unit;
                $contractInvoiceProduct->details = $s->details ?? [];
                $contractInvoiceProduct->updated_at = $current_time;
                if (!$s->id) {
                    $contractInvoiceProduct->created_at = $current_time;
                }
                $contractInvoiceProduct->save();
            }

            ContractInvoiceProduct::whereIn("id", $service_attribute_deleted)->where("contract_invoice_id", $contract_invoice_id)->delete();
            // $contractInvoice->invoice_total = ContractInvoiceProduct::where("contract_invoice_id",$contractInvoice->id)->selectRaw("SUM(pax*price) as subtotal")->first();
            $contractInvoice->save();
            DB::commit();
            return ["success" => true, "message" => "Data berhasil diubah", "data" => $contractInvoice, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            //throw $th;
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteContractInvoice($request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $contractInvoice = ContractInvoice::find($id);
        if (!$contractInvoice) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
        $contractInvoice->deleted_at = date('Y-m-d H:i:s');
        $contractInvoice->save();
        return ["success" => true, "message" => "Data berhasil dihapus", "status" => 200];
    }

    //* Contract History / Addendum

    public function getContractHistories(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access['success'] === false) {
            return $access;
        }

        $validator = Validator::make($request->all(), [
            'contract_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['success' => false, 'message' => $errors, 'status' => 400];
        }

        try {
            $id = $request->contract_id;

            $contract = Contract::query()->with(['initial', 'addendum'])->find($id);

            if (!$contract) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];

            return [
                'success' => true,
                'message' => 'Data Berhasil Diambil',
                'data' => $contract,
                'status' => 200,
            ];
        } catch (Exception $err) {
            return ['success' => false, 'message' => $err, 'status' => 400];
        }
    }

    public function getContractHistory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $validator = Validator::make($request->all(), [
            "contract_id" => "required|numeric",
            "history_id" => "required|numeric",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $fieldStatusQuery = "CASE
                WHEN CURDATE() > contract_histories.end_date THEN 'selesai'
                WHEN DATEDIFF(contract_histories.end_date, CURDATE()) < 14 THEN 'segeraberakhir'
                WHEN DATEDIFF(contract_histories.end_date, CURDATE()) > 14 THEN 'berlangsung'
            END AS status";
            $contract = ContractHistory::select()->addSelect(DB::raw(
                "$fieldStatusQuery,
                DATEDIFF(contract_histories.end_date, CURDATE()) as duration
                "
            ))->with(["client", "requester", "services", "services.product", "invoice_template"])
                ->where('contract_id', $request->contract_id)->find($request->history_id);

            if (!$contract) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $contract, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function addContractHistory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $contract = Contract::with("services")->find($request->contract_id);
        if (!$contract) return ["success" => false, "message" => "Kontrak tidak ditemukan.", "status" => 400];
        $old_extras = $contract->extras ?? [];
        $old_extras_remap = [];
        $old_extra_key_array = [];
        foreach ($old_extras as $oe) {
            $old_extra_key_array[] = $oe['key'];
            $old_extras_remap[$oe['key']] = $oe;
        }

        $is_posted_rules = !$request->is_posted ? "" : "required|";

        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            "contract_id" => "required|numeric",
            "code_number" => $is_posted_rules . "nullable",
            "title" => $is_posted_rules . "nullable",
            "client_id" => $is_posted_rules . "numeric|nullable",
            "requester_id" => $is_posted_rules . "numeric|nullable",
            "initial_date" => $is_posted_rules . "date|nullable",
            "start_date" => $is_posted_rules . "date|nullable",
            "end_date" => $is_posted_rules . "date|nullable",
            "extras" => "array|nullable",
            "extras.*.key" => "nullable|in:" . implode(",", $old_extra_key_array),
            "extras.*.type" => [
                "in:1,2,3",
                "required_with:extras",
                function ($attribute, $value, $fail) use ($requestData, $old_extras_remap) {
                    $index = explode(".", $attribute)[1];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($issetInOld && ($value != $old_extras_remap[$key]['type'])) {
                        $fail("The " . $attribute . " can be change.");
                    }
                }
            ],
            "extras.*.name" => "required_with:extras",
            "extras.*.value" => [
                "required_without:extras.*.key",
                function ($attribute, $value, $fail) use ($requestData) {
                    $index = explode(".", $attribute)[1];
                    $type = $requestData["extras"][$index]["type"];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($type == 1 && !is_string($value)) {
                        $fail("The " . $attribute . " must be an string if type is 1.");
                    }
                    if ($type == 2 && !is_array($value)) {
                        $fail("The " . $attribute . " must be an array if type is 2.");
                    }
                    if ($type == 3) {
                        if (($key == NULL && !$issetInOld) && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())) {
                            $fail("The " . $attribute . " must be a valid file if type is 3 and key not exist in old data.");
                        }
                    }
                }
            ],
            "extras.*.is_deleted" => "boolean|nullable",
            "services" => "array|nullable",
            "services.*.product_id" => "required_with:services|numeric",
            "services.*.pax" => "required_with:services",
            "services.*.price" => "required_with:services",
            "services.*.unit" => "required_with:services:in:jam,hari,bulan,tahun"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $lastAddendum = ContractHistory::query()->where('contract_id', $contract->id)
            ->orderBy('id', 'DESC')->first();

            $history = new ContractHistory();
            $history->category = 'addendum';
            $history->contract_id = $request->contract_id;
            $history->code_number = $request->code_number ?? NULL;
            $history->title = $request->title ?? NULL;
            $history->client_id = $request->client_id ?? NULL;
            $history->requester_id = $request->requester_id ?? NULL;
            $history->initial_date = $request->initial_date ?? NULL;
            $history->start_date = $request->start_date ?? NULL;
            $history->end_date = $request->end_date ?? NULL;
            $history->is_posted = 1;
            $current_time = date('Y-m-d H:i:s');
            $history->created_at = $current_time;
            $history->created_by = auth()->user()->id;
            $history->updated_at = $current_time;

            // Extra Section
            $fileService = new FileService;
            $extras_arr = $request->extras ?? [];
            $extras = [];
            $extra_key_array = [];
            foreach ($extras_arr as $k => $e) {
                $extra = [];
                // $extra["key"] = Str::uuid()->toString();
                $isset_key = isset($e["key"]);
                $is_deleted = isset($e["is_deleted"]) ? $e["is_deleted"] : false;
                $extra["key"] = $key = $isset_key ? $e["key"] : Str::uuid()->toString();
                $extra_key_array[] = $key;
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];

                if ($isset_key) {
                    if ($is_deleted) { // for_delete
                        if ($e["type"] == 3) {
                            $fileService->deleteForceFile($old_extras_remap[$key]["value"]["id"]);
                        }
                        unset($old_extras_remap[$key]);
                        unset($extras_arr[$k]);
                        continue;
                    } else {
                        if ($e["type"] == 3) { //for_update
                            if (is_a($e["value"], "Illuminate\Http\UploadedFile")) {
                                try {
                                    $fileService->deleteForceFile($old_extras_remap[$key]["value"]['id']);
                                } catch (Exception $err) {
                                    echo $err;
                                }
                                $file = $e["value"];
                                $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                                if ($add_file_response["success"]) {
                                    $extra["value"] = $add_file_response["new_data"];
                                }
                            } else {
                                try {
                                    $extra["value"] = $old_extras_remap[$key]["value"];
                                } catch (Exception $err) {
                                    echo $err;
                                }
                            }
                        } else {
                            $extra["value"] = $e["value"];
                        }
                    }
                } else {
                    // skip if new but is_deleted true
                    if ($is_deleted) continue;
                    // new data
                    if ($e['type'] == 3) {
                        $file = $e['value'];
                        $add_file_response = $fileService->addFile($contract->id, $file, 'App\Contract', 'contract_extra_file', 'Contract', false);
                        if ($add_file_response["success"]) {
                            $extra['value'] = $add_file_response["new_data"];
                        }
                    } else {
                        $extra["value"] = $e['value'];
                    }
                }
                $extras[] = $extra;
            }

            $diff_extra = array_diff($old_extra_key_array, $extra_key_array);
            $old_extras = [];
            foreach ($diff_extra as $d) {
                $old_extras[] = $old_extras_remap[$d];
            }

            $extras = array_merge($old_extras, $extras);
            $history->extras = $extras;
            $history->save();

            // SERVICES
            // ContractProduct::where("contract_id", $contract->id)->delete();
            $services = $request->services ?? [];
            $serviceData = [];
            foreach ($services as $s) {
                $s = (object)$s;
                $serviceData[] = [
                    "product_id" => $s->product_id,
                    "contract_id" => $history->contract_id,
                    "contract_history_id" => $history->id,
                    "pax" => $s->pax,
                    "price" => $s->price,
                    "unit" => $s->unit,
                    "created_at" => $current_time,
                    "updated_at" => $current_time,
                ];
            };

            $services = ContractProduct::insert($serviceData);
            $history->services();

            $contract->code_number = $history->code_number;
            $contract->title = $history->title;
            $contract->client_id = (int)$history->client_id;
            $contract->requester_id = (int)$history->requester_id;
            $contract->initial_date = $history->initial_date;
            $contract->start_date = $history->start_date;
            $contract->end_date = $history->end_date;
            $contract->extras = $extras;
            $contract->is_posted = $history->is_posted;
            $contract->contract_history_id_active = $history->id;
            $contract->updated_at = $current_time;
            $contract->save();

            $logDataNew = clone $history;
            $logDataOld = clone $lastAddendum;

            $logProperties = [
                "log_type" => "created_contract_history",
                "old" => $logDataOld,
                "new" => $logDataNew
            ];
            $this->logService->addLogContractHistory($history->id, auth()->user()->id, "Created", $logProperties, null);
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambahkan", "data" => $history, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function updateContractHistory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $id = $request->id;
        $history = ContractHistory::query()->with('services')->find($id);
        if (!$history) return ["success" => false, "message" => "Data Addendum tidak ditemukan.", "status" => 400];
        $contract = Contract::with("services")->find($history->contract_id);
        if (!$contract) return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
        $logDataOld = clone $history;
        $old_extras = $history->extras;
        $old_extras_remap = [];
        $old_extra_key_array = [];
        foreach ($old_extras as $oe) {
            $old_extra_key_array[] = $oe['key'];
            $old_extras_remap[$oe['key']] = $oe;
        }

        $is_posted_rules = !$request->is_posted ? "" : "required|";
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            "code_number" => $is_posted_rules . "nullable",
            "title" => $is_posted_rules . "nullable",
            "client_id" => $is_posted_rules . "numeric|nullable",
            "requester_id" => $is_posted_rules . "numeric|nullable",
            "initial_date" => $is_posted_rules . "date|nullable",
            "start_date" => $is_posted_rules . "date|nullable",
            "end_date" => $is_posted_rules . "date|nullable",
            "is_posted" => $is_posted_rules . "boolean",
            "extras" => "array|nullable",
            "extras.*.key" => "nullable|in:" . implode(",", $old_extra_key_array),
            "extras.*.type" => [
                "in:1,2,3",
                "required_with:extras",
                function ($attribute, $value, $fail) use ($requestData, $old_extras_remap) {
                    $index = explode(".", $attribute)[1];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($issetInOld && ($value != $old_extras_remap[$key]['type'])) {
                        $fail("The " . $attribute . " can be change.");
                    }
                }
            ],
            "extras.*.name" => "required_with:extras",
            "extras.*.value" => [
                "required_without:extras.*.key",
                function ($attribute, $value, $fail) use ($requestData) {
                    $index = explode(".", $attribute)[1];
                    $type = $requestData["extras"][$index]["type"];
                    $key = isset($requestData["extras"][$index]["key"]) ? $requestData["extras"][$index]["key"] : NULL;
                    $issetInOld = isset($old_extras_remap[$key]);
                    if ($type == 1 && !is_string($value)) {
                        $fail("The " . $attribute . " must be an string if type is 1.");
                    }
                    if ($type == 2 && !is_array($value)) {
                        $fail("The " . $attribute . " must be an array if type is 2.");
                    }
                    if ($type == 3) {
                        if (($key == NULL && !$issetInOld) && (!is_a($value, "Illuminate\Http\UploadedFile") || !$value->isValid())) {
                            $fail("The " . $attribute . " must be a valid file if type is 3 and key not exist in old data.");
                        }
                    }
                }
            ],
            "extras.*.is_deleted" => "boolean|nullable",
            "services" => "array|nullable",
            "services.*.product_id" => "required_with:services|numeric",
            "services.*.pax" => "required_with:services",
            "services.*.price" => "required_with:services",
            "services.*.unit" => "required_with:services:in:jam,hari,bulan,tahun"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            DB::beginTransaction();
            $history->code_number = $request->code_number ?? NULL;
            $history->title = $request->title ?? NULL;
            $history->client_id = (int)$request->client_id ?? NULL;
            $history->requester_id = (int)$request->requester_id ?? NULL;
            $history->initial_date = $request->initial_date ?? NULL;
            $history->start_date = $request->start_date ?? NULL;
            $history->end_date = $request->end_date ?? NULL;
            $history->extras = $request->extras ?? NULL;
            $history->is_posted = (int)$request->is_posted ?? NULL;
            $current_time = date('Y-m-d H:i:s');
            $history->updated_at = $current_time;

            // Extra Section
            $fileService = new FileService;
            $extras_arr = $request->extras ?? [];
            $extras = [];
            $extra_key_array = [];
            foreach ($extras_arr as $k => $e) {
                $extra = [];
                // $extra["key"] = Str::uuid()->toString();
                $isset_key = isset($e["key"]);
                $is_deleted = isset($e["is_deleted"]) ? $e["is_deleted"] : false;
                $extra["key"] = $key = $isset_key ? $e["key"] : Str::uuid()->toString();
                $extra_key_array[] = $key;
                $extra["type"] = $e['type'];
                $extra["name"] = $e['name'];

                if ($isset_key) {
                    if ($is_deleted) { // for_delete
                        if ($e["type"] == 3) {
                            $fileService->deleteForceFile($old_extras_remap[$key]["value"]["id"]);
                        }
                        unset($old_extras_remap[$key]);
                        unset($extras_arr[$k]);
                        continue;
                    } else {
                        if ($e["type"] == 3) { //for_update
                            if (is_a($e["value"], "Illuminate\Http\UploadedFile")) {
                                try {
                                    $fileService->deleteForceFile($old_extras_remap[$key]["value"]['id']);
                                } catch (Exception $err) {
                                    echo $err;
                                }
                                $file = $e["value"];
                                $add_file_response = $fileService->addFile($history->id, $file, 'App\ContractHistory', 'contract_history_extra_file', 'ContractHistory', false);
                                if ($add_file_response["success"]) {
                                    $extra["value"] = $add_file_response["new_data"];
                                }
                            } else {
                                try {
                                    $extra["value"] = $old_extras_remap[$key]["value"];
                                } catch (Exception $err) {
                                    echo $err;
                                }
                            }
                        } else {
                            $extra["value"] = $e["value"];
                        }
                    }
                } else {
                    // skip if new but is_deleted true
                    if ($is_deleted) continue;
                    // new data
                    if ($e['type'] == 3) {
                        $file = $e['value'];
                        $add_file_response = $fileService->addFile($history->id, $file, 'App\ContractHistory', 'contract_history_extra_file', 'ContractHistory', false);
                        if ($add_file_response["success"]) {
                            $extra['value'] = $add_file_response["new_data"];
                        }
                    } else {
                        $extra["value"] = $e['value'];
                    }
                }
                $extras[] = $extra;
            }

            $diff_extra = array_diff($old_extra_key_array, $extra_key_array);
            $old_extras = [];
            foreach ($diff_extra as $d) {
                $old_extras[] = $old_extras_remap[$d];
            }

            $extras = array_merge($old_extras, $extras);
            $history->extras = $extras;

            // SERVICES
            ContractProduct::where("contract_id", $contract->id)
                ->where('contact_history_id', $history->id)->delete();
            $services = $request->services ?? [];
            $serviceData = [];
            foreach ($services as $s) {
                $s = (object)$s;
                $serviceData[] = [
                    "product_id" => $s->product_id,
                    "contract_id" => $history->contract_id,
                    "contract_history_id" => $history->id,
                    "pax" => $s->pax,
                    "price" => $s->price,
                    "unit" => $s->unit,
                    "created_at" => $current_time,
                    "updated_at" => $current_time,
                ];
            };
            $history->save();

            $services = ContractProduct::insert($serviceData);
            $history->services();

            $contract->code_number = $history->code_number;
            $contract->title = $history->title;
            $contract->client_id = (int)$history->client_id;
            $contract->requester_id = (int)$history->requester_id;
            $contract->initial_date = $history->initial_date;
            $contract->start_date = $history->start_date;
            $contract->end_date = $history->end_date;
            $contract->extras = $history->extras;
            $contract->is_posted = $history->is_posted;
            $contract->contract_history_id_active = $history->id;
            $contract->updated_at = $current_time;
            $contract->save();

            $logDataNew = clone $history;

            $logProperties = [
                "log_type" => "updated_contract_history",
                "old" => $logDataOld,
                "new" => $logDataNew
            ];

            unset($logDataOld->updated_at);
            unset($logDataNew->updated_at);

            //if the data is not same, then the write the log
            if (json_encode($logDataOld) != json_encode($logDataNew)) {
                $this->logService->addLogContractHistory($history->id, auth()->user()->id, "Updated", $logProperties, null);
            }

            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Diubah", "data" => $history, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    public function deleteContractHistory(Request $request, $route_name)
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        try {
            $validator = Validator::make($request->all(), [
                "id" => "required|numeric",
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return ["success" => false, "message" => $errors, "status" => 400];
            }

            DB::beginTransaction();
            $id = $request->id;
            $history = ContractHistory::find($id);
            if (!$history)  return ["success" => false, "message" => "Data addendum tidak ditemukan.", "status" => 400];
            $contract = Contract::find($history->contract_id);
            if (!$contract)  return ["success" => false, "message" => "Data tidak ditemukan.", "status" => 400];
            $logDataOld = clone $history;
            $checkLastAddendum = ContractHistory::query()->where('contract_id', $contract->id)
                ->orderBy('id', 'DESC')->limit(2)->get();
            if (($checkLastAddendum[0]->id == $history->id) && (count($checkLastAddendum) == 2)) {
                $contract->code_number = $checkLastAddendum[1]->code_number;
                $contract->title = $checkLastAddendum[1]->title;
                $contract->client_id = (int)$checkLastAddendum[1]->client_id;
                $contract->requester_id = (int)$checkLastAddendum[1]->requester_id;
                $contract->initial_date = $checkLastAddendum[1]->initial_date;
                $contract->start_date = $checkLastAddendum[1]->start_date;
                $contract->end_date = $checkLastAddendum[1]->end_date;
                $contract->extras = $checkLastAddendum[1]->extras;
                $contract->is_posted = $checkLastAddendum[1]->is_posted;
                $contract->contract_history_id_active = $checkLastAddendum[1]->id;
                $current_time = date('Y-m-d H:i:s');
                $contract->updated_at = $current_time;
                $contract->save();
            }
            $history->delete();
            $logProperties = [
                "log_type" => "deleted_contract_history",
                "old" => $logDataOld,
                "new" => null
            ];

            $this->logService->addLogContractHistory($history->id, auth()->user()->id, "Deleted", $logProperties, null);
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Dihapus", "data" => $history, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
