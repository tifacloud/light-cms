<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @Entity
 * @Table(name="lc_link_groups")
 * @HasLifecycleCallbacks
 **/
class LinkGroup
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 **/
	protected $id;
	/**
	 * @Column(type="string", unique=true)
	 **/
    protected $name;
    /**
     * @Column(type="string")
     */
	protected $link;
	/**
	 * @Column(type="string")
	 */
	protected $position;
	/**
	 * @Column(type="datetime", name="created_at")
	 */
	protected $createdAt;
	/**
	 * @Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;

	/**
	 * @OneToMany(targetEntity="Link", mappedBy="group", cascade={"persist"})
	 */
	private $links;

	public function __construct()
	{
		$this->links = new ArrayCollection();
	}

	public function getLinks()
	{
		return $this->links;
	}

	public function hasMany(array $links)
	{
		for ($i = 0; $i < count($links); $i++) {

			$this->links[] = $links[$i];
			$links[$i]->belongsTo($this);

		}
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

				case 'name':
					$this->name = $value;
                    break;
                case 'link':
                    $this->link = $value;
					break;
				case 'position': 
					$this->position = $value;
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
			'position' => $this->position,
			'created_at' => $this->createdAt,
			'updated_at' => $this->updatedAt
		);
	}
}

