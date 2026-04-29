<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    private array $blankUserEmails = [
        'user1@timerr.lat',
        'user2@timerr.lat',
        'user3@timerr.lat',
        'user4@timerr.lat',
    ];

    private array $statuses = [
        JobSubmission::STATUS_CLAIMED,
        JobSubmission::STATUS_PENDING,
        JobSubmission::STATUS_APPROVED,
        JobSubmission::STATUS_DECLINED,
        JobSubmission::STATUS_ADMIN_REVIEW,
    ];

    public function run(): void
    {
        $this->clearDemoData();

        $users = $this->createUsers();
        $jobs = $this->createCategoryJobs($users);

        $testUser = $users->firstWhere('email', 'user@timerr.lat');

        if ($testUser) {
            $receivedSubmissions = $this->createReceivedSubmissionsForTestUser($testUser, $users, $jobs);
            $sentSubmissions = $this->createSentSubmissionsForTestUser($testUser, $users, $jobs);
            $this->createReviewsForTestUser($testUser, $users, $receivedSubmissions, $sentSubmissions);
            $this->createTransactionsForTestUser($testUser);
        }
    }

    public function runWithDefaults(): void
    {
        $this->run();
    }

    private function clearDemoData(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'message_files',
            'messages',
            'reviews',
            'submission_files',
            'job_submissions',
            'transactions',
            'job_listings',
            'contact_messages',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $this->resetSqliteSequences([
            'message_files',
            'messages',
            'reviews',
            'submission_files',
            'job_submissions',
            'transactions',
            'job_listings',
            'contact_messages',
            'users',
        ]);

        Schema::enableForeignKeyConstraints();
    }

    private function createUsers()
    {
        $hasAvatarColumn = Schema::hasColumn('users', 'avatar_path');

        $users = [
            ['Admin', 'Lietotājs', 'admin@timerr.lat', 'admin', 'admin', 1000],
            ['Test', 'User', 'user@timerr.lat', 'user', 'user', 150],
            ['Blank', 'User 1', 'user1@timerr.lat', 'user1', 'user', 10],
            ['Blank', 'User 2', 'user2@timerr.lat', 'user2', 'user', 10],
            ['Blank', 'User 3', 'user3@timerr.lat', 'user3', 'user', 10],
            ['Blank', 'User 4', 'user4@timerr.lat', 'user4', 'user', 10],
            ['Jānis', 'Bērziņš', 'janis.demo@timerr.lat', 'password', 'user', 82],
            ['Māra', 'Kalniņa', 'mara.demo@timerr.lat', 'password', 'user', 64],
            ['Līga', 'Ozola', 'liga.demo@timerr.lat', 'password', 'user', 91],
            ['Andris', 'Liepa', 'andris.demo@timerr.lat', 'password', 'user', 58],
            ['Elīna', 'Zariņa', 'elina.demo@timerr.lat', 'password', 'user', 73],
            ['Rihards', 'Krūmiņš', 'rihards.demo@timerr.lat', 'password', 'user', 47],
            ['Santa', 'Vītola', 'santa.demo@timerr.lat', 'password', 'user', 69],
            ['Roberts', 'Eglītis', 'roberts.demo@timerr.lat', 'password', 'user', 55],
        ];

        return collect($users)->map(function (array $user, int $index) use ($hasAvatarColumn) {
            [$firstName, $lastName, $email, $password, $role, $credits] = $user;

            return User::unguarded(fn () => User::create(array_filter([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $role,
                'time_credits' => $credits,
                'email_verified_at' => now(),
                'avatar_path' => $hasAvatarColumn
                    ? User::DEFAULT_AVATARS[$index % count(User::DEFAULT_AVATARS)]
                    : null,
            ], fn ($value) => $value !== null)));
        });
    }

    private function demoParticipants($users)
    {
        return $users
            ->reject(fn (User $user) => $user->email === 'admin@timerr.lat')
            ->reject(fn (User $user) => in_array($user->email, $this->blankUserEmails, true))
            ->values();
    }

    private function createCategoryJobs($users)
    {
        $categories = $this->categorySlugs();
        $topCategories = $this->topCategorySlugs();
        $hasImageColumn = Schema::hasColumn('job_listings', 'image_path');
        $testUser = $users->firstWhere('email', 'user@timerr.lat');
        $otherUsers = $this->demoParticipants($users)
            ->filter(fn (User $user) => $testUser && $user->id !== $testUser->id)
            ->values();
        $jobs = collect();

        foreach ($topCategories as $index => $category) {
            $credits = 4 + ($index % 9) * 2;

            if ($testUser) {
                $jobs->push(Job::create(array_filter([
                    'user_id' => $testUser->id,
                    'title' => $this->jobTitle($category['slug'], $category['label']),
                    'description' => $this->jobDescription($category['label'], $testUser->first_name),
                    'time_credits' => $credits,
                    'category' => $category['slug'],
                    'image_path' => $hasImageColumn ? Job::defaultImagePathForCategory($category['slug']) : null,
                    'created_at' => now()->subDays(60 - $index),
                    'updated_at' => now()->subDays(60 - $index),
                ], fn ($value) => $value !== null)));
            }
        }

        foreach ($categories as $index => $category) {
            $credits = 5 + ($index % 9) * 2;
            $ownerPool = $otherUsers->isNotEmpty() ? $otherUsers : $users;
            $owner = $ownerPool[$index % $ownerPool->count()];

            $jobs->push(Job::create(array_filter([
                'user_id' => $owner->id,
                'title' => $this->jobTitle($category['slug'], $category['label']),
                'description' => $this->jobDescription($category['label'], $owner->first_name),
                'time_credits' => $credits + 1,
                'category' => $category['slug'],
                'image_path' => $hasImageColumn ? Job::defaultImagePathForCategory($category['slug']) : null,
                'created_at' => now()->subDays(30 - $index),
                'updated_at' => now()->subDays(30 - $index),
            ], fn ($value) => $value !== null)));
        }

        return $jobs;
    }

    private function createReceivedSubmissionsForTestUser(User $testUser, $users, $jobs)
    {
        $testJobs = $jobs->where('user_id', $testUser->id)->values();
        $applicants = $this->demoParticipants($users)
            ->where('id', '!=', $testUser->id)
            ->values();
        $submissions = collect();

        foreach ($this->statuses as $index => $status) {
            $submissions->push($this->createSubmission(
                $testJobs[$index % $testJobs->count()],
                $applicants[$index % $applicants->count()],
                $status,
                "Saņemtais testa pieteikums statusam: {$status}.",
                $index
            ));
        }

        return $submissions;
    }

    private function createSentSubmissionsForTestUser(User $testUser, $users, $jobs)
    {
        $availableJobs = $jobs->where('user_id', '!=', $testUser->id)->values();
        $submissions = collect();

        foreach ($this->statuses as $index => $status) {
            $submissions->push($this->createSubmission(
                $availableJobs[$index],
                $testUser,
                $status,
                "Testa lietotāja nosūtītais pieteikums statusam: {$status}.",
                $index + 10
            ));
        }

        return $submissions;
    }

    private function createReviewsForTestUser(User $testUser, $users, $receivedSubmissions, $sentSubmissions): void
    {
        $reviewTexts = [
            [5, 'Ļoti patīkama sadarbība, viss tika sarunāts skaidri un bez liekas kavēšanās.'],
            [5, 'Uzdevums bija labi aprakstīts, komunikācija ātra un draudzīga.'],
            [4, 'Labs pasūtītājs, nelielas detaļas precizējām darba gaitā, bet kopumā viss noritēja labi.'],
            [5, 'Ātri atbildēja uz jautājumiem un godīgi novērtēja paveikto darbu.'],
            [4, 'Sadarbība bija mierīga un saprotama, labprāt palīdzētu vēlreiz.'],
            [5, 'Ļoti korekta attieksme un skaidras prasības no paša sākuma.'],
            [5, 'Paldies par uzticēšanos, process bija vienkāršs un patīkams.'],
            [4, 'Labs darba apraksts un savlaicīga apstiprināšana pēc pabeigšanas.'],
            [5, 'Viegli vienoties par detaļām, komunikācija bija profesionāla.'],
            [4, 'Pozitīva pieredze, viss nepieciešamais bija pieejams uzdevuma izpildei.'],
        ];

        $reviewers = $this->demoParticipants($users)
            ->where('id', '!=', $testUser->id)
            ->values();
        $reviewSubmissions = $sentSubmissions
            ->merge($receivedSubmissions)
            ->values();

        foreach ($reviewTexts as $index => [$rating, $comment]) {
            $submission = $reviewSubmissions[$index % $reviewSubmissions->count()];

            if ($submission->user_id === $testUser->id) {
                $reviewerId = $submission->jobListing?->user_id ?? $reviewers[$index % $reviewers->count()]->id;
            } else {
                $reviewerId = $submission->user_id;
            }

            Review::create([
                'reviewer_id' => $reviewerId,
                'reviewee_id' => $testUser->id,
                'job_submission_id' => $submission->id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => now()->subDays(10 - $index),
                'updated_at' => now()->subDays(10 - $index),
            ]);
        }
    }

    private function createTransactionsForTestUser(User $testUser): void
    {
        $transactions = [
            [35, 'Sākuma bonuss par profila aizpildīšanu'],
            [-8, 'Vakances izcelšana kategoriju sarakstā'],
            [24, 'Atlīdzība par apstiprinātu pieteikumu'],
            [-5, 'Pieteikuma prioritātes paaugstināšana'],
            [18, 'Kredīti par veiksmīgu sadarbību'],
            [-12, 'Publicētas vakances komisija'],
            [40, 'Administratora kredītu papildinājums'],
            [-6, 'Papildu redzamība vakancei'],
            [15, 'Bonuss par saņemtām atsauksmēm'],
            [-4, 'Profila izcelšanas maksa'],
            [22, 'Atlīdzība par pabeigtu darbu'],
            [10, 'Testa kredītu korekcija'],
        ];

        foreach ($transactions as $index => [$amount, $description]) {
            Transaction::create([
                'user_id' => $testUser->id,
                'amount' => $amount,
                'description' => $description,
                'created_at' => now()->subDays(12 - $index),
                'updated_at' => now()->subDays(12 - $index),
            ]);
        }
    }

    private function createSubmission(Job $job, User $user, string $status, string $message, int $offset): JobSubmission
    {
        return JobSubmission::create([
            'job_listing_id' => $job->id,
            'user_id' => $user->id,
            'message' => $message,
            'status' => $status,
            'admin_notes' => $status === JobSubmission::STATUS_ADMIN_REVIEW
                ? 'Nepieciešama administratora pārskatīšana testa datiem.'
                : null,
            'admin_approved' => $status === JobSubmission::STATUS_APPROVED,
            'dispute_status' => JobSubmission::DISPUTE_NONE,
            'is_frozen' => false,
            'created_at' => now()->subDays(18 - $offset),
            'updated_at' => now()->subDays(18 - $offset),
        ]);
    }

    private function categorySlugs(): array
    {
        $slugs = [];

        foreach (config('job_categories', []) as $parentSlug => $category) {
            $slugs[] = [
                'slug' => $parentSlug,
                'label' => $category['label'],
            ];

            foreach (($category['children'] ?? []) as $childSlug => $label) {
                $slugs[] = [
                    'slug' => $childSlug,
                    'label' => $label,
                ];
            }
        }

        return $slugs;
    }

    private function topCategorySlugs(): array
    {
        return collect(config('job_categories', []))
            ->map(fn (array $category, string $slug) => [
                'slug' => $slug,
                'label' => $category['label'],
            ])
            ->values()
            ->all();
    }

    private function jobTitle(string $slug, string $label): string
    {
        return match (explode('.', $slug)[0]) {
            'creative' => "{$label}: izveidot vizuālo materiālu",
            'technology' => "{$label}: salabot un uzlabot risinājumu",
            'education' => "{$label}: sagatavot mācību sesiju",
            'professional' => "{$label}: palīdzēt ar dokumentiem",
            'marketing' => "{$label}: sagatavot izaugsmes plānu",
            default => "{$label}: praktiska palīdzība",
        };
    }

    private function jobDescription(string $label, string $ownerName): string
    {
        return "{$ownerName} meklē palīdzību kategorijā “{$label}”.";
    }

    private function resetSqliteSequences(array $tables): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        try {
            foreach ($tables as $table) {
                DB::table('sqlite_sequence')->where('name', $table)->delete();
            }
        } catch (\Throwable) {
            //
        }
    }
}
