<?php

namespace App\Entity;

use App\Dto\Item as ItemDto;
use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item
{
    const FILE_TYPE_DOCUMENT = 1;
    const FILE_TYPE_VIDEO = 2;
    const FILE_TYPE_PHOTO = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="text")
     */
    private string $text;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $fileId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $fileType;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="items")
     */
    private ?Category $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $aboutUrl;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $visible;

    /**
     * @ORM\OneToOne(targetEntity=Promocode::class, mappedBy="item", cascade={"persist", "remove"})
     */
    private ?Promocode $promocode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): self
    {
        $this->fileId = $fileId;

        return $this;
    }

    public function getFileType(): ?int
    {
        return $this->fileType;
    }

    public function setFileType(int $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAboutUrl(): ?string
    {
        return $this->aboutUrl;
    }

    public function setAboutUrl(string $aboutUrl): self
    {
        $this->aboutUrl = $aboutUrl;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getPromocode(): ?Promocode
    {
        return $this->promocode;
    }

    public function setPromocode(?Promocode $promocode): self
    {
        $this->promocode = $promocode;

        // set (or unset) the owning side of the relation if necessary
        $newItem = null === $promocode ? null : $this;
        if ($promocode->getItem() !== $newItem) {
            $promocode->setItem($newItem);
        }

        return $this;
    }

    public static function create(ItemDto $dto): self
    {
        return (new Item())
            ->setName($dto->getName())
            ->setCategory($dto->getCategory())
            ->setText($dto->getText())
            ->setFileId($dto->getFileId())
            ->setFileType($dto->getFileType())
            ->setAboutUrl($dto->getAboutUrl())
            ->setVisible($dto->isVisible())
        ;
    }
}
