<?php
namespace RestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Vich\Uploadable
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="RestBundle\Entity\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Assert\NotBlank()
     */
    protected $full_name;

    /**
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_active;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_created;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    protected $role;

    /**
     * @ORM\OneToOne(targetEntity="RestBundle\Entity\Profile", mappedBy="user", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $profile;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $temporary_token;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isRemoved;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $timezone;

    /**
     * @ORM\ManyToOne(targetEntity="RestBundle\Entity\User", inversedBy="users")
     */
    protected $admin;

    /**
     * @ORM\OneToMany(targetEntity="RestBundle\Entity\User", mappedBy="admin")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="RestBundle\Entity\UserPackages", mappedBy="user", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    protected $packages;

    /**
     * @ORM\OneToMany(targetEntity="RestBundle\Entity\Job", mappedBy="user", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     */
    protected $jobs;

    /**
     * @ORM\ManyToMany(targetEntity="RestBundle\Entity\Blog")
     * @ORM\JoinTable(name="likes",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id")}
     *      )
     * @var Blog[]
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="RestBundle\Entity\Blog", mappedBy="admin")
     */
    protected $blogs;

    public function __construct()
    {
        $this->is_active = true;
        $this->isRemoved = false;
        $this->token = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->date_created = new \DateTime();
        $this->packages = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $role
     * @return $this
     */
    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        if ($this->getProfile() === null && !in_array('ROLE_ADMIN', $this->roles, true)) {
            $this->roles[] = 'ROLE_ADMIN';
        }

        return $this;
    }

    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @param $role
     */
    public function removeRole($role)
    {
        $role = strtoupper($role);

        if ($role !== 'ROLE_ADMIN' && $role !== 'ROLE_SUPER_ADMIN' && $this->hasRole($role)) {
            $key = array_search($role, $this->roles, true);
            unset($this->roles[$key]);
        }
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->username,
            $this->password,
            $this->full_name,
            $this->roles,
            $this->token
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->username,
            $this->password,
            $this->roles,
            $this->is_active
            ) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_active;
    }

    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * @param string $full_name
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return mixed
     */
    public function getTemporaryToken()
    {
        return $this->temporary_token;
    }

    /**
     * @param mixed $temporary_token
     */
    public function setTemporaryToken($temporary_token)
    {
        $this->temporary_token = $temporary_token;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * @return mixed
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param mixed $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return mixed
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param $package
     */
    public function addPackages($package)
    {
        $this->packages->add($package);
    }

    /**
     * @return mixed
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param mixed $jobs
     */
    public function setJobs($jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN') || $this->hasRole('ROLE_ADMIN');
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users->toArray();
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @param mixed $user
     */
    public function addUser($user)
    {
        $this->users->add($user);
    }

    /**
     * @return array
     */
    public function getLikes()
    {
        $data = [];

        foreach ($this->likes as $like) {
            $like->setLiked(true);

            $data[] = $like;
        }

        return $data;
    }

    /**
     * @param Blog $like
     */
    public function addLike(Blog $like)
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
        }
    }

    /**
     * @param Blog $like
     */
    public function removeLike(Blog $like)
    {
        $this->likes->removeElement($like);
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * remove
     *
     * @return self
     */
    public function remove()
    {
        $this->isRemoved = true;

        return $this;
    }

    /**
     * release
     *
     * @return self
     */
    public function release()
    {
        $this->isRemoved = false;

        return $this;
    }

    /**
     * getIsRemoved
     *
     * @return bool
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * setTimezone
     *
     * @param string $timezone
     *
     * @return self
     */
    public function setTimezone($timezone)
    {
        if (!preg_match("/GMT[+|-][0-9][0-2]?/", $timezone)) {
            return $this;
        }

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * getTimezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * getBlogs
     *
     * @return mixed
     */
    public function getBlogs()
    {
        return $this->blogs->toArray();
    }
}