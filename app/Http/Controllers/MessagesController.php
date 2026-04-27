<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageFile;
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
    public function downloadFile(MessageFile $file)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $message = $file->message;
        $userId = Auth::id();

        if ($userId !== $message->sender_id && $userId !== $message->recipient_id) {
            abort(403, 'Jums nav atļauts piekļūt šim failam.');
        }

        if (! Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Fails nav atrasts.');
        }

        $path = Storage::disk('public')->path($file->file_path);

        if ($file->isImage()) {
            return Response::file($path, [
                'Content-Type' => $file->mime_type,
                'Content-Disposition' => 'inline; filename="' . addslashes($file->file_name) . '"',
            ]);
        }

        return Response::download($path, $file->file_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    // show conversation between authenticated user and given user
    public function conversation(User $user)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();

        // prevent opening a conversation with yourself
        if ($user->id === $me->id) {
            return redirect('/profile');
        }

        // mark unread messages addressed to user from the other user as read
        Message::where('sender_id', $user->id)
            ->where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // check for job relationships between users
        $jobRelationships = $this->getJobRelationships($me, $user);

        // load messages between the two users (after marking read)
        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me->id)->where('recipient_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('recipient_id', $me->id);
        })->with('files')->orderBy('created_at')->get();

        // build conversations list so the view can render a sidebar
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

        // get job-related contacts even if no messages exist
        $jobContacts = $this->getJobRelatedContacts($me);
        
        foreach ($jobContacts as $contact) {
            $contactId = $contact['user']->id;
            if (!isset($convos[$contactId])) {
                // Create a dummy message for sorting purposes
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
                // Add job relationship info to existing conversation
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

    // list conversations (inbox) for authenticated user
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();

        // get all messages involving me ordered by latest
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

        // get job-related contacts even if no messages exist
        $jobContacts = $this->getJobRelatedContacts($me);
        
        foreach ($jobContacts as $contact) {
            $contactId = $contact['user']->id;
            if (!isset($convos[$contactId])) {
                // Create a dummy message for sorting purposes
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
                // Add job relationship info to existing conversation
                $convos[$contactId]['job_relationship'] = $contact['role'];
            }
        }

        // compute unread counts per sender (messages where recipient is me and read_at is null)
        $unreads = Message::where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->select('sender_id', DB::raw('count(*) as cnt'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        foreach ($convos as $id => &$c) {
            $c['unread'] = $unreads->has($id) ? $unreads->get($id)->cnt : 0;
        }

        // convert to list sorted by latest created_at
        $conversations = collect($convos)->sortByDesc(function ($c) {
            return $c['latest']->created_at;
        })->values();

        return view('messages.index', [
            'conversations' => $conversations
        ]);
    }

    public function create(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $me = Auth::user();
        $search = trim((string) $request->query('q', ''));

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

    // store a new message
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $files = $this->normalizeUploadedFiles($request->allFiles()['files'] ?? []);

        $validator = Validator::make($request->all(), [
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', 'file', 'max:20480']
        ], [
            'files.*.file' => 'Vienu no pievienotajiem failiem neizdevās augšupielādēt. Lūdzu, mēģiniet vēlreiz.',
            'files.*.max' => 'Katram pielikumam jābūt ne lielākam par 20 MB.',
        ]);

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

        // prevent sending message to yourself
        if ($data['recipient_id'] == Auth::id()) {
            return redirect('/profile')->with('error', 'Jūs nevarat nosūtīt ziņojumu sev.');
        }

        try {
            DB::transaction(function () use ($data, $files) {
                $message = Message::create([
                    'sender_id' => Auth::id(),
                    'recipient_id' => $data['recipient_id'],
                    'body' => $data['body'] ?? ''
                ]);

                // handle file uploads if any were provided
                foreach ($files as $index => $file) {
                    try {
                        $path = $file->store('message-files', 'public');

                        if (! $path) {
                            throw new \Exception('Krātuve neatgrieza faila ceļu.');
                        }

                        MessageFile::create([
                            'message_id' => $message->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'mime_type' => $file->getMimeType(),
                            'file_size' => $file->getSize()
                        ]);
                    } catch (\Exception $fileError) {
                        \Log::error("Error uploading file {$index}: " . $fileError->getMessage());
                        throw new \Exception("Neizdevās augšupielādēt failu {$index}: " . $fileError->getMessage());
                    }
                }
            });

            return redirect()->route('messages.conversation', ['user' => $data['recipient_id']]);
        } catch (\Exception $e) {
            \Log::error('Message upload error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Kļūda sūtot ziņojumu: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Normalize uploaded files into a flat array.
     */
    private function normalizeUploadedFiles(mixed $files): array
    {
        return collect(Arr::wrap($files))
            ->flatten(1)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->values()
            ->all();
    }

    /**
     * Convert PHP upload errors into something the UI can show directly.
     */
    private function uploadErrorMessage(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Šis pielikums ir pārāk liels. Lūdzu, izvēlieties failu, kas ir mazāks par 20 MB.',
            UPLOAD_ERR_PARTIAL => 'Šis pielikums tika augšupielādēts tikai daļēji. Lūdzu, mēģiniet vēlreiz.',
            UPLOAD_ERR_NO_FILE => 'Pielikums netika saņemts. Lūdzu, izvēlieties failu un mēģiniet vēlreiz.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Serveris nevarēja saglabāt šo pielikumu. Lūdzu, mēģiniet vēlreiz.',
            default => $file->getErrorMessage(),
        };
    }

    /**
     * Get job relationships between two users
     */
    private function getJobRelationships($user1, $user2)
    {
        $relationships = [];
        $seenRoles = [];
        
        // Check if they have any job submissions together
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
            
            // Only add each role once
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

    /**
     * Get all users you have job relationships with
     */
    private function getJobRelatedContacts($user)
    {
        $contacts = [];
        
        // Get users where you are the job owner
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
        
        // Get users where you are the worker
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
        
        // Remove duplicates and return unique contacts
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
