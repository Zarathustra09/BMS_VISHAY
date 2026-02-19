<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaintenanceController extends controller
{

    public function index()
    {
        return view('maintenance.index');
    }
}
