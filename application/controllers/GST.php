<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class GST extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function gstNotPaid_get($clinic_id) {
        $billInfo = $this->DefaultModel->getJoinRecords('billing b', 'billing_line_items bl', 'b.billing_id = bl.billing_id', 'b.clinic_id="' . $clinic_id . '"', '', 'b.gst_status, bl.drug_id, bl.batch_no, bl.quantity, bl.amount, bl.cgst, bl.sgst, bl.igst');
        if(count($billInfo) > 0) {
            $total = 0;$price = 0;$amount = 0;$gstTotal = 0;$gst = 0;$profit = 0;
            foreach($billInfo as $value) {
                $unit_price = 0;
                $inwardInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory_inward', array('clinic_id'=>$clinic_id,'drug_id'=>$value->drug_id,'batch_no'=>$value->batch_no), '', 'purchase_price, mrp, pack_size');
                $unit_price = $inwardInfo->purchase_price / $inwardInfo->pack_size;
                $price = $value->quantity * $unit_price;
                $amount += $value->amount;
                $total += $price;

                $a = $value->amount - $price;
                $profit += $a;
                
                $taxValue = ($a * 100) / (100 + $value->cgst + $value->sgst + $value->igst);
                $cgst = round($taxValue * ($value->cgst / 100), 2);
                $sgst = round($taxValue * ($value->sgst / 100), 2);
                $igst = round($taxValue * ($value->igst / 100), 2);
                $gst = $cgst + $igst + $sgst;
                $gstTotal += $gst;
            }
            $data['profit'] = number_format($profit, 2);
            $data['gstTobePaid'] = number_format($gstTotal, 2);
            $this->response(array('code'=>'200','message'=>'success','result'=>$data));
        }
    }

}