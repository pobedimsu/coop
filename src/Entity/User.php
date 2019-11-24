<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\UserModel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table("users",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"firstname"}),
 *          @ORM\Index(columns={"lastname"}),
 *          @ORM\Index(columns={"last_login"}),
 *          @ORM\Index(columns={"is_alcohol"}),
 *          @ORM\Index(columns={"is_enabled"}),
 *          @ORM\Index(columns={"is_smoking"}),
 *          @ORM\Index(columns={"is_meat_consumption"}),
 *          @ORM\Index(columns={"sex"}),
 *      },
 * )
 *
 * @UniqueEntity(fields="username", message="Username is already exists")
 */
class User extends UserModel
{
    const SEX_NA     = 0;
    const SEX_MALE   = 1;
    const SEX_FEMALE = 2;
    static protected $sex_values = [
        self::SEX_NA     => 'Не указан',
        self::SEX_MALE   => 'Мужской',
        self::SEX_FEMALE => 'Женский',
    ];

    /**
     * Пол
     *
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $sex;

    /**
     * Курение
     *
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected $is_smoking;

    /**
     * Алкаголь
     *
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected $is_alcohol;

    /**
     * Потребление мяса
     *
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected $is_meat_consumption;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->is_alcohol       = null;
        $this->is_meat_consumption = null;
        $this->is_smoking       = null;
        $this->sex              = self::SEX_NA;
    }

    // Start SEX block of setters and getters

    /**
     * @return array
     */
    static public function getSexFormChoices(): array
    {
        return array_flip(self::$sex_values);
    }

    /**
     * @return array
     */
    static public function getSexValues(): array
    {
        return self::$sex_values;
    }

    /**
     * @return bool
     */
    static public function isSexExist($sex): bool
    {
        if (isset(self::$sex_values[$sex])) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSexAsText(): string
    {
        if (isset(self::$sex_values[$this->sex])) {
            return self::$sex_values[$this->sex];
        }

        return 'N/A';
    }

    /**
     * @return int
     */
    public function getSex(): int
    {
        return $this->sex;
    }

    /**
     * @param int $sex
     *
     * @return $this
     */
    public function setSex(?int $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    // __End SEX block of setters and getters

    /**
     * @return bool|null
     */
    public function getIsSmoking(): ?bool
    {
        return $this->is_smoking;
    }

    /**
     * @param bool|null $is_smoking
     *
     * @return $this
     */
    public function setIsSmoking(?bool $is_smoking): self
    {
        $this->is_smoking = $is_smoking;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsAlcohol(): ?bool
    {
        return $this->is_alcohol;
    }

    /**
     * @param bool|null $is_alcohol
     *
     * @return $this
     */
    public function setIsAlcohol(?bool $is_alcohol): self
    {
        $this->is_alcohol = $is_alcohol;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsMeatConsumption(): ?bool
    {
        return $this->is_meat_consumption;
    }

    /**
     * @param bool|null $is_meat_consumption
     *
     * @return $this
     */
    public function setIsMeatConsumption(?bool $is_meat_consumption): self
    {
        $this->is_meat_consumption = $is_meat_consumption;

        return $this;
    }
}
