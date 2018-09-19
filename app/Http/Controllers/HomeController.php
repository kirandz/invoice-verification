<?php

namespace App\Http\Controllers;

use App\FlightDetail;
use App\InvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard(Request $request)
    {
        if($request->flag == 'all'){
            $data = InvoiceDetail::selectRaw('date, SUM(route_charge) as total_rc, SUM(terminal_charge) as total_tnc')
                ->groupBy(DB::raw('YEAR(date), MONTH(date) ASC'))
                ->whereRaw('date > DATE_SUB(now(), INTERVAL 12 MONTH)')
                ->get();
        }
        else{
            $data = InvoiceDetail::selectRaw('date, SUM(route_charge) as total_rc, SUM(terminal_charge) as total_tnc')
                ->whereRaw('date >= (?) and date <= (?) and date > DATE_SUB(now(), INTERVAL 12 MONTH)')
                ->setBindings([$request->from_period, $request->to_period])
                ->groupBy(DB::raw('YEAR(date), MONTH(date) ASC'))
                ->get();
        }


        return response()->json($data);
    }

    public function dashboardpie(Request $request)
    {
        $data = InvoiceDetail::selectRaw('COUNT(invoice_details.id) as total_flight, type, service')
            ->join('invoices as i', 'i.id', '=', 'invoice_details.invoice_id')
            ->groupBy(DB::raw($request->group_by))
            ->whereRaw('MONTH(date) = (?) and YEAR(date) and charge_type = (?)')->setBindings([$request->month, $request->charge_type])
            ->get();

        return response()->json($data);
    }
}
