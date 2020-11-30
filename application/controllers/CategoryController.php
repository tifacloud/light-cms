<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class CategoryController extends CI_Controller
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

        $this->load->model('Category');
        $this->load->model('Content');
        $this->load->model('Comment');
    }

    public function save()
    {
        $name = $this->input->post('name');

        try {
            $newCategory = new Category();

            $newCategory->set(array(
                'name' => $name
            ));

            $this->_em->persist($newCategory);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['category_save_failed'],
                    'detail' => $e->getMessage()
                )));
        }
        
        $result = $this->_fetchAllCategories();

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
        $contents = $this->input->post('contents');
        $type = $this->input->post('type');

        if ($contents !== null) {
            $contents = json_decode($contents);
        }

        $category = $this->_em->getRepository('Category')->findOneBy(array('name' => $name));

        if ($category !== null) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['category_name_has_existed'],
                    'detail' => 'Category name has existed.'
                )));
        }

        try {
            $category = $this->_em->getRepository('Category')->findOneBy(array('id' => $id));

            if ($name !== null) {
                $category->set(array(
                'name' => $name,
                ));
            }

            if ($contents !== null && $type === 'add') {
                $contentObjects = array();
                foreach ($contents as $contentID) {
                    $contentObjects[] = $this->_em
                        ->getRepository('Content')->findOneBy(array('id' => $contentID));
                }
                $category->hasManyContents($contentObjects);
            }

            if ($contents !== null && $type === 'remove') {
                $existedContents = $category->getContents();
                $newContents = array();
                foreach ($existedContents as $content) {
                    $contentID = $content->get()['id'];

                    if (in_array($contentID, $contents) === false) {
                        $newContents[] = $content;
                    }
                }
                $category->setContents($newContents);
            }

            $this->_em->merge($category);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['category_update_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        if ($contents !== null) {
            $result = $this->_fetchCategoryByID($id, true, false);
        } else {
            $result = $this->_fetchAllCategories();
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
            $category = $this->_em->getRepository('Category')->findOneBy(array('id' => $id));

            $this->_em->remove($category);
            $this->_em->flush();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['category_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        $result = $this->_fetchAllCategories();

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function fetch()
    {
        $id = $this->input->get('id');
        $withContents = $this->input->get('with-contents');
        $fullContent = $this->input->get('full-content');

        $result = array();

        if (empty($id) === false) {
            $result = $this->_fetchCategoryByID($id, $withContents === 'true', $fullContent === 'true');
        } else {
            $result = $this->_fetchAllCategories();
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    private function _fetchCategoryByID($id, $withContents, $fullContent)
    {
        $result = array();

        $category = $this->_em->getRepository('Category')->findOneBy(array('id' => $id));

        $contents = $category->getContents();

        $result = array(
                'id' => $category->get()['id'],
                'name' => $category->get()['name'],
                'contents' => array()
            );

        if ($withContents) {
            foreach ($contents as $content) {
                $visible = $content->get()['visible'];

                if ($visible === true) {
                    $tmpContent = array(
                        'id' => $content->get()['id'],
                        'title' => $content->get()['title'],
                        'views' => $content->get()['views'],
                        'created_at' => $content->get()['created_at'],
                        'updated_at' => $content->get()['updated_at']
                    );

                    if ($fullContent === true) {
                        $tmpContent['introduction'] = $content->get()['introduction'];
                        $tmpContent['image'] = $content->get()['cover'];
                    }

                    $result['contents'][] = $tmpContent;
                }
            }
        }

        return $result;
    }

    private function _fetchAllCategories()
    {
        $result = array();

        $categories = $this->_em->getRepository('Category')->findAll();

        foreach ($categories as $category) {
            $contents = $category->getContents();

            $result[] = array(
                'id' => $category->get()['id'],
                'name' => $category->get()['name'],
            );
        }

        return $result;
    }
}
