<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @Entity
 * @Table(name="lc_page_access")
 * @HasLifecycleCallbacks
 **/
class PageAccess
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     * @Column(type="string", name="ip")
     */
    protected $ip;
    /**
     * @Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @PrePersist
     */
    public function saveTimestamp()
    {
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

                case 'ip':
                    $this->ip = $value;
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
            'ip' => $this->ip,
            'created_at' => $this->createdAt,
        );
    }
}
