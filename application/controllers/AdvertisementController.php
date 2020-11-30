<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Doctrine\ORM\EntityManager;

/**
 * @property Doctrine $doctrine
 */
class AdvertisementController extends CI_Controller
{
    /**
     * @var EntityManager
     */
    private $_em;

    private $_IMAGE_PATH;

    public function __construct()
    {
        parent::__construct();

        $this->config->load('error_messages');

        $this->_IMAGE_PATH = $this->config->base_url() . '?c=advertisementController&m=fetchImage';

        $this->load->library('Doctrine');
        $this->_em = $this->doctrine->entityManager;

        $this->load->library('Auth');

        $this->load->model('Advertisement');
        $this->load->model('AdvertisementPosition');
    }

    public function fetch()
    {
        $position = $this->input->get('position');
        
        $result = array();

        if ($position !== null) {
            $result = $this->_fetchByPosition($position);
        } else {
            $result = $this->_fetchAll();
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    private function _fetchAll()
    {
        $result = array();

        $advertisements = $this->_em->getRepository('Advertisement')->findAll();

        foreach ($advertisements as $advertisement) {
            $result[] = array(
                'id' => $advertisement->get()['id'],
                'title' => $advertisement->get()['title'],
                'link' => $advertisement->get()['link'],
                'image' => $this->_IMAGE_PATH . '&name=' . $advertisement->get()['image'],
            );
        }

        return $result;
    }

    private function _fetchByPosition($position)
    {
        $result = array();

        $position = $this->_em
            ->getRepository('AdvertisementPosition')->findOneBy(array('position' => $position));

        $advertisements = $position->getAdvertisements();
        foreach ($advertisements as $advertisement) {
            $result[] = array(
                'id' => $advertisement->get()['id'],
                'title' => $advertisement->get()['title'],
                'link' => $advertisement->get()['link'],
                'image' => $this->_IMAGE_PATH . '&name=' . $advertisement->get()['image'],
            );
        }

        return $result;
    }

    public function fetchImage()
    {
        $name = $this->input->get('name');

        $SOURCE_PATH = APPPATH . '../ad_uploads';

        $sourceFile = $SOURCE_PATH . DIRECTORY_SEPARATOR . $name;

        $mime = mime_content_type($sourceFile);

        return $this->output
            ->set_content_type($mime)
            ->set_output(file_get_contents($sourceFile));
    }

    public function fetchAdsByPosition()
    {
        $position = $this->input->post('position');
    }

    public function save()
    {
        $title = $this->input->post('title');
        $link = $this->input->post('link');

        $result = null;

        $image = $_FILES['image'];

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $advertisement = new Advertisement();

            $image = $this->_saveADImage($image, APPPATH . '..' . DIRECTORY_SEPARATOR . 'ad_uploads');

            $advertisement->set(array(
                'title' => $title,
                'link' => $link,
                'image' => $image,
            ));

            $this->_em->persist($advertisement);
            $this->_em->flush();

            $result = $advertisement->get();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['advertisement_save_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    private function _saveADImage($image, $path)
    {
        if (isset($image) === false || empty($image)) {
            return '';
        }

        $result = null;

        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 10000;
        $config['max_width'] = 3840;
        $config['max_height'] = 2160;

        $this->load->library('upload');

        $_FILES['userfile']['name']= $image['name'];
        $_FILES['userfile']['type']= $image['type'];
        $_FILES['userfile']['tmp_name']= $image['tmp_name'];
        $_FILES['userfile']['error']= $image['error'];
        $_FILES['userfile']['size']= $image['size'];

        $this->upload->initialize($config);
            
        if (!$this->upload->do_upload()) {
            throw new Exception($this->upload->display_errors());
        } else {
            $result = $this->upload->data('file_name');
        }

        return $result;
    }

    public function addToPosition()
    {
        $position = $this->input->post('position');
        $ids = $this->input->post('ids');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $position = $this->_em
                ->getRepository('AdvertisementPosition')
                ->findOneBy(array('position' => $position));

            $advertisements = array();
            foreach ($ids as $id) {
                $advertisements[] = $this->_em
                    ->getRepository('Advertisement')->findOneBy(array('id' => $id));
            }

            $position->hasManyAdvertisements($advertisements);

            $this->_em->merge($position);
            $this->_em->flush();

            $result = array('updated' => count($advertisements));

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['advertisement_add_to_position_fail'],
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
        $title = $this->input->post('title');
        $link = $this->input->post('link');
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $advertisement = $this->_em
                ->getRepository('Advertisement')->findOneBy(array('id' => $id));

            $imageName = $this->_saveADImage(
                $image,
                APPPATH . '..' . DIRECTORY_SEPARATOR . 'ad_uploads'
            );

            $oldImage = $advertisement->get()['image'];

            if (empty($imageName) === false && $imageName !== $oldImage) {
                $this->_removeFile(
                    $oldImage,
                    APPPATH . '..' . DIRECTORY_SEPARATOR . 'ad_uploads'
                );
            }

            if (empty($title) === false) {
                $advertisement->set(array('title' => $title));
            }

            if (empty($link) === false) {
                $advertisement->set(array('link' => $link));
            }

            if (empty($imageName) === false) {
                $advertisement->set(array('image' => $imageName));
            }

            $this->_em->merge($advertisement);
            $this->_em->flush();

            $result = $advertisement->get();

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['advertisement_update_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    private function _removeFile($name, $path)
    {
        $filePath = $path . DIRECTORY_SEPARATOR . $name;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return true;
    }

    public function remove()
    {
        $id = $this->input->post('id');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $advertisement = $this->_em
                ->getRepository('Advertisement')->findOneBy(array('id' => $id));
            
            $adID = $advertisement->get()['id'];
            $imageName = $advertisement->get()['image'];

            $this->_em->remove($advertisement);
            $this->_em->flush();

            $this->_removeFile(
                $imageName,
                APPPATH . '..' . DIRECTORY_SEPARATOR . 'ad_uploads'
            );

            $result = array('removed' => $adID);

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['advertisement_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function removeFromPosition()
    {
        $position = $this->input->post('position');
        $targetID = $this->input->post('id');

        //  start transaction
        $this->_em->getConnection()->beginTransaction();

        try {
            $position = $this->_em
                ->getRepository('AdvertisementPosition')
                ->findOneBy(array('position' => $position));
            
            $advertisements = $position->getAdvertisements();
            $newAds = array();
            foreach ($advertisements as $advertisement) {
                if ($advertisement->get()['id'] === intval($targetID)) {
                    continue;
                }

                $newAds[] = $advertisement;
            }

            $position->setAdvertisements($newAds);

            $this->_em->merge($position);
            $this->_em->flush();

            $result = array('removed' => $targetID);

            $this->_em->getConnection()->commit();
        } catch (Exception $e) {
            $this->_em->getConnection()->rollBack();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['advertisement_remove_from_position_fail'],
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
