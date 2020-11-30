<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_categories")
 * @HasLifecycleCallbacks
 **/
class Category
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     * @Column(type="string", unique=true)
     */
    protected $name;
    /**
     * @Column(type="datetime", name="created_at")
     */
    protected $createdAt;
    /**
     * @Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;
    /**
     * @ManyToMany(targetEntity="Content")
     * @JoinTable(
     *     name="lc_category_contents",
     *     joinColumns={
     *         @JoinColumn(name="category_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @JoinColumn(name="content_id", referencedColumnName="id")
     *     }
     * )
     */
    private $contents;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    /**
     * @PrePersist
     */
    public function saveTimestamp()
    {
        if (empty($this->get()['updated_at'])) {
            $this->set(array('updated_at' => new DateTime('now')));
        }

        if (empty($this->get()['created_at'])) {
            $this->set(array('created_at' => new DateTime('now')));
        }
    }

    /**
     * @PreUpdate
     */
    public function updateTimestamp()
    {
        $this->set(array('updated_at' => new DateTime('now')));

        if (empty($this->get()['created_at'])) {
            $this->set(array('created_at' => new DateTime('now')));
        }
    }

    /**
     * @param $values
     * @return $this
     */
    public function set($values)
    {
        foreach ($values as $key => $value) {
            switch ($key) {

                case 'name':
                    $this->name = $value;
                    break;
                case 'updated_at':
                    if (is_string($value)) {
                        $this->updatedAt = new DateTime($value);
                    } else {
                        $this->updatedAt = $value;
                    }

                    break;
                case 'created_at':
                    if (is_string($value)) {
                        $this->createdAt = new DateTime($value);
                    } else {
                        $this->createdAt = $value;
                    }

            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        );
    }

    public function hasManyContents(array $contents)
    {
        $size = count($contents);

        if ($size === 0) {
            return $this;
        }

        for ($i = 0; $i < $size; $i++) {
            if ($this->contents->contains($contents[$i]) === false) {
                $this->contents[] = $contents[$i];
            }
        }

        return $this;
    }

    public function setContents(array $contnets)
    {
        $this->contents = $contnets;

        return $this;
    }

    public function getContents()
    {
        return $this->contents;
    }
}
