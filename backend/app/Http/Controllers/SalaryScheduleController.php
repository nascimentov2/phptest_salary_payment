<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryScheduleRequest;
use App\Http\Requests\UpdateSalaryScheduleRequest;
use App\Models\SalarySchedule;
use File;

class SalaryScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalaryScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalaryScheduleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalarySchedule  $salarySchedule
     * @return \Illuminate\Http\Response
     */
    public function show(SalarySchedule $salarySchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalarySchedule  $salarySchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(SalarySchedule $salarySchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalaryScheduleRequest  $request
     * @param  \App\Models\SalarySchedule  $salarySchedule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalaryScheduleRequest $request, SalarySchedule $salarySchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalarySchedule  $salarySchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalarySchedule $salarySchedule)
    {
        //
    }

    /**
     * Get payment schedule for the year
     *
     * @param  (int) $year
     * @param  (int|null) $filter
     * @return (array) $schedule
     */
    public static function getYearSchedule($year, $filter=null)
    {
        $months = range(1,12);

        $schedule = [];

        foreach($months as $month)
        {
            $schedule[$month] = [];
        }

        if($year < 1970)
        {
            return ['status' => false, 'message' => 'The year must be higher than 1969'];
        }

        if(!is_null($filter))
        {
            if(!array_key_exists($filter, $schedule))
            {
                return ['status' => false, 'message' => 'Invalid filter'];
            }
        }

        foreach($schedule as $month => $m_data)
        {
            $schedule[$month]['payment_day']= self::paymentDay($month, $year);
            $schedule[$month]['bonus_day']  = self::bonusDay($month, $year);
            $schedule[$month]['month_name'] = date('F', strtotime($year.'-'.$month.'-01'));
        }

        $return = ['status' => true, 'message' => 'Schedule generated sucessfully!'];

        if(is_null($filter))
        {
            $return['schedule'] = $schedule;
        }
        else
        {
            $return['schedule'][$filter] = $schedule[$filter];
        }

        return $return;
    }

    /**
     * Generates payment day
     *
     * @param  (int) $month
     * @param  (int) $year
     * @return (array) $schedule
     */
    public static function generateFiles($folder, $filename, $filedata, $format='csv')
    {
        $path = storage_path('app/public/csv/'.$folder.'/');

        File::ensureDirectoryExists($path);

        $file = 'payment_'.$filename.'.csv';

        $f_open = fopen($path.$file, 'w');

        $columns = ['month', 'payment', 'bonus'];

        fputcsv($f_open, $columns);

        foreach($filedata['schedule'] as $month => $data)
        {
            $data_to_csv = ['month' => $data['month_name'], 'payment' => $data['payment_day'], 'bonus' => $data['bonus_day']];

            fputcsv($f_open, $data_to_csv);
        }

        fclose($f_open);

        return $path.$file;
    }

    /**
     * Generates payment day
     *
     * @param  (int) $month
     * @param  (int) $year
     * @return (array) $schedule
     */
    private static function paymentDay($month, $year, $param_day='w')
    {
        $last_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $day_of_the_week = date($param_day, strtotime($year.'-'.$month.'-'.$last_day));

        switch($day_of_the_week)
        {
            //Sunday
            case 0:
                //payment on 
                $day_of_payment = $last_day-2;
                break;

            //Sunday
            case 6:
                //payment on 
                $day_of_payment = $last_day-1;
                break;

            //Default
            default:
                //sameday
                $day_of_payment = $last_day;
                break;
        }

        return $day_of_payment;
    }

    /**
     * Generates bonus day
     *
     * @param  (int) $month
     * @param  (int) $year
     * @return (array) $schedule
     */
    private static function bonusDay($month, $year, $bonus_day=15)
    {
        $day_of_the_week = date('w', strtotime($year.'-'.$month.'-'.$bonus_day));

        switch($day_of_the_week)
        {
            //Sunday
            case 0:
                //payment on 
                $day_of_payment = $bonus_day+3;
                break;

            //Saturday
            case 6:
                //payment on 
                $day_of_payment = $bonus_day+4;
                break;

            //Default
            default:
                //sameday
                $day_of_payment = $bonus_day;
                break;
        }

        return $day_of_payment;
    }
}
