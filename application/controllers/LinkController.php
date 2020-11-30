<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class LinkController extends CI_Controller
{
    /**
     * @var EntityManager
     */
    private $_em;

    public function __construct()
    {
        parent::__construct();

        $this->config->load('error_messages');

        $this->load->library('Doctrine');
        $this->_em = $this->doctrine->entityManager;

        $this->load->model('Link');
        $this->load->model('LinkGroup');
    }

    public function save()
    {
        $groupID = $this->input->post('group');
        $name = $this->input->post('name');
        $link = $this->input->post('link');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $newLink = new Link();

            $newLink->set(array(
                'name' => $name,
                'address' => $link,
            ));

            $group = $this->_em->getRepository('LinkGroup')
                ->findOneBy(array('id' => $groupID));

            if ($group === null) {

                $group = $this->_em->getRepository('LinkGroup')
                    ->findOneBy(array('name' => '#default'));

            }

            if ($group === null) {
                $defaultGroup = new LinkGroup();

                $defaultGroup->set(array(
                    'name' => '#default',
                    'link' => '#',
                    'position' => 'bottom'
                ));

                $this->_em->persist($defaultGroup);

                $group = $defaultGroup;
            }

            $newLink->belongsTo($group);

            $this->_em->persist($newLink);
            $this->_em->flush();

            $result = array('id' => $newLink->get()['id']);

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_save_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function update()
    {
        $name = $this->input->post('name');
        $address = $this->input->post('link');
        $id = $this->input->post('id');

        try {
            $link = $this->_em->getRepository('Link')->findOneBy(array('id' => $id));

            $link->set(array(
                'name' => $name,
                'address' => $address
            ));

            $result = array('updated' => $link->get()['id']);

            $this->_em->merge($link);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_update_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function remove()
    {
        $id = $this->input->post('id');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $link = $this->_em->getRepository('Link')->findOneBy(array('id' => $id));

            $result = array('removed' => $link->get()['id']);

            $this->_em->remove($link);
            $this->_em->flush();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function fetch()
    {
        $result = array();

        $linkGroups = $this->_em->getRepository('LinkGroup')->findAll();

        foreach ($linkGroups as $group) {
            $links = $group->getLinks();

            $groupName = $group->get()['name'];
            $result[$groupName] = array(
                'id' => $group->get()['id'],
                'link' => $group->get()['link'],
                'position' => $group->get()['position'],
                'children' => array(),
            );

            if (empty($links) === false) {
                foreach ($links as $link) {
                    $linkName = $link->get()['name'];
                    $childItem = array();

                    $childItem[$linkName] = array(
                        'id' => $link->get()['id'],
                        'link' => $link->get()['address']
                    );

                    $result[$groupName]['children'][] = $childItem;
                }
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function saveGroup()
    {
        $name = $this->input->post('name');
        $link = $this->input->post('link');
        $position = $this->input->post('position');

        if ($position === null) {

            $position = 'bottom';

        }

        try {
            $newGroup = new LinkGroup();

            if (empty($link)) {
                $link = '#';
            }

            $newGroup->set(array(
                'name' => $name,
                'link' => $link,
                'position' => $position
            ));

            $this->_em->persist($newGroup);
            $this->_em->flush();

            $result = array('id' => $newGroup->get()['id']);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_group_save_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function updateGroup()
    {
        $name = $this->input->post('name');
        $link = $this->input->post('link');
        $id = $this->input->post('id');

        try {
            $linkGroup = $this->_em->getRepository('LinkGroup')
                ->findOneBy(array('id' => $id));

            if (empty($link)) {

                $link = '#';

            }

            $existedGroup = $this->_em->getRepository('LinkGroup')
                ->findOneBy(array('name' => $name));

            if ($existedGroup !== null && $existedGroup->get()['id'] !== intval($id)) {

                throw new Exception('Name: ' . $name . ' has existed.');

            }

            $linkGroup->set(array(
                'name' => $name,
                'link' => $link
            ));

            $result = array('updated' => $linkGroup->get()['id']);

            $this->_em->merge($linkGroup);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_group_update_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function removeGroup()
    {
        $id = $this->input->post('id');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $linkGroup = $this->_em->getRepository('LinkGroup')->findOneBy(array('id' => $id));

            $links = $linkGroup->getLinks();
            foreach ($links as $link) {
                $this->_em->remove($link);
            }

            $result = array('removed' => $linkGroup->get()['id']);

            $this->_em->remove($linkGroup);
            $this->_em->flush();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['link_group_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }
}
