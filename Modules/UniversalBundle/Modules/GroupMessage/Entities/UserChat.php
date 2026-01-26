<?php

namespace Modules\GroupMessage\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\MentionUser;
use App\Scopes\ActiveScope;
use App\Models\UserchatFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserChat extends BaseModel
{

    use HasCompany, HasFactory;

    protected $table = 'users_chat';

    protected $guarded = [
        'id'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from')->withoutGlobalScope(ActiveScope::class);
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to')->withoutGlobalScope(ActiveScope::class);
    }

    public function scopeUnreadMessagesForUser($query)
    {
        return $query->where('from', $this->from)->where('to', user()->id)
            ->where('message_seen', 'no')
            ->where('chat_type', 'private')
            ->groupBy('from');
    }

    public function scopeUnreadMessagesForClient($query)
    {
        return $query->where('from', $this->from)->where('to', user()->id)
            ->where('message_seen', 'no')
            ->where('chat_type', 'client')
            ->groupBy('from');
    }

    public static function chatDetail($id, $userID)
    {
        return UserChat::with('fromUser', 'toUser', 'files')->where(function ($q) use ($id, $userID) {
            $q->Where('user_id', $id)->Where('user_one', $userID)
                ->orwhere(function ($q) use ($id, $userID) {
                    $q->Where('user_one', $id)
                        ->Where('user_id', $userID);
                });
        })->orderBy('created_at', 'asc')->get();
    }

    public static function groupChatDetail($id)
    {
        return UserChat::with(['fromUser', 'toUser', 'files', 'group'])->where('group_id', $id)->orderBy('created_at', 'asc')->get();
    }

    public static function channelChatDetail($id)
    {
        return UserChat::with(['fromUser', 'toUser', 'files', 'channel'])->where('channel_id', $id)->orderBy('created_at', 'asc')->get();
    }

    public static function messageSeenUpdate($loginUser, $toUser, $updateData)
    {

        return UserChat::where('from', $toUser)->where('to', $loginUser)->update($updateData);
    }

    public static function groupMessageSeenUpdate($groupId, $updateData)
    {
        return UserChat::where('group_id', $groupId)->where('chat_type', 'group')->update($updateData);
    }

    public static function channelMessageSeenUpdate($channelId, $updateData)
    {
        return UserChat::where('channel_id', $channelId)->where('chat_type', 'channel')->update($updateData);
    }

    /**
     * Get the latest entry for each group.
     *
     * Each group is composed of one or more columns that make a unique combination to return the
     * last entry for.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $fields A list of fields that's considered as a unique entry by the query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastPerGroup(Builder $query, ?array $fields = null): Builder
    {
        return $query->whereIn('id', function (QueryBuilder $query) use ($fields) {
            return $query->from(static::getTable())
                ->selectRaw('max(`id`)')
                ->groupBy($fields);
        });
    }

    public static function userList()
    {
        return UserChat::with('toUser')->select('users_chat.*')
            ->lastPerGroup(['user_id'])
            ->where('from', user()->id)
            ->orWhere('to', user()->id)
            ->get();
    }

    public static function userListLatest($userID, $term)
    {
        if ($term) {
            $termCnd = 'and (users.name like \'%' . $term . '%\' or u.name like \'%' . $term . '%\')';
        }
        else {
            $termCnd = '';
        }

        return DB::select('
            SELECT t1.id, t1.from, t1.to
            FROM users_chat AS t1
            INNER JOIN users ON users.id = t1.user_one
            INNER JOIN users as u ON u.id = t1.user_id
            INNER JOIN
            (
                SELECT
                    LEAST(user_one, user_id) AS sender,
                    GREATEST(user_one, user_id) AS receiver,
                    MAX(id) AS max_id
                FROM users_chat
                GROUP BY
                    LEAST(user_one, user_id),
                    GREATEST(user_one, user_id)
            ) AS t2
                ON LEAST(t1.user_one, t1.user_id) = t2.sender AND
                GREATEST(t1.user_one, t1.user_id) = t2.receiver AND
                t1.id = t2.max_id
                WHERE (t1.user_one = ? OR t1.user_id = ?) ' . $termCnd . '
                ORDER BY t1.created_at DESC
            ', [$userID, $userID]);
    }

    public static function clientListLatest($userID, $term)
    {
        if ($term) {
            $termCnd = 'and (users.name like \'%' . $term . '%\' or u.name like \'%' . $term . '%\')';
        }
        else {
            $termCnd = '';
        }

        return DB::select('
            SELECT t1.id, t1.from, t1.to
            FROM users_chat AS t1
            INNER JOIN users ON users.id = t1.user_one
            INNER JOIN users as u ON u.id = t1.user_id
            INNER JOIN
            (
                SELECT
                    LEAST(user_one, user_id) AS sender,
                    GREATEST(user_one, user_id) AS receiver,
                    MAX(id) AS max_id
                FROM users_chat
                GROUP BY
                    LEAST(user_one, user_id),
                    GREATEST(user_one, user_id)
            ) AS t2
                ON LEAST(t1.user_one, t1.user_id) = t2.sender AND
                GREATEST(t1.user_one, t1.user_id) = t2.receiver AND
                t1.id = t2.max_id
                WHERE (t1.user_one = ? OR t1.user_id = ?)
                AND t1.chat_type = "client" ' . $termCnd . '
                ORDER BY t1.created_at DESC
            ', [$userID, $userID]);
    }

    public static function groupUserListLatest($userID, $term)
    {

        if ($term) {
            $termCnd = 'and (users.name like \'%' . $term . '%\' or u.name like \'%' . $term . '%\')';
        }
        else {
            $termCnd = '';
        }

        return DB::select('
            SELECT t1.id
            FROM users_chat AS t1
            INNER JOIN users ON users.id = t1.user_one
            INNER JOIN users as u ON u.id = t1.user_id
            INNER JOIN
            (
                SELECT
                    LEAST(user_one, user_id) AS sender,
                    GREATEST(user_one, user_id) AS receiver,
                    MAX(id) AS max_id, group_id
                FROM users_chat
                GROUP BY
                    LEAST(user_one, user_id),
                    GREATEST(user_one, user_id)
            ) AS t2
                ON LEAST(t1.user_one, t1.user_id) = t2.sender AND
                GREATEST(t1.user_one, t1.user_id) = t2.receiver AND
                t1.id = t2.max_id
                WHERE (t1.user_one = ? OR t1.user_id = ?) ' . $termCnd . '
                and t2.group_id IS NOT NULL
                ORDER BY t1.created_at DESC
            ', [$userID, $userID]);
    }

    public static function channelUserListLatest($userID, $term)
    {

        if ($term) {
            $termCnd = 'and (users.name like \'%' . $term . '%\' or u.name like \'%' . $term . '%\')';
        }
        else {
            $termCnd = '';
        }

        return DB::select('
            SELECT t1.id
            FROM users_chat AS t1
            INNER JOIN users ON users.id = t1.user_one
            INNER JOIN users as u ON u.id = t1.user_id
            INNER JOIN
            (
                SELECT
                    LEAST(user_one, user_id) AS sender,
                    GREATEST(user_one, user_id) AS receiver,
                    MAX(id) AS max_id, channel_id
                FROM users_chat
                GROUP BY
                    LEAST(user_one, user_id),
                    GREATEST(user_one, user_id)
            ) AS t2
                ON LEAST(t1.user_one, t1.user_id) = t2.sender AND
                GREATEST(t1.user_one, t1.user_id) = t2.receiver AND
                t1.id = t2.max_id
                WHERE (t1.user_one = ? OR t1.user_id = ?) ' . $termCnd . '
                and t2.channel_id IS NOT NULL
                ORDER BY t1.created_at DESC
            ', [$userID, $userID]);
    }

    public function files()
    {
        return $this->hasMany(UserchatFile::class, 'users_chat_id');
    }

    public static function chatDetailViaId($chatId)
    {
        $userChat = UserChat::findOrFail($chatId);

        if ($userChat) {
            $user1 = $userChat->from;
            $user2 = $userChat->to;

            return UserChat::with('fromUser', 'toUser', 'files')->where(function ($q) use ($user1, $user2) {
                $q->Where('user_id', $user1)->Where('user_one', $user2)
                    ->orwhere(function ($q) use ($user1, $user2) {
                        $q->Where('user_one', $user1)
                            ->Where('user_id', $user2);
                    });
            })->orderBy('created_at', 'asc')->get();
        }

        return null;

    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionProject(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'project_id');
    }

}
