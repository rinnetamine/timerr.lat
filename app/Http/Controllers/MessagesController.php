<?php

// Šis fails nodrošina lietotāju sarakstu, pielikumu augšupielādi un drošu failu lejupielādi.

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\JobSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{
    // Pārbauda piekļuves tiesības un izsniedz ziņojumam pievienoto failu.
    public function downloadFile(Message $message)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $userId = Auth::id();

        // Pielikumu drīkst lejupielādēt tikai ziņojuma sūtītājs vai saņēmējs.
        if ($userId !== $message->sender_id && $userId !== $message->recipient_id) {
            abort(403, 'Jums nav atļauts piekļūt šim failam.');
        }

        // Ziņojumam jābūt pielikumam, un failam jāeksistē publiskajā krātuvē.
        if (!$message->hasAttachment() || !Storage::disk('public')->exists($message->attachment_path)) {
            abort(404, 'Fails nav atrasts.');
        }

        $path = Storage::disk('public')->path($message->attachment_path);

        if ($message->attachmentIsImage()) {
            return Response::file($path, [
                'Content-Type' => $message->attachment_mime_type,
                'Content-Disposition' => 'inline; filename="' . addslashes($message->attachment_name) . '"',
            ]);
        }

        return Response::download($path, $message->attachment_name, [
            'Content-Type' => $message->attachment_mime_type,
        ]);
    }

    // Atver sarunu ar konkrētu lietotāju un atzīmē ienākošos ziņojumus kā izlasītus.
    public function conversation(User $user)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();

        // Lietotājs nevar atvērt sarunu pats ar sevi.
        if ($user->id === $me->id) {
            return redirect('/profile');
        }

        // Ienākošie neizlasītie ziņojumi šajā sarunā tiek atzīmēti kā izlasīti.
        Message::where('sender_id', $user->id)
            ->where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Tiek noteikts, vai starp lietotājiem pastāv saistīti darba pieteikumi.
        $jobRelationships = $this->getJobRelationships($me, $user);

        // Tiek ielādēti ziņojumi starp abiem lietotājiem.
        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me->id)->where('recipient_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('recipient_id', $me->id);
        })
            ->orderByDesc('created_at')
            ->paginate(50, ['*'], 'messages_page')
            ->withQueryString();

        $messages->setCollection($messages->getCollection()->reverse()->values());

        // Sarunu saraksts tiek sagatavots sānu joslas attēlošanai.
        $all = Message::where('sender_id', $me->id)
            ->orWhere('recipient_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        $convos = [];
        foreach ($all as $m) {
            $otherId = $m->sender_id === $me->id ? $m->recipient_id : $m->sender_id;
            if (!isset($convos[$otherId])) {
                $convos[$otherId] = [
                    'other' => User::find($otherId),
                    'latest' => $m,
                    'unread' => 0
                ];
            }
        }

        // Darba kontaktpersonas tiek pievienotas arī tad, ja saruna vēl nav sākta.
        $jobContacts = $this->getJobRelatedContacts($me);
        
        foreach ($jobContacts as $contact) {
            $contactId = $contact['user']->id;
            if (!isset($convos[$contactId])) {
                // Palīgziņojums ļauj kārtot kontaktus pēc pieteikuma datuma.
                $dummyMessage = new Message();
                $dummyMessage->created_at = $contact['submission']->created_at;
                $dummyMessage->body = '';
                
                $convos[$contactId] = [
                    'other' => $contact['user'],
                    'latest' => $dummyMessage,
                    'unread' => 0,
                    'job_relationship' => $contact['role']
                ];
            } else {
                // Esošai sarunai tiek pievienota darba attiecību informācija.
                $convos[$contactId]['job_relationship'] = $contact['role'];
            }
        }

        $unreads = Message::where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->select('sender_id', DB::raw('count(*) as cnt'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        foreach ($convos as $id => &$c) {
            $c['unread'] = $unreads->has($id) ? $unreads->get($id)->cnt : 0;
        }

        $conversations = collect($convos)->sortByDesc(function ($c) {
            return $c['latest']->created_at;
        })->values();

        return view('messages.conversation', [
            'other' => $user,
            'messages' => $messages,
            'conversations' => $conversations,
            'jobRelationships' => $jobRelationships
        ]);
    }

    // Sagatavo lietotāja sarunu sarakstu un neizlasīto ziņojumu skaitu.
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();

        // Tiek iegūti visi ziņojumi, kuros piedalās pašreizējais lietotājs.
        $all = Message::where('sender_id', $me->id)
            ->orWhere('recipient_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        $convos = [];

        foreach ($all as $m) {
            $otherId = $m->sender_id === $me->id ? $m->recipient_id : $m->sender_id;
            if (!isset($convos[$otherId])) {
                $convos[$otherId] = [
                    'other' => User::find($otherId),
                    'latest' => $m,
                    'unread' => 0
                ];
            }
        }

        // Darba kontaktpersonas tiek pievienotas arī tad, ja saruna vēl nav sākta.
        $jobContacts = $this->getJobRelatedContacts($me);
        
        foreach ($jobContacts as $contact) {
            $contactId = $contact['user']->id;
            if (!isset($convos[$contactId])) {
                // Palīgziņojums ļauj kārtot kontaktus pēc pieteikuma datuma.
                $dummyMessage = new Message();
                $dummyMessage->created_at = $contact['submission']->created_at;
                $dummyMessage->body = '';
                
                $convos[$contactId] = [
                    'other' => $contact['user'],
                    'latest' => $dummyMessage,
                    'unread' => 0,
                    'job_relationship' => $contact['role']
                ];
            } else {
                // Esošai sarunai tiek pievienota darba attiecību informācija.
                $convos[$contactId]['job_relationship'] = $contact['role'];
            }
        }

        // Neizlasītie ziņojumi tiek saskaitīti katram sūtītājam.
        $unreads = Message::where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->select('sender_id', DB::raw('count(*) as cnt'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        foreach ($convos as $id => &$c) {
            $c['unread'] = $unreads->has($id) ? $unreads->get($id)->cnt : 0;
        }

        // Sarunas tiek sakārtotas pēc pēdējā ziņojuma vai pieteikuma datuma.
        $conversations = collect($convos)->sortByDesc(function ($c) {
            return $c['latest']->created_at;
        })->values();

        return view('messages.index', [
            'conversations' => $conversations
        ]);
    }

    // Sagatavo lietotāju sarakstu jaunas sarunas sākšanai.
    public function create(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();
        $search = trim((string) $request->query('q', ''));

        // Tiek iegūti visi ziņojumi, kuros piedalās pašreizējais lietotājs.
        $all = Message::where('sender_id', $me->id)
            ->orWhere('recipient_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        $convos = [];
        $conversationUserIds = [];

        foreach ($all as $m) {
            $otherId = $m->sender_id === $me->id ? $m->recipient_id : $m->sender_id;
            $conversationUserIds[] = $otherId;

            if (!isset($convos[$otherId])) {
                $convos[$otherId] = [
                    'other' => User::find($otherId),
                    'latest' => $m,
                    'unread' => 0
                ];
            }
        }

        $jobContacts = $this->getJobRelatedContacts($me);

        foreach ($jobContacts as $contact) {
            $contactId = $contact['user']->id;
            $conversationUserIds[] = $contactId;

            if (!isset($convos[$contactId])) {
                // Palīgziņojums ļauj kārtot kontaktus pēc pieteikuma datuma.
                $dummyMessage = new Message();
                $dummyMessage->created_at = $contact['submission']->created_at;
                $dummyMessage->body = '';

                $convos[$contactId] = [
                    'other' => $contact['user'],
                    'latest' => $dummyMessage,
                    'unread' => 0,
                    'job_relationship' => $contact['role']
                ];
            } else {
                // Esošai sarunai tiek pievienota darba attiecību informācija.
                $convos[$contactId]['job_relationship'] = $contact['role'];
            }
        }

        $unreads = Message::where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->select('sender_id', DB::raw('count(*) as cnt'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        foreach ($convos as $id => &$c) {
            $c['unread'] = $unreads->has($id) ? $unreads->get($id)->cnt : 0;
        }

        $conversations = collect($convos)->sortByDesc(function ($c) {
            return $c['latest']->created_at;
        })->values();

        $users = User::query()
            ->whereKeyNot($me->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(8)
            ->withQueryString();

        return view('messages.create', [
            'conversations' => $conversations,
            'users' => $users,
            'search' => $search,
            'conversationUserIds' => collect($conversationUserIds)->unique()->values(),
        ]);
    }

    // Validē ziņojuma tekstu, pielikumu un saglabā ziņojumu datubāzes transakcijā.
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $files = $this->normalizeUploadedFiles($request->allFiles()['files'] ?? []);

        // Tiek validēts saņēmējs, teksts un viens iespējamais pielikums.
        $validator = Validator::make($request->all(), [
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array', 'max:1'],
            'files.*' => ['nullable', 'file', 'max:20480']
        ], [
            'files.*.file' => 'Vienu no pievienotajiem failiem neizdevās augšupielādēt. Lūdzu, mēģiniet vēlreiz.',
            'files.*.max' => 'Katram pielikumam jābūt ne lielākam par 20 MB.',
            'files.max' => 'Vienam ziņojumam var pievienot vienu failu.',
        ]);

        // Papildu validācija pārbauda, vai ziņojumam ir teksts vai vismaz viens pielikums.
        $validator->after(function ($validator) use ($request, $files) {
            $body = trim((string) $request->input('body', ''));

            if ($body === '' && count($files) === 0) {
                $validator->errors()->add('body', 'Uzrakstiet ziņojumu vai pievienojiet vismaz vienu failu.');
            }

            foreach ($files as $index => $file) {
                if (! $file->isValid()) {
                    $validator->errors()->add("files.$index", $this->uploadErrorMessage($file));
                }
            }
        });

        $data = $validator->validate();

        // Lietotājs nevar nosūtīt ziņojumu pats sev.
        if ($data['recipient_id'] == Auth::id()) {
            return redirect('/profile')->with('error', 'Jūs nevarat nosūtīt ziņojumu sev.');
        }

        try {
            // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
            DB::transaction(function () use ($data, $files) {
                $file = $files[0] ?? null;
                $attachment = [];

                if ($file) {
                    $path = $file->store('message-files', 'public');

                    if (!$path) {
                        throw new \Exception('Krātuve neatgrieza faila ceļu.');
                    }

                    // Pielikumam tiek saglabāts faila nosaukums, ceļš, tips un izmērs.
                    $attachment = [
                        'attachment_name' => $file->getClientOriginalName(),
                        'attachment_path' => $path,
                        'attachment_mime_type' => $file->getMimeType(),
                        'attachment_size' => $file->getSize(),
                    ];
                }

                Message::create(array_merge([
                    'sender_id' => Auth::id(),
                    'recipient_id' => $data['recipient_id'],
                    'body' => $data['body'] ?? ''
                ], $attachment));
            });

            return redirect()->route('messages.conversation', ['user' => $data['recipient_id']]);
        } catch (\Exception $e) {
            \Log::error('Message upload error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Kļūda sūtot ziņojumu: ' . $e->getMessage()])->withInput();
        }
    }

    // Pārveido augšupielādes datus vienkāršā failu masīvā.
    private function normalizeUploadedFiles(mixed $files): array
    {
        return collect(Arr::wrap($files))
            ->flatten(1)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->values()
            ->all();
    }

    // Pārveido augšupielādes kļūdas lietotājam saprotamos paziņojumos.
    private function uploadErrorMessage(UploadedFile $file): string
    {
        // Augšupielādes kļūdas tiek pārveidotas lietotājam saprotamos paziņojumos.
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Šis pielikums ir pārāk liels. Lūdzu, izvēlieties failu, kas ir mazāks par 20 MB.',
            UPLOAD_ERR_PARTIAL => 'Šis pielikums tika augšupielādēts tikai daļēji. Lūdzu, mēģiniet vēlreiz.',
            UPLOAD_ERR_NO_FILE => 'Pielikums netika saņemts. Lūdzu, izvēlieties failu un mēģiniet vēlreiz.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Serveris nevarēja saglabāt šo pielikumu. Lūdzu, mēģiniet vēlreiz.',
            default => $file->getErrorMessage(),
        };
    }

    // Nosaka darba attiecības starp diviem lietotājiem.
    private function getJobRelationships($user1, $user2)
    {
        $relationships = [];
        $seenRoles = [];
        
        // Tiek meklēti darba pieteikumi, kuros abi lietotāji ir savstarpēji saistīti.
        $submissions = JobSubmission::where(function($q) use ($user1, $user2) {
            $q->where('user_id', $user1->id)
              ->whereHas('jobListing', function($subQ) use ($user2) {
                  $subQ->where('user_id', $user2->id);
              });
        })->orWhere(function($q) use ($user1, $user2) {
            $q->where('user_id', $user2->id)
              ->whereHas('jobListing', function($subQ) use ($user1) {
                  $subQ->where('user_id', $user1->id);
              });
        })->with('jobListing')->get();

        foreach ($submissions as $submission) {
            $role = $submission->user_id === $user1->id ? 'worker' : 'client';
            
            // Katrs attiecību veids tiek pievienots tikai vienu reizi.
            if (!in_array($role, $seenRoles)) {
                $relationships[] = [
                    'type' => 'job',
                    'submission' => $submission,
                    'job' => $submission->jobListing,
                    'role' => $role
                ];
                $seenRoles[] = $role;
            }
        }
        
        return $relationships;
    }

    // Atgriež lietotājus, ar kuriem pastāv darba attiecības.
    private function getJobRelatedContacts($user)
    {
        $contacts = [];
        
        // Tiek iegūti lietotāji, kas pieteikušies pašreizējā lietotāja darbiem.
        $jobOwnerSubmissions = JobSubmission::whereHas('jobListing', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('user')->get();
        
        foreach ($jobOwnerSubmissions as $submission) {
            if ($submission->user_id !== $user->id) {
                $contacts[] = [
                    'user' => $submission->user,
                    'role' => 'client',
                    'submission' => $submission
                ];
            }
        }
        
        // Tiek iegūti lietotāji, kuru darbiem pašreizējais lietotājs ir pieteicies.
        $workerSubmissions = JobSubmission::where('user_id', $user->id)
            ->with('jobListing.user')
            ->get();
        
        foreach ($workerSubmissions as $submission) {
            if ($submission->jobListing->user_id !== $user->id) {
                $contacts[] = [
                    'user' => $submission->jobListing->user,
                    'role' => 'worker',
                    'submission' => $submission
                ];
            }
        }
        
        // Kontaktpersonu sarakstā tiek novērsti dublikāti.
        $uniqueContacts = [];
        $seenIds = [];
        
        foreach ($contacts as $contact) {
            $userId = $contact['user']->id;
            if (!in_array($userId, $seenIds)) {
                $seenIds[] = $userId;
                $uniqueContacts[] = $contact;
            }
        }
        
        return $uniqueContacts;
    }
}
