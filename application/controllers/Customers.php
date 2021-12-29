<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Customers extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function customersList_get($clinic_id) {
        $billInfo = $this->DefaultModel->getgroupbyRecords('billing', array('clinic_id'=>$clinic_id), 'guest_mobile');
        if(count($billInfo) > 0) {
            $i = 0;
            foreach($billInfo as $value) {
                $data['customers'][$i]['id'] = $i + 1;
                $data['customers'][$i]['person_name'] = $value->guest_name;
                $data['customers'][$i]['mobile'] = DataCrypt($value->guest_mobile, 'decrypt');
                $data['customers'][$i]['email'] = '';
                $i++;
            }
        }
        else {
            $data['customers'] = [];
        }
        $this->response(array('code'=>'200', 'message'=>'Success','result'=>$data));
    }

}