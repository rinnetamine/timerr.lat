<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $availableJobsQuery = Job::query()->whereDoesntHave('submissions');

        // featured / recent jobs
        $featuredJobs = (clone $availableJobsQuery)->with('user')->orderBy('created_at', 'desc')->take(8)->get();

        // top sellers by number of jobs
        $topSellers = User::withCount('jobs')->orderBy('jobs_count', 'desc')->take(8)->get();

        $categories = config('job_categories', []);
        $jobsCount = (clone $availableJobsQuery)->count();
        $completedCount = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)->count();
        $categoryCount = count($categories);
        $availableCredits = (clone $availableJobsQuery)->sum('time_credits');
        $topCategoryKey = (clone $availableJobsQuery)->select('category')
            ->selectRaw('count(*) as total')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->value('category');

        $categoryLabel = function (?string $category) use ($categories) {
            if (!$category) {
                return 'Jauna kopiena';
            }

            $root = explode('.', $category)[0];

            if (isset($categories[$category])) {
                return $categories[$category]['label'];
            }

            if (isset($categories[$root])) {
                if (!empty($categories[$root]['children'][$category])) {
                    return $categories[$root]['children'][$category];
                }

                return $categories[$root]['label'];
            }

            return ucfirst(str_replace(['.', '_'], ' ', $category));
        };

        $homeStats = [
            [
                'value' => User::count(),
                'label' => 'dalībnieki',
                'detail' => 'cilvēki, kas var dot vai saņemt palīdzību',
            ],
            [
                'value' => $jobsCount,
                'label' => 'pakalpojumi',
                'detail' => 'aktīvas idejas laika apmaiņai',
            ],
            [
                'value' => $completedCount,
                'label' => 'pabeigti darbi',
                'detail' => 'apstiprinātas sadarbības Timerr sistēmā',
            ],
            [
                'value' => $availableCredits,
                'label' => 'laika kredīti',
                'detail' => 'kopējā vērtība publicētajos pakalpojumos',
            ],
        ];

        $homeInsights = [
            [
                'label' => '1 kredīts',
                'value' => '1 stunda',
                'detail' => 'vienkārša un godīga laika vērtība',
            ],
            [
                'label' => 'Kategorijas',
                'value' => $categoryCount,
                'detail' => 'no tehnoloģijām līdz mācībām un radošiem darbiem',
            ],
            [
                'label' => 'Populārākais virziens',
                'value' => $categoryLabel($topCategoryKey),
                'detail' => 'balstīts uz pašreiz publicētajiem pakalpojumiem',
            ],
        ];

        return view('home', [
            'featuredJobs' => $featuredJobs,
            'topSellers' => $topSellers,
            'categories' => $categories,
            'homeStats' => $homeStats,
            'homeInsights' => $homeInsights,
        ]);
    }
}
