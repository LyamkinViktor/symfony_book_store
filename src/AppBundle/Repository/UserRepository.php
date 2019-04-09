<?php


namespace AppBundle\Repository;


/**
 * UserRepository
 *
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
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
