<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $database;
    protected $storage;

    public function __construct()
    {
        $this->database = app('firebase.firestore')->database();
        $this->storage = app('firebase.storage')->getBucket();
    }
}
