<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_comments")
 * @HasLifecycleCallbacks
 **/
class Comment
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     * @Column(type="string", name="title")
     */
    protected $title;
    /**
     * @Column(type="text", name="content")
     */
    protected $content;
    /**
     * @Column(type="boolean")
     */
    private $visible;
    /**
     * @Column(type="datetime", name="created_at")
     */
    protected $createdAt;
    /**
     * @Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;
    /**
     * @var Comment $parent
     * @ManyToOne(targetEntity="Comment", inversedBy="children")
     * @JoinColumn(name="parent", referencedColumnName="id")
     */
    protected $parent;
    /**
     * @OneToMany(targetEntity="Comment", mappedBy="parent", cascade={"persist"})
     */
    protected $children;
    /**
     * @var Content $target
     * @ManyToOne(targetEntity="Content")
     * @JoinColumn(name="content_id", referencedColumnName="id")
     */
    private $target;

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

                case 'title':
                    $this->title = $value;
                    break;
                case 'content':
                    $this->content = $value;
                    break;
                case 'visible':
                    $this->visible = $value;
                    break;
                case 'parent':
                    $this->parent = $value;
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
        $parent = null;
        if (empty($this->parent) === false) {
            $parent = $this->parent->get()['id'];
        }

        return array(
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'visible' => $this->visible,
            'parent' => $parent,
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

    public function hasManyComments(array $objects)
    {
        for ($i = 0; $i < count($objects); $i++) {
            $object = $objects[$i];

            $this->children[] = $object;
        }
	}
	
	public function belongsToContent(Content $content)
	{
		$this->target = $content;

		return $this;
	}

	public function belongsToComment(Comment $comment)
	{
		$this->parent = $comment;
		$this->parent->hasManyComments(array($this));

		return $this;
	}

    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->target;
    }
}
