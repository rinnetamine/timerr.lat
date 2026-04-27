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
            $this->createReceivedSubmissionsForTestUser($testUser, $users, $jobs);
            $this->createSentSubmissionsForTestUser($testUser, $users, $jobs);
            $this->createReviewsForTestUser($testUser, $users, $jobs);
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

    private function createCategoryJobs($users)
    {
        $categories = $this->categorySlugs();
        $hasImageColumn = Schema::hasColumn('job_listings', 'image_path');

        return collect($categories)->map(function (array $category, int $index) use ($users, $hasImageColumn) {
            $owner = $users[$index % $users->count()];
            $credits = 4 + ($index % 9) * 2;

            return Job::create(array_filter([
                'user_id' => $owner->id,
                'title' => $this->jobTitle($category['slug'], $category['label']),
                'description' => $this->jobDescription($category['label'], $owner->first_name),
                'time_credits' => $credits,
                'category' => $category['slug'],
                'image_path' => $hasImageColumn ? Job::defaultImagePathForCategory($category['slug']) : null,
                'created_at' => now()->subDays(30 - $index),
                'updated_at' => now()->subDays(30 - $index),
            ], fn ($value) => $value !== null));
        });
    }

    private function createReceivedSubmissionsForTestUser(User $testUser, $users, $jobs): void
    {
        $testJobs = $jobs->where('user_id', $testUser->id)->values();
        $applicants = $users->where('id', '!=', $testUser->id)->values();

        foreach ($this->statuses as $index => $status) {
            $this->createSubmission(
                $testJobs[$index % $testJobs->count()],
                $applicants[$index % $applicants->count()],
                $status,
                "Saņemtais testa pieteikums statusam: {$status}.",
                $index
            );
        }
    }

    private function createSentSubmissionsForTestUser(User $testUser, $users, $jobs): void
    {
        $availableJobs = $jobs->where('user_id', '!=', $testUser->id)->values();

        foreach ($this->statuses as $index => $status) {
            $this->createSubmission(
                $availableJobs[$index],
                $testUser,
                $status,
                "Testa lietotāja nosūtītais pieteikums statusam: {$status}.",
                $index + 10
            );
        }
    }

    private function createReviewsForTestUser(User $testUser, $users, $jobs): void
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
            [5, 'Daudz skaidru piemēru un atsaucīga komunikācija visa darba laikā.'],
            [5, 'Darbs tika pieņemts ātri, un visas norādes bija saprotamas.'],
        ];

        $reviewJobs = $jobs->where('user_id', '!=', $testUser->id)->slice(5)->values();
        $reviewers = $users->where('id', '!=', $testUser->id)->values();

        foreach ($reviewTexts as $index => [$rating, $comment]) {
            $job = $reviewJobs[$index % $reviewJobs->count()];
            $submission = $this->createSubmission(
                $job,
                $testUser,
                JobSubmission::STATUS_APPROVED,
                "Atsauksmes testa pieteikums #{$index}.",
                $index + 20
            );

            Review::create([
                'reviewer_id' => $reviewers[$index % $reviewers->count()]->id,
                'reviewee_id' => $testUser->id,
                'job_submission_id' => $submission->id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => now()->subDays(12 - $index),
                'updated_at' => now()->subDays(12 - $index),
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
        return "{$ownerName} meklē palīdzību kategorijā “{$label}”. Uzdevums ir paredzēts kā skaidrs demo piemērs ar konkrētu rezultātu, saprotamu termiņu un draudzīgu komunikāciju.";
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
