<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class Orders extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function ordersList_get($clinic_id){
        if(isset($_POST)){
            $ordersInfo = $this->DefaultModel->getAllRecords("billing", array('billing_type'=>'Pharmacy', 'clinic_id'=>$clinic_id));
            if(count($ordersInfo) > 0){
                $i = 0;
                foreach($ordersInfo as $value){
                    // $lineItems = $this->DefaultModel->getAllRecords('billing_line_items', array('billing_id' => $value->billing_id));
                    $data['ordersData'][$i]['id'] = $i+1;
                    $data['ordersData'][$i]['billing_id'] = $value->billing_id;
                    $data['ordersData'][$i]['guest_name'] = $value->guest_name;
                    $data['ordersData'][$i]['mobile'] = DataCrypt($value->guest_mobile, 'decrypt');
                    $data['ordersData'][$i]['total_amount'] = number_format($value->total_amount, 2);
                    $data['ordersData'][$i]['billing_amount'] = number_format($value->billing_amount, 2);
                    $data['ordersData'][$i]['gender'] = $value->gender;
                    $data['ordersData'][$i]['invoice_no'] = $value->invoice_no;
                    $data['ordersData'][$i]['payment_mode'] = $value->payment_mode;
                    $data['ordersData'][$i]['transaction_id'] = $value->transaction_id;
                    $data['ordersData'][$i]['age'] = $value->age." ".$value->age_unit;
                    $data['ordersData'][$i]['order_date'] = date("d,M Y h:i A", strtotime($value->billing_date_time));
                    $i++;
                }  
                $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));              
            }
        }
    }

    public function getInventoryDrugs_get($clinic_id, $drug) {
        $thisMonth = date("Y-m-"). "01";
        $checkInventory = $this->DefaultModel->getAllRecords('clinic_pharmacy_inventory_inward', array('clinic_id' => $clinic_id, 'expiry_date > ' => $thisMonth, 'drug_name LIKE ' => '%'. $drug .'%'));
        if(count($checkInventory) > 0) {
            $i = 0;
            foreach($checkInventory as $value) {
                $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id), '', 'trade_name, formulation');
                $clinicDrugInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory', array('drug_id' => $value->drug_id, 'clinic_id' => $clinic_id));
                $data['inventory'][$i]['clinic_pharmacy_inventory_inward_id'] = $value->clinic_pharmacy_inventory_inward_id;
                $data['inventory'][$i]['drug_id'] = $value->drug_id;
                $data['inventory'][$i]['batch_no'] = $value->batch_no;
                $data['inventory'][$i]['clinic_pharmacy_inventory_inward_id'] = $value->clinic_pharmacy_inventory_inward_id;
                $data['inventory'][$i]['drug_name'] = $drugInfo->trade_name;
                $data['inventory'][$i]['formulation'] = $drugInfo->formulation;
                $data['inventory'][$i]['available_qty'] = $value->remaining_quantity;
                $data['inventory'][$i]['cost'] = number_format($value->mrp/$value->pack_size, 2);
                $data['inventory'][$i]['mrp'] = $value->mrp;
                $data['inventory'][$i]['cgst'] = $clinicDrugInfo->cgst;
                $data['inventory'][$i]['igst'] = $clinicDrugInfo->igst;
                $data['inventory'][$i]['sgst'] = $clinicDrugInfo->sgst;
                $data['inventory'][$i]['max_disc'] = $clinicDrugInfo->max_discount_percentage;
                $data['inventory'][$i]['pack_size'] = $value->pack_size;
                $i++;
            }
            $this->response(array('code' => '200', 'message' => 'Success', 'result' => $data));
        }
        else {
            $this->response(array('code' => '201', 'message' => 'No Data Found'));
        }
    }

    public function generateOrder_post() {
        extract($_POST);
        $invoice_no = generateInvoice("Pharmacy", $clinic_id);

        $data['clinic_id'] = $clinic_id;
        $data['invoice_no'] = $invoice_no;
        $data['invoice_no_alias'] = $invoice_no;
        $data['guest_name'] = $customer_name;
        $data['guest_mobile'] = DataCrypt($mobile, 'encrypt');
        $data['gender'] = $gender;
        $data['age'] = $age;
        $data['age_unit'] = $age_unit;
        $data['billing_type'] = "Pharmacy";
        $data['payment_mode'] = $payment_mode;
        $data['transaction_id'] = $transactionID;
        $data['billing_date_time'] = date("Y-m-d H:i:s");
        $data['created_by'] = $user_id;
        $data['created_date_time'] = date("Y-m-d H:i:s");
        $billing_id = $this->DefaultModel->insertDataReturnId('billing', $data);
        $drugs = json_decode($drugFields);
        $total = $amount = 0;
        foreach($drugs as $value) {
            if($value->drug_id != "") {
                $drugInfo = $this->DefaultModel->getSingleRecord('drug', array('drug_id' => $value->drug_id), '', 'trade_name');
                $clinicDrugInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory_inward', array('clinic_id'=>$clinic_id, 'drug_id'=>$value->drug_id, 'batch_no'=>$value->batch_no), '', 'clinic_pharmacy_inventory_inward_id');
                // Inward Information
                $lineItems['billing_id'] = $billing_id;
                $lineItems['item_information'] = $drugInfo->trade_name;
                $lineItems['batch_no'] = $value->batch_no;
                $lineItems['drug_id'] = $value->drug_id;
                $lineItems['unit_price'] = $value->unit_price;
                $lineItems['quantity'] = $value->qty;
                $lineItems['amount'] = $value->disc_amount;
                $lineItems['total_amount'] = $value->amount;
                $lineItems['discount'] = $value->discount;
                $lineItems['discount_unit'] = "%";
                $lineItems['cgst'] = $value->cgst;
                $lineItems['igst'] = $value->igst;
                $lineItems['sgst'] = $value->sgst;
                $lineItems['created_by'] = $user_id;
                $lineItems['created_date_time'] = date("Y-m-d H:i:s");
                $lineItems['modified_by'] = $user_id;
                $lineItems['modified_date_time'] = date("Y-m-d H:i:s");
                $this->DefaultModel->insertData('billing_line_items', $lineItems);
                // Outward Information
                $outward['clinic_pharmacy_inventory_inward_id'] = $clinicDrugInfo->clinic_pharmacy_inventory_inward_id;
                $outward['clinic_id'] = $clinic_id;
                $outward['drug_id'] = $value->drug_id;
                $outward['batch_no'] = $value->batch_no;
                $outward['billing_id'] = $billing_id;
                $outward['outward_date'] = date("Y-m-d");
                $outward['quantity'] = $value->qty;
                $outward['created_by'] = $user_id;
                $outward['created_date_time'] = date("Y-m-d H:i:s");
                $outward['modified_by'] = $user_id;
                $outward['modified_date_time'] = date("Y-m-d H:i:s");
                $this->DefaultModel->insertData('clinic_pharmacy_inventory_outward', $outward);
                $amount += $value->disc_amount;
                $total += $value->amount;
                $this->decreaseQty($clinicDrugInfo->clinic_pharmacy_inventory_inward_id, $value->qty);
            }
        }
        $modData['billing_amount'] = $amount;
        $modData['total_amount'] = $total;
        $this->DefaultModel->updateData('billing', $modData, array('billing_id'=>$billing_id));
        $this->response(array('code' => '200', 'message' => 'Success', 'result' => $billing_id));
    }

    public function decreaseQty($clinic_pharmacy_inventory_inward_id, $qty) {
        $clinicDrugInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory_inward', array('clinic_pharmacy_inventory_inward_id'=>$clinic_pharmacy_inventory_inward_id));
        $data['remaining_quantity'] = $clinicDrugInfo->quantity - $qty;
        $this->DefaultModel->updateData('clinic_pharmacy_inventory_inward', $data, array('clinic_pharmacy_inventory_inward_id'=>$clinic_pharmacy_inventory_inward_id));
    }

}