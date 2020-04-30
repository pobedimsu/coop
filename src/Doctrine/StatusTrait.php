<?php

declare(strict_types=1);

namespace App\Doctrine;

trait StatusTrait
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     */
    protected $status;

    static public function getStatusFormChoices(): array
    {
        return array_flip(self::$status_values);
    }

    static public function getStatusValues(): array
    {
        return self::$status_values;
    }

    static public function isStatusExist($status): bool
    {
        if (isset(self::$status_values[$status])) {
            return true;
        }

        return false;
    }

    public function getStatusAsText(): string
    {
        if (isset(self::$status_values[$this->status])) {
            return self::$status_values[$this->status];
        }

        return 'N/A';
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
