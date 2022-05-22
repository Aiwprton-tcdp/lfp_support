<?php 
namespace App\Http\Traits;

use Illuminate\Http\Request;

trait DepartmentsData {
    use CRM;

  public static function Get(Request $request, $only_ids = true) {
    static::InitialSetup($request);

    $user = static::call("user.current");
    $w = static::call("user.get", ["ID" => $user["result"]["ID"]]);
    $dep = static::call("department.get");
    $iterationsCount = ceil($dep["total"] / 50);
    $Params = array();
    $AllData = $dep["result"];

    for ($i = 1; $i < $iterationsCount; $i++) {
      $Params[] = [
        'method' => 'department.get',
        'params' => [
          'start' => $i * 50
        ]
      ];
    }
    $allDepartments = static::callBatch($Params);

    for ($i = 0; $i < $iterationsCount - 1; $i++) {
      $items = $allDepartments["result"][$i];
      foreach ($items as $item) {
        array_push($AllData, $item);
      }
    }
    
    $Result = array();
    foreach ($w["result"][0]["UF_DEPARTMENT"] as $dep_id) {
      $data = static::GetChildrenDepartments($AllData, $dep_id);
      foreach ($data as $d) {
        if (!in_array($d, $Result)) {
          array_push($Result, $d);
        }
      }
    }

    usort($Result, function ($x, $y) {
      if (isset($x["PARENT"]) && isset($y["PARENT"])) {
        return $x["PARENT"] <=> $y["PARENT"];
      }
    });
    
    return $only_ids == true
      ? static::GetDepartmentsIDs($Result)
      : $Result;
  }

  public static function GetTheFirst(Request $request, $only_ids = true) {
    static::InitialSetup($request);

    $data = static::Get($request, false);
    $lenght = count($data);
    $res = array();
    for ($i = 0; $i < $lenght; $i++) {
      $HasAnyChild = false;
      for ($j = 0; $j < $lenght; $j++) {
        if ($data[$i]["PARENT"] == $data[$j]["ID"]) {
          $HasAnyChild = true;
          break;
        }
      }
      if ($HasAnyChild == false) {
        $res[] = $data[$i];
      }
    }

    usort($res, function ($x, $y) {
      return $x["ID"] <=> $y["ID"];
    });

    return $only_ids == true
      ? $res[0]["ID"]
      : $res[0];
  }

  private static function GetChildrenDepartments($data, $parentId = null) {
    $branch = array();

    foreach ($data as $d) {
      if ($d['ID'] == $parentId) 
      {
        array_push($branch, $d);
      }

      if (empty($d['PARENT'])) {
        continue;
      }
      elseif ($d['PARENT'] == $parentId) {
        $children = static::GetChildrenDepartments($data, $d['ID']);

        if (!empty($children)) {
          foreach ($children as $c) {
            array_push($branch, $c);
          }
        }
      }
    }

    return $branch;
  }

  private static function GetDepartmentsIDs($data) {
    $branch = array();
    foreach ($data as $d) {
      array_push($branch, $d["ID"]);
    }
    return $branch;
  }
}

?>