<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->load('error_messages');

        $this->load->library('session');
        $this->load->library('Auth');
    }

    public function login()
    {
        $DEFAULT_FORWARD_URL = $this->config->base_url() . '?p=admin';

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $remember = $this->input->post('remember');

        try {
            $isLoggedIn = $this->auth->login($username, $password, $remember);
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_login_attempt_fail'],
                    'detail' => $e->getMessage()
                )));
        }
        

        if ($isLoggedIn === false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_not_logged_in'],
                    'detail' => 'Login failed.'
                )));
        }

        $forwardURL = isset($_SESSION['auth_forward'])
            ? $_SESSION['auth_forward'] : $DEFAULT_FORWARD_URL;

        $result = array('forward' => $forwardURL);

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function fetchUser()
    {
        $userID = $this->input->get('id');

        $user = $this->auth->user($userID);

        $result = array(
            'username' => $user->email
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function logout()
    {
        $username = $this->input->post('username');

        $currentUser = $this->auth->user();

        if ($this->auth->hasLoggedIn() === false
            || empty($currentUser)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_not_logged_in'],
                    'detail' => 'User not login yet.'
                )));
        }

        if ($username !== $currentUser->email) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_user_not_match'],
                    'detail' => 'Logout user not match.'
                )));
        }

        $this->auth->logout();

        $forwardURL = $this->config->base_url() . '?p=login';
        $result = array('forward' => $forwardURL);

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }

    public function changePassword()
    {
        //  backup: password
        //  $2y$12$nZbX9nEf5gjNM7O.kiHkUOMFVGrpmKShYFOk9vjftytmfGdz1ece6

        $oldPassword = $this->input->post('old-password');
        $newPassword = $this->input->post('new-password');
        $newPasswordRepeat = $this->input->post('new-password-repeat');

        if ($this->auth->hasLoggedIn() === false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_not_logged_in'],
                    'detail' => 'User not login yet.'
                )));
        }

        $currentUser = $this->auth->user();

        $username = $currentUser->email;

        try {
            $changed = $this->auth->changePassword(
                $username,
                $oldPassword,
                $newPassword,
                $newPasswordRepeat
            );
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(array(
                    'error' => $this->config->item('errors')['auth_change_password_fail'],
                    'detail' => $e->getMessage()
                )));
        }

        $result = array('success' => $changed);

        return $this->output
            ->set_content_type('application/json')
            ->set_header('X-CSRF-NAME: ' . $this->security->get_csrf_token_name())
            ->set_header('X-CSRF-TOKEN: ' . $this->security->get_csrf_hash())
            ->set_output(json_encode($result));
    }
}
