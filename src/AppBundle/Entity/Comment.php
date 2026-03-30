<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'comment')]
#[ORM\Entity(repositoryClass: 'AppBundle\Repository\CommentRepository')]
class Comment
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'AppBundle\Entity\Estate', inversedBy: 'comments', cascade: ['persist'])]
    private $estate;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'comment.blank')]
    #[Assert\Length(
        min: 5,
        minMessage: 'comment.too_short',
        max: 10000,
        maxMessage: 'comment.too_long'
    )]
    private $content;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\Column]
    private $createdBy;

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    protected $enabled;

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (Boolean)$boolean;

        return $this;
    }

    public function isAuthor(User $user)
    {
        return $user->getUserIdentifier() == $this->getCreatedBy();
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setEstate(\AppBundle\Entity\Estate $estate = null)
    {
        $this->estate = $estate;

        return $this;
    }

    public function getEstate()
    {
        return $this->estate;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
