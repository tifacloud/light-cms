<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_contents")
 * @HasLifecycleCallbacks
 **/
class Content
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     **/
    private $id;
    /**
     * @Column(type="string")
     */
    private $title;
    /**
     * @Column(type="text")
     */
    private $introduction;
    /**
     * @Column(type="text")
     */
    private $content;
    /**
     * @Column(type="text")
     */
    private $cover;
    /**
     * @Column(type="boolean")
     */
    private $visible;
    /**
     * @Column(type="integer")
     */
    private $views;
    /**
     * @Column(type="datetime", name="created_at")
     */
    private $createdAt;
    /**
     * @Column(type="datetime", name="updated_at")
     */
    private $updatedAt;
    /**
     * @var ArrayCollection $comments
     * @OneToMany(targetEntity="Comment", mappedBy="target")
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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
     * @param array $values
     * @return $this
     */
    public function set(array $values)
    {
        foreach ($values as $key => $value) {
            switch ($key) {

                case 'title':
                    $this->title = $value;
                    break;
                case 'introduction':
                    $this->introduction = $value;
                    break;
                case 'content':
                    $this->content = $value;
                    break;
                case 'cover':
                    $this->cover = $value;
                    break;
                case 'visible':
                    $this->visible = $value;
                    break;
                case 'views':
                    $this->views = $value;
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
            'title' => $this->title,
            'introduction' => $this->introduction,
            'content' => $this->content,
            'cover' => $this->cover,
            'visible' => $this->visible,
            'views' => $this->views,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        );
    }

    public function getComments()
    {
        return $this->comments;
    }
}
