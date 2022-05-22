<?php

namespace App\Http\Controllers;

use App\Http\Traits\CRM;
use Illuminate\Http\Request;

class WebSocketController extends Controller {
  // use CRM;

  public function Subscribe(Request $r) {
    $client = new \phpcent\Client("https://sms19.ru:1010/api");
    $client->setApiKey("331835cc-3d9c-45d7-b422-3c99184878f7");
    $client->token = $client->setSecret("59a96a60-d964-472b-912a-6bda7dd56adc")->generatePrivateChannelToken($r->get('client'), current($r->get('channels')));

    return response()->json([
      'channels' => [ 
        [
          'channel' => current($r->get('channels')),
          'token' => $client->token
        ]
      ]
    ]);
  }

  public function Refresh(Request $r) {
    $client = new \phpcent\Client("https://sms19.ru:1010/api");
    $client->setApiKey("331835cc-3d9c-45d7-b422-3c99184878f7");
    $client->token = $client->setSecret("59a96a60-d964-472b-912a-6bda7dd56adc")->generateConnectionToken($r->get("user_id"));

    return response()->json([
        'token' => $client->token
    ]);
  }
}