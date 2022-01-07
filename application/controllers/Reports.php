<?php
require(APPPATH.'libraries/Rest_Controller.php');

class Reports extends Rest_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function latestDrugs_get($clinic_id) {
        $thisMonth = date("Y-m");
        $drugsInfo = $this->DefaultModel->getAllRecords('clinic_pharmacy_inventory_inward', array('clinic_id' => $clinic_id, 'created_date_time LIKE' => $thisMonth . '%'));
        if(count($drugsInfo) > 0) {
            $i = 0;
            foreach($drugsInfo as $value) {
                $data['drugs'][$i]['cpii_id'] = $value->clinic_pharmacy_inventory_inward_id;
                $data['drugs'][$i]['drug_id'] = $value->drug_id;
                $data['drugs'][$i]['drug_name'] = $value->drug_name;
                $data['drugs'][$i]['batch_no'] = $value->batch_no;
                $data['drugs'][$i]['quantity'] = $value->remaining_quantity;
                $data['drugs'][$i]['expiry_date'] = date("M'd Y", strtotime($value->expiry_date));
                $i++;
            }
        }
        else {
            $data['drugs'] = [];
        }
        $this->response(array('code'=>'200','message'=>'Success','result'=>$data));
    }

    public function getSales_get($clinic_id) {
        $out_peopleTotal = $this->DefaultModel->getRecordsSum('billing', 'total_amount',array('clinic_id' => $clinic_id, 'patient_prescription_id' => 0));
        $out_peopleDisc = $this->DefaultModel->getRecordsSum('billing', 'billing_amount',array('clinic_id' => $clinic_id, 'patient_prescription_id' => 0));
        $convertedTotal =  $this->DefaultModel->getRecordsSum('billing', 'total_amount',array('clinic_id' => $clinic_id, 'patient_prescription_id !=' => 0));
        $convertedDisc =  $this->DefaultModel->getRecordsSum('billing', 'billing_amount',array('clinic_id' => $clinic_id, 'patient_prescription_id !=' => 0));

        $outpeopleAmount = $out_peopleDisc->billing_amount;
        $convertedAmount = $convertedDisc->billing_amount;

        $outDisc = $out_peopleTotal->total_amount - $out_peopleDisc->billing_amount;
        $convertedDisc = $convertedTotal->total_amount - $convertedDisc->billing_amount;

        // Profit Calculation
        $billInfo = $this->DefaultModel->getJoinRecords('billing b', 'billing_line_items bl', 'b.billing_id = bl.billing_id', array('b.clinic_id' => $clinic_id, 'b.billing_type' => 'Pharmacy'), '','bl.amount,bl.quantity,bl.drug_id,bl.batch_no','');
        if(count($billInfo) > 0) {
            $profit = 0;
            foreach($billInfo as $value) {
                $inwardInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory_inward', array('clinic_id'=>$clinic_id,'drug_id'=>$value->drug_id,'batch_no'=>$value->batch_no), '', 'purchase_price, mrp, pack_size');
                $unit_price = $inwardInfo->purchase_price / $inwardInfo->pack_size;
                $price = $value->quantity * $unit_price;
                $profit += $value->amount - $price;
            }
            $data['profit'] =  number_format($profit, 2);
        }


        $data['out_people'] = number_format($outpeopleAmount, 2);
        $data['out_disc'] = number_format($outDisc, 2);
        $data['converted'] = number_format($convertedAmount, 2);
        $data['converted_disc'] = number_format($convertedDisc, 2);
        $data['overall'] = number_format($convertedAmount + $outpeopleAmount, 2);
        $this->response(array('code'=>'200','message'=>'Success','result'=>$data));
    }

}

?>