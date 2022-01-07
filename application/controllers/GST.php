<?php 
require(APPPATH.'libraries/Rest_Controller.php');
 
class GST extends Rest_Controller {

    public function __construct() {
        parent::__construct();        
    }

    public function gstNotPaid_get($clinic_id) {

        $minDate = $this->DefaultModel->getSingleRecord('billing', array('clinic_id' => $clinic_id, 'billing_type'=>'Pharmacy'), '', 'min(billing_date_time) as minDate');
        $gstFiledDates = $this->DefaultModel->getSingleRecord('billing', array('clinic_id' => $clinic_id, 'gst_status' => 1,'billing_type'=>'Pharmacy'), '', 'min(billing_date_time) as minDate');
        $gstNotDates = $this->DefaultModel->getSingleRecord('billing', array('clinic_id' => $clinic_id, 'gst_status' => 0,'billing_type'=>'Pharmacy'), '', 'min(billing_date_time) as minDate, max(billing_date_time) as maxDate');
        $billInfo = $this->DefaultModel->getJoinRecords('billing b', 'billing_line_items bl', 'b.billing_id = bl.billing_id', array('b.clinic_id' => $clinic_id, 'b.billing_type' => 'Pharmacy'), '','b.gst_status, bl.drug_id, bl.batch_no, bl.quantity, bl.amount, bl.cgst, bl.sgst, bl.igst, b.billing_date_time','b.billing_id ASC');
        if(count($billInfo) > 0) {
            $total = 0;$price = 0;$amount = 0;$gstPaid = 0;$gstNotPaid = 0;$gst = 0;$profit = 0;$paidTotal = 0;$notPaid = 0;
            $data['profitMinDate'] = date("M'd Y", strtotime($billInfo[0]->billing_date_time));
            foreach($billInfo as $value) {
                $unit_price = 0;
                $inwardInfo = $this->DefaultModel->getSingleRecord('clinic_pharmacy_inventory_inward', array('clinic_id'=>$clinic_id,'drug_id'=>$value->drug_id,'batch_no'=>$value->batch_no), '', 'purchase_price, mrp, pack_size');
                $unit_price = $inwardInfo->purchase_price / $inwardInfo->pack_size;
                $price = $value->quantity * $unit_price;
                $amount += $value->amount;
                $total += $price;
                $a = 0;$taxValue = 0;$cgst = $sgst = $igst = 0;$gst = 0;
                if($value->gst_status == 1) {
                    $a = $value->amount - $price;
                    $profit += $a;
                    
                    $taxValue = ($a * 100) / (100 + $value->cgst + $value->sgst + $value->igst);
                    $cgst = round($taxValue * ($value->cgst / 100), 2);
                    $sgst = round($taxValue * ($value->sgst / 100), 2);
                    $igst = round($taxValue * ($value->igst / 100), 2);
                    
                    $gst = $cgst + $igst + $sgst;
                    $gstPaid += $gst;
                }
                else {
                    $a = $value->amount - $price;
                    $profit += $a;
    
                    
                    $taxValue = ($a * 100) / (100 + $value->cgst + $value->sgst + $value->igst);
                    $cgst = round($taxValue * ($value->cgst / 100), 2);
                    $sgst = round($taxValue * ($value->sgst / 100), 2);
                    $igst = round($taxValue * ($value->igst / 100), 2);
                    
                    $gst = $cgst + $igst + $sgst;
                    $gstNotPaid += $gst;
                }
            }
            $data['profit'] = number_format($profit, 2);
            $data['gstTobePaid'] = number_format($gstNotPaid, 2);
            $data['gstTobePaidDateMin'] = date("M'd Y", strtotime($gstNotDates->minDate));
            $data['gstPaid'] = number_format($gstPaid, 2);
            $data['gstPaidDateMin'] = date("M'd Y", strtotime($gstNotDates->minDate));
            $data['gstPaidDateMax'] = date("M'd Y", strtotime($gstNotDates->maxDate));
            $this->response(array('code'=>'200','message'=>'success','result'=>$data));
        }
    }

    public function clearGst_get($clinic_id) {
        $check = $this->DefaultModel->getSingleRecord('billing', array('clinic_id'=>$clinic_id,'billing_type'=>'Pharmacy'));
        if(is_null($check)) {
            $this->response(array('code'=>'201','message'=>'No Items Found for GST'));    
        }
        else {
            $data['gst_status'] = 1;
            $data['gst_cleared_date'] = date("Y-m-d");
            $this->DefaultModel->updateData('billing', $data, array('clinic_id' => $clinic_id, 'billing_type' => 'Pharmacy'));
            $this->response(array('code'=>'200','message'=>'Success'));
        }        
    }

}