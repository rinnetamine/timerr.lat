<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
{
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

        // load messages between the two users (after marking read)
        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me->id)->where('recipient_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('recipient_id', $me->id);
        })->orderBy('created_at')->get();

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
            'conversations' => $conversations
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

    // store a new message
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // prevent sending message to yourself
        if ($data['recipient_id'] == Auth::id()) {
            return redirect('/profile')->with('error', 'You cannot send a message to yourself.');
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $data['recipient_id'],
            'body' => $data['body']
        ]);

        return redirect()->route('messages.conversation', ['user' => $data['recipient_id']])->with('success', 'Message sent.');
    }
}
