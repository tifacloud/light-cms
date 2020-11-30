<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class PageController extends CI_Controller
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

        $this->load->model('Page');
    }

    public function save()
    {
        $name = $this->input->post('name');
        $link = $this->input->post('link');
        $parent = $this->input->post('parent');

        $result = array();

        $page = $this->_em->getRepository('Page')->findOneBy(array('name' => $name));

        if ($page !== null) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['page_name_has_existed'],
                    'detail' => 'Page name has existed.'
                )));
        }

        try {
            $newPage = new Page();
            $newPage->set(array(
                'name' => $name,
                'link' => $link,
            ));

            if (empty($parent) === false && $parent !== 'null') {
                $parent = $this->_em->getRepository('Page')
                    ->findOneBy(array('id' => $parent));
                
                $newPage->belongsToPage($parent);
            }

            $this->_em->persist($newPage);
            $this->_em->flush();

            $result = array('id' => $newPage->get()['id']);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['page_save_fail'],
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
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $link = $this->input->post('link');

        try {
            $page = $this->_em->getRepository('Page')->findOneBy(array('id' => $id));

            $page->set(array(
                'name' => $name,
                'link' => $link
            ));

            $existedPage = $this->_em->getRepository('Page')
                ->findOneBy(array('name' => $name));

            if ($existedPage !== null && $existedPage->get()['id'] !== intval($id)) {

                throw new Exception('Name: ' . $name . ' has existed.');

            }

            $result = array('updated' => $page->get()['id']);

            $this->_em->merge($page);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['page_update_fail'],
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
            $page = $this->_em->getRepository('Page')->findOneBy(array('id' => $id));
            $parent = $page->getParent();
            $children = $page->getChildren();

            if (empty($children) === false) {
                foreach ($children as $child) {
                    $this->_em->remove($child);
                }
            }

            if (empty($parent) === false) {
                $children = $parent->getChildren();
                $newChildren = array();

                foreach ($children as $child) {
                    if ($child->get()['id'] !== $id) {
                        $newChildren[] = $child;
                    }
                }

                $parent->hasManyPages($newChildren);
                $this->_em->merge($parent);
            }

            $result = array('removed' => $page->get()['id']);

            $this->_em->remove($page);
            $this->_em->flush();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['page_remove_fail'],
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

        $pages = $this->_em->getRepository('Page')->findBy(array('parent' => null));

        foreach ($pages as $page) {
            $pageName = $page->get()['name'];

            $result[$pageName] = array(
                'id' => $page->get()['id'],
                'link' => $page->get()['link'],
                'children' => array()
            );

            $children = $page->getChildren();
            foreach ($children as $child) {
                $childPageName = $child->get()['name'];

                $childItem = array();
                $childItem[$childPageName] = array(
                    'id' => $child->get()['id'],
                    'link' => $child->get()['link'],
                );

                $result[$pageName]['children'][] = $childItem;
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}
