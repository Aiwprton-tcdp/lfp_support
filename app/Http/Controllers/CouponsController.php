<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\History;
use App\Models\Operation;
use App\Http\Traits\CRM;
use App\Http\Traits\UserData;
use Illuminate\Support\Facades\DB;

class CouponsController extends Controller {
  use CRM;
  use UserData;

  public function Get(Request $request) {
    static::InitialSetup($request);
    
    $user = static::call("user.current");
    $isSupporter = static::IsSupporter($user["result"]["ID"]);

    if ($isSupporter == false) {
      return response()->json([
        "message" => "У Вас нет доступа к этим данным.",
        "status" => "error"
      ]);
    }

    if ($request->get("coupon_status") == 1) {
      $data = DB::table("coupons")->where("active", $request->get("coupon_status"))->get();
    } else {
      $data = DB::table("coupons")->where("active", "!=", 1)->get();
    }
    
    $datesOut = array();
    foreach ($data as $сoupon) {
      if ($сoupon->active == 2 || $request->get("coupon_status") == 1) {
        if ($сoupon->age <= date("Y-m-d")) {
          if ($сoupon->active == 2) {
            $datesOut[] = $сoupon->id;
          }
          
          $result[] = $сoupon;
        } else {
          $result[] = $сoupon;
        }
      }
    }
    if (count($datesOut) > 0) {
      DB::table("coupons")->whereIn("id", $datesOut)->update(["active" => 3]);
    }

    return response()->json([
      "data" => $data,
    ]);
  }

  public function New(Request $request) {
    static::InitialSetup($request);

    $user = static::call("user.current");
    $user_id = $user["result"]["ID"];

    if (static::IsSupporter($user_id) == false) {
      return response()->json([
        "message" => "Вам не доступна возможность создавать купон.",
        "status" => "error"
      ]);
    } elseif ($request->get("age") <= date("Y-m-d")) {
      return response()->json([
        "message" => "Дата истечения купона должна быть позднее даты создания.",
        "status" => "error"
      ]);
    } elseif ($request->get("weight") < 1 || $request->get("weight") > 10) {
      return response()->json([
        "message" => "Указано значение ценности купона вне допустимого диапазона.",
        "status" => "error"
      ]);
    }

    // $coupon_number = $this->GetCouponNumber($CouponRes->id);
    $number = $this->GenerateNumber();
    $CouponRes = Coupon::create([
      "active" => 1,
      "number" => $number,
      "age" => $request->get("age"),
      "weight" => $request->get("weight"),
    ]);


    $HistoryRes = History::create([
      "response_id" => $user_id,
      "coupon_id" => $CouponRes->id,
    ]);
    $OperationRes = Operation::create([
      "history_id" => $HistoryRes->id,
      "activity_id" => 4,
      "new" => $number,
    ]);

    return response()->json([
      "data" => $CouponRes,
      "message" => "Купон #{$number} успешно создан",
      "status" => "success"
    ]);
  }

  private function GenerateNumber() {
    $number = random_int(1, 9999);
    $isNum = DB::table("coupons")->where("number", $number)->exists();
    if ($isNum == true) {
      return $this->GenerateNumber();
    }
    return $number;
  }

  public function Close(Request $request) {
    static::InitialSetup($request);

    $user = static::call("user.current");
    $user_id = $user["result"]["ID"];
    $Coupon = Coupon::find($request->get("coupon_id"));

    if (static::IsSupporter($user_id) == false) {
      return response()->json([
        "message" => "У Вас недостаточно прав для данной операции",
        "status" => "error"
      ]);
    } elseif ($Coupon->active != 1) {
      return response()->json([
        "message" => "Данный купон уже неактивен",
        "status" => "error"
      ]);
    }
    
    $data = DB::table("coupons")
      ->where("id", $request->get("coupon_id"))
      ->update(["active" => 2]);

    $HistoryRes = History::create([
      "response_id" => $user["result"]["ID"],
      "coupon_id" => $request->get("coupon_id"),
    ]);
    $OperationRes = Operation::create([
      "history_id" => $HistoryRes->id,
      "activity_id" => 5,
      "old" => 1,
      "new" => 2,
    ]);
    
    return response()->json([
      "message" => "Купон успешно аннулирован",
      "status" => "success"
    ]);
  }

  public function Repair(Request $request) {
    static::InitialSetup($request);

    $user = static::call("user.current");
    $user_id = $user["result"]["ID"];
    $Coupon = Coupon::find($request->get("coupon_id"));

    if (static::IsSupporter($user_id) == false) {
      return response()->json([
        "message" => "У Вас недостаточно прав для данной операции",
        "status" => "error"
      ]);
    } elseif ($Coupon->age <= date("Y-m-d")) {
      dd("$Coupon->age");
      if ($Coupon->active != 2) {
        $data = DB::table("coupons")
          ->where("id", $request->get("coupon_id"))
          ->update(["active" => 2]);
      }
      
      return response()->json([
        "message" => "Срок данного купона истёк",
        "status" => "error"
      ]);
    } elseif ($Coupon->active == 0) {
      return response()->json([
        "message" => "Данный купон уже активен",
        "status" => "error"
      ]);
    } elseif ($Coupon->active == 1) {
      return response()->json([
        "message" => "Данный купон уже использован",
        "status" => "error"
      ]);
    }
    
    $data = DB::table("coupons")
      ->where("id", $request->get("coupon_id"))
      ->update(["active" => 1]);

    $HistoryRes = History::create([
      "response_id" => $user_id,
      "coupon_id" => $request->get("coupon_id"),
    ]);
    $OperationRes = Operation::create([
      "history_id" => $HistoryRes->id,
      "activity_id" => 8,
      "old" => 1,
      "new" => 0,
    ]);
    
    return response()->json([
      "message" => "Купон успешно восстановлен",
      "status" => "success"
    ]);
  }

  private function GetCouponNumber($id) {
    $prepared = "00000000";
    $length = strlen((string)$id);
    $number = substr($prepared, 0, 4-$length) . $id;

    return $number;
  }
}
