<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Slider extends CI_Controller
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

        $this->load->model('Slide');
    }

    public function fetch()
    {
        /**
         * @var Slide[] $slides
         */
        $slides = $this->_em->getRepository('Slide')->findAll();

        $result = array();

        foreach ($slides as $slide) {
            $result[] = $slide->get();
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function save()
    {
        $slide = $this->input->post('slide');

        $slideData = json_decode($slide);

        $result = array();

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        $slide = new Slide();
        $slide->set(array(
            'image' => $slideData->image,
            'title' => $slideData->title,
        ));
            
        $this->_em->persist($slide);
        $this->_em->flush();

        $result = $slide->get();

        //  commit database change
        try {
            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('erros')['slide_save_fail'],
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
        $slides = $this->input->post('slides');

        $slidesData = json_decode($slides);

        $result = array();

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        foreach ($slidesData as $slideData) {
            $slide = $this->_em->getRepository('Slide')->findOneBy(array('id' => $slideData->id));

            $slide->set(array(
                'image' => $slideData->image,
                'title' => $slideData->title,
            ));
            
            $this->_em->merge($slide);
            $this->_em->flush();

            $result[] = $slide->get();
        }

        //  commit database change
        try {
            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['slide_update_fail'],
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
        $slide = $this->input->post('slide');

        $slideData = json_decode($slide);

        $result = array();

        $target = $this->_em->getRepository('Slide')->findOneBy(array('id' => $slideData->id));

        try {
            $this->_em->remove($target);

            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['slide_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        /**
         * @var Slide[] $slides
         */
        $slides = $this->_em->getRepository('Slide')->findAll();

        $result = array();

        foreach ($slides as $slide) {
            $result[] = $slide->get();
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }
}
