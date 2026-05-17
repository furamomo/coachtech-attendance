<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    public function list() {

        $staffs = User::where('is_admin', 0)->get();

        return view('admin.staff.list',compact('staffs'));
    }
}
