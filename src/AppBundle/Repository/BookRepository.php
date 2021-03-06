<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Author;
use AppBundle\Entity\Category;

/**
 * BookRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BookRepository extends \Doctrine\ORM\EntityRepository
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

    public function findByAuthor(Author $author)
    {
        return $this
            ->createQueryBuilder('books')
            ->where('books.author = :author')
            ->setParameter(':author', $author)
            ->getQuery()
            ->getResult()
            ;
    }
}
