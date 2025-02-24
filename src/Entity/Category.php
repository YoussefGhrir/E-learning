<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tag = null;

    #[ORM\ManyToMany(targetEntity: Poste::class, mappedBy: 'categories')]
    private Collection $postes;

    public function __construct()
    {
        $this->postes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Collection<int, Poste>
     */
    public function getPostes(): Collection
    {
        return $this->postes;
    }

    public function addPoste(Poste $poste): self
    {
        if (!$this->postes->contains($poste)) {
            $this->postes[] = $poste;
            $poste->addCategory($this); // Mettre à jour l'entité inverse
        }

        return $this;
    }

    public function removePoste(Poste $poste): self
    {
        if ($this->postes->removeElement($poste)) {
            $poste->removeCategory($this); // Mettre à jour l'entité inverse
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->tag ?? '';
    }
}