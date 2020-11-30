<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_pages")
 * @HasLifecycleCallbacks
 **/
class Page
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
     * @Column(type="string")
     */
    protected $link;
    /**
     * @Column(type="datetime", name="created_at")
     */
    protected $createdAt;
    /**
     * @Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;
    /**
     * @var Page $parent
     * @ManyToOne(targetEntity="Page", inversedBy="children")
     * @JoinColumn(name="parent", referencedColumnName="id")
     */
    protected $parent;
    /**
     * @OneToMany(targetEntity="Page", mappedBy="parent", cascade={"persist"})
     */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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
                case 'link':
                    $this->link = $value;
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
            'link' => $this->link,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function hasManyPages(array $objects)
    {
        for ($i = 0; $i < count($objects); $i++) {
            $object = $objects[$i];

            $this->children[] = $object;
        }
	}
	
	public function belongsToPage(Page $page)
	{
		$this->parent = $page;
		$this->parent->hasManyPages(array($this));

		return $this;
	}
}
