<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Firestore;

class UserController extends Controller
{

    protected $database;

    public function __construct()
    {
        $this->database = app('firebase.database');
    }

    public function index()
    {
        $database = $firestore->database();

        return response()->json($this->database->getReference('test/blogs')->getValue());
    }
}
