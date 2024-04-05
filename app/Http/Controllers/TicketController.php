<?php

namespace App\Http\Controllers;

use App\Jobs\RemindAssignedTickets;
use App\Jobs\TicketSolvedJob;
use App\Mail\TicketCreated;
use App\Mail\TicketUpdated;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketAssign;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class TicketController extends Controller
{
    public $ticket;
    public $auth_user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->ticket = new Ticket();
        $this->auth_user = Auth::user();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function openTickets()
    {
        //get all tickets
        return view('tickets.open_tickets');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function solvedTickets()
    {
        //get all tickets
        return view('tickets.solved_tickets');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function closedTickets()
    {
        //get all tickets
        return view('tickets.closed_tickets');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reOpenedTickets()
    {
        //get all tickets
        return view('tickets.re_opened_tickets');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $ticket = new Ticket();

        if (Auth::user()->Role != 1) {
            return redirect()->route('tickets.my_tickets');
        }

        return view('tickets.index', compact('ticket'));
    }
    public function filterTicket($filter, $id)
    {
        $title = $filter;
        $tickets = Ticket::where('Status', $id)
            ->where('UserID', Auth::user()->ID)
            ->latest()->get();
        // dd($tickets);
        return view('tickets.filter', compact('tickets', 'title'));
    }

    public function updateTicket(Request $request)
    {
        if ($request->ajax()) {
            Ticket::where('ID', $request->pk)->first()
                ->update([
                    $request->name => $request->value
                ]);
            return response()->json(['success' => true]);
        }
    }

    /**
     * Display a listing of the resource for specific user.
     *
     * @return \Illuminate\Http\Response
     */
    public function myTickets()
    {

        $userID = Auth::user()->ID;
        $ticket = $this->ticket;

        $title = 'My Tickets';

        $criterias = [['UserID', $userID]];
        $pageFilter = 'myTickets';

        return view('tickets.my_tickets', compact('ticket', 'criterias', 'title', 'pageFilter'));
    }

    public function myOfficeTickets()
    {

        $ticket = $this->ticket;

        $title = Auth::user()->Office->Name . ' Tickets';

        $officeID = Auth::user()->OfficeID;

        $criterias = [['OfficeID', $officeID]];

        $pageFilter = 'myOfficeTickets';

        return view('tickets.my_tickets', compact('ticket', 'criterias', 'title', 'pageFilter'));
    }

    public function assigned()
    {
        $ticket = new Ticket();

        $title = 'Tickets Assigned To Me';

        $criterias = [['AssignedTo', Auth::user()->ID]];

        $pageFilter = 'assignedTickets';

        return view('tickets.my_tickets', compact('ticket', 'criterias', 'title', 'pageFilter'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //user data
        $user_data = User::where('ID', auth()->user()->ID)->with('Office')->first();
        //category codes
        $modules = Category::select('ID', 'CategoryCode', 'CategoryName')
            ->where('CategoryType', 'Module')->orderBy('CategorySeq', 'desc')->get();
        // dd($modules);
        $solutions = Category::select('ID', 'CategoryCode', 'CategoryName')
            ->where('CategoryType', 'Solution')->orderBy('CategorySeq', 'desc')->get();
        //priority
        $priorities = Category::select('ID', 'CategoryCode', 'CategoryName')
            ->where('CategoryType', 'Priority')->orderBy('CategorySeq', 'desc')->get();

        return view('tickets.create', compact('user_data', 'modules', 'solutions', 'priorities'));
    }

    /**
     * show picked ticket
     */
    public function pick($ticket_id)
    {
        $ticket = Ticket::where('ID', $ticket_id)->first();
        // abort if no ticket
        if ($ticket == null) {
            abort(404);
        }
        $user = new User();
        return view('tickets.picked_ticket', compact('ticket', 'user'));
    }

    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            //fetch the ticket
            $ticket = Ticket::where('Id', $request->ID)->first();
            $user = Auth::user();

            //*update comment track
            Message::create([
                'ID' => guid(),
                'TicketID' =>  $ticket->Id,
                'TicketNo' => $ticket->TicketNo,
                'Role' => $user->Role,
                'CreatedBy' => $user->ID,
                'Comment' => $request->Message,
                'LastModifiedBy' => $user->ID,
                'Type' => 'Status',
                'PreviousValue' => $ticket->Status,
                'NewValue' => $request->Status
            ]);

            // if status is solved send user an email
            $details = [];

            if ($request->Status == 2) {
                //update the ticket status
                DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
                    'Status' => $request->Status,
                    'SolvedBy' => $user->ID,
                    'SolvedOn' => \Carbon\Carbon::now()
                ]);
                try {
                    $details['email'] = $ticket->user->Email;
                    $details['ticket'] = $ticket;
                    TicketSolvedJob::dispatch($details);
                } catch (Exception $e) {
                    return response()->json(['status' => 'warning', 'message' => 'This Email no longer exists in the system or it has been changed by Admin. Please contact IT support.']);
                }
            }
            // if open to colosed
            if (($ticket->Status == 1) && ($request->Status == 3)) {
                //update the ticket status
                DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
                    'Status' => $request->Status,
                    'SolvedBy' => $user->ID,
                    'SolvedOn' => \Carbon\Carbon::now()
                ]);
            }
            if ($request->Status == 3) {
                //update the ticket status
                DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
                    'Status' => $request->Status,
                    'ClosedBy' => $user->ID,
                    'ClosedOn' => \Carbon\Carbon::now()
                ]);
            }

            // if ($request->Status == 4) {
            //     //update the ticket status
            //     DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
            //         'Status' => $request->Status,
            //         'ReopenedBy' => $user->ID,
            //         'ReopenedOn' => \Carbon\Carbon::now()
            //     ]);
            // }

            if ($request->Status == 0) {
                //update the ticket status on hold
                DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
                    'Status' => $request->Status,
                    'OnholdBy' => $user->ID,
                    'OnholdOn' => \Carbon\Carbon::now()
                ]);
            }
            if ($request->Status == 1) {
                //update the ticket status on hold
                DB::table('TIC_TicketInfo')->where('Id', $request->ID)->update([
                    'Status' => $request->Status,
                    'ReopenedBy' => $user->ID,
                    'ReopenedOn' => \Carbon\Carbon::now()
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            Log::warning(json_encode($th));
            DB::rollback();
            return response()->json(['status' => 'warning', 'message' => $th->getMessage()]);
        }
        // toast('Ticket Status updated successfully', 'success');


        return response()->json(['status' => 'success', 'message' => 'Ticket Status updated successfully']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validation
        $this->validate($request, [
            'ticket_title' => 'required',
            'ticket_description' => 'required',
            'module' => 'required',
            'sub_module' => 'required',
            'solution' => 'required',
            'priority' => 'required',
            'OfficeID' => 'required',
            // 'attachments.*' => 'mimes:doc,pdf,docx,zip'
        ]);

        //* VALIDATE THE FILES
        if ($request->hasFile('attachments')) {
            //file size
            $maxsize    = 2000000; // bytes for 2mb
            // Allowed Extensions
            $allowedfileExtension = ['pdf', 'jpg', 'png', 'PNG', 'jpeg', 'csv', 'doc', 'docx', 'txt', 'xlx', 'xls', 'xlsx', 'text', 'XLSX'];
            foreach ($att = $request->file('attachments') as $key => $file) {
                // dd($file->getClientMimeType() == "application/octet-stream");
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if (!$check && ($file->getClientMimeType() != "application/octet-stream")) {
                    Alert::error('File Format', 'Only pdf,jpg,png,jpeg,csv,doc,docx,txt,xlx,xls,xlsx Allowed')->persistent(true, false);
                    return redirect()->back()->withInput();
                }
                // dd($file->getSize());
                if ($file->getSize() > $maxsize) {
                    Alert::error('File Size', 'File size should be less than 2MB')->persistent(true, false);
                    return redirect()->back()->withInput();
                }
            }
        }
        // ticket object
        $ticket = new Ticket();
        $ticket_created = Carbon::now()->toDateTimeString();
        $user_id = Auth::user()->ID;

        //get solution name
        $solution_name = Category::where('ID', $request->solution)->pluck('CategoryName')->first();
        $module_name =  Category::where('ID', $request->module)->pluck('CategoryName')->first();
        $sub_module_name =  Category::where('ID', $request->sub_module)->pluck('CategoryName')->first();
        $priority_name =  Category::where('ID', $request->priority)->pluck('CategoryName')->first();
        // create new ticket
        $new_ticket_id = $ticket->create([
            'ID' => guid(),
            'TicketNo' => $ticket->generateUniqueCode(Auth::user()->OfficeID),
            'TicketDate' => $ticket_created,
            'OfficeID' => $request->OfficeID,
            'UserID' => $user_id,
            'LoginID' => Auth::user()->LoginID,

            'Title' => $request->ticket_title,
            'Description' => $request->ticket_description,

            'SolutionID' => $request->solution,
            'SolutionName' => $solution_name,

            'ModuleId'  => $request->module,
            'ModuleName' => $module_name,

            'SubModuleId'  => $request->sub_module,
            'SubModuleName' => $sub_module_name,

            'PriorityId'  => $request->priority,
            'Priority' => $priority_name,

            'Status' => 1,
            'CreatedBy' => Auth::user()->full_name,

            'CreatedOn' => $ticket_created,
            'LastModifiedBy' => Auth::user()->full_name,
            'LastModifiedOn' => '',
            'IsActive' => 1,
            'IsDeleted' => 0,
        ])->ID;

        // get inserted ticket
        $new_ticket = Ticket::where('ID', $new_ticket_id)->first();

        // get files
        $attachments = $request->file('attachments');

        // check for files
        if ($request->hasFile('attachments')) {
            foreach ($attachments as $key => $file) {
                $attachment = new Attachment();
                $attachment->ID = guid();

                $attachment->TicketID = $new_ticket_id;
                $attachment->TicketNo = $new_ticket->TicketNo;

                // new file name
                $fileName =   $file->getClientOriginalName();

                // move the file to desired folder
                $request->attachments[$key]->move(storage_path('attachments'), $fileName);

                // assign the location of folder to the model
                $attachment->FileName = $fileName;
                $attachment->FilePath = 'attachments/' . $fileName;

                $attachment->CreatedBy = $user_id;
                $attachment->CreatedOn = $ticket_created;

                $attachment->LastModifiedBy = Auth::user()->ID;

                $attachment->IsActive = 1;
                $attachment->IsDeleted = 0;

                $attachment->save();
            }
        }
        //! Removed for email optimizaiton
        // notify the ticket creator via e-mail
        // TODO add an option for making it optional from the UI
        // $details['email'] = Auth::user()->Email;
        // $details['ticket'] = $new_ticket;
        // $details['admin'] = false;
        //queue the user's email notification
        // dispatch(new \App\Jobs\SendEmailJob($details));
        //notify all admins via e-mail
        // $users = User::where('Role', 1)->get();
        // foreach ($users as $user) {
        //     $details['email'] = $user->Email;
        //     $details['ticket'] = $new_ticket;
        //     $details['admin'] = true;
        // queue the admins' email notification
        // dispatch(new \App\Jobs\SendEmailJob($details));
        // }
        // log ticket history

        TicketStatusHistory::create([
            'ID' => guid(),
            'TicketID' => $new_ticket->Id,
            'TicketNo' => $new_ticket->TicketNo,
            'PriorStatus' => 0,
            'Status' => 1,
            'Description' => 'Open',
            'CreatedBy' => Auth::user()->full_name,
            'LastModifiedBy' => Auth::user()->full_name,
        ]);
        //notify ticket created
        $user = Auth::user();
        Message::create([
            'ID' => guid(),
            'TicketID' =>  $new_ticket->Id,
            'TicketNo' => $new_ticket->TicketNo,
            'Role' => $user->Role,
            'CreatedBy' => $user->ID,
            'Comment' => 'Ticket Created',
            'LastModifiedBy' => $user->ID,
            'Type' => 'Status',
            'PreviousValue' => 0,
            'NewValue' => 1
        ]);
        //notify user ticket created successfully
        toast('Ticket Created successfully', 'success');

        return redirect()->route('tickets.my_tickets');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        // dd($ticket->attachments->toArray());
        $messages = Message::where('TicketID', $ticket->Id)->with('user')
            ->orderBy('CreatedOn', 'asc')->get();

        // $messages = [];

        $user = new User();
        return view('tickets.picked_ticket', compact('ticket', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'TicketID' => 'required',
            'Title' => 'required',
            'Description' => 'required'
        ]);
        // get input data array
        $input = $request->all();
        // remove unrequred data
        unset($input['TicketID']);

        // update ticket
        DB::table('TIC_TicketInfo')->where('ID', $request->TicketID)->update([
            'Title' => $request->Title,
            'Description' => $request->Description,
        ]);

        // // update the ticket information
        // $ticket = Ticket::where('ID', $request->TicketID)->first();
        // // dd($input);
        // $ticket->Title = $input['Title'];
        // $ticket->Description = $input['Description'];
        // $ticket->save();
        // $ticket->update([
        //     'Title'=>$input['Title'],
        //     'Description'=>$input['Description'],
        // ]);
        // inform ticket update
        // toast('ticket updated successfully !!', 'success');

        return response()->json(['status'=>'success', 'message'=>'Ticket Updated.']);

        // return redirect()->back()->with('success', 'ticket updated successfully !!');
    }

    /**
     * Assign a user a ticket
     */
    public function assignTicket(Request $request, $TicketID = null)
    {
        // dd($request);

        if (request()->ajax()) {

            $request->validate([
                'AssignedTo' => 'required',
                'Comment' => 'required',
            ]);
            try {
                DB::beginTransaction();
                $ticket = Ticket::where('ID', $request->TicketID)->first();

                //Log on tic_commenttrack
                $user = Auth::user();
                Message::create([
                    'ID' => guid(),
                    'TicketID' =>  $ticket->Id,
                    'TicketNo' => $ticket->TicketNo,
                    'Role' => $user->Role,
                    'CreatedBy' => $user->ID,
                    'Comment' => $request->Comment,
                    'LastModifiedBy' => $user->ID,
                    'Type' => $request->status ? $request->status : 'Assign',
                    'PreviousValue' =>  $ticket->AssignedTo,
                    'NewValue' => $request->AssignedTo
                ]);

                //ticket assigning history
                TicketAssign::create([
                    'ID' => guid(),
                    'TicketId' => $request->TicketID,
                    'AssignedBy' => Auth::user()->ID,
                    'AssignedTo' => $request->AssignedTo,
                    'Comment' => $request->Comment,
                ]);
                //*log on ticket
                Ticket::where('ID', $request->TicketID)->update([
                    'AssignedTo' => $request->AssignedTo,
                    'AssignedOn' => \Carbon\Carbon::now()
                ]);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
            }


            //TODO: Notify the user Notification
            // $notify = new Notification();
            // $ticket = Ticket::where('ID', $request->TicketID)->first();
            // if (Auth::user()->Role == 1) {
            //     $data['Message'] = '<a href="' . URL::signedRoute('tickets.pick', [$request->TicketID]) . '"> #' . $ticket->TicketNo . '</a> Assigned to' . TicketAssign::latest()->first()->assignedTo->full_name;
            //     $notify->notifyUser($request->AssignedTo, 'Hi', 'Ticket ' . $data['Message'], 'Thanks');
            // } else {
            //     $data['Message'] = '<a href="' . URL::signedRoute('tickets.show', [$request->TicketID]) . '"> #' . $ticket->TicketNo . '</a> Assigned to' . TicketAssign::latest()->first()->assignedTo->full_name;
            //     $notify->notifyUser($ticket->CreatedBy, 'Hi', 'Ticket ' . $data['Message'], 'Thanks');
            // }

            // notify ticket of the assigning
            // $notify->notifyTicket($ticket, '', 'Ticket Assinged to <small>' . $ticket->assignedTo->full_name . '</small>', '');

            // notify success
            // toast('Ticket Assigned !!', 'success');
            //  $assignedTo = $ticket->assignedTo;
            return response()->json(['message' => 'Ticket Assigned', 'assignedTo' => '']);
            //
        }
        // else {

        //     if (!$TicketID) {
        //         abort(404);
        //     }

        //     $admin_users = User::where('Role', 1)->get();
        //     $ticket = Ticket::where('Id', $TicketID)->with('user')->first();
        //     // $ticket = DB::table('TIC_TicketInfo')->where('TIC_TicketInfo.Id', $TicketID)
        //     // ->join('Cust_User','TIC_TicketInfo.UserID','=','Cust_User.ID')->first();
        //     // dd($ticket);
        //     $assign_histories = TicketAssign::where('TicketID', $TicketID)->latest()->get();
        //     return view('tickets.assign_ticket', compact('admin_users', 'ticket', 'assign_histories'));
        // }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
    }
}
