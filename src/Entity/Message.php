<?php
namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\MessageRepository;
use App\Controller\MessageController; // ✅ Correction du namespace
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    paginationItemsPerPage: 10,
    operations: [
        new GetCollection(normalizationContext: ['groups' => 'message:list']),
        new Post(),
        new Get(normalizationContext: ['groups' => 'message:item']),
        new Put(),
        new Patch(),
        new Delete(),
        new GetCollection( 
            uriTemplate: '/messages/parent/{parentId}',
            controller: MessageController::class . '::getMessagesByParent',
            normalizationContext: ['groups' => 'message:list']
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'ASC', 'titre' => 'DESC'])]
#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:list', 'message:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['message:list', 'message:item'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['message:list', 'message:item'])]
    private ?\DateTimeInterface $datePoste = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['message:list', 'message:item'])]
    private ?string $contenu = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:list', 'message:item'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['message:list', 'message:item'])]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[Groups(['message:list', 'message:item'])]  // ✅ Ajout pour voir les réponses directement dans l’API
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDatePoste(): ?\DateTimeInterface
    {
        return $this->datePoste;
    }

    public function setDatePoste(\DateTimeInterface $datePoste): static
    {
        $this->datePoste = $datePoste;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addReponse(self $reponse): static
    {
        if (!$this->messages->contains($reponse)) {
            $this->messages->add($reponse);
            $reponse->setParent($this);
        }

        return $this;
    }

    public function removeReponse(self $reponse): static
    {
        if ($this->messages->removeElement($reponse)) {
            if ($reponse->getParent() === $this) {
                $reponse->setParent(null);
            }
        }

        return $this;
    }
}
