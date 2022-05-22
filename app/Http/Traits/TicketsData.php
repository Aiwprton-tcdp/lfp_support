<?php 
namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

trait TicketsData {
    public static function GetForUser($request, $user_id) {
        $ticket_status = $request->get("ticket_status");
        // $ordering = $ticket_status == 1 ? "tickets.weight" : "tickets.updated_at";
        // $orderIndex = "DESC";
        $allDepartments = DepartmentsData::Get($request);

        if ($ticket_status == 1) {
            $data = DB::table("tickets")
                ->join("reasons", 'tickets.reason_id', "reasons.id")
                ->leftJoin('messages', function($query) {
                    $query->on('messages.ticket_id','=','tickets.id')
                        ->whereRaw('messages.id IN (SELECT MAX(m2.id) FROM messages as m2 join tickets as t2 on t2.id = m2.ticket_id GROUP BY t2.id)');
                })
                ->where("tickets.active", $ticket_status)
                ->where("tickets.user_id", $user_id)
                ->orWhere(function($query) use($ticket_status, $allDepartments) {
                    $query->where("tickets.active", $ticket_status)
                        ->WhereIn('tickets.department_id', $allDepartments);
                })
                ->select("tickets.id", "tickets.response_id", "tickets.user_id", "tickets.active", "tickets.weight", "tickets.updated_at", "reasons.name",
                "messages.user_id AS last_message_user_id")
                
                // DB::raw("COUNT('tickets.id') AS queue_number GROUP BY tickets.id"))
                // DB::raw("SELECT COUNT(t.id) AS queue_number FROM tickets AS t WHERE t.active = 1"))// AND t.weight < tickets.weight AND t.response_id = tickets.response_id"))
                // ->select(DB::raw("tickets.id, tickets.response_id, tickets.user_id, tickets.active, tickets.updated_at, reasons.name,
                // messages.user_id AS last_message_user_id,
                // (SELECT COUNT(t.id) AS queue_number, FROM tickets AS t WHERE t.active = 1 AND t.weight <= tickets.weight"))// ORDER BY t.user_id DESC
                
                ->orderBy(DB::raw("CASE WHEN messages.user_id = tickets.response_id THEN 1 WHEN messages.user_id != tickets.user_id THEN 2 WHEN messages.user_id = ".$user_id." THEN 3 ELSE 4 END"))
                // ->orderByDesc(DB::raw("ABS(last_message_user_id - ".$user_id.")"))
                ->orderByDesc("tickets.weight")
                ->orderBy("tickets.updated_at")
                ->get();

            $data = static::GetQueueNumbers($data);
            // for ($i = 0; $i < count($data); $i++) {
            //     $data[$i]->queue_number = static::GetQueueNumber($data[$i]->id, $data[$i]->user_id, $data[$i]->response_id, $data[$i]->weight);
            // }
        } else {
            $data = DB::table("tickets")
                ->join("reasons", 'tickets.reason_id', "reasons.id")
                ->where("tickets.active", $ticket_status)
                ->where("tickets.user_id", $user_id)
                ->orWhere(function($query) use($ticket_status, $allDepartments) {
                    $query->where("tickets.active", $ticket_status)
                        ->WhereIn('tickets.department_id', $allDepartments);
                })
                ->select("tickets.id", "tickets.response_id", "tickets.user_id", "tickets.active", "tickets.updated_at", "reasons.name")
                ->orderByDesc("tickets.updated_at")
                ->get();
        }

        return $data;
    }

