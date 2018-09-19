<?php

namespace App\Http\Controllers;

use App\FlightDetail;
use App\Invoice;
use App\InvoiceDetail;
use App\Pivot;
use App\UnbilledDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Support\Facades\Validator;

class RouteChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $invoices = Invoice::where('charge_type', 'Route Charge')->orderBy('from_period')->get();

        return view('routecharge.index', compact('invoices'));
    }

    public function invoices(Request $request)
    {
        $data = InvoiceDetail::where('invoice_id', $request->id)->orderBy(DB::raw('flight_num'))->get();

        return response()->json($data);
    }

    public function details(Request $request)
    {
        $data = FlightDetail::selectRaw('flight_details.*')
            ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
            ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
            ->whereRaw('flight_details.id NOT IN (
                SELECT fd.id
                FROM unbilled_details ub
                JOIN invoices i ON i.id = ub.invoice_id
                JOIN flight_details fd ON fd.id = ub.fd_id
                WHERE i.id = (?))and i.id = (?)')->setBindings([$request->id, $request->id])
            ->orderBy(DB::raw('flight_details.fn_number*1'))
            ->get();

        return response()->json($data);
    }

    public function summary(Request $request)
    {
        if($request->id == "all"){
            $data = InvoiceDetail::selectRaw('invoice_details.service, invoice_details.type, invoices.flight_type, 
                SUM(route_charge) as total_charge, COUNT(invoice_details.id) as total_flight')
                ->join(DB::raw('(SELECT invoice_details.id, service, type, date
                    FROM invoice_details
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    GROUP BY invoice_details.id, service, type)
                    as x'),
                    function($join)
                    {
                        $join->on('invoice_details.id', '=' ,'x.id');
                    })
                ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->where('charge_type', 'Route Charge')
                ->where('invoices.flight_type', $request->flight_type)
                ->groupBy(DB::raw('invoice_details.service, invoice_details.type, invoices.flight_type'))
                ->get();
        }
        else{
            $data = InvoiceDetail::selectRaw('invoice_details.service, invoice_details.type, invoices.flight_type, SUM(route_charge) as total_charge, 
                from_period, to_period, COUNT(invoice_details.id) as total_flight')
                ->join(DB::raw('(SELECT invoice_details.id, service, type, date
                    FROM invoice_details
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE invoice_id = (?)
                    GROUP BY invoice_details.id, service, type)
                    as x'),
                    function($join)
                    {
                        $join->on('invoice_details.id', '=' ,'x.id');
                    })->setBindings([$request->id])
                ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->where('invoices.flight_type', $request->flight_type)
                ->groupBy(DB::raw('invoice_details.service, invoice_details.type'))
                ->get();
        }

        return response()->json($data);
    }

    public function summaryperiod(Request $request)
    {
        $data = InvoiceDetail::selectRaw('invoice_details.service, invoice_details.type, invoices.flight_type, SUM(route_charge) 
            as total_charge, COUNT(invoice_details.id) as total_flight')
            ->join(DB::raw('(SELECT invoice_details.id, service, type, date
                    FROM invoice_details
                    JOIN invoices ON invoices.id = invoice_details.invoice_id
                    WHERE date >= (?) and date <= (?)
                    GROUP BY invoice_details.id, service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$request->from_period, $request->to_period])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('charge_type', 'Route Charge')
            ->where('invoices.flight_type', $request->flight_type)
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type'))
            ->get();

        return response()->json($data);
    }

    public function summaryupdatetype(Request $request)
    {
        $invoice_details = InvoiceDetail::whereRaw('invoice_id = ? and type = ?', [$request->id, $request->type_old])->get();

        InvoiceDetail::whereRaw('invoice_id = ? and type = ?', [$request->id, $request->type_old])->update([
            'type' => $request->type,
            'remarks' => $request->type_old
        ]);

        $array_id = [];
        foreach ($invoice_details as $invoice_detail){
            array_push($array_id, $invoice_detail->id);
        }
        $invoice_details_update = InvoiceDetail::whereIn('id', $array_id)->get();

        foreach($invoice_details_update as $invd_update){
            $pivot = Pivot::whereRaw('invoice_id = ? and fn_number = ? and departure = ? and arrival = ? and type = ?',
                [$request->id, $invd_update->fn_number, $invd_update->departure, $invd_update->arrival, $request->type])
                ->first();

            $pivot_old = Pivot::whereRaw('invoice_id = ? and fn_number = ? and departure = ? and arrival = ? and type = ?',
                [$request->id, $invd_update->fn_number, $invd_update->departure, $invd_update->arrival, $request->type_old])
                ->first();

            Pivot::where('id', $pivot->id)->update([
                'total_invoice' => ($pivot->total_invoice+1),
                'difference' => ($pivot->difference-1)
            ]);

            Pivot::where('id', $pivot_old->id)->update([
                'total_invoice' => ($pivot_old->total_invoice-1),
                'difference' => ($pivot_old->difference+1)
            ]);
        }

        return response()->json(['success' => 'Type has been updated']);
    }

    public function pivot(Request $request)
    {
        if($request->id != 'all'){
            $data = Pivot::selectRaw('pivots.id, invoice_details.service, invoice_details.flight_num, pivots.fn_number, pivots.departure,
                pivots.arrival, pivots.type, total_invoice, total_detail, difference, pivots.remarks')
                ->whereRaw('pivots.invoice_id = ? and invoice_details.invoice_id = ?', [$request->id, $request->id])
                ->join('invoices', 'invoices.id', '=', 'pivots.invoice_id')
                ->join('invoice_details', 'invoice_details.fn_number', '=', 'pivots.fn_number')
                ->groupBy(DB::raw('pivots.id'))
                ->orderBy(DB::raw('invoice_details.service, invoice_details.flight_num'))
                ->get();
        }
        else{
            $data = '';
        }

        return response()->json($data);
    }

    public function pivservice(Request $request)
    {
        $pivot = Pivot::find($request->id);

        InvoiceDetail::whereRaw('fn_number = (?) and departure = (?) and arrival = (?) and type = (?) and invoice_id = (?)')
            ->setBindings([$pivot->fn_number, $pivot->departure, $pivot->arrival, $pivot->type, $pivot->invoice_id])
            ->update([
                'service' => $request->service
            ]);

        FlightDetail::whereRaw('fn_number = (?) and dep_ap_act = (?) and arr_ap_act = (?) and type = (?) and flight_id = (?)')
            ->setBindings([$pivot->fn_number, $pivot->departure, $pivot->arrival, $pivot->type, $pivot->flight_id])
            ->update([
                'service' => $request->service
            ]);

        return response()->json(['success'=>'Update success']);
    }

    public function pivinvoices(Request $request)
    {
        $data = InvoiceDetail::where('invoice_id', $request->id)
            ->whereRaw('fn_number = ? and departure = ? and arrival = ? and type = ? and invoice_id = ?',
                [$request->fn_number, $request->departure, $request->arrival, $request->type, $request->id])
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function pivdetails(Request $request)
    {
        $check = Invoice::find($request->id);

        if($check->flight_type == 'DOM'){
            $data = Invoice::selectRaw('fd.*')
                ->join('flights as f', 'f.from_period', '=', 'invoices.from_period')
                ->join('flight_details as fd', 'fd.flight_id', '=', 'f.id')
                ->whereRaw('(invoices.id = (?) and fn_number = (?) and dep_ap_act = (?))
                 or (invoices.id = (?) and fn_number = (?) and arr_ap_act = (?))')
                ->setBindings([$request->id, $request->fn_number, $request->departure, $request->id,
                    $request->fn_number, $request->arrival])
                ->orderBy(DB::raw('arr_act_dt'))
                ->get();
        }
        else{
            $data = Invoice::selectRaw('fd.*')
                ->join('flights as f', 'f.from_period', '=', 'invoices.from_period')
                ->join('flight_details as fd', 'fd.flight_id', '=', 'f.id')
                ->whereRaw('invoices.id = (?) and fn_number = (?) and (dep_ap_act = (?) or dep_ap_act = (?)) and (arr_ap_act = (?) or arr_ap_act = (?))')
                ->setBindings([$request->id, $request->fn_number, $request->departure, $request->arrival, $request->departure, $request->arrival])
                ->orderBy(DB::raw('arr_act_dt'))
                ->get();
        }


        return response()->json($data);
    }

    public function pivcheck(Request $request)
    {
//        Cross to Check
        if($request->flag == 1){
            FlightDetail::where('id', $request->id)->update([
                'flight_check_rc' => 'billed',
            ]);

            UnbilledDetail::where('fd_id', $request->id)
                ->where('invoice_id', $request->invoice_id)
                ->delete();
        }
//        Check to Cross
        else{
            FlightDetail::where('id', $request->id)->update([
                'flight_check_rc' => 'unbilled',
            ]);

            $unbilled = new UnbilledDetail();
            $unbilled->invoice_id = $request->invoice_id;
            $unbilled->fd_id = $request->id;
            $unbilled->save();
        }

        return ['success' => 'Flight check has been updated'];
    }

    public function pivotcorrection(Request $request)
    {
        $pivot = Pivot::where('pivots.id', $request->id)
            ->join('invoices', 'invoices.id' ,'=', 'pivots.invoice_id')
            ->first();

        $pivot_update = Pivot::selectRaw('pivots.id')
            ->join('invoices', 'invoices.id', '=', 'pivots.invoice_id')
            ->whereRaw('invoices.from_period = (?) and pivots.fn_number = (?) and pivots.departure = (?) and pivots.arrival = (?) and type = (?) and charge_type = "TNC"',
                [$pivot->from_period, $pivot->fn_number, $pivot->departure, $pivot->arrival, $pivot->type])
            ->first();

        if($pivot_update != null && $request->flag == 1){
            $update = Pivot::find($pivot_update->id);
            $update->remarks = $request->remarks;
            $update->save();
        }
        elseif($pivot_update != null && $request->flag == 2){
            $update = Pivot::find($pivot_update->id);
            $update->remarks = $pivot_update->remarks.', '.$request->remarks;
            $update->save();
        }

        if($request->flag == 1){
            Pivot::where('id', $request->id)->update([
                'remarks' => $request->remarks
            ]);
        }
        else{
            Pivot::where('id', $request->id)->update([
                'remarks' => $pivot->remarks.', '.$request->remarks
            ]);
        }

        return response()->json(['success'=>'Invoice has been correction.']);
    }

    public function pivinvupdate(Request $request)
    {
//        Route Charge
        $invoice = InvoiceDetail::find($request->inv_id);

        InvoiceDetail::where('id', $request->inv_id)->update([
            'remarks' => $request->remarks
        ]);

//        TNC
        $invoice_update = InvoiceDetail::selectRaw('invoice_details.id')
            ->whereRaw('fn_number = (?) and departure = (?) and arrival = (?) and type = (?) and date = (?) and ac_register = (?) and time_out = (?) and charge_type = "TNC" and invoices.id = (?)',
                [$invoice->fn_number, $invoice->departure, $invoice->arrival, $invoice->type, $invoice->date, $invoice->ac_register, $invoice->time_out, $request->id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->first();

        if($invoice_update != null){
            $update = InvoiceDetail::find($invoice_update->id);
            $update->remarks = $request->remarks;
            $update->save();
        }
    }

    public function pivinvfn(Request $request)
    {
        $piv_target = Pivot::whereRaw('invoice_id = (?) and fn_number = (?) and type = (?) and departure = (?) and arrival = (?)')
            ->setBindings([$request->invoice_id, $request->fn_number, $request->type, $request->departure, $request->arrival])
            ->first();

        $piv = Pivot::find($request->piv_id);

        if(count($piv_target) > 0){
            Pivot::where('id', $piv_target->id)->update([
                'total_invoice' => ($piv_target->total_invoice+1),
                'difference' => ($piv_target->difference-1)
            ]);

            $temp = InvoiceDetail::where('id', $request->inv_det_id)->first();

            InvoiceDetail::where('id', $request->inv_det_id)->update([
                'flight_num' => $request->fligt_num_update,
                'fn_number' => $request->fn_number,
                'remarks' => $temp->flight_num
            ]);

            Pivot::where('id', $piv->id)->update([
                'total_invoice' => ($piv->total_invoice-1),
                'difference' => ($piv->difference+1),
                'remarks' => $temp->flight_num
            ]);

            return response()->json(['success'=>'Flight number data has been updated.']);
        }

        else{
            $new_pivot = Pivot::selectRaw('fd.fn_number, pivots.departure, pivots.arrival, pivots.type, 1 as total_invoice, COUNT(fd.id) as total_detail,
                (1 - COUNT(fd.id)) as difference, pivots.invoice_id, pivots.flight_id')
                ->join('flight_details as fd', 'fd.flight_id', '=', 'pivots.flight_id')
                ->whereRaw('fd.dep_ap_act = pivots.departure and fd.arr_ap_act = pivots.arrival and fd.type = pivots.type and
                pivots.invoice_id = (?) and fd.fn_number = (?) and fd.dep_ap_act = (?) and fd.arr_ap_act = (?) and fd.type = (?)')
                ->setBindings([$request->invoice_id, $request->fn_number, $request->departure, $request->arrival, $request->type])
                ->groupBy(DB::raw('pivots.id'))
                ->first();

            if(count($new_pivot) > 0){
                $temp = InvoiceDetail::where('id', $request->inv_det_id)->first();

                InvoiceDetail::where('id', $request->inv_det_id)->update([
                    'flight_num' => $request->fligt_num_update,
                    'fn_number' => $request->fn_number,
                    'remarks' => $temp->flight_num
                ]);

                Pivot::where('id', $piv->id)->update([
                    'total_invoice' => ($piv->total_invoice-1),
                    'difference' => ($piv->difference+1),
                    'remarks' => $temp->flight_num
                ]);

                $pivot = new Pivot();

                $pivot->fn_number = $new_pivot->fn_number;
                $pivot->departure = $new_pivot->departure;
                $pivot->arrival = $new_pivot->arrival;
                $pivot->type = $new_pivot->type;
                $pivot->total_invoice = $new_pivot->total_invoice;
                $pivot->total_detail = $new_pivot->total_detail;
                $pivot->difference = $new_pivot->difference;
                $pivot->invoice_id = $new_pivot->invoice_id;
                $pivot->flight_id = $new_pivot->flight_id;
                $pivot->remarks = $temp->flight_num;

                $pivot->save();

                return response()->json(['success'=>'Flight number data has been updated.']);
            }
            else{
                return response()->json(['error'=>'Flight number not found.']);
            }
        }
    }

    public function pivinvservice(Request $request)
    {
        $inv_detail = InvoiceDetail::find($request->id);
        $inv_detail->service = $request->service;
        $inv_detail->save();

        return response()->json(['success'=>'Service data has been updated.']);
    }

    public function downloadcharge($id)
    {
        $check = Invoice::find($id);

        $charter = InvoiceDetail::selectRaw('invoice_details.service as Service, invoice_details.type as Type, SUM(route_charge) as "Total Charge"')
            ->join(DB::raw('(SELECT id, service, type, date
                    FROM invoice_details
                    WHERE invoice_id = (?)
                    GROUP BY id, service, type, date
                    ORDER BY service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type, from_period, to_period'))
            ->where('invoice_details.service', 'CHARTER')
            ->get();

        $sum_charter = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?) and service = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id, 'CHARTER'])
            ->first();

        $count_charter = count($charter);

        $hajj = InvoiceDetail::selectRaw('invoice_details.service as Service, invoice_details.type as Type, SUM(route_charge) as "Total Charge"')
            ->join(DB::raw('(SELECT id, service, type, date
                    FROM invoice_details
                    WHERE invoice_id = (?)
                    GROUP BY id, service, type, date
                    ORDER BY service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type, from_period, to_period'))
            ->where('invoice_details.service', 'HAJJ')
            ->get();

        $sum_hajj = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?) and service = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id, 'HAJJ'])
            ->first();

        $count_hajj = count($hajj);

        $test = InvoiceDetail::selectRaw('invoice_details.service as Service, invoice_details.type as Type, SUM(route_charge) as "Total Charge"')
            ->join(DB::raw('(SELECT id, service, type, date
                    FROM invoice_details
                    WHERE invoice_id = (?)
                    GROUP BY id, service, type, date
                    ORDER BY service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type, from_period, to_period'))
            ->where('invoice_details.service', 'Test Flight')
            ->get();

        $sum_test = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?) and service = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id, 'Test Flight'])
            ->first();

        $count_test = count($test);

        $training = InvoiceDetail::selectRaw('invoice_details.service as Service, invoice_details.type as Type, SUM(route_charge) as "Total Charge"')
            ->join(DB::raw('(SELECT id, service, type, date
                    FROM invoice_details
                    WHERE invoice_id = (?)
                    GROUP BY id, service, type, date
                    ORDER BY service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type, from_period, to_period'))
            ->where('invoice_details.service', 'Flight Training')
            ->get();

        $sum_training = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?) and service = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id, 'Flight Training'])
            ->first();

        $count_training = count($training);

        $regular = InvoiceDetail::selectRaw('invoice_details.service as Service, invoice_details.type as Type, SUM(route_charge) as "Total Charge"')
            ->join(DB::raw('(SELECT id, service, type, date
                    FROM invoice_details
                    WHERE invoice_id = (?)
                    GROUP BY id, service, type, date
                    ORDER BY service, type)
                    as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$id])
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.type, from_period, to_period'))
            ->where('invoice_details.service', 'REGULAR')
            ->get();

        $sum_regular = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?) and service = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id, 'REGULAR'])
            ->first();

        $count_regular = count($regular);

        $sum_all = InvoiceDetail::selectRaw('SUM(x.route_charge) as total_charge')
            ->join(DB::raw('(SELECT id, route_charge
                FROM invoice_details
                WHERE invoice_id = (?))
                as x'),
                function($join)
                {
                    $join->on('invoice_details.id', '=' ,'x.id');
                })->setBindings([$check->id])
            ->first();

        $invoices = InvoiceDetail::selectRaw('aerodrome as Aerodrome, arrival as Arr, airport_from as "From", 
            departure as Dept, date as Date, flight_num as "Flight No", ac_register as "AC Register", type as Type, service as Service, 
            point_in as "Point In", time_in as "Time In", point_out as "Point Out", time_out as "Time Out", distance as Distance, weight as Weight, 
            route_unit as "Route Unit", route_charge as "Route Charges", remarks as Remarks')
            ->where('invoice_id', $check->id)->get();

        $pivot = Pivot::selectRaw('invoice_details.service as Service, invoice_details.flight_num as "Flight No", pivots.departure 
            as Departure, pivots.arrival as Arrival, pivots.type as Type, total_invoice as "Total Invoice", total_detail as "Total Detail",
            difference as Difference,
            IF(pivots.remarks = "OK", "", pivots.remarks) as ""')
            ->whereRaw('pivots.invoice_id = ? and total_invoice > 0', [$check->id])
            ->join('invoices', 'invoices.id', '=', 'pivots.invoice_id')
            ->join('invoice_details', 'invoice_details.fn_number', '=', 'pivots.fn_number')
            ->groupBy(DB::raw('invoice_details.service, invoice_details.flight_num, pivots.fn_number, pivots.departure,
            pivots.arrival, pivots.type, total_invoice, total_detail, difference, pivots.remarks'))
            ->orderBy(DB::raw('invoice_details.service, invoice_details.flight_num'))
            ->get();

        $month = date('M', strtotime($check->from_period));
        $month_num = date('m', strtotime($check->from_period));
        $flight_type = substr($check->flight_type,0,1).strtolower(substr($check->flight_type,1));
        $inv_code = substr($check->invoice_num, -4);
        $file_name = $month_num.'. '.$month.' ('.$flight_type.') '.$inv_code;

        return Excel::create($file_name, function($excel) use ($pivot, $check, $charter, $count_charter, $count_test,
            $test, $count_training, $training, $regular, $count_regular, $sum_charter, $sum_regular, $sum_all, $sum_test,
            $sum_training, $invoices, $hajj, $count_hajj, $sum_hajj) {
            $excel->sheet('SUMMARY', function($sheet) use ($check, $charter, $count_charter, $regular, $count_regular,
                $sum_charter, $sum_regular, $sum_all, $hajj, $count_hajj, $sum_hajj, $test, $count_test, $sum_test,
                $training, $count_training, $sum_training)
            {
                $x = 2;
                $total_row = 0;
                $sheet->setCellValue('A1', 'Service')->setCellValue('B1', 'Type')->setCellValue('C1', 'Total Charge');

                if($count_charter != 0){
                    $sheet->fromArray($charter, null, 'A'.$x, true, false)
                        ->mergeCells('A'.($count_charter+$x).':B'.($count_charter+$x))
                        ->setCellValue('A'.($count_charter+$x), 'Charter Total')
                        ->setCellValue('C'.($count_charter+$x), $sum_charter->total_charge);
                    $sheet->data = [];

                    $total_row += $count_charter;
                    $x++;
                }

                if($count_hajj != 0){
                    $sheet->fromArray($hajj, null, 'A'.($total_row+$x), true, false)
                        ->mergeCells('A'.($total_row+$count_hajj+$x).':B'.($total_row+$count_hajj+$x))
                        ->setCellValue('A'.($total_row+$count_hajj+$x), 'Hajj Total')
                        ->setCellValue('C'.($total_row+$count_hajj+$x), $sum_hajj->total_charge);
                    $sheet->data = [];

                    $total_row += $count_hajj;
                    $x++;
                }

                if($count_test != 0){
                    $sheet->fromArray($test, null, 'A'.($total_row+$x), true, false)
                        ->mergeCells('A'.($total_row+$count_test+$x).':B'.($total_row+$count_test+$x))
                        ->setCellValue('A'.($total_row+$count_test+$x), 'Test Flight Total')
                        ->setCellValue('C'.($total_row+$count_test+$x), $sum_test->total_charge);
                    $sheet->data = [];

                    $total_row += $count_test;
                    $x++;
                }

                if($count_training != 0){
                    $sheet->fromArray($training, null, 'A'.($total_row+$x), true, false)
                        ->mergeCells('A'.($total_row+$count_training+$x).':B'.($total_row+$count_training+$x))
                        ->setCellValue('A'.($total_row+$count_training+$x), 'Flight Training Total')
                        ->setCellValue('C'.($total_row+$count_training+$x), $sum_training->total_charge);
                    $sheet->data = [];

                    $total_row += $count_training;
                    $x++;
                }

                $y = $x+1;
                if($count_regular != 0){
                    $sheet->fromArray($regular, null, 'A'.($total_row+$x), true, false)
                        ->mergeCells('A'.($total_row+$count_regular+$x).':B'.($total_row+$count_regular+$x))
                        ->setCellValue('A'.($total_row+$count_regular+$x), 'Regular Total')
                        ->setCellValue('C'.($total_row+$count_regular+$x), $sum_regular->total_charge)
                        ->mergeCells('A'.($total_row+$count_regular+$y).':B'.($total_row+$count_regular+$y))
                        ->setCellValue('A'.($total_row+$count_regular+$y), 'Grand Total')
                        ->setCellValue('C'.($total_row+$count_regular+$y), $sum_all->total_charge);
                }
            });
            $excel->sheet('INVOICE', function($sheet) use ($invoices)
            {
                $sheet->fromArray($invoices, null, 'A1', true);
            });
            $excel->sheet('PIVOT', function($sheet) use ($pivot)
            {
                $sheet->fromArray($pivot, null, 'A1', true);
            });
        })->download('xlsx');
    }

    public function unbilled()
    {
        return view('routecharge.unbilled');
    }

    public function unbilleddetails(Request $request)
    {
        $check = Invoice::where('charge_type', 'Route Charge')->get();

        if(count($check) == 0){
            $data = FlightDetail::selectRaw('flight_details.*')
                ->whereRaw('flight_details.arr_sched_dt >= (?) and flight_details.arr_sched_dt <= (?)+INTERVAL 1 DAY 
                    and flight_details.flight_check_rc = "unbilled" and flight_details.leg_state = "Scheduled"')
                ->setBindings([$request->from_period, $request->to_period])
                ->groupBy(DB::raw('flight_details.fn_number, flight_details.service, flight_details.ac_registration, flight_details.dep_ap_sched, 
                    flight_details.dep_sched_dt, flight_details.dep_ap_act, flight_details.dep_act_dt, flight_details.arr_ap_sched, 
                    flight_details.arr_sched_dt, flight_details.arr_ap_act, flight_details.arr_act_dt, flight_details.leg_state, 
                    flight_details.type'))
                ->orderBy(DB::raw('dep_sched_dt'))
                ->get();
        }
        else{
            $data = FlightDetail::selectRaw('flight_details.*')
                ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
                ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
                ->whereRaw('flight_details.arr_sched_dt >= (?) and flight_details.arr_sched_dt <= (?)+INTERVAL 1 DAY and i.charge_type = "Route Charge" 
                    and flight_details.flight_check_rc = "unbilled" and flight_details.leg_state = "Scheduled"')
                ->setBindings([$request->from_period, $request->to_period])
                ->groupBy(DB::raw('flight_details.fn_number, flight_details.service, flight_details.ac_registration, flight_details.dep_ap_sched, 
                    flight_details.dep_sched_dt, flight_details.dep_ap_act, flight_details.dep_act_dt, flight_details.arr_ap_sched, 
                    flight_details.arr_sched_dt, flight_details.arr_ap_act, flight_details.arr_act_dt, flight_details.leg_state, 
                    flight_details.type'))
                ->orderBy(DB::raw('dep_sched_dt'))
                ->get();
        }

        return response()->json($data);
    }

    public function downloadunbilled($str)
    {
        $exp_date = explode("to",$str);
        $from = $exp_date[0];
        $to = $exp_date[1];

        $unbilled = FlightDetail::selectRaw('flight_details.fn_carrier as FN_CARRIER, flight_details.fn_number as FN_NUMBER, 
            flight_details.ac_version as AC_VERSION, flight_details.ac_registration as AC_REGISTRATION, flight_details.ac_subtype as AC_SUBTYPE,
            flight_details.dep_sched_dt as DEP_SCHED_DT, flight_details.dep_ap_sched as DEP_AP_SCHED, flight_details.arr_sched_dt as ARR_SCHED_DT, 
            flight_details.arr_ap_sched as ARR_AP_SCHED, flight_details.dep_act_dt as DEP_DT, flight_details.dep_ap_act as DEP_AP_ACT, 
            flight_details.arr_act_dt as ARR_DT, flight_details.arr_ap_act as ARR_AP_ACT, flight_details.leg_state as LEG_STATE, 
            flight_details.remarks as REMARKS')
            ->join('flights as f', 'f.id', '=', 'flight_details.flight_id')
            ->join('invoices as i', 'i.from_period', '=', 'f.from_period')
            ->whereRaw('flight_details.arr_sched_dt >= (?) and flight_details.arr_sched_dt <= (?)+INTERVAL 1 DAY and i.charge_type = "Route Charge" 
            and flight_details.flight_check_rc = "unbilled" and flight_details.leg_state = "Scheduled"')
            ->setBindings([$from, $to])
            ->groupBy(DB::raw('flight_details.fn_number, flight_details.service, flight_details.ac_registration, flight_details.dep_ap_sched, 
            flight_details.dep_sched_dt, flight_details.dep_ap_act, flight_details.dep_act_dt, flight_details.arr_ap_sched, 
            flight_details.arr_sched_dt, flight_details.arr_ap_act, flight_details.arr_act_dt, flight_details.leg_state, 
            flight_details.type'))
            ->get();

        $from = date('d M Y', strtotime($from));
        $to = date('d M Y', strtotime($to));
        $file_name = $from.' - '.$to.' Unbilled Flight';

        return Excel::create($file_name, function($excel) use ($unbilled) {
            $excel->sheet('DETAIL FLIGHT', function($sheet) use ($unbilled)
            {
                $sheet->fromArray($unbilled, null, 'A1', true);
            });
        })->download('xlsx');
    }
}
