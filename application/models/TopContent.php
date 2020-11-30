<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_top_contents")
 * @HasLifecycleCallbacks
 **/
class TopContent
{
    /**
     * @Id
     * @Column(type="integer")
     **/
    private $id;
    /**
     * @OneToOne(targetEntity="Content")
     * @JoinColumn(name="content_id", referencedColumnName="id")
     */
    private $content;

    /**
     * @param array $values
     * @return $this
     */
    public function set(array $values)
    {
        foreach ($values as $key => $value) {
            switch ($key) {

                case 'id':
                    $this->id = $value;
                
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
        );
    }

    public function hasOneContent(Content $content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }
}
