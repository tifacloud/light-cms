<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class SearchController extends CI_Controller
{
    /**
     * @var EntityManager
     */
    private $_em;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Doctrine');
        $this->_em = $this->doctrine->entityManager;

        $this->load->library('Auth');

        $this->load->model('Content');
        $this->load->model('Comment');
    }

    public function searchContent()
    {
        $keyword = $this->input->get('key');

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('Content', 'c')
            ->where(
                $qb->expr()->like('c.title', $qb->expr()->literal('%' . $keyword . '%')),
                $qb->expr()->eq('c.visible', ':visible')
            )
            ->orderBy('c.createdAt', 'DESC')
            ->setParameters(array(
                'visible' => true
            ));
        
        $query = $qb->getQuery();

        $tmpResults = $query->getArrayResult();
        $result = array();

        foreach ($tmpResults as $content) {
            $result[] = array(
                'id' => $content['id'],
                'title' => $content['title'],
                'views' => $content['views'],
                'introduction' => $content['introduction'],
                'created_at' => $content['createdAt'],
                'updated_at' => $content['updatedAt'],
                'image' => $content['cover']
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}