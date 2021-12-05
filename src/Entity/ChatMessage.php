<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatMessageRepository")
 * @ORM\Table(name="chats_messages",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"author_id", "recipient_id"}),
 *      },
 * )
 */
class ChatMessage
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;

    /**
     * @ORM\Column(type="text")
     */
    protected string $text;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    protected bool $is_recipient_read = false;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $author;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $recipient;

    public function __construct(?User $author = null, ?User $recipient = null, ?string $text = null)
    {
        $this->created_at = new \DateTime();

        if ($author) {
            $this->author = $author;
        }

        if ($recipient) {
            $this->recipient = $recipient;
        }

        if ($text) {
            $this->text = $text;
        }
    }

    public function __toString(): string
    {
        return $this->text;
    }

    public function getAnnounce(): string
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], ' ', $this->text);

        $a = strip_tags($text);

        if (mb_strlen($a, 'utf-8') > 120) {
            $dotted = '...';
        } else {
            $dotted = '';
        }

        return mb_substr($a, 0, 120, 'utf-8') . $dotted;
    }

    public function isRecipientRead(): bool
    {
        return $this->is_recipient_read;
    }

    public function getIsRecipientRead(): bool
    {
        return $this->is_recipient_read;
    }

    public function setIsRecipientRead(bool $is_recipient_read): self
    {
        $this->is_recipient_read = $is_recipient_read;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }
}
