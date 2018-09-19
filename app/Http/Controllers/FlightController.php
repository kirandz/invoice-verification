<?php

namespace App\Http\Controllers;

use App\ACType;
use App\Flight;
use App\FlightDetail;
use App\HajjFlight;
use App\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\DB;

class FlightController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $flights = Flight::all();

        return view('manage.flights', compact('flights'));
    }

    public function update(Request $request)
    {
        Flight::where('id', $request->id)->update([
            'from_period' => $request->from_period,
            'to_period' => $request->to_period,
        ]);

        return ['success' => 'Detail data has been updated'];
    }

    public function delete(Request $request)
    {
        $detail = Flight::find($request->id);
        $detail->delete();

        Invoice::where('from_period', $detail->from_period)->delete();

        return ['success' => 'Detail data has been deleted'];
    }

    public function importpage()
    {
        $charts = Flight::selectRaw('COUNT(fd.id) as total_flight, dep_sched_dt as date')
            ->join('flight_details as fd', 'fd.flight_id', '=', 'flights.id')
            ->groupBy(DB::raw('YEAR(fd.dep_sched_dt), MONTH(fd.dep_sched_dt) ASC'))
            ->whereRaw('dep_sched_dt > DATE_SUB(now(), INTERVAL 12 MONTH) and leg_state = "scheduled"')
            ->get();

        $charts2 = Flight::selectRaw('COUNT(fd.id) as total_flight, dep_sched_dt as date')
            ->join('flight_details as fd', 'fd.flight_id', '=', 'flights.id')
            ->groupBy(DB::raw('YEAR(fd.dep_sched_dt), MONTH(fd.dep_sched_dt) ASC'))
            ->whereRaw('dep_sched_dt > DATE_SUB(now(), INTERVAL 12 MONTH) and leg_state != "scheduled"')
            ->get();

        return view('import.flightdetails', compact('charts', 'charts2'));
    }

    public function import(Request $request)
    {
        $this->validate($request,[
            'from_period' => 'required|unique:flights',
            'to_period' => 'required|unique:flights',
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $path = $request->file('file')->getRealPath();

        if(Excel::load($path)->getSheetNames()[0] == 'DETAIL FLIGHT' || Excel::load($path)->getSheetNames()[1] == 'DETAIL FLIGHT'){

            Flight::insert([
                'from_period' => $request->from_period,
                'to_period' => $request->to_period,
                'created_at' => Carbon::now(),
            ]);

            $detail = Flight::where('from_period', $request->from_period)->where('to_period', $request->to_period)->first();
            $detail_id = $detail->id;

            Excel::selectSheets('DETAIL FLIGHT')->filter('chunk')->load($path)->chunk(500, function ($results) use (
                $detail_id
            ){
                $dataArray_detail = [];
                foreach ($results->toArray() as $row) {
                    if($row['fn_carrier'] == null){
                        break;
                    }

                    $ac_type = ACType::where('ga_type', $row['ac_subtype'])->first();

                    $fn_number = $row['fn_number'];
                    if(!is_numeric(substr($fn_number, -1))){
                        $fn_number = substr($fn_number, 0, -1);
                        if(!is_numeric(substr($fn_number, -1))){
                            $fn_number = substr($fn_number, 0, -1);
                        }
                    }

                    $dep_sched_dt = date("Y-m-d H:i",
                        strtotime(substr($row['dep_sched_dt'], 0,9) ." "
                            .substr($row['dep_sched_dt'], 10,2) .":"
                            .substr($row['dep_sched_dt'], 12,2)
                        )
                    );

                    $arr_sched_dt = date("Y-m-d H:i",
                        strtotime(substr($row['arr_sched_dt'], 0,9) ." "
                            .substr($row['arr_sched_dt'], 10,2) .":"
                            .substr($row['arr_sched_dt'], 12,2)
                        )
                    );

                    if($row['dep_dt'] == ''){
                        $dep_act_dt = '';
                    }
                    else{
                        $dep_act_dt = date("Y-m-d H:i",
                            strtotime(substr($row['dep_dt'], 0,9) ." "
                                .substr($row['dep_dt'], 10,2) .":"
                                .substr($row['dep_dt'], 12,2)
                            )
                        );
                    }

                    if($row['arr_dt'] == ''){
                        $arr_act_dt = '';
                    }
                    else{
                        $arr_act_dt = date("Y-m-d H:i",
                            strtotime(substr($row['arr_dt'], 0,9) ." "
                                .substr($row['arr_dt'], 10,2) .":"
                                .substr($row['arr_dt'], 12,2)
                            )
                        );
                    }

                    if (stripos($row['remarks'], 'flt training') !== false) {
                        $service = 'Flight Training';
                    }
                    elseif (stripos($row['remarks'], 'test flt') !== false) {
                        $service = 'Test Flight';
                    }
                    elseif(strlen($fn_number) == 4 && substr($fn_number, 3) == '0'){
                        $service = 'Charter';
                    }
                    else{
                        $service = 'Regular';
                    }

                    $hajj_flight = HajjFlight::whereRaw('fn_number = ? and valid_from >= ? and valid_to = ?', [$fn_number, $arr_act_dt, $arr_act_dt])->first();
                    if($hajj_flight != null){
                        $service = 'Hajj';
                    }

                    if(!isset($ac_type->conv_type)){
                        $type = $row['ac_subtype'].'*';
                    }
                    else{
                        $type = $ac_type->conv_type;
                    }

                    array_push($dataArray_detail, [
                        'flight_id' => $detail_id,
                        'fn_carrier' => $row['fn_carrier'],
                        'fn_number' => $fn_number,
                        'ac_version' => $row['ac_version'],
                        'ac_registration' => $row['ac_registration'],
                        'ac_subtype' => $row['ac_subtype'],
                        'type' => $type,
                        'service' => $service,
                        'dep_ap_sched' => $row['dep_ap_sched'],
                        'dep_sched_dt' => $dep_sched_dt,
                        'dep_ap_act' => $row['dep_ap_actual'],
                        'dep_act_dt' => $dep_act_dt,
                        'arr_ap_sched' => $row['arr_ap_sched'],
                        'arr_sched_dt' => $arr_sched_dt,
                        'arr_ap_act' => $row['arr_ap_actual'],
                        'arr_act_dt' => $arr_act_dt,
                        'leg_state' => $row['leg_state'],
                        'remarks' => $row['remarks'],
                        'flight_check_rc' => 'unbilled',
                        'flight_check_tnc' => 'unbilled',
                        'created_at' => Carbon::now(),
                    ]);
                }
                FlightDetail::insert($dataArray_detail);
            });

            return back()->with('success','Insert record successfully.');
        }
        else{
            return back()->with('error','Incorrect worksheet name.');
        };
    }

    public function flightdetails(Request $request)
    {
        $data = FlightDetail::where('flight_id', $request->id)->get();

        return response()->json($data);
    }

    public function downloadfd($id)
    {
        $flight = Flight::find($id);

        $flight_details = FlightDetail::selectRaw('flight_details.fn_carrier as FN_CARRIER, flight_details.fn_number as FN_NUMBER, flight_details.service as SERVICE, flight_details.ac_version as AC_VERSION,
                flight_details.ac_registration as AC_REGISTRATION, flight_details.ac_subtype as AC_SUBTYPE, flight_details.type as AC_TYPE, flight_details.dep_sched_dt as DEP_SCHED_DT, flight_details.dep_ap_sched as DEP_AP_SCHED,
                flight_details.arr_sched_dt as ARR_SCHED_DT, flight_details.arr_ap_sched as ARR_AP_SCHED, flight_details.dep_act_dt as DEP_DT, flight_details.dep_ap_act as DEP_AP_ACT, flight_details.arr_act_dt as ARR_DT,
                flight_details.arr_ap_act as ARR_AP_ACT, flight_details.leg_state as LEG_STATE, flight_details.remarks as REMARKS, IF(flight_check_rc = "billed", "billed", "unbilled") as STATUS_RC, IF(flight_check_tnc = "billed", "billed", "unbilled") as STATUS_TNC')
            ->whereRaw('flight_id = ?', [$id])
            ->get();

        $month = date('M', strtotime($flight->from_period));
        $month_num = date('m', strtotime($flight->from_period));

        $file_name = $month_num.'. '.$month.' Detail Flight';

        return Excel::create($file_name, function($excel) use ($flight_details) {
            $excel->sheet('DETAIL FLIGHT', function($sheet) use ($flight_details)
            {
                $sheet->fromArray($flight_details, null, 'A1', true);
            });
        })->download('xlsx');
    }
}
