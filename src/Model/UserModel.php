<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserModel implements UserInterface
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\IsEnabled;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, unique=true)
     * @Assert\NotNull(message="This value is not valid.")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190)
     * @Assert\Length(min = 6, minMessage = "Password length must be at least {{ limit }} characters long")
     */
    protected $password;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $roles;

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
     * UserModel constructor.
     */
    public function __construct()
    {
        $this->created_at       = new \DateTime();
        $this->invited_users    = new ArrayCollection();
        $this->is_enabled       = true;
        $this->password         = '';
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
