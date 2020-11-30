<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class ContentController extends CI_Controller
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

        $this->load->library('Auth');

        $this->load->model('Content');
        $this->load->model('Comment');
        $this->load->model('TopContent');
    }

    public function fetch()
    {
        $id = $this->input->get('cid');
        $type = $this->input->get('type');
        $location = $this->input->get('location');
        $ids = $this->input->get('ids');
        if ($ids !== null) {
            $ids = json_decode($ids);
        }

        $withContent = $this->input->get('with-content');
        $withIntroduction = $this->input->get('with-intro');
        $withCover = $this->input->get('with-cover');

        $length = $this->config->item('content_ranks_number');
        $topContentsDuration = '-' . strval($this->config->item('content_ranks_duration')) . ' days';

        $result = array();

        if (empty($id) === false) {
            $result = $this->_fetchContentByID(
                $id,
                $withIntroduction === 'true',
                $withCover === 'true'
            );
        } elseif (empty($type) === false) {
            switch ($type) {

                case 'all':
                    $result = $this->_fetchAllContent(
                        $withContent === 'true',
                        $withIntroduction === 'true',
                        $withCover === 'true',
                        $location
                    );
                    break;
                case 'some':
                    $result = $this->_fetchSomeContents(
                        $ids
                    );
                    break;
                case 'top-views':
                    $result = $this->_fetchTopViews(intval($length), $topContentsDuration);
                    break;
                case 'top-comments':
                    $result = $this->_fetchTopComments(intval($length), $topContentsDuration);
                    break;

            }
        }
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    private function _fetchContentByID($id, $withIntroduction = false, $withCover = false)
    {
        /**
         * @var Content $content
         */
        $content = $this->_em->getRepository('Content')->findOneBy(array('id' => $id));

        $result = array(
            'title' => $content->get()['title'],
            'content' => $content->get()['content'],
            'created_at' => $content->get()['created_at'],
            'updated_at' => $content->get()['updated_at']
        );

        if ($withIntroduction === true) {
            $result['introduction'] = $content->get()['introduction'];
        }

        if ($withCover === true) {
            $result['image'] = $content->get()['cover'];
        }

        return $result;
    }

    private function _fetchAllContent(
        $withContent = false,
        $withIntroduction = false,
        $withCover = false,
        $location = 'index'
    ) {
        $result = array();

        $qb = $this->_em->createQueryBuilder();

        if ($this->auth->hasLoggedIn() && $this->auth->isAdmin() && $location === 'admin') {
            $qb
                ->select('c')
                ->from('Content', 'c');
        } else {
            $qb
                ->select('c')
                ->from('Content', 'c')
                ->where(
                    $qb->expr()->eq('c.visible', ':visible')
                )
                ->orderBy('c.createdAt', 'DESC')
                ->setParameters(array(
                    'visible' => true,
                ));
        }

        $query = $qb->getQuery();
        /**
         * @var array $contents
         */
        $contents = $query->getArrayResult();
        
        foreach ($contents as $content) {
            $resultItem = array(
                'id' => $content['id'],
                'title' => $content['title'],
                'visible' => $content['visible'],
                'created_at' => $content['createdAt'],
                'updated_at' => $content['updatedAt']
            );

            if ($location !== 'admin') {
                $resultItem['views'] = $content['views'];
            }

            if ($withContent === true) {
                $resultItem['content'] = $content['content'];
            }

            if ($withIntroduction === true) {
                $resultItem['introduction'] = $content['introduction'];
            }

            if ($withCover === true) {
                $resultItem['image'] = $content['cover'];
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    private function _fetchTopViews($limit = 15, $duration)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('Content', 'c')
            ->where(
                $qb->expr()->between('c.createdAt', ':interval', ':now'),
                $qb->expr()->eq('c.visible', ':visible')
            )
            ->orderBy('c.views', 'DESC')
            ->setParameters(array(
                'visible' => true,
                'now' => new DateTime(),
                'interval' => new DateTime($duration)
            ))
            ->setMaxResults($limit);
        $query = $qb->getQuery();

        $tmpResults = $query->getArrayResult();
        $result = array();

        foreach ($tmpResults as $item) {
            $result[] = array(
                'id' => $item['id'],
                'title' => $item['title']
            );
        }
    
        return $result;
    }

    private function _fetchTopComments($limit = 15, $duration)
    {
        $result = array();

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(array('c1', 'c2'))
            ->from('Content', 'c1')
            ->innerJoin('c1.comments', 'c2')
            ->where(
                $qb->expr()->between('c2.createdAt', ':interval', ':now'),
                $qb->expr()->eq('c1.visible', ':visible'),
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
                'id' => $content['id'],
                'title' => $content['title'],
                'comments' => count($content['comments'])
            );
        }

        usort($result, array('ContentController', 'cmpContentsByComments'));

        return $result;
    }

    public static function cmpContentsByComments($content1, $content2)
    {
        if ($content1['comments'] === $content2['comments']) {
            return 0;
        }

        return $content1['comments'] > $content2['comments'] ? -1 : 1;
    }

    private function _fetchSomeContents(array $ids)
    {
        $result = array();

        if ($this->auth->hasLoggedIn() === false || $this->auth->isAdmin() === false) {
            return $result;
        }

        foreach ($ids as $id) {
            $content = $this->_em->getRepository('Content')->findOneBy(array('id' => $id));
           
            $result[] = array(
               'id' => $content->get()['id'],
               'title' => $content->get()['title'],
               'created_at' => $content->get()['created_at'],
               'updated_at' => $content->get()['updated_at']
           );
        }

        return $result;
    }

    public function remove()
    {
        $ids = $this->input->post('ids');

        try {
            $result = $this->_removeContents($ids);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['content_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    private function _removeContents(array $contentIDs)
    {
        $result = array();

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        foreach ($contentIDs as $contentID) {
            $contentToRemove = $this->_em->getRepository('Content')
                ->findOneBy(array('id' => $contentID));

            $this->_em->remove($contentToRemove);

            $this->_em->flush();

            $result[] = $contentID;
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

    public function update()
    {
        $id = $this->input->post('id');
        $visibility = $this->input->post('visibility');
        $title = $this->input->post('title');
        $content = $this->input->post('content');
        $introduction = $this->input->post('introduction');
        $views = $this->input->post('views');
        $cover = $this->input->post('cover');

        $oldContent = $this->_em->getRepository('Content')->findOneBy(array('id' => $id));

        if ($visibility !== null) {
            $oldContent->set(array('visible' => $visibility === 'true'));
        }
        
        if ($title !== null) {
            $oldContent->set(array('title' => $title));
        }
        
        if ($content !== null) {
            $oldContent->set(array('content' => $content));
        }
        
        if ($introduction !== null) {
            $oldContent->set(array('introduction' => $introduction));
        }

        if ($views !== null) {
            $oldContent->set(array('views' => $oldContent->get()['views'] + 1));
        }

        if ($cover !== null) {
            $oldContent->set(array('cover' => $cover));
        }

        try {
            $newContent = $this->_em->merge($oldContent);
            
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode(array(
                'error' => $this->config->item('errors')['content_update_fail'],
                'detail' => $e->getMessage()
            )));
        }

        $result = array(
            'affected' => $newContent->get()['id']
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function updateTopContents()
    {
        $contents = $this->input->post('contents');

        $contentsData = json_decode($contents);

        $result = array();

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        $targetContents = $this->_em->getRepository('TopContent')->findAll();

        foreach ($targetContents as $content) {
            $this->_em->remove($content);
            $this->_em->flush();
        }

        $index = 1;
        foreach ($contentsData as $contentData) {
            if (isset($contentData->id)) {
                $topContent = new TopContent();

                $topContent->set(array('id' => $index));

                $content = $this->_em
                ->getRepository('Content')
                ->findOneBy(array('id' => $contentData->id));

                $topContent->hasOneContent($content);

                $this->_em->persist($topContent);
                $this->_em->flush();

                $result[] = array(
                    'id' => $content->get()['id'],
                    'title' => $content->get()['title'],
                    'image' => $content->get()['cover']
                );
            }

            $index += 1;
        }

        try {
            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode(array(
                'error' => $this->config->item('errors')['content_top_update_fail'],
                'detail' => $e->getMessage()
            )));
        }

        if (count($result) < 4) {
            $placeholderNumber = 4 - count($result);

            for ($i=0; $i < $placeholderNumber; $i++) {
                $result[] = array();
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function fetchTopContents()
    {
        $withCover = $this->input->get('with-cover');

        $topContents = $this->_em->getRepository('TopContent')->findAll();
       
        $result = array();

        $index = 1;

        if (count($topContents) === 0) {
            $result = array(
                '1' => array(),
                '2' => array(),
                '3' => array(),
                '4' => array()
            );
        } else {
            foreach ($topContents as $topContent) {
                $content = $topContent->getContent();
    
                $resultItem = array(
                    'id' => $content->get()['id'],
                    'title' => $content->get()['title'],
                    'created_at' => $content->get()['created_at'],
                    'updated_at' => $content->get()['updated_at']
                );
    
                if ($withCover === 'true') {
                    $resultItem['image'] = $content->get()['cover'];
                }
    
                $result[$index] = $resultItem;
    
                $index += 1;
            }
        }

        if (count($result) < 4) {
            $placeholderNumber = 4 - count($result);

            for ($i=0; $i < $placeholderNumber; $i++) {
                $result[] = array();
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function save()
    {
        $content = $this->input->post('content');
        $title = $this->input->post('title');
        $introduction = $this->input->post('introduction');
        $cover = $this->input->post('cover');

        $contentInfo = array(
            'title' => $title,
            'introduction' => $introduction,
            'content' => $content,
            'visible' => false,
            'cover' => $cover,
            'views' => 0,
        );

        $model = new Content();
        $model->set($contentInfo);

        try {
            $this->_em->persist($model);
            $this->_em->flush();
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['content_save_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        $result = array(
            'id' => $model->get()['id']
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }
}
