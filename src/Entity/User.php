<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\UserModel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table("users",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"confirmation_token"}),
 *          @ORM\Index(columns={"firstname"}),
 *          @ORM\Index(columns={"lastname"}),
 *          @ORM\Index(columns={"last_login"}),
 *          @ORM\Index(columns={"level"}),
 *          @ORM\Index(columns={"is_alcohol"}),
 *          @ORM\Index(columns={"is_enabled"}),
 *          @ORM\Index(columns={"is_smoking"}),
 *          @ORM\Index(columns={"is_meat_consumption"}),
 *          @ORM\Index(columns={"sex"}),
 *      },
 * )
 *
 * @Gedmo\Tree(type="closure")
 * @Gedmo\TreeClosure(class="UserClosure")
 *
 * @UniqueEntity(
 *     fields="username_canonical",
 *     errorPath="username",
 *     message="Username is already exists"
 * )
 */
class User extends UserModel
{
    use ColumnTrait\Description;

    const SEX_NA     = 0;
    const SEX_MALE   = 1;
    const SEX_FEMALE = 2;
    static protected $sex_values = [
        self::SEX_NA     => 'Не указан',
        self::SEX_MALE   => 'Мужской',
        self::SEX_FEMALE => 'Женский',
    ];

    /**
     * This parameter is optional for the closure strategy
     *
     * @ORM\Column(type="integer", nullable=false, options={"default":1})
     * @Gedmo\TreeLevel
     */
    protected int $level;

    /**
     * Пол
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected int $sex;

    /**
     * Курение
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected ?bool $is_smoking;

    /**
     * Алкаголь
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected ?bool $is_alcohol;

    /**
     * Потребление мяса
     *
     * @ORM\Column(type="boolean", nullable=true)
     * Assert\NotNull(message="This value is not valid.")
     */
    protected ?bool $is_meat_consumption;

    public function __construct()
    {
        parent::__construct();

        $this->level            = 1;
        $this->is_alcohol       = null;
        $this->is_meat_consumption = null;
        $this->is_smoking       = null;
        $this->sex              = self::SEX_NA;
    }

    // Start SEX block of setters and getters

    static public function getSexFormChoices(): array
    {
        return array_flip(self::$sex_values);
    }

    static public function getSexValues(): array
    {
        return self::$sex_values;
    }

    static public function isSexExist($sex): bool
    {
        if (isset(self::$sex_values[$sex])) {
            return true;
        }

        return false;
    }

    public function getSexAsText(): string
    {
        if (isset(self::$sex_values[$this->sex])) {
            return self::$sex_values[$this->sex];
        }

        return 'N/A';
    }

    public function getSex(): int
    {
        return $this->sex;
    }

    public function setSex(?int $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    // __End SEX block of setters and getters

    public function getIsSmoking(): ?bool
    {
        return $this->is_smoking;
    }

    public function setIsSmoking(?bool $is_smoking): self
    {
        $this->is_smoking = $is_smoking;

        return $this;
    }

    public function getIsAlcohol(): ?bool
    {
        return $this->is_alcohol;
    }

    public function setIsAlcohol(?bool $is_alcohol): self
    {
        $this->is_alcohol = $is_alcohol;

        return $this;
    }

    public function getIsMeatConsumption(): ?bool
    {
        return $this->is_meat_consumption;
    }

    public function setIsMeatConsumption(?bool $is_meat_consumption): self
    {
        $this->is_meat_consumption = $is_meat_consumption;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }
}
