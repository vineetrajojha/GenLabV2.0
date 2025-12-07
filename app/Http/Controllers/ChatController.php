<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected function user()
    {
        // Prefer superadmin if guard exists, then admin, then web
        $super = config('auth.guards.superadmin') ? auth('superadmin')->user() : null;
        return $super ?: (auth('admin')->user() ?: auth('web')->user());
    }

    protected function guardName()
    {
        if (config('auth.guards.superadmin') && auth('superadmin')->check()) return 'superadmin';
        if (auth('admin')->check()) return 'admin';
        if (auth('web')->check()) return 'web';
        return null;
    }

    protected function isAdminUser($user = null): bool
    {
        $g = $this->guardName();
        return in_array($g, ['admin','superadmin','super_admin'], true);
    }

    protected function isBookingsGroup(ChatGroup $group = null): bool
    {
        if (!$group) return false;
        $slug = Str::slug($group->slug ?: $group->name ?: '');
        return $slug === 'bookings';
    }

    protected function isDmGroup(ChatGroup $group = null): bool
    {
        if (!$group || !$group->slug) return false;
        return str_starts_with((string)$group->slug, 'dm-');
    }

    protected function dmParticipants(ChatGroup $group): array
    {
        if (!$this->isDmGroup($group)) return [];
        $parts = explode('-', (string)$group->slug);
        if (count($parts) !== 3) return [];
        return [ (int)$parts[1], (int)$parts[2] ];
    }

    protected function userInGroup(ChatGroup $group = null, $user = null): bool
    {
        if (!$group || !$user) return false;
        if (!$this->isDmGroup($group)) return true; // public groups visible to all
        $ids = $this->dmParticipants($group);
        return in_array($user->id, $ids, true);
    }

    protected function filterVisibleMessages($messages, $group, $viewer)
    {
        if (!$group || !$this->isBookingsGroup($group)) return $messages;
        if ($this->isAdminUser($viewer)) return $messages;
        $viewerId = $viewer?->id;
        return $messages->filter(function($m) use ($viewerId){
            $guard = Str::slug($m->sender_guard ?? '');
            $isAdminMsg = in_array($guard, ['admin','super-admin','superadmin'], true);
            $isMine = $viewerId && $m->user_id === $viewerId;
            return $isAdminMsg || $isMine;
        })->values();
    }

    protected function buildGroupPayload(ChatGroup $group, $viewerId = null)
    {
        $last = $group->messages()->latest('id')->first();
        // For deterministic DM slugs dm-{a}-{b}, show the peer's name to the viewer
        $displayName = $group->name;
        if ($viewerId && str_starts_with((string)$group->slug, 'dm-')) {
            $parts = explode('-', $group->slug);
            if (count($parts) === 3) {
                $a = (int)$parts[1]; $b = (int)$parts[2];
                $peerId = $viewerId === $a ? $b : $a;
                if ($peerId > 0) {
                    $peer = User::select('id','name')->find($peerId);
                    if ($peer && $peer->name) { $displayName = $peer->name; }
                }
            }
        }
        return [
            'id' => $group->id,
            'slug' => $group->slug,
            'name' => $displayName,
            'avatar' => null,
            'last_msg_id' => $last?->id,
            'last_msg_at' => $last?->created_at?->toISOString(),
            'latest' => $last ? [
                'id' => $last->id,
                'type' => $last->type,
                'content' => $last->content,
                'original_name' => $last->original_name,
                'sender_guard' => $last->sender_guard,
                'sender_name' => $last->sender_name,
                'user' => $last->user ? ['id'=>$last->user->id, 'name'=>$last->user->name] : null,
                'created_at' => $last->created_at?->toISOString(),
            ] : null,
            'unread' => 0,
        ];
    }

    protected function ensureDmGroup($aId, $bId, $displayName)
    {
        [$lo, $hi] = [$aId, $bId];
        if ($lo > $hi) { [$lo, $hi] = [$hi, $lo]; }
        $slug = 'dm-'.$lo.'-'.$hi;
        $group = ChatGroup::firstOrCreate(
            ['slug' => $slug],
            ['name' => $displayName, 'created_by' => $aId]
        );
        return $group;
    }

    public function groups()
    {
        // Ensure default groups exist
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        $user = $this->user();
        $groups = ChatGroup::orderBy('id')->get();
        // Only show DM groups the viewer is part of
        $filtered = $groups->filter(function($g) use ($user){
            if (!$this->isDmGroup($g)) return true;
            return $user && $this->userInGroup($g, $user);
        });
        return $filtered->map(fn($g)=> $this->buildGroupPayload($g, $user?->id))->values();
    }

    public function messages(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id']);
        $user = $this->user();
        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json([], 403);
        $list = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->orderBy('id')
            ->limit(200)
            ->get();
        $list = $this->filterVisibleMessages($list, $group, $user)
            ->map(function(ChatMessage $m) use ($user) { return $this->serializeMessage($m, $user); });
        return response()->json($list);
    }

    public function searchUsers(Request $request)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json([], 401);
        $q = trim((string) $request->get('q', ''));
        if ($q === '') return response()->json([]);
        $users = User::query()
            ->select('id','name','email')
            ->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")
                  ->orWhere('email','like',"%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
        return response()->json($users);
    }

    public function direct($userId)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json(['message'=>'Unauthorized'], 401);
        $other = User::findOrFail($userId);
        if ($viewer->id === $other->id) return response()->json(['message'=>'Cannot chat with yourself'], 422);
        $group = $this->ensureDmGroup($viewer->id, $other->id, $other->name ?? 'Chat');
        return response()->json($this->buildGroupPayload($group, $viewer->id));
    }

    public function directWith($userId)
    {
        // Symmetric user-to-user DM for all roles
        return $this->direct($userId);
    }

    public function messagesSince(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'after_id' => 'required|integer']);
        $user = $this->user();
        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json([], 403);
        $list = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->where('id', '>', $request->integer('after_id'))
            ->orderBy('id')
            ->limit(200)
            ->get();
        $list = $this->filterVisibleMessages($list, $group, $user)
            ->map(function(ChatMessage $m) use ($user) { return $this->serializeMessage($m, $user); });
        return response()->json($list);
    }

    public function send(Request $request)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate([
            'group_id' => 'required|integer|exists:chat_groups,id',
            'type' => 'required|string|in:text,image,pdf,voice',
            'content' => 'nullable|string|max:5000',
            'message' => 'nullable|string|max:5000',
            'body' => 'nullable|string|max:5000',
            'text' => 'nullable|string|max:5000',
            'description' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $type = (string) $request->string('type');
        // Gather text from any alias
        $contentAliases = [
            $request->input('content'),
            $request->input('message'),
            $request->input('body'),
            $request->input('text'),
            $request->input('description'),
        ];
        $firstNonEmpty = null;
        foreach ($contentAliases as $v){ if (is_string($v) && trim($v) !== '') { $firstNonEmpty = trim($v); break; } }

        $filePath = null; $original = null;

        if (in_array($type, ['image','pdf','voice'])) {
            $request->validate([
                'file' => [
                    'required','file','max:20480',
                    function ($attr, $value, $fail) use ($type) {
                        $mime = $value->getMimeType();
                        if ($type === 'image' && !str_starts_with($mime, 'image/')) $fail('Invalid image file');
                        if ($type === 'pdf' && $mime !== 'application/pdf') $fail('Invalid PDF file');
                        if ($type === 'voice' && !str_starts_with($mime, 'audio/')) $fail('Invalid audio file');
                    }
                ]
            ]);
            $original = $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->store('public/chat/'.$request->integer('group_id'));
        } else if ($type === 'text') {
            if ($firstNonEmpty === null) {
                return response()->json(['message' => 'Text content is required'], 422);
            }
        }

        $msg = ChatMessage::create([
            'group_id' => $request->integer('group_id'),
            'user_id' => $user->id,
            'type' => $type,
            'content' => $type === 'text' ? $firstNonEmpty : null,
            'file_path' => $filePath,
            'original_name' => $original,
            'sender_guard' => $this->guardName(),
            'sender_name' => $user->name ?? null,
        ]);

        $msg->load('user:id,name');
        // Recompute group payload for sidebar ordering
        $payload = $this->serializeMessage($msg, $user);
        $groupPayload = $this->buildGroupPayload(ChatGroup::find($msg->group_id), $user->id ?? null);
        return response()->json(['message' => $payload, 'group' => $groupPayload], 201);
    }

    public function react(Request $request, ChatMessage $message)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $request->validate(['type' => 'required|string|max:32']);
        ChatReaction::updateOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'type' => $request->string('type')
        ]);
        return response()->json(['status' => 'ok']);
    }

    protected function serializeMessage(ChatMessage $m, $viewer)
    {
        $fileUrl = $m->file_path ? Storage::url($m->file_path) : null;
        $viewerId = $viewer?->id;
        $senderName = $m->sender_name ?: ($m->relationLoaded('user') && $m->user ? $m->user->name : null);
        $data = [
            'id' => $m->id,
            'group_id' => $m->group_id,
            'user_id' => $m->user_id,
            'user' => $m->relationLoaded('user') && $m->user ? [ 'id' => $m->user->id, 'name' => $m->user->name ] : null,
            'sender_guard' => $m->sender_guard,
            'sender_name' => $senderName,
            'type' => $m->type,
            'content' => $m->content,
            'file_url' => $fileUrl,
            'original_name' => $m->original_name,
            'created_at' => $m->created_at?->toISOString(),
            'mine' => $viewerId !== null && $viewerId === $m->user_id,
        ];
        // Reactions visible only to the message sender (owner)
        if ($viewer && $viewer->id === $m->user_id) {
            $data['reactions'] = $m->relationLoaded('reactions') ? $m->reactions->map(function(ChatReaction $r){
                return [ 'type' => $r->type, 'user_id' => $r->user_id, 'user' => $r->relationLoaded('user') && $r->user ? [ 'id'=>$r->user->id, 'name'=>$r->user->name ] : null ];
            })->values() : [];
        } else {
            $data['reactions'] = [];
        }
        // Legacy records: ensure sender_name exists for admin chats
        if (!$data['sender_name'] && $m->sender_guard && in_array($m->sender_guard, ['admin','superadmin','super_admin'])) {
            $data['sender_name'] = ucfirst(str_replace('_',' ', $m->sender_guard));
        }
        return $data;
    }
}
