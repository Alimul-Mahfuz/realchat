<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(){

        $users=User::query()->where('id','!=',auth()->id())->get();
        return view('dashboard',compact('users'));
    }
}
