<?php


namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 */
class UserRepository extends EntityRepository
{

    public function findByEmail($email)
    {
        return $this
            ->createQueryBuilder('user')
            ->where('user.email = :email')
            ->setParameter(':email', $email)
            ->getQuery()
            ->getResult()
            ;
    }

}
