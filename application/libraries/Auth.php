<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth
{
    protected $CI;

    /**
     * @var array
     */
    private $_config;

    /**
     * @var array
     */
    private $_defaultConfig;

    public function __construct($config = array())
    {
        //  load database configuration
        require APPPATH . 'config/database.php';

        $this->_defaultConfig = array();

        $this->_config = $this->_mergeConfig($config);

        //  codeigniter super class
        $this->CI = &get_instance();

        $this->CI->load->database();
        $this->CI->load->add_package_path(APPPATH.'third_party/ion_auth/');
        $this->CI->load->library('ion_auth');
        $this->CI->load->library('form_validation');
        
        $this->CI->form_validation->set_error_delimiters(
            $this->CI->config->item('error_start_delimiter', 'ion_auth'),
            $this->CI->config->item('error_end_delimiter', 'ion_auth')
        );
    }

    /**
     * @param $config
     * @return array
     */
    private function _mergeConfig($config)
    {
        if (empty($config)) {
            $config = $this->_defaultConfig;
        }

        foreach ($this->_defaultConfig as $key => $value) {
            if (array_key_exists($key, $config) === false) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    public function hasLoggedIn()
    {
        return $this->CI->ion_auth->logged_in();
    }

    public function isAdmin()
    {
        return $this->CI->ion_auth->is_admin();
    }

    public function login($username, $password, $remember)
    {
        // validate form input
        $this->CI->form_validation->set_rules('username', 'Username', 'required');
        $this->CI->form_validation->set_rules('password', 'Password', 'required');

        if ($this->CI->form_validation->run() === true) {
            $remember = $remember === 'true';

            return $this->CI->ion_auth->login($username, $password, $remember);
        } else {
            throw new Exception('Form data is invalid.');
        }

        return false;
    }

    public function user($id = null)
    {
        if ($id === null) {
            return $this->CI->ion_auth->user()->row();
        }

        return $this->CI->ion_auth->user($id)->row();
    }

    public function logout()
    {
        return $this->CI->ion_auth->logout();
    }

    public function changePassword($username, $oldPwd, $newPwd, $confirmPwd)
    {
        // validate form input
        $this->CI->form_validation->set_rules('old-password', 'Old Password', 'required');
        $this->CI->form_validation->set_rules('new-password', 'New Password', 'required');
        $this->CI->form_validation->set_rules('new-password-repeat', 'New Password Repeat', 'required');

        $result = false;

        if ($newPwd !== $confirmPwd) {
            throw new Exception('New password and repeat password not matched.');
        }

        if ($this->CI->form_validation->run() === true) {
            $result = $this->CI->ion_auth->change_password($username, $oldPwd, $newPwd);
        } else {
            throw new Exception('Form data is invalid.');
        }

        return $result;
    }
}
