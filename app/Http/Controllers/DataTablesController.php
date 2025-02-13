<?php

namespace App\Http\Controllers;

use App\Models\ClientParty;
use App\Models\KRABillingHeader;
use App\Models\TRABillingHeader;
use App\Models\RRABillingHeader;
use App\Models\Ticket;
use App\Models\TicketView;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use JeroenNoten\LaravelAdminLte\Components\Tool\Datatable;
use Yajra\Datatables\Datatables;
use DB;

class DataTablesController extends Controller
{
    public $auth_user;

    function __construct()
    {
        $this->middleware('auth');
        $this->auth_user = Auth::user();
    }
    public function allTickets()
    {
        $tickets = TicketView::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'OfficeName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )->with('user');

        $tickets = $tickets->whereNotIn('Status', [3]);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="">
                    <p class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></p>
                </a>';
                return $html;
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->editColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 0:
                        return '<span class="badge badge-xs badge-purple"
                            style="background-color: #6f42c1; color: white;">On Hold</span>';
                        break;
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            // ->addColumn('AssignedTo', function ($row) {
            //     return ($row->assignedTo != null) ?  $row->assignedTo->full_name : 'Not Assigned';
            // })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority', 'AssignedTo'])
            ->make(true);
    }
    public function myTickets()
    {
        $all_office_id = Office::where('Code', 'ALL')->value('ID');

        $tickets = TicketView::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'OfficeName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        );
        // if (isset($all_office_id )) {
        //     $tickets = $tickets->Where('OfficeID', $all_office_id)->orWhere('Status', '=', 1);
        // }

        // if (url()->previous() === url('ticktes/my-office-tickets')) {
        //     $tickets->whereIn('UserID', function ($query) {
        //         $query->select('ID')->from('Cust_user')->where('OfficeID', Auth::user()->OfficeID);
        //     });
        // } else {
        $tickets->where('UserID', Auth::user()->ID);
        $tickets = $tickets->whereNotIn('Status', [3]);

        // }

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                return '<a href="' . URL::signedRoute('tickets.show', [$row->ID]) . '" class="">
                <p class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></p>
                </a>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.show', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
                // return ($row->Priority);
            })
            ->editColumn('Status', function ($row) {

                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Clos                     ed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            // ->addColumn('OfficeName', function ($row) {
            //     $user = User::select('OfficeID')
            //         ->where('ID', $row->UserID)
            //         ->with('office')
            //         ->first();
            //     return $user->office->Name;
            // })
            // ->addColumn('AssignedTo', function ($row) {
            //     return ($row->assignedTo != null) ?  $row->assignedTo : 'Not Assigned';
            // })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn ? with(new Carbon($row->CreatedOn))->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function myOfficeTickets()
    {
        $all_office_id = Office::where('Code', 'ALL')->value('ID');

        $tickets = TicketView::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'OfficeName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        );
        // if (isset($all_office_id )) {
        //     $tickets = $tickets->Where('OfficeID', $all_office_id)->orWhere('Status', '=', 1);
        // }
        // if (url()->previous() === url('ticktes/my-office-tickets')) {
        //     $tickets->whereIn('UserID', function ($query) {
        //         $query->select('ID')->from('Cust_user')->where('OfficeID', Auth::user()->OfficeID);
        //     });
        // } else {
        $tickets->where('OfficeID', Auth::user()->OfficeID);
        $tickets = $tickets->whereNotIn('Status', [3]);
        // }

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                return '<a href="' . URL::signedRoute('tickets.show', [$row->ID]) . '" class=" ">
                <p class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></p>
                </a>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.show', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
                // return ($row->Priority);
            })
            ->editColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 0:
                        return '<span class="badge badge-xs badge-purple"
                            style="background-color: #6f42c1; color: white;">On Hold</span>';
                        break;
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            // ->addColumn('OfficeName', function ($row) {


            //     $user = "";
            //     return $user;
            //     // $user = User::select('OfficeID')
            //     //     ->where('ID', $row->UserID)
            //     //     ->with('office')
            //     //     ->first();
            //     // return $user->office->Name;
            // })
            // ->addColumn('AssignedTo', function ($row) {
            //     return ($row->assignedTo != null) ?  $row->assignedTo->full_name : 'Not Assigned';
            // })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn ? with(new Carbon($row->CreatedOn))->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function assigned()
    {
        $user = Auth::user();
        if ($user) {
            if ($user->FirstName && $user->LastName === null) {
                $fullName = $user->FirstName;
            } elseif ($user->FirstName && $user->LastName) {
                $fullName = $user->FirstName . ' ' . $user->LastName;
            } else {
                $fullName = 'Not Assigned';
            }
        } else {
            $fullName = 'Not Assigned';
        }

        $tickets = TicketView::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'OfficeName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )->where('AssignedTo', $fullName);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                $html = '
                        <a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="">
                           <p class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></p>
                        </a>';
                return $html;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->editColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 0:
                        return '<span class="badge badge-xs badge-purple"
                            style="background-color: #6f42c1; color: white;">On Hold</span>';
                        break;
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority', 'Details'])
            ->make(true);
    }

    public function closedTickets()
    {
        $tickets = Ticket::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )
            ->where('Status', '=', 3);
        if (Auth::user()->Role != 1) {
            $tickets->where('CreatedBy', Auth::user()->ID);
        }

        $tickets = $tickets->whereNotIn('Status', [3]);


        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {

                // switch ($row->Status) {
                //     case 1:
                //         $html = '
                //         <a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                //             <i class="fas fa-dot-circle"></i> View
                //         </a>';
                //         break;
                //     case 2:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-primary m-1 shadow">
                //                 <i class="fas fa-check"></i> Solved
                //             </a>';
                //         break;
                //     case 3:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-success m-1 shadow">
                //                 <i class="fas fa-thumbs-up"></i> Closed
                //             </a>';
                //         break;
                //     case 4:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-danger m-1 shadow">
                //                 <i class="fas fa-times"></i> Re-Opened
                //             </a>';
                //         break;
                //     default:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-secondary m-1 shadow">
                //     <i class="fas fa-smile"></i> Settled
                // </a>';
                //         break;
                // }
                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                            <i class="fas fa-dot-circle"></i> View
                        </a>';
                if ($row->AssignedTo != null) {
                    $html .= '<a href="' . URL::signedRoute('tickets.assign_ticket', [$row->ID]) . '" class="btn btn-info btn-xs shadow">Re-Assign</a>';
                } else {
                    $html .= '<a href="' . URL::signedRoute('tickets.assign_ticket', [$row->ID]) . '" class="btn btn-outline-info btn-xs">Assign</a>';
                }
                return $html;
            })
            ->addColumn('OfficeName', function ($row) {
                $user = User::select('OfficeID')
                    ->where('ID', $row->UserID)
                    ->with('office')
                    ->first();
                return $user->office->Name;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->addColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
                // return ($row->Priority);
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            // ->editColumn('CreatedBy', function ($row) {
            //     $user = User::where('ID', $row->UserID)->first();
            //     return  $user ? $user->CreatedBy : '';
            // })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function openTickets()
    {
        $tickets = Ticket::select(
            'ID',
            'TicketNo',
            'UserID',
            'OfficeID',
            'Description',
            'SolutionName',
            'ModuleName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )
            ->where('Status', '=', 1);

        if (Auth::user()->Role != 1) {
            $tickets->where('CreatedBy', Auth::user()->ID);
        }

        $tickets = $tickets->whereNotIn('Status', [3]);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {

                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                    View </a>';

                return $html;
            })
            ->addColumn('AssignedTo', function ($row) {
                return ($row->assignedTo != null) ? $row->assignedTo->full_name : 'Not Assigned';
            })
            ->addColumn('OfficeName', function ($row) {
                $user = User::select('OfficeID')
                    ->where('ID', $row->UserID)
                    ->with('office')
                    ->first();
                return isset($row->Office) ? $row->Office->Name : $user->office->Name;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->addColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                switch (trim($row->Priority)) {
                    case "ShowStopper":
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case "High":
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case "Medium":
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case "Low":
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">N/A</span>';
                        break;
                }
                // return ($row->Priority);
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function reOpenedTickets()
    {
        $tickets = Ticket::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )
            ->where('Status', '=', 4);
        if (Auth::user()->Role != 1) {
            $tickets->where('CreatedBy', Auth::user()->ID);
        }

        $tickets = $tickets->whereNotIn('Status', [3]);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                // switch ($row->Status) {
                //     case 1:
                //         $html = '
                //         <a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                //             <i class="fas fa-dot-circle"></i> View
                //         </a>';
                //         break;
                //     case 2:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-primary m-1 shadow">
                //                 <i class="fas fa-check"></i> Solved
                //             </a>';
                //         break;
                //     case 3:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-success m-1 shadow">
                //                 <i class="fas fa-thumbs-up"></i> Closed
                //             </a>';
                //         break;
                //     case 4:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-danger m-1 shadow">
                //                 <i class="fas fa-times"></i> Re-Opened
                //             </a>';
                //         break;
                //     default:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-secondary m-1 shadow">
                //     <i class="fas fa-smile"></i> Settled
                // </a>';
                //         break;
                // }
                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                <i class="fas fa-dot-circle"></i> View
            </a>';
                if ($row->AssignedTo != null) {
                    $html .= '<a href="' . URL::signedRoute('tickets.assign_ticket', [$row->ID]) . '" class="btn btn-info btn-xs shadow">Re-Assign</a>';
                } else {
                    $html .= '<a href="' . URL::signedRoute('tickets.assign_ticket', [$row->ID]) . '" class="btn btn-outline-info btn-xs">Assign</a>';
                }
                return $html;
            })
            ->addColumn('OfficeName', function ($row) {
                $user = User::select('OfficeID')
                    ->where('ID', $row->UserID)
                    ->with('office')
                    ->first();
                return $user->office->Name;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->addColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                // switch ($row->Priority) {
                //     case 'ShowStopper':
                //         return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                //         break;
                //     case 'High':
                //         return '<span class="badge badge-xs badge-warning">High</span>';
                //         break;
                //     case 'Medium':
                //         return '<span class="badge badge-xs badge-primary">Medium</span>';
                //         break;
                //     case 'Low':
                //         return '<span class="badge badge-xs badge-info">Low</span>';
                //         break;
                //     default:
                //         return '<span class="badge badge-xs badge-secondary"></span>';
                //         break;
                // }
                return ($row->Priority);
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            // ->editColumn('CreatedBy', function ($row) {
            //     $user = User::where('ID', $row->UserID)->first();
            //     return  $user ? $user->CreatedBy : '';
            // })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function solvedTickets()
    {
        $tickets = Ticket::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )
            ->where('Status', '=', 2);
        if (Auth::user()->Role != 1) {
            $tickets->where('CreatedBy', Auth::user()->ID);
        }

        $tickets = $tickets->whereNotIn('Status', [3]);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {
                // switch ($row->Status) {
                //     case 1:
                //         $html = '
                //         <a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                //             <i class="fas fa-dot-circle"></i> View
                //         </a>';
                //         break;
                //     case 2:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-primary m-1 shadow">
                //                 <i class="fas fa-check"></i> Solved
                //             </a>';
                //         break;
                //     case 3:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-success m-1 shadow">
                //                 <i class="fas fa-thumbs-up"></i> Closed
                //             </a>';
                //         break;
                //     case 4:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-danger m-1 shadow">
                //                 <i class="fas fa-times"></i> Re-Opened
                //             </a>';
                //         break;
                //     default:
                //         $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-secondary m-1 shadow">
                //     <i class="fas fa-smile"></i> Settled
                // </a>';
                //         break;
                // }
                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                <i class="fas fa-dot-circle"></i> View
            </a>';
                return $html;
            })
            ->addColumn('OfficeName', function ($row) {
                $user = User::select('OfficeID')
                    ->where('ID', $row->UserID)
                    ->with('office')
                    ->first();
                return $user->office->Name;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->addColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                // switch ($row->Priority) {
                //     case 'ShowStopper':
                //         return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                //         break;
                //     case 'High':
                //         return '<span class="badge badge-xs badge-warning">High</span>';
                //         break;
                //     case 'Medium':
                //         return '<span class="badge badge-xs badge-primary">Medium</span>';
                //         break;
                //     case 'Low':
                //         return '<span class="badge badge-xs badge-info">Low</span>';
                //         break;
                //     default:
                //         return '<span class="badge badge-xs badge-secondary"></span>';
                //         break;
                // }
                return ($row->Priority);
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            ->addColumn('AssignedTo', function ($row) {
                return ($row->assignedTo != null) ? $row->assignedTo->full_name : 'Not Assigned';
            })
            // ->editColumn('CreatedBy', function ($row) {
            //     $user = User::where('ID', $row->UserID)->first();
            //     return  $user ? $user->CreatedBy : '';
            // })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }
    public function settledTickets()
    {
        $tickets = Ticket::select(
            'ID',
            'TicketNo',
            'UserID',
            'Description',
            'SolutionName',
            'ModuleName',
            'SubModuleName',
            'AssignedTo',
            'Title',
            'Status',
            'Priority',
            'CreatedOn',
            'CreatedBy'
        )
            ->where('Status', '=', 5);
        if (Auth::user()->Role != 1) {
            $tickets->where('CreatedBy', Auth::user()->ID);
        }

        $tickets = $tickets->whereNotIn('Status', [3]);

        return Datatables::of($tickets)
            ->addColumn('Action', function ($row) {

                $html = '<a href="' . URL::signedRoute('tickets.pick', [$row->ID]) . '" class="btn btn-xs btn-warning m-1 shadow">
                <i class="fas fa-dot-circle"></i> View
            </a>';

                return $html;
            })
            ->addColumn('OfficeName', function ($row) {
                $user = User::select('OfficeID')
                    ->where('ID', $row->UserID)
                    ->with('office')
                    ->first();
                return $user->office->Name;
            })
            ->addColumn('Details', function ($row) {
                return '<span calss="small">' . $row->SolutionName . '>' . $row->ModuleName . '>' . $row->SubModuleName . '</span>';
            })
            ->editColumn('TicketNo', function ($row) {
                return '<a href=' . URL::signedRoute('tickets.pick', [$row->ID]) . '>' . str_replace(' ', '', $row->TicketNo) . '</a>';
            })
            ->addColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 1:
                        return '<span class="badge badge-xs badge-warning">Open</span>';
                        break;
                    case 2:
                        return '<span class="badge badge-xs badge-primary">Solved</span>';
                        break;
                    case 3:
                        return '<span class="badge badge-xs badge-success">Closed</span>';
                        break;
                    case 4:
                        return '<span class="badge badge-xs badge-danger">Re-Opened</span>';
                        break;
                    case 5:
                        return '<span class="badge badge-xs badge-secondary">Settled</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Priority', function ($row) {
                switch ($row->Priority) {
                    case 'ShowStopper':
                        return '<span class="badge badge-xs badge-danger">ShowStopper</span>';
                        break;
                    case 'High':
                        return '<span class="badge badge-xs badge-warning">High</span>';
                        break;
                    case 'Medium':
                        return '<span class="badge badge-xs badge-primary">Medium</span>';
                        break;
                    case 'Low':
                        return '<span class="badge badge-xs badge-info">Low</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary"></span>';
                        break;
                }
            })
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:i:s');
            })
            // ->editColumn('CreatedBy', function ($row) {
            //     $user = User::where('ID', $row->UserID)->first();
            //     return  $user ? $user->CreatedBy : '';
            // })
            ->rawColumns(['TicketNo', 'Action', 'Status', 'Priority'])
            ->make(true);
    }

    /**!ENTITY REQUESTS DATATABLES*/
    /**!All Entity */
    public function allEntityRequests()
    {

        $user = auth()->user();
        $user_office = $user->OfficeCode;

        // Define common columns
        $commonColumns = [
            'Id',
            'Code',
            'Name',
            'CreatedBy',
            'CreatedOn',
            'Office',
            'OperatioEntityType',
            'LegalEntityType',
            'Status',
            'OfficeCode',
        ];

        // Initial query with common columns
        $entities = ClientParty::select($commonColumns);

        // Check permissions
        if (!$user->can('create-client-party-SCM')) {
            $entities->where('OfficeCode', $user_office);
        }

        // Retrieve entities
        $entities = $entities->get();


        // Return Datatable of $entities
        return Datatables::of($entities)
            ->editColumn('Code', function ($row) {
                return '<a href=' . URL::signedRoute("client-party.show", $row->Id) . '>' . $row->Code . '</a>';
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = '<a href=' . URL::signedRoute("client-party.show", $row->Id) . ' title="View">
                    <span class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></span></a>';
                if (($row->Status != 'Rejected' && $row->Status == 'Submitted')) {
                    $rawHtml .= '<a href="#" code=' . $row->Id . ' id="deny" class="text-danger reject" title="Reject">
                    <i class="fa fa-times-circle fa-lg"></i></a>';
                }
                return $rawHtml;
            })
            ->editColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 'Submitted':
                        return '<span class="badge badge-xs badge-warning">PendingFinanceApproval</span>';
                        break;
                    case 'Pending':
                        return '<span class="badge badge-xs badge-primary">Pending</span>';
                        break;
                    case 'Created':
                        return '<span class="badge badge-xs badge-success">Created</span>';
                        break;
                    case 'Rejected':
                        return '<span class="badge badge-xs badge-danger">Rejected</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">' . $row->Status . '</span>';
                        break;
                }
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn ? with(new Carbon($row->CreatedOn))->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['Status', 'Code', 'Action'])
            ->make(true);
    }

    public function approvalEntityRequests()
    {
        $user_office = auth()->user()->OfficeCode;

        $entities = ClientParty::select(
            'Id',
            'Code',
            'Name',
            'CreatedBy',
            'CreatedOn',
            'Office',
            'OperatioEntityType',
            'LegalEntityType',
            'Status',
            'OfficeCode'
        );

        // todo: if auth user has can-approve-my-office-entities
        $entities->where('Status', 'PendingManagerApproval')->where('OfficeCode', $user_office);


        return Datatables::of($entities)
            // ->editColumn('Code', function ($row) {
            //     return '<a href=' . URL::signedRoute("client-party.show", $row->Id) . '>' . $row->Code . '</a>';
            // })
            ->addColumn('Action', function ($row) {

                $rawHtml = '<a href=' . URL::signedRoute("client-party.show", $row->Id) . ' title="View">
                    <span class="text-center"> <i class="fas fa-info-circle fa-lg shadow"></i></span></a>';

                return $rawHtml;
            })
            ->editColumn('Status', function ($row) {
                switch ($row->Status) {
                    case 'Submitted':
                        return '<span class="badge badge-xs badge-warning">Submitted</span>';
                        break;
                    case 'Pending':
                        return '<span class="badge badge-xs badge-primary">Pending</span>';
                        break;
                    case 'Created':
                        return '<span class="badge badge-xs badge-success">Created</span>';
                        break;
                    case 'Rejected':
                        return '<span class="badge badge-xs badge-danger">Rejected</span>';
                        break;
                    default:
                        return '<span class="badge badge-xs badge-secondary">' . $row->Status . '</span>';
                        break;
                }
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn ? with(new Carbon($row->CreatedOn))->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['Status', 'Code', 'Action'])
            ->make(true);
    }


    public function users()
    {
        $users = User::with('roles');

        return Datatables::of($users)
            ->addColumn('FullName', function ($row) {
                return $row->FirstName . " " . $row->LastName;
            })
            ->addColumn('Office', function ($row) {
                return $row->office->Name;
            })
            ->addColumn('Roles', function ($row) {
                $html = '';
                if (!empty($row->getRoleNames())) {
                    foreach ($row->getRoleNames() as $v) {
                        $html .= '<label class="badge badge-success">' . $v . '</label> ';
                    }
                }
                return $html;
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = ' <a class="btn btn-xs btn-info" href=' . route("users.show", $row->ID) . '>Show</a>
                            <a class="btn btn-xs btn-primary" href=' . route("users.edit", $row->ID) . '>Edit</a> ';
                return $rawHtml;
            })
            ->rawColumns(['FullName', 'Roles', 'Office', 'Action'])
            ->make();
    }

    public function tims()
    {
        $invoice_creds = KRABillingHeader::select(
            'ID',
            'DocNo',
            'DocDate as DocDate',
            'DebtorName',
            'DocumentType as DocType',
            'DocStatus',
            'SubmissionStatus as Remarks',
            'KRAQRCode',
            'InvoiceType'
        );
        return Datatables::of($invoice_creds)
            ->editColumn('DocStatus', function ($row) {
                $status = "";
                if ($row->DocStatus == 'Submitted') {
                    $status = '<a href="' . $row->KRAQRCode . '" target="__blank">' . $row->DocStatus . '</a>';
                } else {
                    $status = $row->DocStatus;
                }
                return $status;
            })
            ->editColumn('DocDate', function ($row) {
                return Carbon::parse($row->DocDate)->format('m-d-Y');
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = "";
                if ($row->DocStatus != "Submitted") {
                    $rawHtml = '<a class="btn btn-xs btn-info mr-1 re-trigger" onclick="editInvoice(`' . $row->ID . '`,`re-trigger`,`' . $row->DocNo . '`)" title="Re-Trigger" id=' . $row->DocNo . '><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                    $rawHtml .= '<a class="btn btn-xs btn-warning mr-1 edit-invoice" onclick="editInvoice(`' . $row->ID . '`,`edit`)" title="Edit" id=' . $row->ID . '><i class="fa fa-edit" aria-hidden="true"></i></a>';
                }

                $rawHtml .= '<a class="btn btn-xs btn-primary mr-1" target="__blank" title="Invoice Data" href=' . route("tims.json", $row->ID) . '><i class="fa fa-download" aria-hidden="true"></i></a> ';

                if (($row->DocStatus == "Submitted") && ($row->InvoiceType == 'Export')) {
                    $rawHtml .= '<a class="btn btn-xs btn-primary" target="__blank" title="Invoice File" href=' . URL::signedRoute("tims.invoiceDownload", $row->ID) . '><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></a> ';
                }

                return $rawHtml;
            })
            ->rawColumns(['Action', 'DocStatus'])
            ->make();
    }

    public function efris()
    {

        $invoice_creds = DB::select('EXEC EFRIS_InvoiceRegister');

        return Datatables::of($invoice_creds)
            ->editColumn('Status', function ($row) {
                $status = "";
                if ($row->DocStatus == 'Submitted') {
                    $status = '<a href="' . $row->KRAQRCode . '" target="__blank">' . $row->DocStatus . '</a>';
                } else {
                    $status = $row->DocStatus;
                }
                return $status;
            })
            ->editColumn('Date', function ($row) {
                return $row->DocDate;
            })
            ->editColumn('Type', function ($row) {
                return $row->DocType;
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = "";
                if ($row->DocStatus == "FAILED") {
                    $rawHtml = '<a class="btn btn-xs btn-info mr-1 re-trigger" onclick="editInvoice(`' . $row->ID . '`,`re-trigger`,`' . $row->DocNo . '`)" title="Re-Trigger" id=' . $row->DocNo . '><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                    // $rawHtml .= '<a class="btn btn-xs btn-warning mr-1 edit-invoice" onclick="editInvoice(`'.$row->ID.'`,`edit`)" title="Edit" id=' . $row->ID . '><i class="fa fa-edit" aria-hidden="true"></i></a>';
                }

                // $rawHtml .= '<a class="btn btn-xs btn-primary mr-1" target="__blank" title="Invoice Data" href=' . route("tims.json", $row->ID) . '><i class="fa fa-download" aria-hidden="true"></i></a> ';

                // if (($row->DocStatus == "Submitted") && ($row->InvoiceType == 'Export')) {
                //     $rawHtml .= '<a class="btn btn-xs btn-primary" target="__blank" title="Invoice File" href=' . URL::signedRoute("tims.invoiceDownload", $row->ID) . '><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></a> ';
                // }
                return $rawHtml;
            })
            ->rawColumns(['Action', 'Status', 'Date', 'Type'])
            ->make();
    }

    public function tra()
    {
        // $invoice_creds = TRABillingHeader::select(
        //     'ID',
        //     'DocNo',
        //     'DocDate as DocDate',
        //     'DebtorName',
        //     'DocumentType as DocType',
        //     'DocStatus',
        //     'SubmissionStatus as Remarks',
        //     'KRAQRCode',
        //     'InvoiceType'
        // );
        $invoice_creds = TRABillingHeader::filterByOfficeCode(['TZ'])->select(
            'ID',
            'DocNo',
            'DocDate as DocDate',
            'DebtorName',
            'DocumentType as DocType',
            'DocStatus',
            'SubmissionStatus as Remarks',
            'TRAQRCode',
            'InvoiceType'
        );


        return Datatables::of($invoice_creds)
            ->editColumn('DocStatus', function ($row) {
                $status = "";
                if ($row->DocStatus == 'Submitted') {
                    $status = '<a href="' . $row->KRAQRCode . '" target="__blank">' . $row->DocStatus . '</a>';
                } else {
                    $status = $row->DocStatus;
                }
                return $status;
            })
            ->editColumn('DocDate', function ($row) {
                return Carbon::parse($row->DocDate)->format('m-d-Y');
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = "";
                if ($row->DocStatus != "Submitted") {
                    $rawHtml = '<a class="btn btn-xs btn-info mr-1 re-trigger" onclick="editInvoice(`' . $row->ID . '`,`re-trigger`,`' . $row->DocNo . '`)" title="Re-Trigger" id=' . $row->DocNo . '><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                    $rawHtml .= '<a class="btn btn-xs btn-warning mr-1 edit-invoice" onclick="editInvoice(`' . $row->ID . '`,`edit`)" title="Edit" id=' . $row->ID . '><i class="fa fa-edit" aria-hidden="true"></i></a>';
                }

                $rawHtml .= '<a class="btn btn-xs btn-primary mr-1" target="__blank" title="Invoice Data" href=' . route("tims.json", $row->ID) . '><i class="fa fa-download" aria-hidden="true"></i></a> ';

                if (($row->DocStatus == "Submitted") && ($row->InvoiceType == 'Export')) {
                    $rawHtml .= '<a class="btn btn-xs btn-primary" target="__blank" title="Invoice File" href=' . URL::signedRoute("tims.invoiceDownload", $row->ID) . '><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></a> ';
                }

                return $rawHtml;
            })
            ->rawColumns(['Action', 'DocStatus'])
            ->make();
    }


    public function traFITTZ()
    {

        $invoice_creds = TRABillingHeader::filterByOfficeCode(['AR'])->select(
            'ID',
            'DocNo',
            'DocDate as DocDate',
            'DebtorName',
            'DocumentType as DocType',
            'DocStatus',
            'SubmissionStatus as Remarks',
            'TRAQRCode',
            'InvoiceType'
        );


        return Datatables::of($invoice_creds)
            ->editColumn('DocStatus', function ($row) {
                $status = "";
                if ($row->DocStatus == 'Submitted') {
                    $status = '<a href="' . $row->KRAQRCode . '" target="__blank">' . $row->DocStatus . '</a>';
                } else {
                    $status = $row->DocStatus;
                }
                return $status;
            })
            ->editColumn('DocDate', function ($row) {
                return Carbon::parse($row->DocDate)->format('m-d-Y');
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = "";
                if ($row->DocStatus != "Submitted") {
                    $rawHtml = '<a class="btn btn-xs btn-info mr-1 re-trigger" onclick="editInvoice(`' . $row->ID . '`,`re-trigger`,`' . $row->DocNo . '`)" title="Re-Trigger" id=' . $row->DocNo . '><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                    $rawHtml .= '<a class="btn btn-xs btn-warning mr-1 edit-invoice" onclick="editInvoice(`' . $row->ID . '`,`edit`)" title="Edit" id=' . $row->ID . '><i class="fa fa-edit" aria-hidden="true"></i></a>';
                }

                $rawHtml .= '<a class="btn btn-xs btn-primary mr-1" target="__blank" title="Invoice Data" href=' . route("tims.json", $row->ID) . '><i class="fa fa-download" aria-hidden="true"></i></a> ';

                if (($row->DocStatus == "Submitted") && ($row->InvoiceType == 'Export')) {
                    $rawHtml .= '<a class="btn btn-xs btn-primary" target="__blank" title="Invoice File" href=' . URL::signedRoute("tims.invoiceDownload", $row->ID) . '><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></a> ';
                }

                return $rawHtml;
            })
            ->rawColumns(['Action', 'DocStatus'])
            ->make();
    }

    public function rra()
    {
        $invoice_creds = RRABillingHeader::select(
            'ID',
            'DocNo',
            'DocDate as DocDate',
            'DebtorName',
            'DocumentType as DocType',
            'DocStatus',
            'SubmissionStatus as Remarks',
            'RRA_SDC_Data_QRCode',
            'InvoiceType'
        );

        return Datatables::of($invoice_creds)
            ->editColumn('DocStatus', function ($row) {
                $status = "";
                if ($row->DocStatus == 'Submitted') {
                    $status = '<a href="' . $row->RRA_SDC_Data_QRCode . '" target="__blank">' . $row->DocStatus . '</a>';
                } else {
                    $status = $row->DocStatus;
                }
                return $status;
            })
            ->editColumn('DocDate', function ($row) {
                return Carbon::parse($row->DocDate)->format('m-d-Y');
            })
            ->addColumn('Action', function ($row) {
                $rawHtml = "";
                if ($row->DocStatus != "Submitted") {
                    $rawHtml = '<a class="btn btn-xs btn-info mr-1 re-trigger" onclick="editInvoice(`' . $row->ID . '`,`re-trigger`,`' . $row->DocNo . '`)" title="Re-Trigger" id=' . $row->DocNo . '><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                    $rawHtml .= '<a class="btn btn-xs btn-warning mr-1 edit-invoice" onclick="editInvoice(`' . $row->ID . '`,`edit`)" title="Edit" id=' . $row->ID . '><i class="fa fa-edit" aria-hidden="true"></i></a>';
                }

                $rawHtml .= '<a class="btn btn-xs btn-primary mr-1" target="__blank" title="Invoice Data" href=' . route("rra.json", $row->ID) . '><i class="fa fa-download" aria-hidden="true"></i></a> ';

                if (($row->DocStatus == "Submitted") && ($row->InvoiceType == 'Export')) {
                    $rawHtml .= '<a class="btn btn-xs btn-primary" target="__blank" title="Invoice File" href=' . URL::signedRoute("rra.invoiceDownload", $row->ID) . '><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></a> ';
                }

                return $rawHtml;
            })
            ->rawColumns(['Action', 'DocStatus'])
            ->make();
    }
}
