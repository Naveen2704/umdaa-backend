<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Settings extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function settings_get($clinic_id) {
        $info = $this->DefaultModel->getSingleRecord('clinic_pharmacy', array('clinic_id' => $clinic_id));
        if(is_null($info)) {
            $data['settings'] = [];
        }
        else {
            $data['settings']['name'] = $info->name;
            $data['settings']['email'] = $info->email;
            $data['settings']['logo'] = base_url('uploads/pharmacy_logos/'. $info->logo);
            $data['settings']['mobile'] = $info->mobile;
            $data['settings']['gst'] = $info->gst_number;
            $data['settings']['max_disc'] = $info->max_discount;
            $data['settings']['address'] = $info->address;
        }
        $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
    }

    public function saveSettings_post() {
        extract($_POST);
        $info = $this->DefaultModel->getSingleRecord('clinic_pharmacy', array('clinic_id' => $clinic_id));
        $config['upload_path'] = "./uploads/pharmacy_logos/";
        $config['allowed_types'] = 'jpg|JPG|png|PNG|jpeg|JPEG';
        $this->load->library('upload');
        $this->upload->initialize($config);
        $this->upload->do_upload('pharmacy_logo');
        $fileData = $this->upload->data('file_name');

        if ($fileData != "") {
            $data['logo'] = $fileData;
        }

        if(is_null($info)) {
            $data['clinic_id'] = $clinic_id;
            $data['name'] = $name;
            $data['mobile'] = $mobile;
            $data['email'] = $email;
            $data['gst_number'] = $gst;
            $data['max_discount'] = $max_disc;
            $data['address'] = $address;
            $data['created_by'] = $user_id;
            $data['created_date_time'] = date("Y-m-d H:i:s");
            $this->DefaultModel->insertData('clinic_pharmacy', $data);
        }
        else {
            $data['clinic_id'] = $clinic_id;
            $data['name'] = $name;
            $data['mobile'] = $mobile;
            $data['email'] = $email;
            $data['gst_number'] = $gst;
            $data['max_discount'] = $max_disc;
            $data['address'] = $address;
            $data['modified_by'] = $user_id;
            $data['modified_date_time'] = date("Y-m-d H:i:s");
            $this->DefaultModel->updateData('clinic_pharmacy', $data, array('clinic_pharmacy_id'=>$info->clinic_pharmacy_id));
        }
        $this->response(array('code'=>'200', 'message'=>'Success'));
    }

}
?>
