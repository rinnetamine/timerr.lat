<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    /**
     * display a searchable list of user profiles.
     */
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        $sort = $request->query('sort', 'name_asc');

        $usersQuery = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ;

        // add helpful aggregates
        $usersQuery->withCount('jobs')
                   ->withCount('completedJobs') 
                   ->withCount('reviewsReceived')
                   ->withAvg('reviewsReceived', 'rating');
        
        // add a subquery to ensure average rating is calculated correctly
        $usersQuery->addSelect([
            'reviews_received_rating_avg' => function ($query) {
                $query->selectRaw('COALESCE(AVG(rating), 0)')
                      ->from('reviews')
                      ->whereColumn('reviews.reviewee_id', 'users.id');
            }
        ]);

        // sorting options for people listing
        switch ($sort) {
            case 'newest':
                $usersQuery->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $usersQuery->orderBy('created_at', 'asc');
                break;
            case 'most_credits':
                $usersQuery->orderBy('time_credits', 'desc');
                break;
            case 'most_completed':
                $usersQuery->orderBy('completed_jobs_count', 'desc');
                break;
            case 'most_jobs':
                $usersQuery->orderBy('jobs_count', 'desc');
                break;
            case 'top_rated':
                // users with higher average rating first
                $usersQuery->orderBy('reviews_received_rating_avg', 'desc');
                break;
            case 'name_desc':
                $usersQuery->orderBy('first_name', 'desc')->orderBy('last_name', 'desc');
                break;
            case 'name_asc':
            default:
                $usersQuery->orderBy('first_name')->orderBy('last_name');
                break;
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        return view('people.index', [
            'users' => $users,
            'q' => $q,
            'sort' => $sort
        ]);
    }

    /**show a public profile for a user.
     */
    public function show(User $user)
    {
        $user->loadCount(['jobs', 'completedJobs', 'reviewsReceived', 'transactions']);
        $user->load(['reviewsReceived.reviewer']);

        return view('people.show', [
            'user' => $user
        ]);
    }

    /**
     * show admin user management page
     */
    public function manage(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $user->loadCount(['jobs', 'completedJobs', 'reviewsReceived', 'transactions']);
        $user->load(['transactions' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('people.manage', [
            'user' => $user
        ]);
    }

    /**
     * adjust user credits (admin only)
     */
    public function adjustCredits(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|integer|min:-1000|max:1000',
            'description' => 'required|string|max:255'
        ]);

        $user->adjustCredits($request->amount, $request->description);

        return back()->with('success', "Kredu012bti pielu0101goti veiksmu012bgi. Jauns bilance: {$user->time_credits}");
    }

    /**
     * ban user (admin only)
     */
    public function ban(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Nevar blou0137u0113t administratora lietotu0101ju.');
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        // Ban the user
        $user->ban($request->reason);

        // Invalidate all sessions for the banned user
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Lietotu0101js ir blou0137u0113ts un nekavu0113joties izrakstu012bts.');
    }

    /**
     * unban user (admin only)
     */
    public function unban(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $user->unban();

        return back()->with('success', 'Lietotu0101ja blou0137u0113u0161ana atcelta.');
    }
}
