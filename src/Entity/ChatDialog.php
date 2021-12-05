<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatDialogRepository")
 * @ORM\Table(name="chats_dialogs",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"last_message_date"}),
 *          @ORM\Index(columns={"owner_id", "recipient_id"}),
 *      },
 * )
 */
class ChatDialog
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected \DateTime $last_message_date;

    /**
     * @ORM\Column(type="text")
     */
    protected string $last_message_text;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    protected int $unread_owner_count = 0;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    protected int $unread_recipient_count = 0;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $owner;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $recipient;

    public function __construct(?User $owner = null, ?User $recipient = null)
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();

        if ($owner) {
            $this->owner = $owner;
        }

        if ($recipient) {
            $this->recipient = $recipient;
        }
    }

    public function __toString(): string
    {
        return $this->last_message_text;
    }

    public function getAnnounce(): string
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], ' ', $this->last_message_text);

        $a = strip_tags($text);

        if (mb_strlen($a, 'utf-8') > 120) {
            $dotted = '...';
        } else {
            $dotted = '';
        }

        return mb_substr($a, 0, 120, 'utf-8') . $dotted;
    }

    public function incrementUnreadOwnerCount(): self
    {
        $this->unread_owner_count++;

        return $this;
    }

    public function incrementUnreadRecipientCount(): self
    {
        $this->unread_recipient_count++;

        return $this;
    }

    public function getLastMessageText(): string
    {
        return $this->last_message_text;
    }

    public function setLastMessageText(string $last_message_text): self
    {
        $this->last_message_text = $last_message_text;
        $this->last_message_date = new \DateTime();

        return $this;
    }

    public function getLastMessageDate(): \DateTime
    {
        return $this->last_message_date;
    }

    public function setLastMessageDate(\DateTime $last_message_date): self
    {
        $this->last_message_date = $last_message_date;

        return $this;
    }

    public function getUnreadOwnerCount(): int
    {
        return $this->unread_owner_count;
    }

    public function setUnreadOwnerCount(int $unread_owner_count): self
    {
        $this->unread_owner_count = $unread_owner_count;

        return $this;
    }

    public function getUnreadRecipientCount(): int
    {
        return $this->unread_recipient_count;
    }

    public function setUnreadRecipientCount(int $unread_recipient_count): self
    {
        $this->unread_recipient_count = $unread_recipient_count;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

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
