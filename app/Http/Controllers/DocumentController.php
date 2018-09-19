<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::all();

        return view('document', compact('documents'));
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'document' => 'required|mimes:pdf',
        ]);

        $document = new Document();

        if ($request->file('document') != null)
        {
            $file = $request->file('document');
            $file->move(public_path('/files'), $file->getClientOriginalName());
            $document->name = $file->getClientOriginalName();
            $document->save();
        }

        return back()->with('message', 'Upload success.');
    }

    public function delete(Request $request)
    {
        $document = Document::find($request->id);

        unlink('files/' . $document->name);
        $document->delete();

        return ['success' => 'Document has been deleted'];
    }
}
