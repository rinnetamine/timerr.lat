<?php
//$jobs = Job::factory(5)->create();
namespace Database\Factories;


use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        // build flat list of category slugs from config
        $categories = config('job_categories', []);
        $flat = [];
        foreach ($categories as $key => $group) {
            $flat[] = $key;
            if (!empty($group['children']) && is_array($group['children'])) {
                $flat = array_merge($flat, array_keys($group['children']));
            }
        }

        $category = fake()->randomElement($flat ?: ['other.general']);

        // choose a group (top-level) for generating category-specific titles/descriptions
        $groupKey = explode('.', $category)[0];

        // category-aware title pools
        $pools = [
            'creative' => [
                'Design a modern logo for my startup',
                'Create social media graphics for product launch',
                'Edit a short promotional video',
                'Write landing page copy and headlines',
            ],
            'technology' => [
                'Build a small Laravel API endpoint',
                'Fix a bug in a React component',
                'Setup CI/CD pipeline for repository',
                'Integrate third-party OAuth provider',
            ],
            'education' => [
                'Math tutoring: calculus session',
                'English conversation practice',
                'Career coaching: resume review',
                'Design mentoring: portfolio feedback',
            ],
            'professional' => [
                'Prepare bookkeeping for last quarter',
                'Review a basic contract',
                'Business strategy session (1 hour)',
                'Recruiting: shortlist candidates',
            ],
            'marketing' => [
                'SEO audit of a small website',
                'Run Facebook ad campaign setup',
                'Create weekly social media calendar',
                'Email sequence for onboarding',
            ],
            'other' => [
                'Quick research and data entry task',
                'General virtual assistant: 1 hour',
            ],
        ];

        // pick a title from pool or fallback
    $titlePool = $pools[$groupKey] ?? [fake()->sentence(6)];
        $title = fake()->randomElement($titlePool);

        // build a slightly longer description using faker and a category hint
        $descIntro = "Category: " . ($categories[$groupKey]['label'] ?? ucfirst($groupKey)) . ".\n";
        $descBody = fake()->paragraphs(2, true);
        $description = $descIntro . "\n" . $descBody;

        // sensible credits ranges based on category
        $creditRanges = [
            'creative' => [1, 10],
            'technology' => [5, 40],
            'education' => [1, 8],
            'professional' => [3, 30],
            'marketing' => [2, 25],
            'other' => [1, 6],
        ];

        [$minCredits, $maxCredits] = $creditRanges[$groupKey] ?? [1, 10];

        return [
            'title' => $title,
            'description' => $description,
            'time_credits' => fake()->numberBetween($minCredits, $maxCredits),
            'category' => $category,
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a state for a specific category slug so seeded data can be
     * generated with titles/descriptions appropriate to that category.
     */
    public function forCategory(string $category)
    {
        return $this->state(function (array $attributes) use ($category) {
            $categories = config('job_categories', []);
            $groupKey = explode('.', $category)[0];

            $pools = [
                'creative' => [
                    'Design a modern logo for my startup',
                    'Create social media graphics for product launch',
                    'Edit a short promotional video',
                    'Write landing page copy and headlines',
                ],
                'technology' => [
                    'Build a small Laravel API endpoint',
                    'Fix a bug in a React component',
                    'Setup CI/CD pipeline for repository',
                    'Integrate third-party OAuth provider',
                ],
                'education' => [
                    'Math tutoring: calculus session',
                    'English conversation practice',
                    'Career coaching: resume review',
                    'Design mentoring: portfolio feedback',
                ],
                'professional' => [
                    'Prepare bookkeeping for last quarter',
                    'Review a basic contract',
                    'Business strategy session (1 hour)',
                    'Recruiting: shortlist candidates',
                ],
                'marketing' => [
                    'SEO audit of a small website',
                    'Run Facebook ad campaign setup',
                    'Create weekly social media calendar',
                    'Email sequence for onboarding',
                ],
                'other' => [
                    'Quick research and data entry task',
                    'General virtual assistant: 1 hour',
                ],
            ];

            $titlePool = $pools[$groupKey] ?? [fake()->sentence(6)];
            $title = fake()->randomElement($titlePool);

            $descIntro = "Category: " . ($categories[$groupKey]['label'] ?? ucfirst($groupKey)) . ".\n";
            $descBody = fake()->paragraphs(2, true);
            $description = $descIntro . "\n" . $descBody;

            $creditRanges = [
                'creative' => [1, 10],
                'technology' => [5, 40],
                'education' => [1, 8],
                'professional' => [3, 30],
                'marketing' => [2, 25],
                'other' => [1, 6],
            ];

            [$minCredits, $maxCredits] = $creditRanges[$groupKey] ?? [1, 10];

            return [
                'title' => $title,
                'description' => $description,
                'time_credits' => fake()->numberBetween($minCredits, $maxCredits),
                'category' => $category,
            ];
        });
    }
}
