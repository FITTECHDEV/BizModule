<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use File;
use Illuminate\Support\Facades\Response;

class AttachmentController extends Controller
{
    public function download($id)
    {
        $attachment = Attachment::where('ID', $id)->firstOrFail();
        $path = storage_path('attachments/' . $attachment->FileName);
        // return response()->download($pathToFile);

        // $path = storage_public('images/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        // $file = File::get($path);
        // $type = File::mimeType($path);
        // // dd($type);
        // $downloadtypes = ['csv','doc','docx','txt','xlx','xls','xlsx','text/plain'];

        // if (in_array($type, $downloadtypes) ) {
        //     $response =  response()->download($path);
        //     ob_end_clean();
        //     return $response;
        // }

        // $response = Response::make($file, 200);
        // $response->header("Content-Type", $type);
            $response =  response()->download($path);
            ob_end_clean();
            return $response;
    }

    /**
     * upload
     *
     * @param  mixed $request
     * @return void
     */
    public function upload(Request $request)
    {
       $validated = $request->validate([
            'TicketID' => 'required',
            'attachments' => 'required|max:2048',
            // 'attachment' => 'max:2048|pdf, jpg, png, jpeg, csv, doc, docx, txt, xlx, xls, xlsx',
        ]);

        $new_ticket = Ticket::where('ID', $request->TicketID)->first();
        if ($request->hasFile('attachments')) {
            foreach ($request->attachments as $key => $file) {
                $attachment = new Attachment();
                $attachment->ID = guid();

                $attachment->TicketID = $new_ticket->Id;
                $attachment->TicketNo = $new_ticket->TicketNo;

                // new file name
                // $fileName = time() . '.' . $file->getClientOriginalExtension();
                $fileName =   $file->getClientOriginalName();
                // move the file to desired folder
                // $request->$file->storeAs('attachments',  $fileName);
                // $file->move('attachments/', $fileName);
                $request->attachments[$key]->move(storage_path('attachments'), $fileName);

                // assign the location of folder to the model
                $attachment->FileName = $fileName;
                $attachment->FilePath = 'attachments/' . $fileName;

                $attachment->CreatedBy = Auth::user()->ID;
                $attachment->CreatedOn = Carbon::now()->toDateTimeString();

                $attachment->LastModifiedBy = Auth::user()->ID;
                // $attachment->LastModifiedOn = '';

                $attachment->IsActive = 1;
                $attachment->IsDeleted = 0;

                $attachment->save();
            }
            toast('Attachment Uploaded !!', 'success');
            // return redirect()->back();
            return response()->json(['message' =>'Success']);
        }

        // return response()->json($validated);
    }

    public function destroy()
    {
        // dd('here');
        Attachment::where('ID', request()->ID)->update([
            'IsDeleted' => 1
        ]);

        Attachment::where('ID', request()->ID)->delete();

        toast('Attachment Deleted !!', 'success');

         return response()->json(request()->ID);

    }
}
