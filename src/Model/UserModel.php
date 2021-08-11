<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Invite;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserModel implements UserInterface
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\IsEnabled;

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Column(type="string", length=64, unique=true, nullable=true)
     */
    protected ?string $api_token;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected string $username;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    protected string $username_canonical;

    /**
     * @ORM\Column(type="string", length=190)
     * @Assert\Length(min = 6, minMessage = "Password length must be at least {{ limit }} characters long", allowEmptyString=false)
     */
    protected string $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    protected ?string $plain_password;

    /**
     * @ORM\Column(type="array")
     */
    protected array $roles;

    /**
     * Имя
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected ?string $firstname;

    /**
     * Фамилия
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected ?string $lastname;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $last_login;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    protected ?float $latitude;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     */
    protected ?float $longitude;

    /**
     * @ORM\Column(type="integer", nullable=true, unique=true)
     */
    protected ?int $telegram_user_id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected ?string $telegram_username;

    /**
     * Хеш для восстановления пароля
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected ?string $confirmation_token;

    /**
     * Код для подтверждения сброса пароля
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $reset_password_code;

    /**
     * Дата создания запроса на восстановление пароля
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $password_requested_at;

    /**
     * Пригласивший пользователь
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="invited_users")
     * @Gedmo\TreeParent
     */
    protected ?User $invited_by_user;

    /**
     * Приглашение
     *
     * @ORM\OneToOne(targetEntity="Invite", cascade={"persist"})
     */
    protected ?Invite $invite;

    /**
     * Приглашенные пользователи
     *
     * @var User[]|Collection
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="invited_by_user", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created_at" = "DESC"})
     */
    protected Collection $invited_users;

    public function __construct()
    {
        $this->created_at       = new \DateTime();
        $this->invited_users    = new ArrayCollection();
        $this->is_enabled       = true;
        $this->password         = '';
        $this->roles            = [];
        $this->username         = '';
    }

    public function __toString(): string
    {
        if (empty($this->getFirstname()) and empty($this->getLastname())) {
            return $this->getUsername();
        }

        return $this->getFirstname().' '.$this->getLastname();
    }

    static public function canonicalize(string $string): ?string
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->is_enabled, $this->username_canonical, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->is_enabled, $this->username_canonical, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        $this->username_canonical = self::canonicalize($this->username);

        return $this;
    }

    public function getUsernameCanonical(): string
    {
        return $this->username_canonical;
    }

    public function setUsernameCanonical(string $username_canonical): self
    {
        $this->username_canonical = $username_canonical;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role): self
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Returns the roles or permissions granted to the user for security.
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude ? (float) $this->latitude : null;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude ? (float) $this->longitude : null;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getInvitedByUser(): ?self
    {
        return $this->invited_by_user;
    }

    /**
     * @param User|UserModel|UserInterface $invited_by_user
     */
    public function setInvitedByUser(User $invited_by_user): self
    {
        $this->invited_by_user = $invited_by_user;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getInvitedUsers(): Collection
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

    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getTelegramUserId(): ?int
    {
        return $this->telegram_user_id;
    }

    public function setTelegramUserId(?int $telegram_user_id): self
    {
        $this->telegram_user_id = $telegram_user_id;

        return $this;
    }

    public function getTelegramUsername(): ?string
    {
        return $this->telegram_username;
    }

    public function setTelegramUsername(?string $telegram_username): self
    {
        $this->telegram_username = $telegram_username;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->api_token;
    }

    public function setApiToken(?string $api_token): self
    {
        $this->api_token = $api_token;

        return $this;
    }

    public function getInvite(): ?Invite
    {
        return $this->invite;
    }

    public function setInvite(Invite $invite): self
    {
        $invite->setIsUsed(true);

        $this->invite = $invite;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    /**
     * @param string|null $confirmation_token
     *
     * @return $this
     */
    public function setConfirmationToken(?string $confirmation_token): self
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->password_requested_at;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $password_requested_at): self
    {
        $this->password_requested_at = $password_requested_at;

        return $this;
    }

    public function getResetPasswordCode(): ?int
    {
        return $this->reset_password_code;
    }

    public function setResetPasswordCode(?int $reset_password_code): self
    {
        $this->reset_password_code = $reset_password_code;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plain_password;
    }

    public function setPlainPassword(?string $plain_password): self
    {
        $this->password = $plain_password;
        $this->plain_password = $plain_password;

        return $this;
    }
}
