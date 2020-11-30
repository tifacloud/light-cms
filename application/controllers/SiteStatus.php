<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class SiteStatus extends CI_Controller
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

        $this->load->model('Content');
        $this->load->model('Comment');
        $this->load->model('PageAccess');
    }

    public function recordPageAccess()
    {
        $guestIP = $_SERVER['REMOTE_ADDR'];

        try {
            $pageAccess = new PageAccess();

            $pageAccess->set(array(
                'ip' => $guestIP,
            ));

            $this->_em->persist($pageAccess);

            $this->_em->flush();

            $result = array('id' => $pageAccess->get()['id']);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errros')['status_page_access_save_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }
    
    public function summaryPageAccess()
    {
        $duration = $this->config->item('admin_status_duration');

        if ($duration === null) {
            $duration = '-30 days';
        }

        $duration = '-' . strval($duration) . ' days';

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('p')
            ->from('PageAccess', 'p')
            ->where(
                $qb->expr()->between('p.createdAt', ':interval', ':now')
            )
            ->orderBy('p.createdAt', 'ASC')
            ->setParameters(array(
                'now' => new DateTime(),
                'interval' => new DateTime($duration)
            ));
        $query = $qb->getQuery();

        $result = $query->getArrayResult();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function summaryTopViews()
    {
        $duration = $this->config->item('admin_status_duration');

        if ($duration === null) {
            $duration = '-30 days';
        }

        $duration = '-' . strval($duration) . ' days';

        $limit = 10;

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('Content', 'c')
            ->where(
                $qb->expr()->between('c.createdAt', ':interval', ':now')
            )
            ->orderBy('c.views', 'DESC')
            ->setParameters(array(
                'now' => new DateTime(),
                'interval' => new DateTime($duration)
            ))
            ->setMaxResults($limit);
        $query = $qb->getQuery();

        $tmpResults = $query->getArrayResult();
        $result = array();

        foreach ($tmpResults as $item) {
            $result[] = array(
                'title' => $item['title'],
                'views' => $item['views']
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function summaryTopComments()
    {
        $duration = $this->config->item('admin_status_duration');

        if ($duration === null) {
            $duration = '-30 days';
        }

        $duration = '-' . strval($duration) . ' days';

        $limit = 10;

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(array('c1', 'c2'))
            ->from('Content', 'c1')
            ->innerJoin('c1.comments', 'c2')
            ->where(
                $qb->expr()->between('c2.createdAt', ':interval', ':now'),
                $qb->expr()->eq('c2.visible', ':visible')
            )
            ->setParameters(array(
                'visible' => true,
                'now' => new DateTime(),
                'interval' => new DateTime($duration)
            ))
            ->setMaxResults($limit);
        $query = $qb->getQuery();

        $topCommentContents = $query->getArrayResult();
        $result = array();

        foreach ($topCommentContents as $content) {

            $result[] = array(
                'title' => $content['title'],
                'comments' => count($content['comments'])
            );

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}
