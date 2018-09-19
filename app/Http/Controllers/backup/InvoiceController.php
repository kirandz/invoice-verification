<?php

namespace App\Http\Controllers;

use App\ACType;
use App\Airport;
use App\Flight;
use App\FlightDetail;
use App\HajjFlight;
use App\Invoice;
use App\InvoiceDetail;
use App\Pivot;
use App\RouteUnit;
use App\UnbilledDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $invoices = Invoice::all();

        return view('manage.invoices', compact('invoices'));
    }

    public function update(Request $request)
    {
        Invoice::where('id', $request->id)->update([
            'invoice_num' => $request->invoice_num,
            'from_period' => $request->from_period,
            'to_period' => $request->to_period,
            'charge_type' => $request->charge_type,
            'flight_type' => $request->flight_type
        ]);

        return ['success' => 'Invoice data has been updated'];
    }

    public function delete(Request $request)
    {
        $invoices = Invoice::find($request->id);

        $invoices->delete();

        return ['success' => 'Invoice data has been deleted'];
    }

    public function importpage()
    {
        $flights = Flight::orderBy('from_period')->get();

        $charts = Invoice::selectRaw('COUNT(invoice_details.id) as total_flight, date')
            ->join('invoice_details', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->groupBy(DB::raw('YEAR(invoice_details.date), MONTH(invoice_details.date) ASC'))
            ->whereRaw('date > DATE_SUB(now(), INTERVAL 12 MONTH)')
            ->get();

        return view('import.invoices', compact('flights', 'charts'));
    }

    public function flightperiod(Request $request)
    {
        $data = Flight::find($request->id);

        return response()->json($data);
    }

    public function import(Request $request)
    {
        if($request->flight_type == 'DOM'){
            $this->validate($request,[
                'invoice_type' => 'required',
                'flight_type' => 'required',
                'invoice_num' => 'required|unique:invoices',
                'from_period' => 'required',
                'to_period' => 'required',
                'file' => 'required|mimes:xlsx,xls',
            ]);
        }
        else{
            $this->validate($request,[
                'invoice_type' => 'required',
                'flight_type' => 'required',
                'ex_rate' => 'required|numeric|min:1',
                'invoice_num' => 'required|unique:invoices',
                'from_period' => 'required',
                'to_period' => 'required',
                'file' => 'required|mimes:xlsx,xls',
            ]);
        }

        $path = $request->file('file')->getRealPath();

        if(Excel::load($path)->getSheetNames()[0] == 'INVOICE' || Excel::load($path)->getSheetNames()[1] == 'INVOICE'){

            Invoice::insert([
                'invoice_num' => $request->invoice_num,
                'from_period' => $request->from_period,
                'to_period' => $request->to_period,
                'charge_type' => $request->invoice_type,
                'flight_type' => $request->flight_type,
                'created_at' => Carbon::now(),
            ]);

            $invoice = Invoice::where('invoice_num', $request->invoice_num)->first();
            $invoice_id = $invoice->id;
            $invoice_type = $request->invoice_type;
            $flight_type = $request->flight_type;
            $ex_rate = $request->ex_rate;

            config(['excel.import.dates.columns' => [
                'date',
                'time_in',
                'time_out',
            ]]);

            Excel::selectSheets('INVOICE')->filter('chunk')->load($path)->chunk(500, function ($results) use (
                $flight_type, $invoice_id, $invoice_type, $ex_rate
            ){
                $dataArray_invoice = [];

                foreach ($results->toArray() as $row) {
                    if($row['aerodrome'] == null){
                        break;
                    }

                    $ac_type = ACType::where('ac_type', $row['type'])->first();
                    $departure = Airport::where('icao_code', $row['from'])->first();
                    $arrival = Airport::where('icao_code', $row['aerodrome'])->first();

                    $time_out = date("H:i", strtotime($row['time_out']));
                    $date = date("Y-m-d", strtotime($row['date']));
                    $flight_num = $row['flight_no'];

                    if($invoice_type == 'Route Charge'){
                        $time_in = date("H:i", strtotime($row['time_in']));

                        if($flight_type == 'DOM'){
                            $route_unit = RouteUnit::whereRaw('from_date <= (?) and to_date >= (?) and flight_type = "DOM"')
                                ->setBindings([$date, $date])
                                ->first();

                            if(!isset($route_unit->charge)){
                                $charge = 0;
                            }
                            else{
                                $charge = $route_unit->charge;
                            }

                            $route_charge = $row['route_unit']*$charge;
                        }
                        else{
                            $route_unit = RouteUnit::whereRaw('from_date <= (?) and to_date >= (?) and flight_type = "INT"')
                                ->setBindings([$date, $date])
                                ->first();

                            if(!isset($route_unit->charge)){
                                $charge = 0;
                            }
                            else{
                                $charge = $route_unit->charge;
                            }

                            $route_charge = round($row['route_unit']*$charge,2)*$ex_rate;
                        }
                    }
                    elseif($invoice_type == 'TNC'){
                        if($flight_type == 'DOM'){
                            $terminal_charge = $row['total_mtow']*$row['charge'];
                        }
                        else{
                            $terminal_charge = round($row['total_mtow']*$row['charge'],2)*$ex_rate;
                        }
                    }

                    if(!is_numeric(substr($flight_num, -1))){
                        $flight_num = substr($flight_num, 0, -1);
                        if(!is_numeric(substr($flight_num, -1))){
                            $flight_num = substr($flight_num, 0, -1);
                        }
                    }

                    for($i=0 ; $i<strlen($flight_num) ; $i++){
                        if(!is_numeric($flight_num[$i])){
                            $fn_number = substr($flight_num, ($i+1));
                        }
                    }
                    $flight_num = 'GIA'.$fn_number;

                    if(substr($fn_number,0,1) == '0'){
                        $fn_number = substr($fn_number, 1);
                        if(substr($fn_number,0,1) == '0'){
                            $fn_number = substr($fn_number, 1);;
                        }
                    }

                    //SERVICE
                    if(strlen($fn_number) == 4 && substr($fn_number, 3) == '0'){
                        $service = 'Charter';
                    }
                    else{
                        $service = 'Regular';
                    }

                    $hajj_flight = HajjFlight::whereRaw('fn_number = ? and valid_from <= ? and valid_to >= ?', [$fn_number, $date, $date])->first();
                    if($hajj_flight != null){
                        $service = 'Hajj';
                    }

                    //CHECK NULL
                    if(!isset($arrival->iata_code)){
                        $arr_code = $row['aerodrome'].'*';
                    }
                    else{
                        $arr_code = $arrival->iata_code;
                    }
                    if(!isset($departure->iata_code)){
                        $dept_code = $row['from'].'*';
                    }
                    else{
                        $dept_code = $departure->iata_code;
                    }
                    if(!isset($ac_type->conv_type)){
                        $type = $row['type'].'*';
                    }
                    else{
                        $type = $ac_type->conv_type;
                    }

                    if($invoice_type == 'Route Charge'){
                        array_push($dataArray_invoice, [
                            'invoice_id' => $invoice_id,
                            'airport_from' => $row['from'],
                            'departure' => $dept_code,
                            'aerodrome' => $row['aerodrome'],
                            'arrival' => $arr_code,
                            'date' => $date,
                            'flight_num' => $flight_num,
                            'fn_number' => $fn_number,
                            'ac_register' => $row['ac_register'],
                            'ac_type' => $row['type'],
                            'type' => $type,
                            'service' => $service,
                            'point_in' => $row['point_in'],
                            'time_in' => $time_in,
                            'point_out' => $row['point_out'],
                            'time_out' => $time_out,
                            'distance' => $row['distance'],
                            'weight' => $row['weight'],
                            'route_unit' => $row['route_unit'],
                            'route_charge' => $route_charge,
                            'remarks' => 'OK',
                            'fn_updated' => $flight_num,
                            'created_at' => Carbon::now(),
                        ]);
                    }
                    elseif($invoice_type == 'TNC'){
                        array_push($dataArray_invoice, [
                            'invoice_id' => $invoice_id,
                            'airport_from' => $row['from'],
                            'departure' => $dept_code,
                            'aerodrome' => $row['aerodrome'],
                            'arrival' => $arr_code,
                            'date' => $date,
                            'flight_num' => $flight_num,
                            'fn_number' => $fn_number,
                            'ac_register' => $row['ac_register'],
                            'ac_type' => $row['type'],
                            'type' => $type,
                            'service' => $service,
                            'time_out' => $time_out,
                            'mtow' => $row['mtow'],
                            'category' => $row['category'],
                            'tg' => $row['tg'],
                            'total_mtow' => $row['total_mtow'],
                            'charge' => $row['charge'],
                            'terminal_charge' => $terminal_charge,
                            'remarks' => 'OK',
                            'fn_updated' => $flight_num,
                            'created_at' => Carbon::now(),
                        ]);
                    }
                }
                InvoiceDetail::insert($dataArray_invoice);
            });

            //Pivot, Unbilled
            $flight = Flight::selectRaw('flights.id')
                ->where('invoices.id', $invoice_id)
                ->join('invoices', 'invoices.from_period', '=', 'flights.from_period')
                ->first();

            if($flight_type == 'DOM'){
                $pivots = InvoiceDetail::selectRaw('(?) as invoice_id, (?) as flight_id, (?) as created_at, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type,
                    COUNT(invoice_details.flight_num) as total_invoice, IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail,
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as difference,
                    IF(IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) < 0, "EXCESS", "OK") as remarks')
                    ->leftJoin(DB::raw('(SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                    FROM invoice_details i 
                    JOIN(
                        SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                        FROM invoices
                        JOIN flights f ON f.from_period = invoices.from_period
                        JOIN flight_details fd ON fd.flight_id = f.id
                        WHERE invoices.id = (?)
                        GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                    )AS d ON i.fn_number = d.fn_number
                    WHERE departure = dep_ap_act and arrival = arr_ap_act and i.type = d.type
                    GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail)
                    as piv'),
                        function($join)
                        {
                            $join->on('invoice_details.id', '=' ,'piv.id');
                        })->setBindings([$invoice_id, $flight->id, Carbon::now(), $invoice_id])
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->groupBy(DB::raw('invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type, total_detail, invoice_details.service'))
                    ->get()->toArray();

                $inv_remarks = InvoiceDetail::selectRaw('id')
                    ->join(DB::raw('(SELECT invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, 
                    invoice_details.arrival, invoice_details.type, from_period, to_period, COUNT(invoice_details.flight_num) as total_invoice, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as diff
                    FROM invoice_details
                    LEFT JOIN
                    (
                        SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                        FROM invoice_details i 
                        JOIN(
                            SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                            FROM invoices
                            JOIN flights f ON f.from_period = invoices.from_period
                            JOIN flight_details fd ON fd.flight_id = f.id
                            WHERE invoices.id = (?)
                            GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                        )AS d ON i.fn_number = d.fn_number
                        WHERE departure = dep_ap_act and arrival = arr_ap_act and i.type = d.type
                        GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail
                    )as piv ON invoice_details.id = piv.id
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE invoice_details.invoice_id = (?)
                    GROUP BY invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, 
                    invoice_details.type, total_detail, from_period, to_period)
                    as x'),
                        function($join)
                        {
                            $join->on('x.fn_number', '=' ,'invoice_details.fn_number');
                        })->setBindings([$invoice_id, $invoice_id])
                    ->whereRaw('invoice_details.departure = x.departure and invoice_details.arrival = x.arrival and invoice_details.type = x.type and x.diff < 0')
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->get();
            }
            else{
                $pivots = InvoiceDetail::selectRaw('(?) as invoice_id, (?) as flight_id, (?) as created_at, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type,
                    COUNT(invoice_details.flight_num) as total_invoice, IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail,
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as difference,
                    IF(IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) < 0, "EXCESS", "OK") as remarks')
                    ->leftJoin(DB::raw('(SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                    FROM invoice_details i 
                    JOIN(
                        SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                        FROM invoices
                        JOIN flights f ON f.from_period = invoices.from_period
                        JOIN flight_details fd ON fd.flight_id = f.id
                        WHERE invoices.id = (?)
                        GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                    )AS d ON i.fn_number = d.fn_number
                    WHERE (departure = dep_ap_act or departure = arr_ap_act) and (arrival = dep_ap_act or arrival = arr_ap_act) and i.type = d.type
                    GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail)
                    as piv'),
                        function ($join) {
                            $join->on('invoice_details.id', '=', 'piv.id');
                        })->setBindings([$invoice_id, $flight->id, Carbon::now(), $invoice_id])
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->groupBy(DB::raw('invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type, total_detail, invoice_details.service'))
                    ->get()->toArray();

                $inv_remarks = InvoiceDetail::selectRaw('id')
                    ->join(DB::raw('(SELECT invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, 
                    invoice_details.arrival, invoice_details.type, from_period, to_period, COUNT(invoice_details.flight_num) as total_invoice, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as diff
                    FROM invoice_details
                    LEFT JOIN
                    (
                        SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                        FROM invoice_details i 
                        JOIN(
                            SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                            FROM invoices
                            JOIN flights f ON f.from_period = invoices.from_period
                            JOIN flight_details fd ON fd.flight_id = f.id
                            WHERE invoices.id = (?)
                            GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                        )AS d ON i.fn_number = d.fn_number
                        WHERE (departure = dep_ap_act or departure = arr_ap_act) and (arrival = dep_ap_act or arrival = arr_ap_act) and i.type = d.type
                        GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail
                    )as piv ON invoice_details.id = piv.id
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE invoice_details.invoice_id = (?)
                    GROUP BY invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, 
                    invoice_details.type, total_detail, from_period, to_period)
                    as x'),
                        function($join)
                        {
                            $join->on('x.fn_number', '=' ,'invoice_details.fn_number');
                        })->setBindings([$invoice_id, $invoice_id])
                    ->whereRaw('invoice_details.departure = x.departure and invoice_details.arrival = x.arrival and invoice_details.type = x.type and x.diff < 0')
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->get();
            }

            $pivot = new Pivot();
            $pivot->insert($pivots);

            for($i = 0 ; $i < count($inv_remarks) ; $i++){
                $invoice = InvoiceDetail::find($inv_remarks[$i]->id);
                $invoice->remarks = 'EXCESS';
                $invoice->save();
            }

            if($flight_type == 'DOM'){
                $unbilled = FlightDetail::selectRaw('(?) as invoice_id, flight_details.id as fd_id, (?) as created_at')
                    ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                    ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                    ->whereRaw('flight_details.id NOT IN(
                    SELECT fd.id
                    FROM invoices i
                    JOIN flights as f ON f.from_period = i.from_period
                    JOIN flight_details fd ON fd.flight_id = f.id
                    JOIN pivots p ON p.fn_number = fd.fn_number
                    JOIN(
                        SELECT *
                        FROM invoice_details
                        WHERE invoice_id = (?)
                    )as x ON x.fn_number = fd.fn_number
                    WHERE fd.fn_number = x.fn_number and dep_ap_act = x.departure and arr_ap_act = x.arrival and fd.type = x.type 
                    and (DATE(arr_act_dt) = x.date or (p.departure = dep_ap_act and p.arrival = arr_ap_act and p.type = fd.type 
                    and p.difference <= 0)) and i.id = (?) and p.invoice_id = (?)
                    GROUP BY fd.id
                )and i.id = (?)')
                    ->setBindings([$invoice_id, Carbon::now(), $invoice_id, $invoice_id, $invoice_id, $invoice_id])
                    ->get()->toArray();
            }
            else{
                $unbilled = FlightDetail::selectRaw('(?) as invoice_id, flight_details.id as fd_id, (?) as created_at')
                    ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                    ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                    ->whereRaw('flight_details.id NOT IN(
                    SELECT fd.id
                    FROM invoices i
                    JOIN flights as f ON f.from_period = i.from_period
                    JOIN flight_details fd ON fd.flight_id = f.id
                    JOIN pivots p ON p.fn_number = fd.fn_number
                    JOIN(
                        SELECT *
                        FROM invoice_details
                        WHERE invoice_id = (?)
                    )as x ON x.fn_number = fd.fn_number
                    WHERE fd.fn_number = x.fn_number and (dep_ap_act = x.departure or dep_ap_act = x.arrival) and 
                    (arr_ap_act = x.arrival or arr_ap_act = x.departure) and fd.type = x.type and (DATE(arr_act_dt) = x.date 
                    or ((p.departure = dep_ap_act or p.departure = arr_ap_act) and (p.arrival = arr_ap_act or p.arrival = dep_ap_act)
                    and p.type = fd.type and p.difference <= 0)) and i.id = (?) and p.invoice_id = (?)
                    GROUP BY fd.id
                )and i.id = (?)')
                    ->setBindings([$invoice_id, Carbon::now(), $invoice_id, $invoice_id, $invoice_id, $invoice_id])
                    ->get()->toArray();
            }

            $unbilled_details = new UnbilledDetail();
            $unbilled_details->insert($unbilled);

            $flight_check = FlightDetail::selectRaw('flight_details.*')
                ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                ->whereRaw('flight_details.id NOT IN (
                SELECT fd.id
                FROM unbilled_details ub
                JOIN invoices i ON i.id = ub.invoice_id
                JOIN flight_details fd ON fd.id = ub.fd_id
                WHERE i.id = (?)) 
                and i.id = (?)')->setBindings([$invoice_id, $invoice_id])
                ->get();

            if($invoice_type == 'Route Charge'){
                for($i = 0 ; $i < count($flight_check) ; $i++){
                    $flight_detail = FlightDetail::find($flight_check[$i]->id);
                    $flight_detail->flight_check_rc = 'billed';
                    $flight_detail->save();
                }
            }
            elseif($invoice_type == 'TNC'){
                for($i = 0 ; $i < count($flight_check) ; $i++){
                    $flight_detail = FlightDetail::find($flight_check[$i]->id);
                    $flight_detail->flight_check_tnc = 'billed';
                    $flight_detail->save();
                }

                $inv_details_update = InvoiceDetail::selectRaw('invoice_details.id, idrc.remarks')
                    ->join(DB::raw('(SELECT fn_number, departure, arrival, type, date, time_out, id.remarks
                        FROM invoices as irc
                        JOIN invoices as i ON i.from_period = irc.from_period
                        JOIN invoice_details as id ON id.invoice_id = irc.id
                        WHERE i.id = (?) and i.flight_type = irc.flight_type and irc.charge_type = "Route Charge"
                        GROUP BY id.id)
                        as idrc'),
                        function($join)
                        {
                            $join->on('idrc.fn_number', '=' ,'invoice_details.fn_number');
                        })->setBindings([$invoice_id])
                    ->whereRaw('invoice_details.departure = idrc.departure and invoice_details.arrival = idrc.arrival and invoice_details.type = idrc.type and 
                        invoice_details.date = idrc.date and invoice_details.time_out = idrc.time_out and invoice_details.invoice_id = ?', [$invoice_id])
                    ->get();

                for($i = 0 ; $i < count($inv_details_update) ; $i++){
                    $invoice_detail = InvoiceDetail::find($inv_details_update[$i]->id);
                    $invoice_detail->remarks = $inv_details_update[$i]->remarks;
                    $invoice_detail->save();
                }

                $pivots_update = Pivot::selectRaw('pivots.id, prc.remarks')
                    ->join(DB::raw('(SELECT fn_number, departure, arrival, type, p.remarks
                        FROM invoices as irc
                        JOIN invoices as i ON i.from_period = irc.from_period
                        JOIN pivots as p ON p.invoice_id = irc.id
                        WHERE i.id = (?) and i.flight_type = irc.flight_type and irc.charge_type = "Route Charge"
                        GROUP BY p.id)
                        as prc'),
                        function($join)
                        {
                            $join->on('prc.fn_number', '=' ,'pivots.fn_number');
                        })->setBindings([$invoice_id])
                    ->whereRaw('pivots.departure = prc.departure and pivots.arrival = prc.arrival and pivots.type = prc.type and 
                        pivots.invoice_id = ?', [$invoice_id])
                    ->get();

                for($i = 0 ; $i < count($pivots_update) ; $i++){
                    $invoice_detail = Pivot::find($pivots_update[$i]->id);
                    $invoice_detail->remarks = $pivots_update[$i]->remarks;
                    $invoice_detail->save();
                }
            }

            return back()->with('success','Insert record successfully.');
        }
        else{
            return back()->with('error','Incorrect worksheet name.');
        }
    }

    public function importaddpage()
    {
        $invoices = Invoice::orderBy('from_period')->get();

        $charts = Invoice::selectRaw('COUNT(invoice_details.id) as total_flight, date')
            ->join('invoice_details', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->groupBy(DB::raw('YEAR(invoice_details.date), MONTH(invoice_details.date) ASC'))
            ->get();

        return view('import.addinvoices', compact('invoices', 'charts'));
    }

    public function importadd(Request $request)
    {
        if($request->flight_type == 'DOM'){
            $this->validate($request,[
                'unbilled_inv_id' => 'required',
                'invoice_num' => 'required|unique:invoices',
                'file' => 'required|mimes:xlsx,xls',
            ]);
        }
        else{
            $this->validate($request,[
                'unbilled_inv_id' => 'required',
                'ex_rate' => 'required|numeric|min:1',
                'invoice_num' => 'required|unique:invoices',
                'file' => 'required|mimes:xlsx,xls',
            ]);
        }

        $path = $request->file('file')->getRealPath();

        if(Excel::load($path)->getSheetNames()[0] == 'INVOICE' || Excel::load($path)->getSheetNames()[1] == 'INVOICE'){
            $temp = Invoice::where('id', $request->unbilled_inv_id)->first();

            Invoice::insert([
                'invoice_num' => $request->invoice_num,
                'from_period' => $temp->from_period,
                'to_period' => $temp->to_period,
                'charge_type' => $temp->charge_type,
                'flight_type' => $temp->flight_type,
                'created_at' => Carbon::now(),
            ]);

            $invoice = Invoice::where('invoice_num', $request->invoice_num)->first();
            $invoice_id = $invoice->id;
            $unbilled_inv_id = $request->unbilled_inv_id;
            $invoice_type = $temp->charge_type;
            $flight_type = $temp->flight_type;
            $ex_rate = $request->ex_rate;

            config(['excel.import.dates.columns' => [
                'date',
                'time_in',
                'time_out',
            ]]);

            Excel::selectSheets('INVOICE')->filter('chunk')->load($path)->chunk(500, function ($results) use (
                $flight_type, $invoice_id, $invoice_type, $ex_rate
            ){
                $dataArray_invoice = [];

                foreach ($results->toArray() as $row) {
                    if($row['aerodrome'] == null){
                        break;
                    }

                    $ac_type = ACType::where('ac_type', $row['type'])->first();
                    $departure = Airport::where('icao_code', $row['from'])->first();
                    $arrival = Airport::where('icao_code', $row['aerodrome'])->first();

                    $time_out = date("H:i", strtotime($row['time_out']));
                    $date = date("Y-m-d", strtotime($row['date']));
                    $flight_num = $row['flight_no'];

                    if($invoice_type == 'Route Charge'){
                        $time_in = date("H:i", strtotime($row['time_in']));

                        if($flight_type == 'DOM'){
                            $route_unit = RouteUnit::whereRaw('from_date <= (?) and to_date >= (?) and flight_type = "DOM"')
                                ->setBindings([$date, $date])
                                ->first();

                            if(!isset($route_unit->charge)){
                                $charge = 0;
                            }
                            else{
                                $charge = $route_unit->charge;
                            }

                            $route_charge = $row['route_unit']*$charge;
                        }
                        else{
                            $route_unit = RouteUnit::whereRaw('from_date <= (?) and to_date >= (?) and flight_type = "INT"')
                                ->setBindings([$date, $date])
                                ->first();

                            if(!isset($route_unit->charge)){
                                $charge = 0;
                            }
                            else{
                                $charge = $route_unit->charge;
                            }

                            $route_charge = round($row['route_unit']*$charge,2)*$ex_rate;
                        }
                    }
                    elseif($invoice_type == 'TNC'){
                        if($flight_type == 'DOM'){
                            $terminal_charge = $row['total_mtow']*$row['charge'];
                        }
                        else{
                            $terminal_charge = round($row['total_mtow']*$row['charge'],2)*$ex_rate;
                        }
                    }

                    if(!is_numeric(substr($flight_num, -1))){
                        $flight_num = substr($flight_num, 0, -1);
                        if(!is_numeric(substr($flight_num, -1))){
                            $flight_num = substr($flight_num, 0, -1);
                        }
                    }

                    for($i=0 ; $i<strlen($flight_num) ; $i++){
                        if(!is_numeric($flight_num[$i])){
                            $fn_number = substr($flight_num, ($i+1));
                        }
                    }

                    if(substr($fn_number,0,1) == '0'){
                        $fn_number = substr($fn_number, 1);
                        if(substr($fn_number,0,1) == '0'){
                            $fn_number = substr($fn_number, 1);;
                        }
                    }

                    if(strlen($fn_number) == 4 && substr($fn_number, 3) == '0'){
                        $service = 'Charter';
                    }
                    else{
                        $service = 'Regular';
                    }

                    $hajj_flight = HajjFlight::whereRaw('fn_number = ? and valid_from <= ? and valid_to >= ?', [$fn_number, $date, $date])->first();
                    if($hajj_flight != null){
                        $service = 'Hajj';
                    }

                    if(!isset($arrival->iata_code)){
                        $arr_code = $row['aerodrome'].'*';
                    }
                    else{
                        $arr_code = $arrival->iata_code;
                    }
                    if(!isset($departure->iata_code)){
                        $dept_code = $row['from'].'*';
                    }
                    else{
                        $dept_code = $departure->iata_code;
                    }
                    if(!isset($ac_type->conv_type)){
                        $type = $row['type'].'*';
                    }
                    else{
                        $type = $ac_type->conv_type;
                    }

                    $flight_training = FlightDetail::where('service', 'Flight Training')->get();
                    $test_flight = FlightDetail::where('service', 'Test Flight')->get();

                    foreach($flight_training as $a){
                        if($fn_number == $a->fn_number && $type == $a->type && $dept_code == $a->dep_ap_act && $arr_code == $a->arr_ap_act){
                            $service = 'Flight Training';
                        }
                    }
                    foreach($test_flight as $b){
                        if($fn_number == $b->fn_number && $type == $b->type && $dept_code == $b->dep_ap_act && $arr_code == $b->arr_ap_act){
                            $service = 'Test Flight';
                        }
                    }

                    if($invoice_type == 'Route Charge'){
                        array_push($dataArray_invoice, [
                            'invoice_id' => $invoice_id,
                            'airport_from' => $row['from'],
                            'departure' => $dept_code,
                            'aerodrome' => $row['aerodrome'],
                            'arrival' => $arr_code,
                            'date' => $date,
                            'flight_num' => $flight_num,
                            'fn_number' => $fn_number,
                            'ac_register' => $row['ac_register'],
                            'ac_type' => $row['type'],
                            'type' => $type,
                            'service' => $service,
                            'point_in' => $row['point_in'],
                            'time_in' => $time_in,
                            'point_out' => $row['point_out'],
                            'time_out' => $time_out,
                            'distance' => $row['distance'],
                            'weight' => $row['weight'],
                            'route_unit' => $row['route_unit'],
                            'route_charge' => $route_charge,
                            'remarks' => 'OK',
                            'fn_updated' => $flight_num,
                            'created_at' => Carbon::now(),
                        ]);
                    }
                    elseif($invoice_type == 'TNC'){
                        array_push($dataArray_invoice, [
                            'invoice_id' => $invoice_id,
                            'airport_from' => $row['from'],
                            'departure' => $dept_code,
                            'aerodrome' => $row['aerodrome'],
                            'arrival' => $arr_code,
                            'date' => $date,
                            'flight_num' => $flight_num,
                            'fn_number' => $fn_number,
                            'ac_register' => $row['ac_register'],
                            'ac_type' => $row['type'],
                            'type' => $type,
                            'service' => $service,
                            'time_out' => $time_out,
                            'mtow' => $row['mtow'],
                            'category' => $row['category'],
                            'tg' => $row['tg'],
                            'total_mtow' => $row['total_mtow'],
                            'charge' => $row['charge'],
                            'terminal_charge' => $terminal_charge,
                            'remarks' => 'OK',
                            'fn_updated' => $flight_num,
                            'created_at' => Carbon::now(),
                        ]);
                    }
                }
                InvoiceDetail::insert($dataArray_invoice);
            });

            //Pivot
            $flight = Flight::selectRaw('flights.id')
                ->where('invoices.id', $invoice_id)
                ->join('invoices', 'invoices.from_period', '=', 'flights.from_period')
                ->first();

            if($flight_type == 'DOM'){
                $pivots = InvoiceDetail::selectRaw('(?) as invoice_id, (?) as flight_id, (?) as created_at, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type,
                    COUNT(invoice_details.flight_num) as total_invoice, IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail,
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as difference,
                    IF(IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) < 0, "EXCESS", "OK") as remarks')
                    ->leftJoin(DB::raw('(SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                    FROM invoice_details i 
                    JOIN(
                        SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                        FROM invoices
                        JOIN flights f ON f.from_period = invoices.from_period
                        JOIN (
                                SELECT fd.*
                                FROM unbilled_details ub
                                JOIN flight_details as fd ON fd.id = ub.fd_id
                                WHERE ub.invoice_id = (?)
                            )as fd ON fd.flight_id = f.id
                        WHERE invoices.id = (?)
                        GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                    )AS d ON i.fn_number = d.fn_number
                    WHERE departure = dep_ap_act and arrival = arr_ap_act and i.type = d.type
                    GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail)
                    as piv'),
                        function($join)
                        {
                            $join->on('invoice_details.id', '=' ,'piv.id');
                        })->setBindings([$invoice_id, $flight->id, Carbon::now(), $unbilled_inv_id, $invoice_id])
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->groupBy(DB::raw('invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type, total_detail, invoice_details.service'))
                    ->get()->toArray();

                $inv_remarks = InvoiceDetail::selectRaw('id')
                    ->join(DB::raw('(SELECT invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, 
                    invoice_details.arrival, invoice_details.type, from_period, to_period, COUNT(invoice_details.flight_num) as total_invoice, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as diff
                    FROM invoice_details
                    LEFT JOIN
                    (
                        SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                        FROM invoice_details i 
                        JOIN(
                            SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                            FROM invoices
                            JOIN flights f ON f.from_period = invoices.from_period
                            JOIN (
                                SELECT fd.*
                                FROM unbilled_details ub
                                JOIN flight_details as fd ON fd.id = ub.fd_id
                                WHERE ub.invoice_id = (?)
                            )as fd ON fd.flight_id = f.id
                            WHERE invoices.id = (?)
                            GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                        )AS d ON i.fn_number = d.fn_number
                        WHERE departure = dep_ap_act and arrival = arr_ap_act and i.type = d.type
                        GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail
                    )as piv ON invoice_details.id = piv.id
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE invoice_details.invoice_id = (?)
                    GROUP BY invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, 
                    invoice_details.type, total_detail, from_period, to_period
                    )as x'),
                        function($join)
                        {
                            $join->on('x.fn_number', '=' ,'invoice_details.fn_number');
                        })->setBindings([$unbilled_inv_id, $invoice_id, $invoice_id])
                    ->whereRaw('invoice_details.departure = x.departure and invoice_details.arrival = x.arrival and invoice_details.type = x.type and x.diff < 0')
                    ->get();
            }
            else{
                $pivots = InvoiceDetail::selectRaw('(?) as invoice_id, (?) as flight_id, (?) as created_at, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type,
                    COUNT(invoice_details.flight_num) as total_invoice, IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail,
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as difference,
                    IF(IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) < 0, "EXCESS", "OK") as remarks')
                    ->leftJoin(DB::raw('(SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                    FROM invoice_details i 
                    JOIN(
                        SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                        FROM invoices
                        JOIN flights f ON f.from_period = invoices.from_period
                        JOIN (
                                SELECT fd.*
                                FROM unbilled_details ub
                                JOIN flight_details as fd ON fd.id = ub.fd_id
                                WHERE ub.invoice_id = (?)
                            )as fd ON fd.flight_id = f.id
                        WHERE invoices.id = (?)
                        GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                    )AS d ON i.fn_number = d.fn_number
                    WHERE (departure = dep_ap_act or departure = arr_ap_act) and (arrival = dep_ap_act or arrival = arr_ap_act) and i.type = d.type
                    GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail)
                    as piv'),
                        function ($join) {
                            $join->on('invoice_details.id', '=', 'piv.id');
                        })->setBindings([$invoice_id, $flight->id, Carbon::now(), $unbilled_inv_id, $invoice_id])
                    ->where('invoice_details.invoice_id', $invoice_id)
                    ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->groupBy(DB::raw('invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, invoice_details.type, total_detail, invoice_details.service'))
                    ->get()->toArray();

                $inv_remarks = InvoiceDetail::selectRaw('id')
                    ->join(DB::raw('(SELECT invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, 
                    invoice_details.arrival, invoice_details.type, from_period, to_period, COUNT(invoice_details.flight_num) as total_invoice, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) as total_detail, 
                    IF(piv.total_detail IS NULL, 0, piv.total_detail) - COUNT(invoice_details.flight_num) as diff
                    FROM invoice_details
                    LEFT JOIN
                    (
                        SELECT id, flight_num, i.fn_number, departure, arrival, i.type, COUNT(flight_num) as total_invoice, total_detail
                        FROM invoice_details i 
                        JOIN(
                            SELECT fn_number, COUNT(fn_number) as total_detail, dep_ap_act, arr_ap_act, type
                            FROM invoices
                            JOIN flights f ON f.from_period = invoices.from_period
                            JOIN (
                                SELECT fd.*
                                FROM unbilled_details ub
                                JOIN flight_details as fd ON fd.id = ub.fd_id
                                WHERE ub.invoice_id = (?)
                            )as fd ON fd.flight_id = f.id
                            WHERE invoices.id = (?)
                            GROUP BY fn_number, dep_ap_act, arr_ap_act, type
                        )AS d ON i.fn_number = d.fn_number
                        WHERE (departure = dep_ap_act or departure = arr_ap_act) and (arrival = dep_ap_act or arrival = arr_ap_act) and i.type = d.type
                        GROUP BY id, flight_num, i.fn_number, departure, arrival, i.type, total_detail
                    )as piv ON invoice_details.id = piv.id
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE invoice_details.invoice_id = (?)
                    GROUP BY invoice_details.flight_num, invoice_details.fn_number, invoice_details.departure, invoice_details.arrival, 
                    invoice_details.type, total_detail, from_period, to_period)
                    as x'),
                        function($join)
                        {
                            $join->on('x.fn_number', '=' ,'invoice_details.fn_number');
                        })
                    ->setBindings([$unbilled_inv_id, $invoice_id, $invoice_id])
                    ->whereRaw('invoice_details.departure = x.departure and invoice_details.arrival = x.arrival and invoice_details.type = x.type and x.diff < 0')
                    ->get();
            }

            $pivot = new Pivot();
            $pivot->insert($pivots);

            for($i = 0 ; $i < count($inv_remarks) ; $i++){
                $invoice = InvoiceDetail::find($inv_remarks[$i]->id);
                $invoice->remarks = 'EXCESS';
                $invoice->save();
            }

            if($flight_type == 'DOM'){
                $unbilled = FlightDetail::selectRaw('(?) as invoice_id, flight_details.id as fd_id, (?) as created_at')
                    ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                    ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                    ->whereRaw('flight_details.id NOT IN(
                    SELECT fd.id
                    FROM invoices i
                    JOIN flights as f ON f.from_period = i.from_period
                    JOIN flight_details fd ON fd.flight_id = f.id
                    JOIN pivots p ON p.fn_number = fd.fn_number
                    JOIN(
                        SELECT *
                        FROM invoice_details
                        WHERE invoice_id = (?)
                    )as x ON x.fn_number = fd.fn_number
                    WHERE fd.fn_number = x.fn_number and dep_ap_act = x.departure and arr_ap_act = x.arrival and fd.type = x.type 
                    and (DATE(arr_act_dt) = x.date or (p.departure = dep_ap_act and p.arrival = arr_ap_act and p.type = fd.type 
                    and p.difference <= 0)) and i.id = (?) and p.invoice_id = (?)
                    GROUP BY fd.id
                )and i.id = (?)')
                    ->setBindings([$invoice_id, Carbon::now(), $invoice_id, $invoice_id, $invoice_id, $invoice_id])
                    ->get()->toArray();
            }
            else{
                $unbilled = FlightDetail::selectRaw('(?) as invoice_id, flight_details.id as fd_id, (?) as created_at')
                    ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                    ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                    ->whereRaw('flight_details.id NOT IN(
                    SELECT fd.id
                    FROM invoices i
                    JOIN flights as f ON f.from_period = i.from_period
                    JOIN flight_details fd ON fd.flight_id = f.id
                    JOIN pivots p ON p.fn_number = fd.fn_number
                    JOIN(
                        SELECT *
                        FROM invoice_details
                        WHERE invoice_id = (?)
                    )as x ON x.fn_number = fd.fn_number
                    WHERE fd.fn_number = x.fn_number and (dep_ap_act = x.departure or dep_ap_act = x.arrival) and 
                    (arr_ap_act = x.arrival or arr_ap_act = x.departure) and fd.type = x.type and (DATE(arr_act_dt) = x.date 
                    or ((p.departure = dep_ap_act or p.departure = arr_ap_act) and (p.arrival = arr_ap_act or p.arrival = dep_ap_act)
                    and p.type = fd.type and p.difference <= 0)) and i.id = (?) and p.invoice_id = (?)
                    GROUP BY fd.id
                )and i.id = (?)')
                    ->setBindings([$invoice_id, Carbon::now(), $invoice_id, $invoice_id, $invoice_id, $invoice_id])
                    ->get()->toArray();
            }

            $unbilled_details = new UnbilledDetail();
            $unbilled_details->insert($unbilled);

            $flight_check = FlightDetail::selectRaw('flight_details.*')
                ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                ->whereRaw('flight_details.id NOT IN (
                SELECT fd.id
                FROM unbilled_details ub
                JOIN invoices i ON i.id = ub.invoice_id
                JOIN flight_details fd ON fd.id = ub.fd_id
                WHERE i.id = (?)) 
                and i.id = (?)')->setBindings([$invoice_id, $invoice_id])
                ->get();

            if($invoice_type == 'Route Charge'){
                for($i = 0 ; $i < count($flight_check) ; $i++){
                    $flight_detail = FlightDetail::find($flight_check[$i]->id);
                    $flight_detail->flight_check_rc = 'billed';
                    $flight_detail->save();
                }
            }
            elseif($invoice_type == 'TNC'){
                for($i = 0 ; $i < count($flight_check) ; $i++){
                    $flight_detail = FlightDetail::find($flight_check[$i]->id);
                    $flight_detail->flight_check_tnc = 'billed';
                    $flight_detail->save();
                }
            }

            return back()->with('success','Insert record successfully.');
        }
        else{
            return back()->with('error','Incorrect worksheet name.');
        }
    }

    public function findflighttype(Request $request)
    {
        $data = Invoice::find($request->id);

        return response()->json($data);
    }
}
