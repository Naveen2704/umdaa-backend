<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Suppliers extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function suppliersList_get($clinic_id) {
        $vendorsInfo = $this->DefaultModel->getAllRecords('vendor_master', array('clinic_id' => $clinic_id));
        if(count($vendorsInfo) > 0){
            $i = 0;
            foreach($vendorsInfo as $value){
                $data['vendors'][$i]['id'] = $i+1;
                $data['vendors'][$i]['supplier_id'] = (int)$value->vendor_id;
                $data['vendors'][$i]['store_name'] = $value->vendor_storeName;
                $data['vendors'][$i]['person_name'] = $value->vendor_name;
                $data['vendors'][$i]['mobile'] = (int)$value->vendor_mobile;
                $data['vendors'][$i]['email'] = $value->vendor_email;
                $data['vendors'][$i]['address'] = $value->vendor_address;
                $data['vendors'][$i]['location'] = $value->vendor_location;
                $i++;                
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else{
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function delSupplier_get($vendor_id) {
        $vendorsInfo = $this->DefaultModel->getSingleRecord('vendor_master', array('vendor_id' => $vendor_id));
        if(isset($vendorsInfo->vendor_id)){
            $this->DefaultModel->deleteRecord('vendor_master', array('vendor_id' => $vendor_id));
            $this->response(array('code' => '200', 'message' => 'Success'));
        }
        else{
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function addSupplier_post() {
        extract($_POST);
        if(isset($_POST)){
            $checkVendorInfo = $this->DefaultModel->getSingleRecord('vendor_master', array('vendor_mobile' => $mobile, 'vendor_email' => $email, 'clinic_id' => $clinic_id));
            if(is_null($checkVendorInfo)){
                $data['vendor_name'] = $contact_person;
                $data['vendor_email'] = $email;
                $data['vendor_mobile'] = $mobile;
                $data['vendor_location'] = $location;
                $data['vendor_address'] = $address;
                $data['vendor_storeName'] = $store_name;
                $data['clinic_id'] = $clinic_id;
                $data['status'] = 1;
                $data['created_date_time'] = date("Y-m-d H:i:s");
                $this->DefaultModel->insertData('vendor_master', $data);
                $this->response(array('code' => '200', 'message' => 'Success'));
            }
            else{
                $this->response(array('code' => '201', 'message' => 'Vendor already exists with Mobile / Email', 'data' => $checkVendorInfo));
            }
        }
        else{
            $this->response(array('code' => '202', 'message' => 'Something went wrong'));
        }
    }

    public function updateSupplier_post() {
        extract($_POST);
        if(isset($_POST)){
            $checkVendorInfo = $this->DefaultModel->getSingleRecord('vendor_master', array('vendor_id' => $vendor_id));
            if(isset($checkVendorInfo->vendor_id)){
                $data['vendor_name'] = $name;
                $data['vendor_email'] = $email;
                $data['vendor_mobile'] = $mobile;
                $data['vendor_location'] = $location;
                $data['vendor_address'] = $address;
                $data['vendor_storeName'] = $store_name;
                $data['clinic_id'] = $clinic_id;
                $data['status'] = 1;
                $this->DefaultModel->updateData('vendor_master', $data, array('vendor_id' => $vendor_id));
                $this->response(array('code' => '200', 'message' => 'Success'));
            }
            else{
                $this->response(array('code' => '201', 'message' => 'No Supplier Data Found'));
            }
        }
        else{
            $this->response(array('code' => '202', 'message' => 'Something went wrong'));
        }
    }

}