<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // featured / recent jobs
        $featuredJobs = Job::with('user')->orderBy('created_at', 'desc')->take(8)->get();

        // top sellers by number of jobs
        $topSellers = User::withCount('jobs')->orderBy('jobs_count', 'desc')->take(8)->get();

        $categories = config('job_categories', []);

        return view('home', [
            'featuredJobs' => $featuredJobs,
            'topSellers' => $topSellers,
            'categories' => $categories
        ]);
    }
}
