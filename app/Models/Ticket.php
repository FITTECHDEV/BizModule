<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    // use Cachable;
    use HasFactory, SoftDeletes, Notifiable;

    protected $guarded = [];
    // protected $fillable = [
    //   'Title',
    //   'Description',
    // ];

    protected $table = 'TIC_TicketInfo';

    protected $primarykey = 'Id';

    // public $timestamps = false;

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    // protected $dateFormat = 'Y-m-d H:m:s';


    // public function setDateAttribute($value)
    // {
    //   $this->attributes['created_at'] = (new Carbon($value))->format('d/m/y');
    // }

    //has many ticket histories
    public function ticketHistories()
    {
        return $this->hasMany(TicketStatusHistory::class, 'TicketID', 'Id');
    }

    public function TicketStatus($status)
    {
        switch ($status) {
            case 1:
                return  'Open';
                break;
            case 2:
                return  'Solved';
                break;
            case 3:
                return  'Closed';
                break;
            case 4:
                return  'Re-Opened';
                break;
            case 5:
                return  'Settled';
                break;
            default:
                return  '';
                break;
        }
    }
    public function TicketStatusPaint($status)
    {
        switch (trim($status)) {
            case 0:
                return '<span class="badge badge-xs bg-cyan">Pending</span>';
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
    }
    public function TicketPriorityPaint($priority)
    {
        switch (trim($priority)) {
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
    }
    public function generateUniqueCode($id)
    {
        // if id is empty use kenya
        if ($id == null) {
            $id = '4E7AD2C3-CC4F-4A90-AA24-4C1DEEF39A1F';
        }
        // generate code  sample KE2100000
        // get country code eg. KE
        $office = Office::select('Code')->where('ID', $id)->first();
        // get year 2-digit eg. 21
        $year = date('y');
        // get integer continuation
        // count the number of tickets then prepend the zeros;
        $ticket_count = Ticket::count();
        $ticket_number = str_pad($ticket_count, 5, '0', STR_PAD_LEFT);

        //CODE
        $code = trim($office->Code . '' . $year . '' . $ticket_number, "");
        // do {
        //   $code = random_int(100000, 999999);
        // } while ($this->where('TicketNo', '=', $code)->first());
        return $code;
    }

    public function getDateFilter($date)
    {
        $dateA = date('Y-m-d');
        $dateB = date('Y-m-d');
        if ($date == "Today") {
            $dateA = date('Y-m-d');
        } elseif ($date == "Yesterday") {
            $dateA = Carbon::yesterday();
        } elseif ($date == 'LastMonth') {
            $dateA = Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d');
            $dateB = Carbon::now()->startOfMonth()->format('Y-m-d');
        } elseif ($date == 'PreviousMonth') {
            $dateA = Carbon::now()->subMonth()->month;
        } elseif ($date == 'ThisQuarter') {
            $date = Carbon::now();
            $dateA = $date->firstOfQuarter()->format('Y-m-d');
            $dateB = $date->lastOfQuarter()->format('Y-m-d');
        } elseif ($date == 'Last3Months') {
            $dateA = Carbon::now()->startOfMonth()->subMonth(3);
            $dateB = Carbon::now()->startOfMonth();
        } elseif ($date == 'Last6Months') {
            $dateA = Carbon::now()->startOfMonth()->subMonth(6);
            $dateB = Carbon::now()->startOfMonth();
        }

        return [$dateA, $dateB];
    }

    /**
     * ticket user relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'ID');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'AssignedTo', 'ID');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'TicketID', 'Id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'TicketID', 'Id')->orderBy('CreatedOn', 'DESC');
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(NewNotifications::class, 'notifiable', '', '', 'TicketNo')
            ->orderBy('created_at', 'desc');
    }

    public function ticketAssign()
    {
        return $this->hasMany(TicketAssign::class, 'TicketID', 'Id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'OfficeID', 'ID');
    }

    public function ticketStatistics()
    {
        $auth_user = Auth::user();

        if ($auth_user->Role == 1) {
            return DB::select(DB::raw("select
                ti.Status as StatusNo,
                COUNT(ti.Status) as StatusCount,
                CAST(
                        CASE
                            WHEN ti.Status = 0
                                THEN 'ON HOLD'
                            WHEN ti.Status = 1
                                THEN 'OPEN'
                            WHEN ti.Status = 2
                                THEN 'SOLVED'
                            WHEN ti.Status = 3
                                THEN 'CLOSED'
                            WHEN ti.Status = 4
                                THEN ''
                            WHEN ti.Status = 5
                                THEN 'RE-OPENED'
                            ELSE ''
                        END AS varchar) as StatusName
                    from
                    tic_ticketinfo ti
                    group by ti.Status
                    order by StatusCount desc
                "));
        } else {
            return DB::select(DB::raw("select
                ti.Status as StatusNo,
                COUNT(ti.Status) as StatusCount,
                CAST(
                    CASE
                        WHEN ti.Status = 0
                            THEN 'ON HOLD'
                        WHEN ti.Status = 1
                            THEN 'OPEN'
                        WHEN ti.Status = 2
                            THEN 'SOLVED'
                        WHEN ti.Status = 3
                            THEN 'CLOSED'
                        WHEN ti.Status = 4
                            THEN ''
                        WHEN ti.Status = 5
                            THEN 'RE-OPENED'
                        ELSE ''
                    END AS varchar) as StatusName
                from
                tic_ticketinfo ti
                where ti.LoginID = '" . $auth_user->LoginID . "'
                group by ti.Status
                order by StatusCount desc
            "));
        }
    }

    public function ticketCount(String $column = null, $value = null, array $constraints = null)
    {
        $count = 0;
        $auth_user = Auth::user();

        if ($column == null || $value == null) {
            return $count;
        }

        if ($value == '*') {
            if ($auth_user->Role != 1) {
                $count = $this->where('CreatedBy', $auth_user->full_name)->count();
            } else {
                $count = Ticket::count();
            }
        } else {
            if ($auth_user->Role != 1) {
                $count = $this->where('CreatedBy', $auth_user->full_name)
                    ->where($column, (string)$value)->count();
            } else {
                $count = Ticket::where($column, $value)->count();
            }
        }

        if (isset($constraints) && !empty($constraints)) {
            if ($value == '*') {
                $ticket = new Ticket();
            } else {
                $ticket = Ticket::where($column, $value);
            }
            foreach ($constraints as $key => $constraint) {
                $ticket = $ticket->where($constraint[0], $constraint[1]);
            }
            if ($auth_user->Role != 1) {
                $ticket = $ticket->where('CreatedBy', $auth_user->full_name);
            }
            $count = $ticket->count();
        }
        return number_format($count, 0, '', ',');
    }
}
