<?php

namespace App\Http\Controllers;

use App\RouteUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $routeunits = RouteUnit::all();

        return view('manage.routeunits', compact('routeunits'));
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'charge' => 'required|numeric|min:0',
            'valid_from' => 'required',
            'valid_to' => 'required',
            'flight_type' => 'required',
        ]);


        if ($validator->passes()) {
            $route_unit = New RouteUnit();

            $route_unit->charge = $request->charge;
            $route_unit->from_date = $request->valid_from;
            $route_unit->to_date = $request->valid_to;
            $route_unit->flight_type = $request->flight_type;
            $route_unit->save();

            return response()->json(['success'=>'Route Unit has been created.']);
        }

        return response()->json(['error'=>$validator->errors()->first()]);
    }

    public function update(Request $request)
    {
        RouteUnit::where('id', $request->id)->update([
//            'charge' => $request->charge,
            'from_date' => $request->valid_from,
            'to_date' => $request->valid_to  ,
            'flight_type' => $request->flight_type,
        ]);

        return ['success' => 'Route Unit has been updated.'];
    }

    public function delete(Request $request)
    {
        $route_unit = RouteUnit::find($request->id);

        $route_unit->delete();

        return ['success' => 'Route Unit has been deleted.'];
    }
}
