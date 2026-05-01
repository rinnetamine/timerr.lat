<?php

// Šis fails sagatavo publisko cilvēku katalogu un administratora lietotāju pārvaldības darbības.

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    // Parāda meklējamu un kārtojamu lietotāju profilu sarakstu.
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

        // Lietotāju kartītēm tiek pievienoti skaitītāji un vidējais vērtējums.
        $usersQuery->withCount('jobs')
                   ->withCount('completedJobs') 
                   ->withCount('reviewsReceived')
                   ->withAvg('reviewsReceived', 'rating');
        
        // Apakšvaicājums nodrošina korektu nulles vērtību lietotājiem bez atsauksmēm.
        $usersQuery->addSelect([
            'reviews_received_rating_avg' => function ($query) {
                $query->selectRaw('COALESCE(AVG(rating), 0)')
                      ->from('reviews')
                      ->whereColumn('reviews.reviewee_id', 'users.id');
            }
        ]);

        // Kārtošanas izvēle tiek piemērota pirms lapošanas.
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
                // Lietotāji ar augstāku vidējo vērtējumu tiek rādīti vispirms.
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

    // Parāda lietotāja publisko profilu.
    public function show(User $user)
    {
        $user->loadCount(['jobs', 'completedJobs', 'reviewsReceived', 'transactions']);
        $user->load(['reviewsReceived.reviewer']);

        return view('people.show', [
            'user' => $user
        ]);
    }

    // Parāda administratora lietotāja pārvaldības lapu.
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

    // Pielāgo lietotāja kredītus administratora vārdā.
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

        return back()->with('success', "Kredīti pielāgoti veiksmīgi. Jaunā bilance: {$user->time_credits}");
    }

    // Bloķē lietotāju un anulē viņa aktīvās sesijas.
    public function ban(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Nevar bloķēt administratora lietotāju.');
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $user->ban($request->reason);

        // Visas sesijas tiek dzēstas, lai bloķēšana stātos spēkā nekavējoties.
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Lietotājs ir bloķēts un nekavējoties izrakstīts.');
    }

    // Atbloķē lietotāju administratora vārdā.
    public function unban(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $user->unban();

        return back()->with('success', 'Lietotāja bloķēšana atcelta.');
    }
}
