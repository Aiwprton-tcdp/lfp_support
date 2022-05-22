<?php 
namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

trait UserData {
    use CRM;

    public function IsSupporter($id) {
        return DB::table("supporters")->where("user_id", $id)->first() != null;
    }

    public function GetAllAdmins($id) {
        $data = DB::table("supporters")
            ->join("groups", "groups.id", "supporters.group_id")
            ->get()->toArray();
        $result = $this->WithoutCurrent($data, $id);
        return $result;
    }

    private function WithoutCurrent($var, $id) {
        $array = [];
        foreach ($var as $v) {
            $array[] = $v->user_id;
        }
        unset($array[array_search($id, $array)]);
        return $array;
    }

    public function GetAdmins($group_id) {
        return DB::table("supporters")
            ->join("groups", "groups.id", "supporters.group_id")
            ->where("supporters.group_id", '=', $group_id)
            ->get()->toArray();
    }

    public function GetResponsibleUserID($group_id) {
        $sums = DB::table("supporters")
            ->join("tickets", "tickets.response_id", "supporters.user_id")
            ->where("supporters.group_id", '=', $group_id)
            ->where("tickets.active", '=', 1)
            ->select(DB::raw("SUM(tickets.weight) as sum"), "supporters.user_id")
            ->groupBy("supporters.user_id")
            ->get()->toArray();
        $ids = $this->GetNewUsers($sums, $group_id);

        if (count($ids) > 0) {
            $u = array_rand($ids, 1);
            $id = $ids[$u];
        } else {
            $min = $sums[0];
            $min_sums = 0;
            foreach ($sums as $s) {
                if ($s->sum < $min->sum) {
                    $min = $s;
                    $min_sums = 0;
                } elseif ($s->sum == $min->sum) {
                    $min_sums++;
                }
            }

            $id = $min->user_id;
        }

        return $id;
    }

    private function GetNewUsers($sums, $group_id) {
        $result = [];

        foreach ($this->GetAdmins($group_id) as $admin) {
            $isset = false;
            foreach ($sums as $s) {
                if ($admin->user_id == $s->user_id) {
                    $isset = true;
                    break;
                }
            }
            if ($isset == false) {
                $result[] = $admin->user_id;
            }
        }

        return $result;
    }

    // public function SendNotification($user_id, $message) {
    //     $result = static::call('im.message.add', Array(
    //         'DIALOG_ID' => $user_id,
    //         'MESSAGE' => $message,
    //         'SYSTEM' => 'N',
    //         'URL_PREVIEW' => 'Y',
    //     ), static::$AUTH_ID);
    // }

    public function SendNotification($user_id, $message) {
        static::$C_REST_WEB_HOOK_URL = 'YOUR_SUPPORT_NOTOFOCATION_USER__WEB_HOOK_URL';
        static::$DOMAIN = null;
        $result = static::call('im.message.add', Array(
            'USER_ID' => $user_id,
            'MESSAGE' => $message . "\r\n[URL=YOUR_INTEGRATION_URL]Перейти[/URL]",
            'URL_PREVIEW' => "Y",
        ));
        static::$C_REST_WEB_HOOK_URL = '';
    }
}

?>