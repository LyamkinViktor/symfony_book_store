<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @UniqueEntity(fields={"email"}, message="user already exists!")
 */
class User implements UserInterface
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="fill this field")
     * @Assert\Email()
     * @ORM\Column(type="string", unique=true)
     */
    private $email;

    /**
     * @Assert\NotBlank(message="fill this field")
     * @ORM\Column(type="string")
     */
    private $password;

    private $plainPassword;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ['ROLE_USER'];



    public function getUsername()
    {
        return $this->email;
    }




    public function getEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }


    public function getSalt()
    {
    }


    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }


    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;
    }


    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }


}