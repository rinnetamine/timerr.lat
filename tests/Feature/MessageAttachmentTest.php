<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\MessageFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessageAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_message_attachment(): void
    {
        Storage::fake('public');

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $file = UploadedFile::fake()->create('notes.pdf', 512, 'application/pdf');

        $response = $this
            ->actingAs($sender)
            ->post(route('messages.store'), [
                'recipient_id' => $recipient->id,
                'body' => 'Here is the file you asked for.',
                'files' => [$file],
            ]);

        $response->assertRedirect(route('messages.conversation', ['user' => $recipient->id]));

        $message = Message::first();

        $this->assertNotNull($message);
        $this->assertSame($sender->id, $message->sender_id);
        $this->assertSame($recipient->id, $message->recipient_id);
        $this->assertSame('Here is the file you asked for.', $message->body);

        $attachment = MessageFile::first();

        $this->assertNotNull($attachment);
        $this->assertSame($message->id, $attachment->message_id);
        $this->assertSame('notes.pdf', $attachment->file_name);

        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_it_allows_sending_a_file_without_a_text_body(): void
    {
        Storage::fake('public');

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $file = UploadedFile::fake()->create('brief.txt', 8, 'text/plain');

        $response = $this
            ->actingAs($sender)
            ->post(route('messages.store'), [
                'recipient_id' => $recipient->id,
                'body' => '',
                'files' => [$file],
            ]);

        $response->assertRedirect(route('messages.conversation', ['user' => $recipient->id]));

        $this->assertSame(1, Message::count());
        $this->assertSame(1, MessageFile::count());
    }
}
