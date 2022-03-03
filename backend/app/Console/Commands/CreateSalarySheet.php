<?php

namespace App\Console\Commands;

use File;

use Illuminate\Console\Command;

use App\Http\Controllers\SalaryScheduleController as SalarySchedule;

class CreateSalarySheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:create_sheet {--month=all} {--year=current}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a salary sheet based on rules specified on config/salary.php';

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
     * @return int
     */
    public function handle()
    {
        //if all will generate all months in the year
        $opt_month = $this->option('month');

        if($opt_month == 'all')
        {
            $month = null;
        }
        else
        {
            if(!is_numeric($opt_month))
            {
                $this->info('The month must be a numeric value');

                return 0;
            }

            $month = $opt_month;
        }

        //if current will generate for the current year
        $opt_year = $this->option('year');

        if($opt_year == 'current')
        {
            $year = date('Y', time());
        }
        else
        {
            if(!is_numeric($opt_year))
            {
                $this->info('The year must be a numeric value');

                return 0;
            }

            $year = $opt_year;
        }
        
        $schedule = SalarySchedule::getYearSchedule($year, $month);
        
        if($schedule['status'] === true)
        {
            $path = storage_path('app/public/csv/'.$year.'/');

            File::ensureDirectoryExists($path);

            $file = 'payment_'.$opt_month.'.csv';

            $f_open = fopen($path.$file, 'w');

            $columns = ['month', 'payment', 'bonus'];

            fputcsv($f_open, $columns);

            foreach($schedule['schedule'] as $month => $data)
            {
                $data_to_csv = ['month' => $month, 'payment' => $data['payment_day'], 'bonus' => $data['bonus_day']];

                fputcsv($f_open, $data_to_csv);
            }

            fclose($f_open);

            $this->info($schedule['message']);
        }
        else
        {
            $this->info($schedule['message']);
        }

        return 0;
    }
}