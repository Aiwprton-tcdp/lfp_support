<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\CRM;

class InstallController extends Controller
{
    use CRM;

    public function Install(Request $request)
    {
        static::$C_REST_WEB_HOOK_URL = "";
        static::$AUTH_ID = $_REQUEST["AUTH_ID"];
        static::$DOMAIN = $_REQUEST["DOMAIN"];
        static::$REFRESH_ID = $_REQUEST["REFRESH_ID"];
        static::$member_id = $_REQUEST["member_id"];
        static::$APP_SID = $_REQUEST["APP_SID"];

        $result = static::installApp();

        return view('app.install', compact('result'));
    }
}
