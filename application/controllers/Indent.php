<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Indent extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function shortageDrugs_get($clinic_id) {
        $checkShortage = $this->DefaultModel->getJoinRecords('clinic_pharmacy_inventory_inward cpi','clinic_pharmacy_inventory cp','cp.clinic_pharmacy_inventory_id=cpi.clinic_pharmacy_inventory_id',array('cpi.clinic_id'=>$clinic_id),'','cpi.*,cp.reorder_level,cp.vendor_id');
        if(count($checkShortage) > 0) {
            $i = 0;
            foreach($checkShortage as $value) {
                if($value->remaining_quantity <= $value->reorder_level) {
                    $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id),'','trade_name');
                    $data['drugs'][$i]['drug_name'] = $drugInfo->trade_name;
                    $data['drugs'][$i]['drug_id'] = $value->drug_id;
                    $data['drugs'][$i]['batch_no'] = $value->batch_no;
                    $data['drugs'][$i]['quantity'] = $value->remaining_quantity;
                    $data['drugs'][$i]['vendor_id'] = $value->vendor_id;
                }
                $i++;
            }      
            $this->response(array('code' => '200', 'result' => $data, 'message' => 'Success'));      
        }
        else {
            $this->response(array('code' => '201', 'message' => 'Error'));      
        }
    }

}
?>