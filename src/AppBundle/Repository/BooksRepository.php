<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;

/**
 * BooksRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BooksRepository extends \Doctrine\ORM\EntityRepository
{

    public function findByCategory(Category $category)
    {
        return $this
            ->createQueryBuilder('books')
            ->where('books.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getResult()
            ;
    }
}
