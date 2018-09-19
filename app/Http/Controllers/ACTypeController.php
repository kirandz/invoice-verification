<?php

namespace App\Http\Controllers;

use App\ACType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ACTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $actypes = ACType::all();

        return view('manage.actypes', compact('actypes'));
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ac_type' => 'required',
        ]);


        if ($validator->passes()) {
            $ac_type = New ACType();

            $ac_type->ac_type = $request->airnav_type;
            $ac_type->ga_type = $request->ga_type;
            $ac_type->conv_type = $request->ac_type;
            $ac_type->save();

            return response()->json(['success'=>'AC Type data has been created']);
        }

        return response()->json(['error'=>$validator->errors()->first()]);
    }

    public function update(Request $request)
    {
        ACType::where('id', $request->id)->update([
            'ac_type' => $request->ac_type,
            'ga_type' => $request->ga_type,
            'conv_type' => $request->type,
        ]);

        return ['success' => 'AC Type data has been updated'];
    }

    public function delete(Request $request)
    {
        $ac_type = ACType::find($request->id);

        $ac_type->delete();

        return ['success' => 'AC Type data has been deleted'];
    }
}
