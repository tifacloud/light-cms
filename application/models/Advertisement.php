<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @Entity
 * @Table(name="lc_advertisements")
 * @HasLifecycleCallbacks
 **/
class Advertisement
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
     * @Column(type="string")
     */
    private $image;
    /**
     * @Column(type="string")
     */
    private $link;
    /**
     * @Column(type="datetime", name="created_at")
     */
    private $createdAt;
    /**
     * @Column(type="datetime", name="updated_at")
     */
    private $updatedAt;

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
                case 'image':
                    $this->image = $value;
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
            'title' => $this->title,
            'image' => $this->image,
            'link' => $this->link,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        );
    }
}
