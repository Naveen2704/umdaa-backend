<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Prescriptions extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function prescriptions_get($clinic_id) {
        $prescriptions = $this->DefaultModel->getAllRecords('patient_prescription', array('clinic_id' => $clinic_id), '', 'patient_prescription_id,patient_id,doctor_id,created_date_time');
        if(count($prescriptions) > 0) {
            $i = 0;
            foreach($prescriptions as $value) {
                $data['prescriptions'][$i]['patient_prescription_id'] = $value->patient_prescription_id;
                $data['prescriptions'][$i]['patient_id'] = $value->patient_id;
                $data['prescriptions'][$i]['patient_name'] = getPatientName($value->patient_id);
                $data['prescriptions'][$i]['doctor_id'] = $value->doctor_id;
                $data['prescriptions'][$i]['doctor_name'] = getDoctorName($value->doctor_id);
                $data['prescriptions'][$i]['prescription_date'] = date("M,d Y", strtotime($value->created_date_time));
                $i++;
            }
        }
        else {
            $data['prescriptions'] = [];
        }
        $this->response(array('code'=>'200','message'=>'Success','result'=>$data));
    }

}
?>