<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Media extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->load('error_messages');

        $this->load->helper(array('form', 'url'));
    }

    public function uploadImages()
    {
        $filesField = 'files';

        $config['upload_path'] = APPPATH . '../uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 10000;
        $config['max_width'] = 3840;
        $config['max_height'] = 2160;

        $this->load->library('upload');

        $files = $_FILES[$filesField];
        for ($i=0; $i< count($files['name']); $i++) {
            $_FILES['userfile']['name']= $files['name'][$i];
            $_FILES['userfile']['type']= $files['type'][$i];
            $_FILES['userfile']['tmp_name']= $files['tmp_name'][$i];
            $_FILES['userfile']['error']= $files['error'][$i];
            $_FILES['userfile']['size']= $files['size'][$i];

            $this->upload->initialize($config);
            
            if (!$this->upload->do_upload()) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(500)
                    ->set_output(json_encode(array(
                        'error' => $this->config->item('errors')['media_file_upload_fail'],
                        'detail' => $this->upload->display_errors()
                    )));
            } else {
                $result[] = $this->upload->data('file_name');
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function fetch()
    {
        $url = $this->config->base_url();
        $THUMBNAIL_PATH = $url . '?c=media&m=fetchThumbnails';

        $files = $this->_read_all_files(APPPATH . '../uploads', true);

        $result = array();

        foreach ($files as $file) {
            $result[] = array(
                'image' => $THUMBNAIL_PATH . '&name=' . $file,
                'title' => $file,
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    private function _read_all_files($root = '/uploads', $excludeDir = false)
    {
        $files  = array();
        $directories  = array();
        $last_letter  = $root[strlen($root)-1];
        $root  = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR;
       
        $directories[]  = $root;
       
        while (sizeof($directories)) {
            $dir  = array_pop($directories);
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }

                    $path  = $dir . $file;

                    if (is_dir($path)) {
                        $directory_path = $path.DIRECTORY_SEPARATOR;
                        array_push($directories, $directory_path);
                    } elseif (is_file($path)) {
                        $files[]  = $excludeDir ? $file : $path;
                    }
                }

                closedir($handle);
            }
        }
       
        return $files;
    }

    public function fetchThumbnails()
    {
        $name = $this->input->get('name');

        $SOURCE_PATH = APPPATH . '../uploads';
        $THUMBNAIL_PATH = APPPATH . '../thumbnails';

        $sourceFile = $SOURCE_PATH . DIRECTORY_SEPARATOR . $name;
        $thumbnail = $THUMBNAIL_PATH . DIRECTORY_SEPARATOR . $name;

        if (file_exists($thumbnail) === false) {
            $config['image_library'] = 'gd2';
            $config['source_image'] = $sourceFile;
            $config['new_image'] = $thumbnail;
            $config['maintain_ratio'] = true;
            $config['width'] = 720;
            $config['height'] = 480;

            $this->load->library('image_lib', $config);

            $this->image_lib->resize();
        }

        $mime = mime_content_type($thumbnail);

        return $this->output
            ->set_content_type($mime)
            ->set_output(file_get_contents($thumbnail));
    }

    public function remove()
    {
        $names = $this->input->post('names');

        try {
            $tmpResult = $this->_removeFiles($names);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['media_file_remove_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        $result = array(
            'remove' => $tmpResult ? 'success' : 'fail',
        );

        if ($tmpResult !== true) {

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['media_file_remove_fail'],
                    'detail' => 'remove file failed'
                )));

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    private function _removeFiles(array $names)
    {
        $THUMBNAIL_PATH = APPPATH . '..' . DIRECTORY_SEPARATOR . 'thumbnails';
        $FILE_PATH = APPPATH . '..' . DIRECTORY_SEPARATOR . 'uploads';

        foreach ($names as $name) {

            $filePath = $FILE_PATH . DIRECTORY_SEPARATOR . $name;
            $thumbnailPath = $THUMBNAIL_PATH . DIRECTORY_SEPARATOR . $name;

            if (file_exists($filePath)) {

                unlink($filePath);

            }

            if (file_exists($thumbnailPath)) {

                unlink($thumbnailPath);

            }
            
        }

        return true;
    }
}
