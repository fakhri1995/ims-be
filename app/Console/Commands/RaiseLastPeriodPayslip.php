<?php

namespace App\Console\Commands;

use App\Services\EmployeeService;
use Illuminate\Console\Command;

class RaiseLastPeriodPayslip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:raise-last-period-payslip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Raise last period payslip';

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
        $employeeService = new EmployeeService;
        $employeeService->raiseLastPeriodPayslipFunction();    
    }
}
