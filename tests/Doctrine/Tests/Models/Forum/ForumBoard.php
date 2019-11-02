<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Forum;

use Doctrine\ORM\Annotation as ORM;

/**
 * Represents a board in a forum.
 *
 * @ORM\Entity
 * @ORM\Table(name="forum_boards")
 */
class ForumBoard
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;
    /** @ORM\Column(type="integer") */
    public $position;
    /**
     * @ORM\ManyToOne(targetEntity=ForumCategory::class, inversedBy="boards")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    public $category;
}
