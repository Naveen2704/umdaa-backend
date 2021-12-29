<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Inventory extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function drugsList_get($drug) {
        $drugInfo = $this->DefaultModel->getAllRecords('drug', array('trade_name LIKE ' => '%' . $drug . '%'));
        if(count($drugInfo) > 0) {
            $i = 0;
            foreach($drugInfo as $value) {
                $data['drugs'][$i]['drug_id'] = $value->drug_id;
                $data['drugs'][$i]['trade_name'] = $value->trade_name;
                $data['drugs'][$i]['formulation'] = $value->formulation;
                $data['drugs'][$i]['hsn_code'] = $value->hsn_code;
                $data['drugs'][$i]['cgst'] = $value->cgst;
                $data['drugs'][$i]['sgst'] = $value->sgst;
                $data['drugs'][$i]['igst'] = $value->igst;
                $i++;
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function addDrug_post() {
        if(isset($_POST)) {
            extract($_POST);
            $checkPharmacy = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory', array('clinic_id' => $clinic_id, 'drug_id' => $drug_id));
            if(is_null($checkPharmacy)) {
                $main['clinic_id'] = $clinic_id;
                $main['drug_id'] = $drug_id;
                $main['reorder_level'] = $rl;
                $main['igst'] = $igst;
                $main['cgst'] = $cgst;
                $main['sgst'] = $sgst;
                $main['max_discount_percentage'] = $max_disc;
                $main['vendor_id'] = $suppliers;
                $main['status'] = 1;
                $main['archieve'] = 0;
                $main['created_by'] = $user_id;
                $main['created_date_time'] = date('Y-m-d H:i:s');
                $main['modified_by'] = $user_id;
                $main['modified_date_time'] = date('Y-m-d H:i:s');
                $clinic_pharmacy_inventory_id = $this->DefaultModel->insertDataReturnId('clinic_pharmacy_inventory', $main);
            }
            else {
                $clinic_pharmacy_inventory_id = $checkPharmacy->clinic_pharmacy_inventory_id;
            }
            $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $drug_id));
            $child['clinic_id'] = $clinic_id;
            $child['drug_id'] = $drug_id;
            $child['drug_name'] = $drugInfo->trade_name;
            $child['clinic_pharmacy_inventory_id'] = $clinic_pharmacy_inventory_id;
            $child['batch_no'] = $batch_no;
            $child['quantity'] = $qty;
            $child['remaining_quantity'] = $qty;
            $child['mrp'] = $mrp;
            $child['purchase_price'] = $pp;
            $child['pack_size'] = $pack_size;
            $child['expiry_date'] = $expiry_year."-".$expiry_month."-01";
            $child['supplied_date'] = date('Y-m-d');
            $child['status'] = 1;
            $child['archieve'] = 0;
            $child['created_by'] = $user_id;
            $child['created_date_time'] = date('Y-m-d H:i:s');
            $child['modified_by'] = $user_id;
            $child['modified_date_time'] = date('Y-m-d H:i:s');
            $this->DefaultModel->insertData('clinic_pharmacy_inventory_inward', $child);
            $this->response(array('code' => '200', 'message' => 'Success'));
        }
        else {
            $this->response(array('code' => '202', 'message' => 'Error Occured'));
        }
    }

    public function inventory_get($clinic_id) {
        $checkInventory = $this->DefaultModel->getAllRecords('clinic_pharmacy_inventory_inward', array('clinic_id' => $clinic_id));
        if(count($checkInventory) > 0) {
            $sum = $this->DefaultModel->getRecordsSum('clinic_pharmacy_inventory_inward', 'mrp', array('clinic_id' => $clinic_id));
            $total = 0;
            $i = 0;
            foreach($checkInventory as $value) {
                $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id));
                $invInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory', array('drug_id' => $value->drug_id, 'clinic_id' => $clinic_id));
                if(is_null($invInfo) || is_null($drugInfo)){
                    continue;
                }
                $cost = $value->mrp/$value->pack_size;
                $data['inventory'][$i]['id'] = (int) $i;
                $data['inventory'][$i]['inventory_id'] = (int) $value->clinic_pharmacy_inventory_inward_id;
                $data['inventory'][$i]['trade_name'] = $drugInfo->trade_name;
                $data['inventory'][$i]['formulation'] = $drugInfo->formulation;
                $data['inventory'][$i]['batch_no'] = $value->batch_no;
                $data['inventory'][$i]['expiry_date'] = date("M' Y", strtotime($value->expiry_date));
                $data['inventory'][$i]['quantity'] = (int) $value->remaining_quantity;
                $data['inventory'][$i]['mrp'] = number_format($value->mrp, 2);
                $data['inventory'][$i]['pack_size'] = (int) $value->pack_size;
                $data['inventory'][$i]['hsn_code'] = $drugInfo->hsn_code;
                $data['inventory'][$i]['cost'] = number_format($value->mrp/$value->pack_size, 2);
                $data['inventory'][$i]['purchaseCost'] = number_format($value->purchase_price, 2);
                $data['inventory'][$i]['discount'] = (int) $invInfo->max_discount_percentage;
                $data['inventory'][$i]['reorder_level'] = (int) $invInfo->reorder_level;
                $total += $cost * $value->remaining_quantity;
                $i++;
            }
            $data['totalValue'] = number_format($total, 2);
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function expired_get($clinic_id) {
        $today = date("Y-m-d");
        $checkInventory = $this->DefaultModel->getAllRecords('clinic_pharmacy_inventory_inward', array('clinic_id' => $clinic_id, 'expiry_date < ' => $today));
        if(count($checkInventory) > 0) {
            $i = 0;
            foreach($checkInventory as $value) {
                $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id));
                if(is_null($drugInfo)){
                    continue;
                }
                $data['inventory'][$i]['id'] = (int) $i;
                $data['inventory'][$i]['inventory_id'] = (int) $value->clinic_pharmacy_inventory_inward_id;
                $data['inventory'][$i]['trade_name'] = $drugInfo->trade_name;
                $data['inventory'][$i]['batch_no'] = $value->batch_no;
                $data['inventory'][$i]['expiry_date'] = $value->expiry_date;
                $data['inventory'][$i]['quantity'] = (int) $value->remaining_quantity;
                $i++;
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function expiringSoon_get($clinic_id) {
        $today = date("Y-m-d");
        $comingDate = date("Y-m-d", strtotime(" +3 months "));
        $checkInventory = $this->DefaultModel->getAllRecords('clinic_pharmacy_inventory_inward', array('clinic_id' => $clinic_id, 'expiry_date >' => $today, 'expiry_date <' => $comingDate));
        if(count($checkInventory) > 0) {
            $i = 0;
            foreach($checkInventory as $value) {
                $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id));
                if(is_null($drugInfo)){
                    continue;
                }
                $data['inventory'][$i]['id'] = (int) $i;
                $data['inventory'][$i]['inventory_id'] = (int) $value->clinic_pharmacy_inventory_inward_id;
                $data['inventory'][$i]['trade_name'] = $drugInfo->trade_name;
                $data['inventory'][$i]['batch_no'] = $value->batch_no;
                $data['inventory'][$i]['expiry_date'] = $value->expiry_date;
                $data['inventory'][$i]['quantity'] = (int) $value->remaining_quantity;
                $i++;
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function shortage_get($clinic_id) {
        $checkInventory = $this->DefaultModel->getJoinRecords('clinic_pharmacy_inventory_inward cpi', 'clinic_pharmacy_inventory cp', 'cpi.clinic_pharmacy_inventory_id = cp.clinic_pharmacy_inventory_id', array('cpi.clinic_id' => $clinic_id), '', 'cpi.*,cp.reorder_level,cp.vendor_id');
        if(count($checkInventory) > 0) {
            $i = 0;
            foreach($checkInventory as $value) {
                if($value->remaining_quantity <= $value->reorder_level) {
                    $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id));
                    if(is_null($drugInfo)){
                        continue;
                    }
                    $data['inventory'][$i]['id'] = (int) $i+1;
                    $data['inventory'][$i]['inventory_id'] = (int) $value->clinic_pharmacy_inventory_inward_id;
                    $data['inventory'][$i]['trade_name'] = $drugInfo->trade_name;
                    $data['inventory'][$i]['batch_no'] = $value->batch_no;
                    $data['inventory'][$i]['expiry_date'] = $value->expiry_date;
                    $data['inventory'][$i]['status'] = $value->status;
                    $data['inventory'][$i]['quantity'] = (int) $value->remaining_quantity;
                    $data['inventory'][$i]['vendor_id'] = $value->vendor_id;
                    $i++;
                }
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

}