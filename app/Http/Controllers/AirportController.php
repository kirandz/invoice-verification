<?php

namespace App\Http\Controllers;

use App\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AirportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $airports = Airport::all();

        return view('manage.airports', compact('airports'));
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'icao_code' => 'required',
            'iata_code' => 'required',
            'airport_name' => 'required',
            'country' => 'required',
        ]);


        if ($validator->passes()) {
            $airport = New Airport();

            $airport->icao_code = $request->icao_code;
            $airport->iata_code = $request->iata_code;
            $airport->name = $request->airport_name;
            $airport->country = $request->country;
            $airport->save();

            return response()->json(['success'=>'Airport data has been created.']);
        }

        return response()->json(['error'=>$validator->errors()->first()]);
    }

    public function update(Request $request)
    {
        Airport::where('id', $request->id)->update([
            'icao_code' => $request->icao_code,
            'iata_code' => $request->iata_code,
            'name' => $request->name,
            'country' => $request->country
        ]);

        return ['success' => 'Airport data has been updated'];
    }

    public function delete(Request $request)
    {
        $airport = Airport::find($request->id);

        $airport->delete();

        return ['success' => 'Airport data has been deleted'];
    }

    public function deleteall()
    {
        Airport::truncate();

        return ['success' => 'Airport data has been deleted'];
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'airport' => 'required|mimes:xlsx,xls',
        ]);

        $path = $request->file('airport')->getRealPath();

        Excel::filter('chunk')->load($path)->chunk(500, function ($results){

            $dataArray = [];
            foreach ($results->toArray() as $row) {
                array_push($dataArray, [
                    'iata_code' => $row['iata_code'],
                    'icao_code' => $row['icao_code'],
                    'name' => $row['name'],
                    'country' => $row['country'],
                    'created_at' => Carbon::now(),
                ]);
            }
            Airport::insert($dataArray);
        });
        return back();
    }
}
