<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2000)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $priceHT = null;

    #[ORM\Column]
    private ?bool $promoActive = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(nullable: true)]
    private ?float $promoPercent = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Tax $taxeId = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\ManyToMany(targetEntity: Image::class, mappedBy: 'produits', cascade: ['persist'])]
    private Collection $images;

    #[ORM\Column(nullable: true)]
    private ?int $delay = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    private ?bool $noStockProduct = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\ManyToMany(targetEntity: Booking::class, mappedBy: 'products')]
    private Collection $bookings;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPriceHT(): ?float
    {
        return $this->priceHT;
    }

    public function setPriceHT(float $priceHT): static
    {
        $this->priceHT = $priceHT;

        return $this;
    }

    public function isPromoActive(): ?bool
    {
        return $this->promoActive;
    }

    public function setPromoActive(bool $promoActive): static
    {
        $this->promoActive = $promoActive;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getPromoPercent(): ?float
    {
        return $this->promoPercent;
    }

    public function setPromoPercent(?float $promoPercent): static
    {
        $this->promoPercent = $promoPercent;

        return $this;
    }

    public function getTaxeId(): ?Tax
    {
        return $this->taxeId;
    }

    public function setTaxeId(?Tax $taxeId): static
    {
        $this->taxeId = $taxeId;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->addProduit($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            $image->removeProduit($this); // Si relation inverse existe dans l'entité Image
        }

        return $this;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    public function setDelay(?int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    public function getPrixTTC(): float
    {
        if ($this->taxeId) {
            $prixTTC = $this->priceHT * (1 + ($this->taxeId->getValue() ?? 0) / 100);

            if($this->promoActive && $this->promoPercent && $this->promoPercent !== 0){
                $prixTTC -= ($prixTTC * ($this->promoPercent /100));
            }

            return $prixTTC;
        }

        return 0.0;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function isNoStockProduct(): ?bool
    {
        return $this->noStockProduct;
    }

    public function setNoStockProduct(bool $noStockProduct): static
    {
        $this->noStockProduct = $noStockProduct;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->addProduct($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            $booking->removeProduct($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