    public static function GetForAdmin($request, $user_id) {
        $ticket_status = $request->get("ticket_status");
        $ordering = $request->get("ticket_status") == 1 ? "tickets.weight" : "tickets.updated_at";
        // $orderIndex = "DESC";

        if ($ticket_status == 1) {
            $data = DB::table("tickets")
                ->join('reasons', function($join) {
                    $join->on('tickets.reason_id', "reasons.id");
                })
                ->leftJoin('additional_responsives', function($join) use($user_id) {
                    $join->on('additional_responsives.ticket_id', "tickets.id")
                        ->where('additional_responsives.active', 1)
                        ->where('additional_responsives.user_id', $user_id);
                })
                ->leftJoin('messages', function($query) {
                    $query->on('messages.ticket_id','=','tickets.id')
                    ->whereRaw('messages.id IN (SELECT MAX(m2.id) FROM messages as m2 join tickets as t2 on t2.id = m2.ticket_id GROUP BY t2.id)');
                })
                ->where('tickets.active', $request->get("ticket_status"))
                ->where('tickets.response_id', $user_id)
                ->orWhere(function($query) use($ticket_status, $user_id) {
                    $query->where("tickets.active", $ticket_status)
                        ->where('additional_responsives.user_id', $user_id);
                })
                ->select("tickets.id", "tickets.response_id", "tickets.user_id", "tickets.weight", "tickets.with_coupon", "tickets.active", "tickets.updated_at", "reasons.name", "messages.user_id AS last_message_user_id")
                // ->orderBy(DB::raw("CASE WHEN messages.user_id = tickets.user_id THEN 1 WHEN messages.user_id = tickets.response_id THEN 3 ELSE 2 END"))
                ->orderBy(DB::raw("CASE 
                    WHEN messages.user_id = tickets.user_id AND ".$user_id." = tickets.response_id THEN 1
                    WHEN messages.user_id = tickets.response_id AND ".$user_id." != tickets.response_id THEN 2
                    WHEN ".$user_id." = messages.user_id AND messages.user_id != tickets.user_id AND messages.user_id != tickets.response_id THEN 3
                    WHEN ".$user_id." != messages.user_id AND messages.user_id != tickets.user_id AND messages.user_id != tickets.response_id THEN 4
                    WHEN messages.user_id = tickets.user_id AND ".$user_id." != tickets.response_id THEN 5
                    WHEN messages.user_id = tickets.response_id AND ".$user_id." = tickets.response_id THEN 6
                    ELSE 7 END"))
                // ->orderBy(DB::raw("CASE WHEN tickets.response_id = ".$user_id." THEN 1 ELSE 2 END"))
                ->orderByDesc($ordering)
                ->get();
        } else {
            $data = DB::table("tickets")
                ->join('reasons', function($join) {
                    $join->on('tickets.reason_id', "reasons.id");
                })
                ->leftJoin('additional_responsives', function($join) use($user_id) {
                    $join->on('additional_responsives.ticket_id', "tickets.id")
                        ->where('additional_responsives.active', 1)
                        ->where('additional_responsives.user_id', $user_id);
                })
                ->leftJoin('messages', function($query) {
                    $query->on('messages.ticket_id','=','tickets.id')
                        ->whereRaw('messages.id IN (SELECT MAX(m2.id) FROM messages as m2 join tickets as t2 on t2.id = m2.ticket_id GROUP BY t2.id)');
                })
                ->where('tickets.active', $request->get("ticket_status"))
                ->where('tickets.response_id', $user_id)
                ->orWhere(function($query) use($ticket_status, $user_id) {
                    $query->where("tickets.active", $ticket_status)
                        ->where('additional_responsives.user_id', $user_id);
                })
                ->select("tickets.id", "tickets.response_id", "tickets.user_id", "tickets.active", "tickets.updated_at", "reasons.name")
                ->orderByDesc("tickets.updated_at")
                ->get();
        }

        return $data;
    }

    public static function GetQueueNumber($ticket_id) {
        $Number = 1;
        $Numbers = [];

        $Data = DB::table("tickets")
            ->leftJoin('messages', function($query) {
                $query->on('messages.ticket_id','=','tickets.id')
                ->whereRaw('messages.id IN (SELECT MAX(m2.id) FROM messages as m2 join tickets as t2 on t2.id = m2.ticket_id GROUP BY t2.id)');
            })
            ->where("tickets.active", 1)
            ->select('tickets.id')
            ->orderBy(DB::raw("CASE WHEN messages.user_id = tickets.user_id THEN 1 ELSE 2 END"))
            ->orderByDesc("tickets.weight")
            ->orderBy("tickets.created_at")
            ->get()->toArray();
        
        for ($i = 0; $i < count($Data); $i++) {
            $Numbers[] = $Data[$i]->id;
        }
    
        $Number += array_search($ticket_id, $Numbers);

        return $Number;
    }

    public static function GetQueueNumbers($tickets) {
        $Numbers = [];

        $Data = DB::table("tickets")
            ->leftJoin('messages', function($query) {
                $query->on('messages.ticket_id','=','tickets.id')
                ->whereRaw('messages.id IN (SELECT MAX(m2.id) FROM messages as m2 join tickets as t2 on t2.id = m2.ticket_id GROUP BY t2.id)');
            })
            ->where("tickets.active", 1)
            ->select('tickets.id')
            ->orderBy(DB::raw("CASE WHEN messages.user_id = tickets.user_id THEN 1 ELSE 2 END"))
            ->orderByDesc("tickets.weight")
            ->orderBy("tickets.created_at")
            ->get()->toArray();

        for ($i = 0; $i < count($Data); $i++) {
            $Numbers[] = $Data[$i]->id;
        }

        foreach ($tickets as $t) {
            $t->queue_number = array_search($t->id, $Numbers) + 1;
        }
            // dd($tickets);

        return $tickets;
    }
}

?>