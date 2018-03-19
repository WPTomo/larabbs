<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    public function creating(Reply $reply)
    {
        $reply->content = clean($reply->content, 'user_topic_body');
    }

    public function created(Reply $reply)
    {
        $topic = $reply->topic;
        // 话题回复数 +1
        $topic->increment('reply_count', 1);
        // 更新最后回复的用户ID，注意此处避免使用ORM
        \DB::table('topics')->where('id', $reply->topic_id)->update(['last_reply_user_id' => $reply->user_id]);

        // 通知该话题的作者有新的回复
        $topic->user->notify(new TopicReplied($reply));
    }

    public function deleted(Reply $reply)
    {
        $reply->topic->decrement('reply_count', 1);
    }
}
