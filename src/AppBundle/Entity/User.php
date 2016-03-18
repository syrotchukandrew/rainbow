<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var array
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Estate")
     * @ORM\JoinTable(name="users_estates",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="estate_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $estates;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    protected $googleId;

    /**
     * @ORM\Column(name="vkontakte_id", type="string", length=255, nullable=true)
     */
    protected $vkontakteId;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->estates = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Add estates
     *
     * @param \AppBundle\Entity\Estate $estates
     * @return User
     */
    public function addEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates[] = $estates;

        return $this;
    }

    /**
     * Remove estates
     *
     * @param \AppBundle\Entity\Estate $estates
     */
    public function removeEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates->removeElement($estates);
    }

    /**
     * Get estates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEstates()
    {
        return $this->estates;
    }

    public function hasEstate(Estate $estate)
    {
        $estates = $this->getEstates();
        $has = false;
        foreach ($estates as $item) {
            if ($item->getId() === $estate->getId()) {
                $has = true;
            }
        }
        return $has;
    }

    /**
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $vkontakteId
     * @return User
     */
    public function setVkontakteId($vkontakteId)
    {
        $this->vkontakteId = $vkontakteId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVkontakteId()
    {
        return $this->vkontakteId;
    }

    /**
     * @param string $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }
}
