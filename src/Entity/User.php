<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
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
class User implements UserInterface
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\IsEnabled;

    const SEX_FEMALE = 0;
    const SEX_MALE   = 1;
    static protected $sex_values = [
        self::SEX_MALE   => 'Мужской',
        self::SEX_FEMALE => 'Женский',
    ];

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, unique=true)
     * @Assert\NotNull(message="This value is not valid.")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190)
     * @Assert\Length(min = 6, minMessage = "Password length must be at least {{ limit }} characters long")
     */
    private $password;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * Имя
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $firstname;

    /**
     * Фамилия
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $lastname;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_login;

    /**
     * Пол
     *
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":1})
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $sex;

    /**
     * Курение
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $is_smoking;

    /**
     * Алкаголь
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $is_alcohol;

    /**
     * Потребление мяса
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $is_meat_consumption;

    /**
     * @var float|null
     *
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    protected $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     */
    protected $longitude;

    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true, unique=true)
     */
    protected $telegram_user_id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $telegram_username;

    /**
     * Пригласивший пользователь
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="invited_users")
     */
    protected $invited_by_user;

    /**
     * Приглашенные пользователи
     *
     * @var User[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="invited_by_user", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $invited_users;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->created_at       = new \DateTime();
        $this->invited_users    = new ArrayCollection();
        $this->is_enabled       = true;
        $this->is_alcohol       = false;
        $this->is_meat_consumption = false;
        $this->is_smoking       = false;
        $this->has_children     = false;
        $this->password         = '';
        $this->sex              = 1;
        $this->roles            = [];
        $this->username         = '';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->getFirstname()) and empty($this->getLastname())) {
            return $this->getUsername();
        }

        return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);

    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
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

    /**
     * @return bool
     */
    public function isIsSmoking(): bool
    {
        return $this->is_smoking;
    }

    /**
     * @param bool $is_smoking
     *
     * @return $this
     */
    public function setIsSmoking(?bool $is_smoking): self
    {
        $this->is_smoking = $is_smoking;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsAlcohol(): bool
    {
        return $this->is_alcohol;
    }

    /**
     * @param bool $is_alcohol
     *
     * @return $this
     */
    public function setIsAlcohol(?bool $is_alcohol): self
    {
        $this->is_alcohol = $is_alcohol;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsMeatConsumption(): bool
    {
        return $this->is_meat_consumption;
    }

    /**
     * @param bool $is_meat_consumption
     *
     * @return $this
     */
    public function setIsMeatConsumption(?bool $is_meat_consumption): self
    {
        $this->is_meat_consumption = $is_meat_consumption;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     *
     * @return $this
     */
    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     *
     * @return $this
     */
    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude ? (float) $this->latitude : null;
    }

    /**
     * @param float|null $latitude
     *
     * @return $this
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude ? (float) $this->longitude : null;
    }

    /**
     * @param float|null $longitude
     *
     * @return $this
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return User
     */
    public function getInvitedByUser(): ?self
    {
        return $this->invited_by_user;
    }

    /**
     * @param User $invited_by_user
     *
     * @return $this
     */
    public function setInvitedByUser(User $invited_by_user): self
    {
        $this->invited_by_user = $invited_by_user;

        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getInvitedUsers()
    {
        return $this->invited_users;
    }

    /**
     * @param User[]|ArrayCollection $invited_users
     *
     * @return $this
     */
    public function setInvitedUsers($invited_users): self
    {
        $this->invited_users = $invited_users;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    /**
     * @param \DateTime|null $last_login
     *
     * @return $this
     */
    public function setLastLogin(?\DateTime $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTelegramUserId(): ?int
    {
        return $this->telegram_user_id;
    }

    /**
     * @param int|null $telegram_user_id
     *
     * @return $this
     */
    public function setTelegramUserId(?int $telegram_user_id): self
    {
        $this->telegram_user_id = $telegram_user_id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTelegramUsername(): ?string
    {
        return $this->telegram_username;
    }

    /**
     * @param string|null $telegram_username
     *
     * @return $this
     */
    public function setTelegramUsername(?string $telegram_username): self
    {
        $this->telegram_username = $telegram_username;

        return $this;
    }
}
