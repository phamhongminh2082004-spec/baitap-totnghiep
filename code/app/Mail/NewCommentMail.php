<?php

/**
 * EMAIL báo cho tác giả khi có bình luận mới. implements ShouldQueue = gửi qua hàng đợi (nhóm E).
 */

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    // Tiêu đề email.
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bài viết "'.$this->comment->post->title.'" có bình luận mới',
        );
    }

    // Nội dung email lấy từ view resources/views/emails/new-comment.blade.php.
    public function content(): Content
    {
        return new Content(view: 'emails.new-comment');
    }
}
