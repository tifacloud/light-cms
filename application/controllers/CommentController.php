<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class CommentController extends CI_Controller
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

        $this->load->model('Comment');
        $this->load->model('Content');
    }

    public function fetch()
    {
        $cid = $this->input->get('cid');
        $root = $this->input->get('root');

        $result = array();

        if (empty($cid) === false) {
            $comments = $this->_fetchCommentByID($cid);
        } else {
            $comments = $this->_fetchAllComments();
        }

        foreach ($comments as $comment) {
            $result[] = $comment->get();
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    private function _fetchAllComments()
    {
        return $this->_em->getRepository('Comment')->findAll();
    }

    private function _fetchCommentByID($id)
    {
        $content = $this->_em->getRepository('Content')
            ->findOneBy(array('id' => $id));

        $comments = $content->getComments();

        $result = array();
        foreach ($comments as $comment) {
            if ($comment->get()['visible']) {
                $result[] = $comment;
            }
        }

        return $result;
    }

    public function save()
    {
        $cid = $this->input->post('cid');
        $title = $this->input->post('title');
        $comment = $this->input->post('comment');

        if (empty($cid)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['comment_content_not_specify'],
                    'detail' => 'Target content not provided.'
                )));
        }

        $content = $this->_em->getRepository('Content')->findOneBy(array('id' => $cid));

        if (empty($content)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['comment_content_not_existed'],
                    'detail' => 'Comment must be saved for specific content.'
                )));
        }

        try {
            $newComment = new Comment();

            $newComment->set(array(
                'title' => $title,
                'content' => $comment,
                'visible' => false,
            ));

            $newComment->belongsToContent($content);

            $this->_em->persist($newComment);

            $this->_em->flush();

            $result = array('id' => $newComment->get()['id']);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['comment_save_failed'],
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
        $visibility = $this->input->post('visibility');

        $oldComment = $this->_em->getRepository('Comment')->findOneBy(array('id' => $id));

        if ($visibility !== null) {
            $oldComment->set(array('visible' => $visibility === 'true'));
        }
        
        try {
            $newComment = $this->_em->merge($oldComment);
            
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode(array(
                'error' => $this->config->item('errors')['comment_update_fail'],
                'detail' => $e->getMessage()
            )));
        }

        $result = array(
            'affected' => $newComment->get()['id']
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function remove()
    {
        $ids = $this->input->post('ids');

        try {
            $result = $this->_removeComments($ids);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['comment_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    private function _removeComments(array $commentIDs)
    {
        $result = array();

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        foreach ($commentIDs as $commentID) {
            $commentToRemove = $this->_em->getRepository('Comment')
                ->findOneBy(array('id' => $commentID));

            $this->_em->remove($commentToRemove);

            $this->_em->flush();

            $result[] = $commentID;
        }

        //  commit database change
        try {
            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            throw $e;
        }

        return $result;
    }
}
