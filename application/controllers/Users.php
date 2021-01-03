<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once FCPATH . '/vendor/autoload.php';
use \Firebase\JWT\JWT;

class Users extends CI_Controller
{

    private $secret = "This is a secret key";
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user');

        ///Allowing CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
    }

    public function response($data, $status = 200)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status)
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    }

    public function register()
    {
        return $this->response($this->user->save());
    }

    public function all_users()
    {
        return $this->response($this->user->get_all());
    }

    public function detail_user($id)
    {
        if ($this->check_token()) {
            return $this->response($this->user->get_all('id', $id));
        } else {
            return $this->response([
                'success' => false,
                'message' => "User is different.",
            ], 404);
        }

    }

    public function login()
    {
        if (!$this->user->is_valid()) {
            return $this->response([
                'success' => false,
                'message' => 'Password or Email is wrong',
            ], 401);
        }

        //Get User data
        $email = $this->input->post('email');
        $user  = $this->user->get_all('email', $email);

        // Collect data
        $date             = new DateTime();
        $payload['id']    = $user[0]->id;
        $payload['email'] = $user[0]->email;
        $payload['iat']   = $date->getTimestamp();
        $payload['exp']   = $date->getTimestamp() + 60 * 60 * 2;

        $output['id_token'] = JWT::encode($payload, $this->secret);
        $this->response($output);
    }
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($this->protected_method($id)) {
            return $this->response($this->user->update($id, $data));
        }
    }

    /*public function protected_method($id)
    {
        if ($id_from_token = $this->check_token()) {
            if ($id_from_token == $id) {
                return true;
            } else {
                return $this->response([
                    'success' => false,
                    'message' => "User is different.",
                ], 404);
            }
        }
    }*/

    public function check_token()
    {
        $jwt = $this->input->get_request_header('Authorization');
        try {
            //decode token with HS256 method
            $decode = JWT::decode($jwt, $this->secret, array('HS256'));
            //return $decode->id;
            return true;
        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'message' => 'invalid token',
            ], 401);
        }
    }

    public function get_input()
    {
        return json_decode(file_get_contents('php://input'));
    }

    public function delete($id)
    {
        if ($this->protected_method($id)) {
            return $this->response($this->user->delete($id));
        }
    }

}
