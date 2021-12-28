<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Authentication extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }
    
    public function login_post() {
        if(isset($_POST)) {
            extract($_POST);
            $checkUserData = $this->DefaultModel->getSingleRecord('users', array('username' => $username,'password' => md5($password)));
            if(isset($checkUserData->user_id)) {
                $data['userInfo'] = $checkUserData;
                $this->response(array('code' => '200', 'result' => $data));
            }
            else{
                $this->response(array('code' => '201', 'message' => 'Invalid Credentials'));
            }
        }
        else{
            $this->response(array('code' => '202', 'message' => 'Invalid Request'));
        }
    }    

}
?>