<?php
defined('BASEPATH') or exit('No direct script access allowed');

class IndexPage extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('session');
        $this->load->library('Auth');
    }

    public function index()
    {
        $page = $this->input->get('p');
        $staticPageName = $this->input->get('name');

        $_LOGIN_URL = $this->config->base_url() . '?p=login';
        $_ADMIN_URL = $this->config->base_url() . '?p=admin';

        if ($page === 'detail') {
            $view = 'detail_page';
        } elseif ($page === 'admin') {
            if ($this->_canAccessAdmin() === false) {
                $_SESSION['auth_forward'] = $_ADMIN_URL;

                return $this->output
                    ->set_status_header(301)
                    ->set_header('Location: ' . $_LOGIN_URL);
            }

            $view = 'admin_page';
        } elseif ($page === 'login') {
            $view = 'login_page';
        } elseif ($page === 'search') {
            $view = 'search_page';
        } elseif ($page === 'category') {
            $view = 'category_page';
        } elseif ($page === 'static') {
            $view = 'static_page';
        } else {
            $view = 'index_page';
        }

        $assets = array(
            'js' => 'public/js',
            'css' => 'public/css',
            'url' => $this->config->base_url(),
            'site_icon' => 'public/img/' . $this->config->item('site_favicon'),
            'site_name' => $this->config->item('site_name'),
            'site_logo' => empty($this->config->item('site_logo'))
                ? '' : 'public/img/' . $this->config->item('site_logo')
        );

        $pageTitle = implode('_', array($view, 'title'));
        $assets[$pageTitle] = $this->config->item($pageTitle);

        if ($page === 'static' && empty($staticPageName) === false) {

            $assets['page_name'] = $staticPageName;

        }

        return $this->load->view($view, $assets);
    }

    private function _canAccessAdmin()
    {
        return $this->auth->hasLoggedIn() && $this->auth->isAdmin();
    }
}
