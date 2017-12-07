<?php
namespace RestBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RestBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * Class UserRepository
 * @package RestBundle\Entity\Repository
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * @param string $username
     * @return mixed
     */
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.token = :token')
            ->andWhere('u.isRemoved = false')
            ->setParameter('username', $username)
            ->setParameter('token', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return mixed
     */
    public function getAdmins()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.is_active = true')
            ->orderBy('u.date_created')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getAdminsForAssignToUser()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.is_active = true')
            ->andWhere('u.role != :role')
            ->orderBy('u.date_created')
            ->setParameters([
                'roles' => '%ROLE_ADMIN%',
                'role' => 'ROLE_MANAGER_BLOG',
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $admin
     * @param $page
     * @param int $limit
     * @return mixed
     */
    public function getUsers(User $admin, $page, $limit = 15)
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.profile IS NOT NULL')
            ->andWhere('u.is_active = true');

        if (!$admin->isSuperAdmin()) {
            $query->andWhere('u.admin = :admin')
                ->setParameter('admin', $admin);
        }

        $query
            ->orderBy('u.date_created', 'DESC')
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return ['count' => ceil(count($paginator) / $limit), 'users' => $paginator->getQuery()->getResult()];
    }

    /**
     * @return User
     */
    public function getSuperAdmin()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}