<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function __construct(){
        $this->setTitle('Title not stated |');

        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    public function setTitle($title){
        $this->title = $title;
        view()->share('title', $this->title);
    }
}
