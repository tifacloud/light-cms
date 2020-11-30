<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @Entity
 * @Table(name="lc_links")
 * @HasLifecycleCallbacks
 **/
class Link
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 **/
	protected $id;
	/**
	 * @Column(type="string")
	 **/
	protected $address;
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
	 * @ManyToOne(targetEntity="LinkGroup", inversedBy="links")
	 * @JoinColumn(name="group_id", referencedColumnName="id")
	 */
	private $group;

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
				case 'address':
					$this->address = $value;
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
			'address' => $this->address,
			'created_at' => $this->createdAt,
			'updated_at' => $this->updatedAt
		);
	}

    /**
     * @param $group
     * @return $this
     */
	public function belongsTo($group)
	{
		$this->group = $group;

		return $this;
	}

    /**
     * @return LinkGroup
     */
	public function getGroup()
    {
        return $this->group;
    }
}
