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

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('people.index', [
            'users' => $users,
            'q' => $q
        ]);
    }

    /**show a public profile for a user.
     */
    public function show(User $user)
    {
        $user->loadCount(['jobs', 'transactions']);

        return view('people.show', [
            'user' => $user
        ]);
    }
}
