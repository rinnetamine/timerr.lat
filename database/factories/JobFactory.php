<?php
//$jobs = Job::factory(5)->create();
namespace Database\Factories;


use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

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

        // category-aware title pools in Latvian
        $pools = [
            'creative' => [
                'Izveidot modernu logotipu manam uzņēmumam',
                'Izveidot sociālo mediju grafiku produktu palaišanai',
                'Rediģēt īsu reklāmas video',
                'Uzrakstīt satura tekstu un virsrakstus mājaslapai',
            ],
            'technology' => [
                'Izveidot mazu Laravel API galapunktu',
                'Salabot kļūdu React komponentā',
                'Iestatīt CI/CD konveijeru repozitorijam',
                'Integrēt trešās puses OAuth nodrošinātāju',
            ],
            'education' => [
                'Matemātikas privātstundas: kalkulu sesija',
                'Angļu valodas konversāciju prakse',
                'Karjeras konsultācijas: CV pārskatīšana',
                'Dizaina mentorēšana: portfolio atsauksmes',
            ],
            'professional' => [
                'Sagatavot grāmatvedību par pēdējo ceturksni',
                'Pārskatīt pamata līgumu',
                'Biznesa stratēģijas sesija (1 stunda)',
                'Darbinieku atlase: kandidātu saraksta izveide',
            ],
            'marketing' => [
                'SEO audits mazai mājaslapai',
                'Facebook reklāmas kampaņas iestatīšana',
                'Izveidot iknedēļu sociālo mediju kalendāru',
                'E-pasta sērija jauniem lietotājiem',
            ],
            'other' => [
                'Ātra pētniecība un datu ievades uzdevums',
                'Vispārīgais virtuālais asistents: 1 stunda',
            ],
        ];

        // pick a title from pool or fallback
    $titlePool = $pools[$groupKey] ?? [fake()->sentence(6)];
        $title = fake()->randomElement($titlePool);

        // build a slightly longer description using faker and a category hint in Latvian
        $descIntro = "Kategorija: " . ($categories[$groupKey]['label'] ?? ucfirst($groupKey)) . ".\n";
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

        $attributes = [
            'title' => $title,
            'description' => $description,
            'time_credits' => fake()->numberBetween($minCredits, $maxCredits),
            'category' => $category,
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('job_listings', 'image_path')) {
            $attributes['image_path'] = Job::defaultImagePathForCategory($category);
        }

        return $attributes;
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
                    'Izveidot modernu logotipu manam uzņēmumam',
                    'Izveidot sociālo mediju grafiku produktu palaišanai',
                    'Rediģēt īsu reklāmas video',
                    'Uzrakstīt satura tekstu un virsrakstus mājaslapai',
                ],
                'technology' => [
                    'Izveidot mazu Laravel API galapunktu',
                    'Salabot kļūdu React komponentā',
                    'Iestatīt CI/CD konveijeru repozitorijam',
                    'Integrēt trešās puses OAuth nodrošinātāju',
                ],
                'education' => [
                    'Matemātikas privātstundas: kalkulu sesija',
                    'Angļu valodas konversāciju prakse',
                    'Karjeras konsultācijas: CV pārskatīšana',
                    'Dizaina mentorēšana: portfolio atsauksmes',
                ],
                'professional' => [
                    'Sagatavot grāmatvedību par pēdējo ceturksni',
                    'Pārskatīt pamata līgumu',
                    'Biznesa stratēģijas sesija (1 stunda)',
                    'Darbinieku atlase: kandidātu saraksta izveide',
                ],
                'marketing' => [
                    'SEO audits mazai mājaslapai',
                    'Facebook reklāmas kampaņas iestatīšana',
                    'Izveidot iknedēļu sociālo mediju kalendāru',
                    'E-pasta sērija jauniem lietotājiem',
                ],
                'other' => [
                    'Ātra pētniecība un datu ievades uzdevums',
                    'Vispārīgais virtuālais asistents: 1 stunda',
                ],
            ];

            $titlePool = $pools[$groupKey] ?? [fake()->sentence(6)];
            $title = fake()->randomElement($titlePool);

            $descIntro = "Kategorija: " . ($categories[$groupKey]['label'] ?? ucfirst($groupKey)) . ".\n";
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

            $attributes = [
                'title' => $title,
                'description' => $description,
                'time_credits' => fake()->numberBetween($minCredits, $maxCredits),
                'category' => $category,
            ];

            if (Schema::hasColumn('job_listings', 'image_path')) {
                $attributes['image_path'] = Job::defaultImagePathForCategory($category);
            }

            return $attributes;
        });
    }
}
