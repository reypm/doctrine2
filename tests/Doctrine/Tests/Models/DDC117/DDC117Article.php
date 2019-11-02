<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\DDC117;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 */
class DDC117Article
{
    /** @ORM\Id @ORM\Column(type="integer", name="article_id") @ORM\GeneratedValue */
    private $id;

    /** @ORM\Column */
    private $title;

    /** @ORM\OneToMany(targetEntity=DDC117Reference::class, mappedBy="source", cascade={"remove"}) */
    private $references;

    /** @ORM\OneToOne(targetEntity=DDC117ArticleDetails::class, mappedBy="article", cascade={"persist", "remove"}) */
    private $details;

    /** @ORM\OneToMany(targetEntity=DDC117Translation::class, mappedBy="article", cascade={"persist", "remove"}) */
    private $translations;

    /** @ORM\OneToMany(targetEntity=DDC117Link::class, mappedBy="source", indexBy="target_id", cascade={"persist", "remove"}) */
    private $links;

    public function __construct($title)
    {
        $this->title        = $title;
        $this->references   = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function id()
    {
        return $this->id;
    }

    public function addReference($reference)
    {
        $this->references[] = $reference;
    }

    public function references()
    {
        return $this->references;
    }

    public function addTranslation($language, $title)
    {
        $this->translations[] = new DDC117Translation($this, $language, $title);
    }

    public function getText()
    {
        return $this->details->getText();
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function resetText()
    {
        $this->details = null;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
