<?php

namespace App\Console\Commands\Exclusive;

use App\Employee;
use App\EmployeeContract;
use App\EmployeeContractOld;
use App\EmployeeOld;
use App\EmployeePayslip;
use App\EmployeePayslipOld;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetEncryptionEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-encryption-employee';

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
        dump('starting encryption data');
        $data = EmployeeOld::query()->orderBy('id', 'desc')->get();

        dump(count($data) . ' data akan diproses');
        foreach ($data as $list) {
            try {
                DB::beginTransaction();
                dump('memulai proses id =>'. $list->id);

                // encrypt contract
                $contracts = EmployeeContractOld::query()->where('employee_id', $list->id)->orderBy('id', 'desc')->get();
                foreach ($contracts as $item) {
                    $contract = EmployeeContract::query()->find($item->id);
                    $contract->gaji_pokok = $item->gaji_pokok;
                    $contract->bpjs_ks = $item->bpjs_ks;
                    $contract->bpjs_tk_jht = $item->bpjs_tk_jht;
                    $contract->bpjs_tk_jkk = $item->bpjs_tk_jkk;
                    $contract->bpjs_tk_jkm = $item->bpjs_tk_jkm;
                    $contract->bpjs_tk_jp = $item->bpjs_tk_jp;
                    $contract->pph21 = $item->pph21;
                    $contract->save();
                    dump('contract di enkripsi');
                }

                // encrypt slip gaji
                $payslips = EmployeePayslipOld::query()->where('employee_id', $list->id)->orderBy('id', 'desc')->get();
                foreach ($payslips as $item) {
                    $payslip = EmployeePayslip::query()->find($item->id);
                    $payslip->gaji_pokok = $item->gaji_pokok;
                    $payslip->bpjs_ks = $item->bpjs_ks;
                    $payslip->bpjs_tk_jht = $item->bpjs_tk_jht;
                    $payslip->bpjs_tk_jkk = $item->bpjs_tk_jkk;
                    $payslip->bpjs_tk_jkm = $item->bpjs_tk_jkm;
                    $payslip->bpjs_tk_jp = $item->bpjs_tk_jp;
                    $payslip->pph21 = $item->pph21;
                    $payslip->total_gross_penerimaan = $item->total_gross_penerimaan;
                    $payslip->total_gross_pengurangan = $item->total_gross_pengurangan;
                    $payslip->take_home_pay = $item->take_home_pay;
                    $payslip->save();
                    dump('payslip di enkripsi');
                }

                // encrypt main data
                $employee = Employee::query()->find($list->id);
                $employee->domicile = $list->domicile;
                $employee->phone_number = $list->phone_number;
                $employee->acc_name_another = $list->acc_name_another;
                $employee->nik = $list->nik;
                $employee->npwp = $list->npwp;
                $employee->acc_number_bukopin = $list->acc_number_bukopin;
                $employee->bpjs_ketenagakerjaan = $list->bpjs_ketenagakerjaan;
                $employee->bpjs_kesehatan = $list->bpjs_kesehatan;
                $employee->acc_number_another = $list->acc_number_another;
                $employee->save();
                dump('data utama di enkripsi');


                DB::commit();
                dump('data id => ' . $list->id . ' telah dienkripsi');
            } catch (\Throwable $th) {
                DB::rollBack();
                // throw $th;
                dump('gagal untuk mengenkripsi data id =>' . $list->id);
            }
        }
    }
}
