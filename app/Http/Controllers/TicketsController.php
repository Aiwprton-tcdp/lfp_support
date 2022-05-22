<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Reason;
use App\Models\Message;
use App\Models\History;
use App\Models\Operation;
use App\Models\Additional_responsive;
use App\Models\Hint;
use App\Http\Traits\CRM;
use App\Http\Traits\UserData;
use App\Http\Traits\TicketsData;
use App\Http\Traits\DepartmentsData;
use Carbon\Carbon;
use Exception;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TicketsController extends Controller {
    use CRM;
    use UserData;

    public function Start(Request $request) {
        static::$C_REST_WEB_HOOK_URL = "";
        static::$AUTH_ID = $_REQUEST["AUTH_ID"];
        static::$DOMAIN = $_REQUEST["DOMAIN"];
        static::$REFRESH_ID = $_REQUEST["REFRESH_ID"];
        static::$member_id = $_REQUEST["member_id"];
        static::$APP_SID = $_REQUEST["APP_SID"];

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $isSupporter = static::IsSupporter($user_id);

        return Inertia::render('Tickets', [
            'isSupporter' => $isSupporter,
            'user_id' => $user_id,
        ]);
    }

    public function Get(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];

        $data = static::IsSupporter($user_id)
            ? TicketsData::GetForAdmin($request, $user_id)
            : TicketsData::GetForUser($request, $user_id);
        
        return response()->json([
          "data" => $data,
          "user_id" => $user_id,
        ]);
    }

    public function New(Request $request) {
        static::InitialSetup($request);
        
        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        if (static::IsSupporter($user_id) == true) {
            return response()->json([
                "message" => "Вам не доступна возможность создавать тикет.",
                "status" => "error"
            ]);
        }
        
        $coupon_id = $request->get("coupon_id");
        if ($coupon_id == 0) {
            $CouponWeight = 0;
        } else {
            $CouponData = static::GetCouponWeight($coupon_id);
            try {
                if ($CouponData == 0) {
                    return response()->json([
                        "message" => "Указанный купон не существует или уже использован.",
                        "status" => "error"
                    ]);
                }
            }
            catch(Exception) {
                
            }

            $CouponWeight = $CouponData->weight;
            if ($CouponWeight > 0) {
                DB::table("coupons")->where("id", $CouponData->id)->update(["active" => 0]);
                $HistoryRes = History::create([
                    "response_id" => $user["result"]["ID"],
                    "coupon_id" => $CouponData->id,
                ]);
                $OperationRes = Operation::create([
                    "history_id" => $HistoryRes->id,
                    "activity_id" => 9,
                    "old" => 1,
                    "new" => 0,
                ]);
            }
        }

        $current_reason = DB::table("reasons")->select("weight", "group_id")->find($request->get("reason"));
        $response_id = static::GetResponsibleUserID($current_reason->group_id);
        $department_id = DepartmentsData::GetTheFirst($request);

        $TicketRes = Ticket::create([
            "user_id" => $user_id,
            "reason_id" => $request->get("reason"),
            "weight" => $current_reason->weight + $CouponWeight,
            "with_coupon" => $CouponWeight > 0,
            "response_id" => $response_id,
            "department_id" => $department_id,
        ]);
        $MessageRes = Message::create([
            "user_id" => $user_id,
            "ticket_id" => $TicketRes->id,
            "content" => $request->get("description"),
        ]);

        $data = DB::table("tickets")
            ->join("reasons", 'tickets.reason_id', "=", "reasons.id")
            ->where('active', 1)
            ->where('tickets.id', '=', $TicketRes->id)
            ->select("tickets.id", "tickets.user_id", "tickets.weight", "tickets.active", "tickets.response_id", "tickets.updated_at", "reasons.name")
            ->first();
        $data->queue_number = TicketsData::GetQueueNumber($TicketRes->id);
        
        static::SendNotification($response_id, "У Вас новый тикет #" . $TicketRes->id);
        
        return response()->json([
            "data" => $data,
            "message" => "Тикет #" . $TicketRes->id . " успешно создан",
            "status" => "success"
        ]);
    }

    public function GetMessageTemplates(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        if (static::IsSupporter($user_id) == false) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }

        $messages = DB::table("message_templates")->select("message_templates.content")->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function AddReason(Request $request) {
        static::InitialSetup($request);
        $AllReasons = $request->get("reasons");
        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        if (static::IsSupporter($user_id) == false) {
            return response()->json([
                "message" => "Вам не доступна возможность создавать новую проблему для тикета.",
                "status" => "error"
            ]);
        } elseif (count($AllReasons) == 0) {
            return response()->json([
                "message" => "Недостаточно данных для сохранения.",
                "status" => "error"
            ]);
        }
        
        foreach ($AllReasons as $R) {
            $ReasonRes = Reason::create([
                "name" => $R["name"],
                "weight" => $R["weight"],
                "group_id" => $R["group_id"],
                "parent_id" => $R["parent_id"],
            ]);

            $HistoryRes = History::create([
                "response_id" => $user["result"]["ID"],
                "reason_id" => $ReasonRes->id,
            ]);
            $OperationRes = Operation::create([
                "history_id" => $HistoryRes->id,
                "activity_id" => 7,
                "old" => null,
                "new" => $ReasonRes->id,
            ]);
        }

        return response()->json([
            "message" => count($AllReasons) == 1 ? "Причина успешно добавлена": "Причины успешно добавлены",
            "status" => "success"
        ]);
    }

    public function GetMessages(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $isSupporter = static::IsSupporter($user_id);
        $messages = DB::table("tickets")
            ->join("reasons", 'reasons.id', "=", "tickets.reason_id") 
            ->join("messages", 'tickets.id', "=", "messages.ticket_id")
            ->where('tickets.id', $request->get("ticket_id"))
            ->select("messages.id", "messages.user_id", "messages.content", "messages.updated_at")
            ->get();

        return response()->json([
            'messages' => $messages,
            'isSupporter' => $isSupporter,
            "currentUserID" => $user_id,
        ]);
    }

    public function AddMessage(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $ticket_id = $request->get("ticket_id");
        $ids = DB::table("tickets")->select("active", "user_id", "response_id")->find($ticket_id);
        if ($ids->active != 1) {
            return response()->json([
                "message" => "Данный тикет уже закрыт",
                "status" => "error"
            ]);
        }

        $isResponsive = DB::table("additional_responsives")
            ->where('additional_responsives.active', 1)
            ->where('additional_responsives.ticket_id', $ticket_id)
            ->where('additional_responsives.user_id', $user_id)
            ->exists();
        if ($user_id != $ids->user_id && $user_id != $ids->response_id && $isResponsive == false) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }
        
        Message::where("ticket_id", $ticket_id)->where('status', 0)->update(['status' => 1]);
        $message = Message::create([
            "user_id" => $user_id,
            "ticket_id" => $ticket_id,
            "content" => $request->get("message")
        ]);

        Ticket::where("id", $ticket_id)->update(["updated_at" => Carbon::now()]);
        
        $opponent = $user_id == $ids->user_id ? $ids->response_id : $ids->user_id;
        $notification_id = static::SendNotification($opponent, "У Вас новое сообщение в тикете #" . $ticket_id);

        $client = new \phpcent\Client("https://sms19.ru:1010/api");
        $client->setApiKey("331835cc-3d9c-45d7-b422-3c99184878f7");
        $client->setSafety(false);
        $client->publish('public:ticket.' . $ticket_id, [
            "content" => $request->get("message"),
            "user_id" => $user_id,
            "message_type" => 'text'
        ]);

        return response()->json([
            "data" => $message,
            "message" => "Сообщение отправлено",
            "status" => "success"
        ]);
    }

    public function AddFile(Request $request) {
        static::InitialSetup($request);
        dd($request);
        return;
        $this->validate($request, [
            'item' => ['mimes:jpeg,jpg,bmp,png,pdf,doc,docx|max:2048']
        ], [
            'item.mimes' => 'Этот тип файла недопустим по соображениям безопасности'
        ]);
        if(count($_FILES) > 10) {
            return ['msg' => 'Файлов больше 10!', 'status' => 'error'];
        }

        // $contact = static::call("crm.contact.get", [
        //     'id' =>  Auth::user()->contact_id
        // ]);
        // if(!$contact) return;

        // 
        //   Ишем все папки что находятся в хранилище
        //  
        // $folder = static::call("disk.storage.getchildren", [
        //     'id' => 3660,
        //     'generateUniqueName' => true 
        // ]);
        /**
         * Загрузка файла в указанную папку
         */
        $uploadfile = static::call("disk.folder.uploadfile", [
            'id' => 1565954,
            'generateUniqueName' => true 
        ]);
        $url = $uploadfile["result"]['uploadUrl'];

        $user = static::call("user.current");
        //Записываем временный файл на диск в битриксе
        $file_name = preg_replace('/\s+/', '_', strtolower(urldecode($request->file("item")->getClientOriginalName())));
        $file_path = $request->file("item")->store("files/" . $user["result"]["ID"], "local");
        // dd($file_name, $file_path);
        // return;
        // $upload = Upload::create([
        //     'user_id' => $user["result"]["ID"],
        //     'filename' => $file_name,
        //     'patch' => $file_pach,
        //     'mime' => $request->file("item")->getMimeType(),
        //     'size' => $request->file("item")->getSize(),
        // ]);
        $message = Message::create([
            "user_id" => $user["result"]["ID"],
            "ticket_id" => $request->get("ticket_id"),
            "content" => $_FILES["files"]
        ]);

        // $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        // $contents = file_get_contents($storagePath.$file_pach);

        // UploadFile::dispatch($upload, $url, $contact['result']['ASSIGNED_BY_ID'], $contact['result']['ID'])->delay(now()->addSeconds(5));

        // $count = count($_FILES);
        // return response()->json([ 'msg' => "Файлы успешно загружены в количестве {$count} шт.", 'status' => 'success']);


        // static::InitialSetup($request);
        // $user = static::call("user.current");
        // $message = Message::create([
        //     "user_id" => $user["result"]["ID"],
        //     "ticket_id" => $request->get("ticket_id"),
        //     "content" => $_FILES["files"]
        // ]);

        // $ids = DB::table("tickets")->select("user_id", "response_id")->find($request->get("ticket_id"));
        // if ($user["result"]["ID"] != $ids->user_id && $user["result"]["ID"] != $ids->response_id) {
        //     return response()->json([
        //         "message" => "У Вас недостаточно прав для данной операции",
        //         "status" => "error"
        //     ]);
        // }
        
        // Ticket::where("id", $request->get("ticket_id"))->update(["updated_at" => Carbon::now()]);
        
        // $notification_id = static::SendNotification($ids->user_id, $request->get("ticket_id"));

        // return response()->json([
        //     "data" => $message,
        //     "message" => "Сообщение отправлено",
        //     "status" => "success"
        // ]);
    }

    public function GetReasons() {
        $reasons = Reason::where("active", 1)->get()->toArray();

        return response()->json([
            'data' => $reasons
        ]);
    }

    public function GetHints(Request $request) {
        $hints = Hint::where('reason_id', $request->get('reason_id'))->select('content')->get();
        $data = array();

        foreach ($hints as $h) {
            array_push($data, $h->content);
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function EditHints(Request $request) {
        static::InitialSetup($request);
        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $hints = $request->get("hints");
        $reason_id = $request->get("reason_id");

        if (static::IsSupporter($user_id) == false) {
            return response()->json([
                "message" => "Вам не доступна возможность создавать новую проблему для тикета.",
                "status" => "error"
            ]);
        } elseif (count($hints) == 0) {
            return response()->json([
                "message" => "Недостаточно данных для сохранения.",
                "status" => "error"
            ]);
        }

        $current_hint = DB::table("hints")
            ->where("reason_id", $reason_id)
            ->select("content")->first();
        $content = null;

        if ($current_hint == null) {
            Hint::create([
                'reason_id' => $reason_id,
                'content' => json_encode($hints),
            ]);
        } else {
            Hint::where("reason_id", $reason_id)->update(["content" => json_encode($hints)]);
            $content = $current_hint->content;
        }

        $HistoryRes = History::create([
            "response_id" => $user["result"]["ID"],
            "reason_id" => $reason_id,
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 14,
            "old" => $content,
            "new" => json_encode($hints),
        ]);

        return response()->json([
            "message" => count($hints) == 1 ? "Совет успешно добавлен": "Советы успешно добавлены",
            "status" => "success"
        ]);
    }

    public function Transfer(Request $request) {
        static::InitialSetup($request);

        $ticket_id = $request->get("ticket_id");
        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $current_ticket = DB::table("tickets")->select("response_id", "active")->find($ticket_id);

        if ($current_ticket->active == 0) {
            return response()->json([
                "message" => "Данный тикет уже закрыт",
                "status" => "error"
            ]);
        } elseif ($user_id != $current_ticket->response_id) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }
        
        $new_response_id = $request->get("response_id");
        $data = DB::table("tickets")
            ->where("id", $ticket_id)
            ->update(["response_id" => $new_response_id]);
        
        $HistoryRes = History::create([
            "response_id" => $user_id,
            "ticket_id" => $ticket_id,
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 6,
            "old" => $new_response_id,
            "new" => null,
        ]);

        $HistoryRes = History::create([
            "response_id" => $user_id,
            "ticket_id" => $ticket_id,
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 1,
            "old" => $user_id,
            "new" => $new_response_id,
        ]);

        $notification_id = static::SendNotification($new_response_id, "Вам был передан тикет #" . $ticket_id);
        return response()->json([
            "message" => "Тикет успешно передан",
            "status" => "success"
        ]);
    }

    public function AddResponsive(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $ticket_id = $request->get("ticket_id");
        $new_response_id = $request->get("response_id");
        $current_ticket = DB::table("tickets")->select("response_id", "active")->find($ticket_id);

        if ($current_ticket->active == 0) {
            return response()->json([
                "message" => "Данный тикет уже закрыт",
                "status" => "error"
            ]);
        } elseif ($user_id != $current_ticket->response_id) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }

        $responsibles_ids = DB::table("additional_responsives")
            ->where('additional_responsives.active', '=', 1)
            ->where('additional_responsives.ticket_id', '=', $ticket_id)
            ->select("user_id")
            ->get();
        
        foreach ($responsibles_ids as $id) {
            if ($id->user_id == $new_response_id) {
                return response()->json([
                    "message" => "Данный сотрудник уже является ответственным",
                    "status" => "error"
                ]);
            }
        }
        
        $data = Additional_responsive::create([
            "ticket_id" => $ticket_id,
            "user_id" => $new_response_id,
            "active" => 1,
        ]);
        
        $HistoryRes = History::create([
            "response_id" => $user_id,
            "ticket_id" => $ticket_id,
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 3,
            "old" => null,
            "new" => $new_response_id,
        ]);

        $notification_id = static::SendNotification($new_response_id, "Вы были добавлены ответственным к тикету #" . $ticket_id);
        return response()->json([
            "message" => "Ответственный успешно добавлен",
            "status" => "success"
        ]);
    }

    public function RemoveResponsive(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $ticket_response_id = DB::table("tickets")->select("response_id")->find($request->get("ticket_id"));

        if ($user["result"]["ID"] != $ticket_response_id->response_id) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }
        
        $new_response_id = $request->get("response_id");
        $data = Additional_responsive::create([
            "ticket_id" => $request->get("ticket_id"),
            "user_id" => $new_response_id,
            "active" => 0,
        ]);
        $data = DB::table("additional_responsives")
            ->where("id", $request->get("ticket_id"))
            ->update(["active" => 0]);
        
        $HistoryRes = History::create([
            "response_id" => $user["result"]["ID"],
            "ticket_id" => $request->get("ticket_id"),
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 6,
            "old" => $user["result"]["ID"],
            "new" => $new_response_id,
        ]);

        $notification_id = static::SendNotification($new_response_id, "Вы были удалены из тикета #" . $request->get("ticket_id"));
        return response()->json([
            "message" => "Ответственный успешно удалён",
            "status" => "success"
        ]);
    }

    public function Close(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        $ticket_id = $request->get("ticket_id");
        $current_ticket = DB::table("tickets")->select("user_id")->find($ticket_id);

        if ($user_id != $current_ticket->user_id) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }
        
        $data = DB::table("tickets")
            ->where("id", $ticket_id)
            ->update(["active" => 0]);

        $HistoryRes = History::create([
            "response_id" => $user_id,
            "ticket_id" => $ticket_id,
        ]);
        $OperationRes = Operation::create([
            "history_id" => $HistoryRes->id,
            "activity_id" => 2,
            "old" => 1,
            "new" => 0,
        ]);
        
        return response()->json([
            "message" => "Тикет успешно завершён",
            "status" => "success"
        ]);
    }

    public function GetResponsives(Request $request) {
        static::InitialSetup($request);

        $user = static::call("user.current");
        $user_id = $user["result"]["ID"];
        if (static::IsSupporter($user_id) == false) {
            return response()->json([
                "message" => "У Вас недостаточно прав для данной операции",
                "status" => "error"
            ]);
        }

        $responsives = [];
        $admins = static::GetAllAdmins($user_id);

        foreach ($admins as $a) {
            if ($a == $user_id) continue;

            $userdata = static::call("user.get", ["ID" => $a]);
            $responsives[] = [
                "id" => $userdata["result"][0]["ID"],
                "name" => $userdata["result"][0]["NAME"],
                "last_name" => $userdata["result"][0]["LAST_NAME"],
                "patronymic" => $userdata["result"][0]["SECOND_NAME"],
            ];
        }
        
        return response()->json([
            "responsives" => $responsives
        ]);
    }

    private static function GetCouponWeight($number) {
        $Weight = 0;

        if ($number != null && $number != 0) {
            $Coupon = DB::table("coupons")
                ->where("number", $number)
                ->where("active", 1)
                ->select("id", "weight")
                ->first();
            
            if ($Coupon != null && isset($Coupon)) {
                $Weight = $Coupon;
            }
        }

        return $Weight;
    }
}
