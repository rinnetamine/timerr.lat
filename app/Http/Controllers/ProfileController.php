<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\JobSubmission;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

// controller for user profile and dashboard
class ProfileController extends Controller
{
    // display user profile with all related data
    public function show(Request $request)
    {
        $user = auth()->user();
        $submissionStatuses = [
            JobSubmission::STATUS_CLAIMED,
            JobSubmission::STATUS_PENDING,
            JobSubmission::STATUS_APPROVED,
            JobSubmission::STATUS_DECLINED,
            JobSubmission::STATUS_ADMIN_REVIEW,
        ];
        
        // load stats for display
        $user->loadCount(['jobs', 'completedJobs', 'reviewsReceived']);
        
        // add average rating calculation
        $user->reviews_received_rating_avg = Review::where('reviewee_id', $user->id)->avg('rating') ?? 0;
        
        // fetch user's job listings
        $servicesQuery = $user->jobs()->withCount('submissions');

        if ($search = $request->string('vacancy_search')->trim()->toString()) {
            $servicesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('vacancy_status')->toString()) {
            if ($status === 'open') {
                $servicesQuery->doesntHave('submissions');
            } elseif ($status === 'has_submissions') {
                $servicesQuery->has('submissions');
            }
        }

        match ($request->string('vacancy_sort')->toString()) {
            'oldest' => $servicesQuery->oldest(),
            'credits_asc' => $servicesQuery->orderBy('time_credits'),
            'credits_desc' => $servicesQuery->orderByDesc('time_credits'),
            'title' => $servicesQuery->orderBy('title'),
            default => $servicesQuery->latest(),
        };

        $services = $servicesQuery
            ->paginate(6, ['*'], 'vacancies_page')
            ->withQueryString();
        
        // fetch submissions for user's jobs
        $receivedSubmissionsQuery = JobSubmission::whereHas('jobListing', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['jobListing', 'user']);

        $this->applySubmissionFilters(
            $receivedSubmissionsQuery,
            $request->string('received_status')->toString(),
            $request->string('received_search')->trim()->toString(),
            $submissionStatuses
        );

        $receivedSubmissions = $receivedSubmissionsQuery
            ->latest()
            ->paginate(4, ['*'], 'received_page')
            ->withQueryString();
        
        // fetch user's job submissions
        $sentSubmissionsQuery = JobSubmission::where('user_id', $user->id)
            ->with('jobListing.user');

        $this->applySubmissionFilters(
            $sentSubmissionsQuery,
            $request->string('sent_status')->toString(),
            $request->string('sent_search')->trim()->toString(),
            $submissionStatuses
        );

        $sentSubmissions = $sentSubmissionsQuery
            ->latest()
            ->paginate(4, ['*'], 'sent_page')
            ->withQueryString();
            
        // fetch user's transaction history
        $lastTransaction = $user->transactions()->latest()->first();
        $transactions = $user->transactions()
            ->latest()
            ->paginate(8, ['*'], 'transactions_page')
            ->withQueryString();

        $profileStats = [
            'active_vacancies' => $user->jobs()->doesntHave('submissions')->count(),
            'pending_received' => JobSubmission::whereHas('jobListing', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', JobSubmission::STATUS_PENDING)->count(),
            'approved_sent' => JobSubmission::where('user_id', $user->id)
                ->where('status', JobSubmission::STATUS_APPROVED)
                ->count(),
            'credit_movement_30_days' => $user->transactions()
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'last_transaction' => $lastTransaction,
        ];

        $reviewsReceived = Review::where('reviewee_id', $user->id)
            ->with('reviewer')
            ->latest()
            ->paginate(4, ['*'], 'reviews_page')
            ->withQueryString();
        
        // fetch admin review submissions if user is admin
        $adminReviewSubmissions = [];
        if ($user->isAdmin()) {
            $adminReviewSubmissions = JobSubmission::where('status', JobSubmission::STATUS_ADMIN_REVIEW)
                ->with(['jobListing', 'user', 'jobListing.user'])
                ->latest()
                ->get();
        }
        
        return view('auth.profile', [
            'user' => $user,
            'services' => $services,
            'receivedSubmissions' => $receivedSubmissions,
            'sentSubmissions' => $sentSubmissions,
            'transactions' => $transactions,
            'reviewsReceived' => $reviewsReceived,
            'adminReviewSubmissions' => $adminReviewSubmissions,
            'submissionStatuses' => $submissionStatuses,
            'defaultAvatars' => User::defaultAvatarOptions(),
            'profileStats' => $profileStats,
        ]);
    }

    private function applySubmissionFilters($query, string $status, string $search, array $allowedStatuses): void
    {
        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('message', 'like', "%{$search}%")
                    ->orWhereHas('jobListing', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jobListing.user', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }
    }

    // change user password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required', 
                'string', 
                'confirmed', 
                'min:6',
                'regex:/[0-9]/', // at least one number
                'regex:/[!@#$%^&*()_+=\-\[\]{};:\'"<>,\.?\/]/' // at least one special character
            ]
        ]);

        $user = auth()->user();

        // verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.'
            ]);
        }

        // update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('password_success', 'Parole nomainīta veiksmīgi!');
    }

    public function updateAvatar(Request $request)
    {
        $attributes = $request->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'default_avatar' => ['nullable', 'string', 'in:' . implode(',', User::defaultAvatarOptions())],
        ]);

        $user = auth()->user();

        if (!$request->hasFile('avatar') && empty($attributes['default_avatar'])) {
            return back()->withErrors([
                'avatar' => 'Izvēlieties noklusējuma attēlu vai augšupielādējiet savu.',
            ]);
        }

        if ($user->avatar_path && !str_starts_with($user->avatar_path, 'images/')) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->hasFile('avatar')
            ? $attributes['avatar']->store('avatars', 'public')
            : $attributes['default_avatar'];

        $user->update([
            'avatar_path' => $path,
        ]);

        return back()->with('profile_success', 'Profila attēls atjaunināts!');
    }
}
