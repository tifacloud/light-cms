<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_advertisement_positions")
 * @HasLifecycleCallbacks
 **/
class AdvertisementPosition
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
    private $position;
    /**
	 * @ManyToMany(targetEntity="Advertisement", inversedBy="positions")
	 * @JoinTable(
	 *     name="lc_ad_ad_positions",
	 *     joinColumns={
	 *         @JoinColumn(name="position_id", referencedColumnName="id")
	 *     },
	 *     inverseJoinColumns={
	 *         @JoinColumn(name="advertisement_id", referencedColumnName="id")
	 *     }
	 * )
	 */
    private $advertisements;

    public function __construct()
    {
        $this->advertisements = new ArrayCollection();
    }

    /**
     * @param array $values
     * @return $this
     */
    public function set(array $values)
    {
        foreach ($values as $key => $value) {
            switch ($key) {

                case 'position':
                    $this->position = $value;
                    break;
                
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
            'position' => $this->position,
        );
    }

    public function hasManyAdvertisements(array $advertisements)
    {
        $size = count($advertisements);

		if ($size === 0) {

			return $this;

		}

		for ($i = 0; $i < $size; $i++) {

			$this->advertisements[] = $advertisements[$i];

		}

    	return $this;
    }

    public function setAdvertisements(array $advertisements)
    {
        $this->advertisements = new ArrayCollection($advertisements);

        return $this;
    }

    public function getAdvertisements()
    {
        return $this->advertisements;
    }
}
