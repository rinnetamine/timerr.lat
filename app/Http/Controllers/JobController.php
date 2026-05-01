<?php

// Šis fails pārvalda darba sludinājumu sarakstu, izveidi, labošanu un dzēšanu.

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    // Atlasa darba sludinājumus un piemēro meklēšanas, kategoriju un kārtošanas nosacījumus.
    public function index()
    {
        $userId = auth()->check() ? auth()->id() : null;
        
        // Darbu vaicājums sākas kopā ar darba autoru un tikai brīvajiem sludinājumiem.
        $jobsQuery = Job::with('user')
            ->whereDoesntHave('submissions')
            ->from('job_listings');

        // Meklēšana pārbauda darba nosaukumu, aprakstu un autora vārdu.
        if ($search = request('search')) {
            $search = strtolower($search);
            $jobsQuery->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(title) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(description) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->whereRaw("LOWER(first_name) LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("LOWER(last_name) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        // Kategoriju filtrs atbalsta gan galvenās kategorijas, gan apakškategorijas.
        if ($category = request('category')) {
            $categories = config('job_categories', []);
            // Ja izvēlēta galvenā kategorija, tiek iekļautas arī tās apakškategorijas.
            if (array_key_exists($category, $categories)) {
                $jobsQuery->where(function ($q) use ($category) {
                    $q->where('category', $category)
                      ->orWhere('category', 'like', $category . '.%');
                });
            } else {
                $jobsQuery->where('category', $category);
            }
        }

        // Statusa filtrs atlasa sludinājumus pēc pieteikumu stāvokļa.
        if ($status = request('status')) {
            $jobsQuery->whereHas('submissions', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        // Kredītu filtri ierobežo sludinājumus pēc minimālās un maksimālās vērtības.
        if (($min = request('min_credits')) !== null) {
            $jobsQuery->where('time_credits', '>=', intval($min));
        }

        if (($max = request('max_credits')) !== null) {
            $jobsQuery->where('time_credits', '<=', intval($max));
        }

        $sort = request('sort', 'latest');

        // Kārtošana ļauj mainīt darbu secību pēc datuma, nosaukuma vai kredītu daudzuma.
        switch ($sort) {
            case 'latest':
                $jobsQuery->orderBy('created_at', 'desc');
                break;
            case 'created_asc':
                $jobsQuery->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $jobsQuery->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $jobsQuery->orderBy('title', 'desc');
                break;
            case 'cheapest':
                $jobsQuery->orderBy('time_credits', 'asc');
                break;
            case 'expensive':
                $jobsQuery->orderBy('time_credits', 'desc');
                break;
            case 'seller_most_credits':
                // Lietotāju tabula tiek piesaistīta, lai kārtotu pēc sludinājuma autora kredītiem.
                $jobsQuery->leftJoin('users', 'job_listings.user_id', '=', 'users.id')
                          ->select('job_listings.*')
                          ->orderBy('users.time_credits', 'desc');
                break;
            default:
                $jobsQuery->orderBy('created_at', 'desc');
                break;
        }

        // Rezultāti tiek sadalīti lapās.
        $jobs = $jobsQuery->paginate(10);

        return view('jobs.index', [
            'jobs' => $jobs,
            'search' => request('search'),
            'sort' => $sort,
            'categories' => config('job_categories')
        ]);
    }

    // Sagatavo darba sludinājuma izveides formu.
    public function create()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return view('jobs.create', ['categories' => config('job_categories')]);
    }

    // Sagatavo viena darba sludinājuma detalizētu skatu.
    public function show(Job $job)
    {
        return view('jobs.show', [
            'job' => $job,
            'categories' => config('job_categories')
        ]);
    }

    // Izveido darba sludinājumu un rezervē autora laika kredītus datubāzes transakcijā.
    public function store()
    {
        // Atļauto kategoriju saraksts tiek izveidots no konfigurācijas.
        $categories = config('job_categories', []);
        $allowed = [];
        foreach ($categories as $key => $group) {
            $allowed[] = $key;
            if (!empty($group['children']) && is_array($group['children'])) {
                $allowed = array_merge($allowed, array_keys($group['children']));
            }
        }

        // Pirms saglabāšanas tiek validēti lietotāja ievaddati.
        $attributes = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', Rule::in($allowed)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (request()->hasFile('image')) {
            $attributes['image_path'] = request()->file('image')->store('job-images', 'public');
        }

        unset($attributes['image']);

        $user = auth()->user();

        // Šī pārbaude nepieļauj darbību, ja lietotājam nepietiek kredītu.
        if ($user->time_credits < $attributes['time_credits']) {
            return back()->withErrors([
                'time_credits' => 'Jums nav pietiekami daudz laika kredītu. Lūdzu, papildiniet kredītus vai samaziniet nepieciešamo daudzumu.'
            ])->withInput();
        }

        try {
            // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
            $job = DB::transaction(function () use ($attributes, $user) {
                // Sludinājumam tiek piesaistīts pašreizējais lietotājs.
                $attributes['user_id'] = $user->id;
                $job = Job::create($attributes);

                // Rezervētie kredīti tiek noņemti no autora bilances.
                $user->update([
                    'time_credits' => $user->time_credits - $attributes['time_credits']
                ]);

                // Kredītu kustība tiek pierakstīta darījumu vēsturē.
                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'amount' => -$attributes['time_credits'],
                    'description' => "Izveidots darba sludinājums: {$attributes['title']}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return $job;
            });

            return redirect('/jobs')->with('success', 'Serviss veiksmīgi izveidots! ' . $attributes['time_credits'] . ' kredīti ir rezervēti šim darbam.');
        } catch (\Exception $e) {
            // Kļūda tiek pierakstīta žurnālā un parādīta lietotājam.
            Log::error('Job creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās izveidot sludinājumu: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // Sagatavo esoša darba sludinājuma labošanas formu.
    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job, 'categories' => config('job_categories')]);
    }

    // Labo sludinājumu un pārrēķina rezervēto kredītu apjomu, ja mainīta darba cena.
    public function update(Job $job)
    {
        // Tiek pārbaudītas lietotāja tiesības labot konkrēto sludinājumu.
        Gate::authorize('edit-job', $job);

        // Atļauto kategoriju saraksts tiek izveidots no konfigurācijas.
        $categories = config('job_categories', []);
        $allowed = [];
        foreach ($categories as $key => $group) {
            $allowed[] = $key;
            if (!empty($group['children']) && is_array($group['children'])) {
                $allowed = array_merge($allowed, array_keys($group['children']));
            }
        }

        // Pirms saglabāšanas tiek validēti lietotāja ievaddati.
        $attributes = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', Rule::in($allowed)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (request()->hasFile('image')) {
            if ($job->image_path) {
                // Aizstājot attēlu, tiek sakopts arī iepriekšējais fails.
                Storage::disk('public')->delete($job->image_path);
            }

            $attributes['image_path'] = request()->file('image')->store('job-images', 'public');
        }

        unset($attributes['image']);

        $user = auth()->user();
        $originalCredits = $job->time_credits;
        $newCredits = $attributes['time_credits'];

        // Ja kredītu apjoms ir mainīts, tiek pārrēķināta rezervētā summa.
        if ($originalCredits != $newCredits) {
            $netChange = $newCredits - $originalCredits;

            // Šī pārbaude nepieļauj darbību, ja lietotājam nepietiek kredītu.
            if ($netChange > 0) {
                if ($user->time_credits < $netChange) {
                    return back()->withErrors([
                        'time_credits' => 'Jums nav pietiekami daudz laika kredītu. Jūsu pašreizējais atlikums ir ' . $user->time_credits . ' kredīti.'
                    ])->withInput();
                }
            }

            try {
                // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
                DB::transaction(function () use ($job, $attributes, $user, $originalCredits, $newCredits) {
                    // Vispirms lietotājam tiek atgriezti iepriekš rezervētie kredīti.
                    $user->update([
                        'time_credits' => $user->time_credits + $originalCredits
                    ]);

                    // Kredītu kustība tiek pierakstīta darījumu vēsturē.
                    DB::table('transactions')->insert([
                        'user_id' => $user->id,
                        'amount' => $originalCredits,
                        'description' => "Atgriezti kredīti no atjaunināta darba: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Pēc tam tiek rezervēts jaunais kredītu apjoms.
                    $user->update([
                        'time_credits' => $user->time_credits - $newCredits
                    ]);

                    // Kredītu kustība tiek pierakstīta darījumu vēsturē.
                    DB::table('transactions')->insert([
                        'user_id' => $user->id,
                        'amount' => -$newCredits,
                        'description' => "Piešķirti kredīti atjauninātam darbam: {$attributes['title']}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Sludinājums tiek atjaunināts ar jaunajiem datiem.
                    $job->update($attributes);
                });

                return redirect('/jobs/' . $job->id)->with('success', 'Serviss atjaunināts veiksmīgi! Kredītu pielāgojums ir apstrādāts.');
            } catch (\Exception $e) {
                // Kļūda tiek pierakstīta žurnālā un parādīta lietotājam.
                Log::error('Job update failed: ' . $e->getMessage());
                
                return back()->withErrors([
                    'error' => 'Neizdevās atjaunināt sludinājumu: ' . $e->getMessage()
                ])->withInput();
            }
        } else {
            // Ja kredīti nav mainīti, tiek atjaunināti tikai sludinājuma dati.
            $job->update($attributes);
            return redirect('/jobs/' . $job->id)->with('success', 'Serviss atjaunināts veiksmīgi!');
        }
    }

    // Dzēš sludinājumu, izņem tā attēlu un atgriež rezervētos kredītus īpašniekam.
    public function destroy(Job $job)
    {
        // Tiek pārbaudītas lietotāja tiesības dzēst konkrēto sludinājumu.
        Gate::authorize('edit-job', $job);
        
        try {
            // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
            DB::transaction(function () use ($job) {
                // Tiek atrasts sludinājuma īpašnieks.
                $jobOwner = User::find($job->user_id);
                
                if ($jobOwner) {
                    // Rezervētie kredīti tiek atgriezti sludinājuma īpašniekam.
                    $jobOwner->update([
                        'time_credits' => $jobOwner->time_credits + $job->time_credits
                    ]);
                    
                    // Kredītu kustība tiek pierakstīta darījumu vēsturē.
                    DB::table('transactions')->insert([
                        'user_id' => $jobOwner->id,
                        'amount' => $job->time_credits,
                        'description' => "Atgriezti kredīti no dzēsta darba: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                if ($job->image_path) {
                    // Dzēšot sludinājumu, tiek sakopts arī saistītais attēla fails.
                    Storage::disk('public')->delete($job->image_path);
                }

                // Sludinājums tiek dzēsts no datubāzes.
                $job->delete();
            });
            
            return redirect('/jobs')->with('success', 'Serviss veiksmīgi dzēsts un kredīti ir atgriezti!');
        } catch (\Exception $e) {
            // Kļūda tiek pierakstīta žurnālā un parādīta lietotājam.
            Log::error(message: 'Job deletion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās dzēst sludinājumu: ' . $e->getMessage()
            ]);
        }
    }
}
