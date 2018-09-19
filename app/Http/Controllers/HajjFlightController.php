<?php

namespace App\Http\Controllers;

use App\HajjFlight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Excel;

class HajjFlightController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $hajjflights = HajjFlight::all();

        return view('manage.hajjflights', compact('hajjflights'));
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_num' => 'required',
            'valid_from' => 'required',
            'valid_to' => 'required',
        ]);


        if ($validator->passes()) {
            $hajj_flight = New HajjFlight();

            $hajj_flight->flight_num = $request->flight_num;
            for($i=0 ; $i<strlen($request->flight_num) ; $i++){
                if(!is_numeric($request->flight_num[$i])){
                    $fn_number = substr($request->flight_num, ($i+1));
                }
            }
            $hajj_flight->fn_number = $fn_number;
            $hajj_flight->valid_from = $request->valid_from;
            $hajj_flight->valid_to = $request->valid_to;
            $hajj_flight->save();

            return response()->json(['success'=>'Hajj Flight data has been created.']);
        }

        return response()->json(['error'=>$validator->errors()->first()]);
    }

    public function update(Request $request)
    {
        HajjFlight::where('id', $request->id)->update([
            'flight_num' => 'GIA'.$request->fn_number,
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
            'fn_number' => $request->fn_number
        ]);

        return ['success' => 'Hajj Flight data has been updated'];
    }

    public function delete(Request $request)
    {
        $ac_type = HajjFlight::find($request->id);

        $ac_type->delete();

        return ['success' => 'Hajj Flight data has been deleted.'];
    }

    public function deleteall()
    {
        HajjFlight::truncate();

        return ['success' => 'Hajj Flight data has been deleted'];
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'valid_from' => 'required',
            'valid_to' => 'required',
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $valid_from = $request->valid_from;
        $valid_to = $request->valid_to;

        $path = $request->file('file')->getRealPath();

        Excel::filter('chunk')->load($path)->chunk(500, function ($results) use (
            $valid_from, $valid_to
        ){
            $dataArray = [];
            foreach ($results->toArray() as $row) {
                $flight_num = (string)$row['flight_num'];
                $fn_number = $flight_num;
                $flight_num_new = $flight_num;

                for($i = 0 ; $i < strlen($flight_num) ; $i++){
                    if(!is_numeric($flight_num[$i]) || $flight_num[$i] == '0'){
                        $fn_number = substr($flight_num, $i+1);
                    }
                    elseif($flight_num[$i] != '0'){
                        break;
                    }
                }

                for($i = 0 ; $i < strlen($flight_num) ; $i++){
                    if(is_numeric($flight_num[$i])) {
                        break;
                    }
                    else{
                        $flight_num_new = substr($flight_num, $i+1);
                    }
                }

                $flight_num_new = 'GIA'.$flight_num_new;

                array_push($dataArray, [
                    'flight_num' => $flight_num_new,
                    'fn_number' => $fn_number,
                    'valid_from' => $valid_from,
                    'valid_to' => $valid_to,
                    'created_at' => Carbon::now(),
                ]);
            }
            HajjFlight::insert($dataArray);
        });

        return back()->with('success','Insert record successfully.');
    }
}
