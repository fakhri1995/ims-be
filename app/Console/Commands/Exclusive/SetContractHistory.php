<?php

namespace App\Console\Commands\Exclusive;

use App\Contract;
use App\ContractHistory;
use App\ContractInvoice;
use App\ContractInvoiceProduct;
use App\ContractInvoiceTemplate;
use App\ContractProduct;
use App\ContractProductTemplate;
use App\ContractProductTemplateValue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetContractHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-contract-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $list = Contract::query()->whereDoesntHave('history')->get();
        dump('total data yang akan di update => ' . count($list));

        try {
            foreach ($list as $data) {
                DB::beginTransaction();

                // set initial
                $history = new ContractHistory();
                $history->category = 'initial';
                $history->contract_id = $data->id;
                $history->code_number = $data->code_number;
                $history->title = $data->title;
                $history->client_id = $data->client_id;
                $history->requester_id = $data->requester_id;
                $history->initial_date = $data->initial_date;
                $history->start_date = $data->start_date;
                $history->end_date = $data->end_date;
                $history->extras = $data->extras;
                $history->created_by = $data->created_by;
                $history->created_at = $data->created_at;
                $history->updated_at = $data->updated_at;
                $history->save();

                // update last id history id
                $data->contract_history_id_active = $history->id;
                $data->save();

                // set new logic relation contract product template
                $service = ContractProduct::query()->where('contract_id', $data->id)->get();
                foreach ($service as $item) {
                    $item->contract_history_id = $history->id;
                    $item->save();

                    $product_template_value = ContractProductTemplateValue::query()->where('contract_id', $data->id)->where('contract_product_id', $item->id)->first();
                    if ($product_template_value) {
                        $product_template_value->contract_history_id = $history->id;
                        $product_template_value->save();
                    }
                }

                $product_template = ContractProductTemplate::query()->where('contract_id', $data->id)->first();
                if ($product_template) {
                    $product_template->contract_history_id = $history->id;
                    $product_template->save();
                }

                $invoice_template = ContractInvoiceTemplate::query()->where('contract_id', $data->id)->first();
                if ($invoice_template) {
                    $invoice_template->contract_history_id = $history->id;
                    $invoice_template->save();
                }
                $invoice = ContractInvoice::query()->where('contract_template_id', $data->id)->first();
                if ($invoice) {
                    $invoice->contract_history_id = $history->id;
                    $invoice->save();
                }

                DB::commit();
            }
            dump("Berhasil Set Contract History");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
