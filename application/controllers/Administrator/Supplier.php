<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Supplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $access = $this->session->userdata('userId');
        $this->brunch = $this->session->userdata('BRANCHid');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model("Model_myclass", "mmc", TRUE);
        $this->load->model('Model_table', "mt", TRUE);
    }
    public function index()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Supplier";
        $data['supplierCode'] = $this->mt->generateSupplierCode();
        $data['content'] = $this->load->view('Administrator/add_supplier', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function addSupplier()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $supplierObj = json_decode($this->input->post('data'));
            $supplierCodeCount = $this->db->query("select * from tbl_supplier where Supplier_Code = ?", $supplierObj->Supplier_Code)->num_rows();
            if ($supplierCodeCount > 0) {
                $supplierObj->Supplier_Code = $this->mt->generateSupplierCode();
            }

            $supplier = (array)$supplierObj;
            unset($supplier['Supplier_SlNo']);
            $supplier["branch_id"] = $this->session->userdata("BRANCHid");

            $supplierId = null;
            $res_message = "";

            $supplierMobileCount = $this->db->query("select * from tbl_supplier where Supplier_Mobile = ? and branch_id = ?", [$supplierObj->Supplier_Mobile, $this->session->userdata("BRANCHid")]);
            if ($supplierMobileCount->num_rows() > 0) {
                $duplicateSupplier = $supplierMobileCount->row();

                unset($supplier['Supplier_Code']);
                $supplier["status"]         = 'a';
                $supplier["UpdateBy"]       = $this->session->userdata("userId");
                $supplier["UpdateTime"]     = date("Y-m-d H:i:s");
                $supplier["last_update_ip"] = get_client_ip();
                $this->db->where('Supplier_SlNo', $duplicateSupplier->Supplier_SlNo)->update('tbl_supplier', $supplier);

                $supplierId = $duplicateSupplier->Supplier_SlNo;
                $supplierObj->Supplier_Code = $duplicateSupplier->Supplier_Code;
                $res_message = 'Supplier updated successfully';
            } else {

                $supplier["status"]         = 'a';
                $supplier["AddBy"] = $this->session->userdata("userId");
                $supplier["AddTime"] = date("Y-m-d H:i:s");
                $supplier["last_update_ip"] = get_client_ip();
                $this->db->insert('tbl_supplier', $supplier);

                $supplierId = $this->db->insert_id();
                $res_message = 'Supplier added successfully';
            }

            if (!empty($_FILES)) {
                $imagePath = $this->mt->uploadImage($_FILES, 'image', 'uploads/suppliers', $supplier['Supplier_Name']);
                $this->db->query("update tbl_supplier set image_name = ? where Supplier_SlNo = ?", [$imagePath, $supplierId]);
            }

            $res = ['success' => true, 'message' => $res_message, 'supplierCode' => $this->mt->generateSupplierCode()];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function updateSupplier()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $supplierObj = json_decode($this->input->post('data'));
            $supplierMobileCount = $this->db->query("select * from tbl_supplier where Supplier_Mobile = ? and Supplier_SlNo != ? and branch_id = ?", [$supplierObj->Supplier_Mobile, $supplierObj->Supplier_SlNo, $this->session->userdata("BRANCHid")])->num_rows();
            if ($supplierMobileCount > 0) {
                $res = ['success' => false, 'message' => 'Mobile number already exists'];
                echo Json_encode($res);
                exit;
            }
            $supplier = (array)$supplierObj;
            $supplierId = $supplierObj->Supplier_SlNo;

            unset($supplier["Supplier_SlNo"]);
            $supplier["branch_id"] = $this->session->userdata("BRANCHid");
            $supplier["UpdateBy"] = $this->session->userdata("userId");
            $supplier["UpdateTime"] = date("Y-m-d H:i:s");
            $supplier["last_update_ip"] = get_client_ip();

            $this->db->where('Supplier_SlNo', $supplierId)->update('tbl_supplier', $supplier);

            if (!empty($_FILES)) {
                $oldImgFile = $this->db->query("select image_name from tbl_supplier where Supplier_SlNo = ?", $supplierId)->row()->image_name;
                if (file_exists($oldImgFile)) {
                    unlink($oldImgFile);
                }
                $imagePath = $this->mt->uploadImage($_FILES, 'image', 'uploads/suppliers', $supplier['Supplier_Name']);
                $this->db->query("update tbl_supplier set image_name = ? where Supplier_SlNo = ?", [$imagePath, $supplierId]);
            }

            $res = ['success' => true, 'message' => 'Supplier updated successfully', 'supplierCode' => $this->mt->generateSupplierCode()];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function deleteSupplier()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $rules = array(
                'status'         => 'd',
                "DeletedBy"      => $this->session->userdata("userId"),
                "DeletedTime"    => date("Y-m-d H:i:s"),
                "last_update_ip" => get_client_ip()
            );

            $this->db->where("Supplier_SlNo", $data->supplierId);
            $this->db->update("tbl_supplier", $rules);

            $res = ['success' => true, 'message' => 'Supplier deleted'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }
    function supplier_due()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = 'Supplier Due';
        $data['content'] = $this->load->view('Administrator/due_report/supplier_due', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }


    public function supplierPaymentPage()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Supplier Payment";
        $data['content'] = $this->load->view('Administrator/due_report/supplierPaymentPage', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    function supplier_payment_history()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Supplier Payment History";
        $data['content'] = $this->load->view('Administrator/reports/supplier_payment_history', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    function supplier_payment_report()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Supplier Payment Reports";
        $data['content'] = $this->load->view('Administrator/payment_reports/supplier_payment_report', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getSuppliers()
    {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = "";
        $limit = "";
        $status = "a";
        if (isset($data->status) && $data->status != '') {
            $status = $data->status;
        }
        if (isset($data->forSearch) && $data->forSearch != '') {
            $limit .= "limit 20";
        }
        if (isset($data->name) && $data->name != '') {
            $clauses .= "and (s.Supplier_Code like '%$data->name%' or s.Supplier_Name like '%$data->name%' or s.Supplier_Mobile like '%$data->name%')";
        }

        $suppliers = $this->db->query("
            select 
            s.*,
            concat(s.Supplier_Name, ' - ', s.Supplier_Code, ' - ', s.Supplier_Mobile) as display_name
            from tbl_supplier s
            where s.status = '$status'
            and s.Supplier_Type != 'G'
            and s.branch_id = ? or branch_id = 0
            $clauses
            order by s.Supplier_SlNo desc
            $limit
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($suppliers);
    }

    public function getSupplierDue()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->supplierId) && $data->supplierId != null) {
            $clauses = " and s.Supplier_SlNo = '$data->supplierId'";
        }
        $supplierDues = $this->mt->supplierDue($clauses);

        echo json_encode($supplierDues);
    }

    public function getSupplierLedger()
    {
        $data = json_decode($this->input->raw_input_stream);
        $previousDueQuery = $this->db->query("select ifnull(previous_due, 0.00) as previous_due from tbl_supplier where Supplier_SlNo = '$data->supplierId'")->row();
        $payments = $this->db->query("
            select
                'a' as sequence,
                pm.PurchaseMaster_SlNo as id,
                pm.PurchaseMaster_OrderDate date,
                concat('Purchase ', pm.PurchaseMaster_InvoiceNo) as description,
                pm.PurchaseMaster_TotalAmount as bill,
                pm.PurchaseMaster_PaidAmount as paid,
                (pm.PurchaseMaster_TotalAmount - pm.PurchaseMaster_PaidAmount) as due,
                0.00 as returned,
                0.00 as cash_received,
                0.00 as balance
            from tbl_purchasemaster pm
            where pm.Supplier_SlNo = '$data->supplierId'
            and pm.status = 'a'
            
            UNION
            select
                'b' as sequence,
                sp.SPayment_id as id,
                sp.SPayment_date as date,
                concat('Paid - ', 
                    case sp.SPayment_Paymentby
                        when 'bank' then concat('Bank - ', ba.account_name, ' - ', ba.account_number, ' - ', ba.bank_name)
                        else 'Cash'
                    end, ' ', sp.SPayment_notes
                ) as description,
                0.00 as bill,
                sp.SPayment_amount as paid,
                0.00 as due,
                0.00 as returned,
                0.00 as cash_received,
                0.00 as balance
            from tbl_supplier_payment sp 
            left join tbl_bank_accounts ba on ba.account_id = sp.account_id
            where sp.SPayment_customerID = '$data->supplierId'
            and sp.SPayment_TransactionType = 'CP'
            and sp.status = 'a'
            
            UNION
            select 
                'c' as sequence,
                sp2.SPayment_id as id,
                sp2.SPayment_date as date,
                concat('Received - ', 
                    case sp2.SPayment_Paymentby
                        when 'bank' then concat('Bank - ', ba.account_name, ' - ', ba.account_number, ' - ', ba.bank_name)
                        else 'Cash'
                    end, ' ', sp2.SPayment_notes
                ) as description,
                0.00 as bill,
                0.00 as paid,
                0.00 as due,
                0.00 as returned,
                sp2.SPayment_amount as cash_received,
                0.00 as balance
            from tbl_supplier_payment sp2
            left join tbl_bank_accounts ba on ba.account_id = sp2.account_id
            where sp2.SPayment_customerID = '$data->supplierId'
            and sp2.SPayment_TransactionType = 'CR'
            and sp2.status = 'a'
            
            UNION
            select
                'd' as sequence,
                pr.PurchaseReturn_SlNo as id,
                pr.PurchaseReturn_ReturnDate as date,
                'Purchase Return' as description,
                0.00 as bill,
                0.00 as paid,
                0.00 as due,
                pr.PurchaseReturn_ReturnAmount as returned,
                0.00 as cash_received,
                0.00 as balance
            from tbl_purchasereturn pr
            where pr.Supplier_IDdNo = '$data->supplierId'
            and pr.status = 'a'
            
            order by date, sequence, id
        ")->result();

        $previousBalance = $previousDueQuery->previous_due;

        foreach ($payments as $key => $payment) {
            $lastBalance = $key == 0 ? $previousDueQuery->previous_due : $payments[$key - 1]->balance;
            $payment->balance = ($lastBalance + $payment->bill + $payment->cash_received) - ($payment->paid + $payment->returned);
        }

        if ((isset($data->dateFrom) && $data->dateFrom != null) && (isset($data->dateTo) && $data->dateTo != null)) {
            $previousPayments = array_filter($payments, function ($payment) use ($data) {
                return $payment->date < $data->dateFrom;
            });

            $previousBalance = count($previousPayments) > 0 ? $previousPayments[count($previousPayments) - 1]->balance : $previousBalance;

            $payments = array_filter($payments, function ($payment) use ($data) {
                return $payment->date >= $data->dateFrom && $payment->date <= $data->dateTo;
            });
        }

        $payments = array_values($payments);
        if (!empty($data->recordType) && $data->recordType == 'with_details') {
            foreach ($payments as $key => $payment) {
                if ($payment->sequence == 'a' && !empty($payment->id)) {
                    $payment->details = $this->db->query("select
                                        p.Product_Code,
                                        p.Product_Name,
                                        pd.PurchaseDetails_TotalQuantity as quantity,
                                        pd.PurchaseDetails_Rate as purchase_rate,
                                        pd.PurchaseDetails_TotalAmount as total
                                        from tbl_purchasedetails pd
                                        left join tbl_product p on p.Product_SlNo = pd.Product_IDNo
                                        where pd.status = 'a'
                                        and pd.PurchaseMaster_IDNo = ?", $payment->id)->result();
                }
                if ($payment->sequence == 'd' && !empty($payment->id)) {
                    $payment->details = $this->db->query("select
                                        p.Product_Code,
                                        p.Product_Name,
                                        prd.PurchaseReturnDetails_ReturnQuantity as quantity,
                                        (prd.PurchaseReturnDetails_ReturnAmount / prd.PurchaseReturnDetails_ReturnQuantity) as purchase_rate,
                                        prd.PurchaseReturnDetails_ReturnAmount as total
                                        from tbl_purchasereturndetails prd
                                        left join tbl_product p on p.Product_SlNo = prd.PurchaseReturnDetailsProduct_SlNo
                                        where prd.status = 'a'
                                        and prd.PurchaseReturn_SlNo = ?", $payment->id)->result();
                }
            }
        }

        $res['previousBalance'] = $previousBalance;
        $res['payments'] = $payments;
        echo json_encode($res);
    }

    public function getSupplierPayments()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        $status = "a";
        if (isset($data->status) && $data->status != '') {
            $status = $data->status;
        }
        if (isset($data->paymentType) && $data->paymentType != '' && $data->paymentType == 'received') {
            $clauses = " and sp.SPayment_TransactionType = 'CR'";
        }
        if (isset($data->paymentType) && $data->paymentType != '' && $data->paymentType == 'paid') {
            $clauses = " and sp.SPayment_TransactionType = 'CP'";
        }

        if (!empty($data->supplierId)) {
            $clauses .= " and sp.SPayment_customerID = '$data->supplierId'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sp.SPayment_date between '$data->dateFrom' and '$data->dateTo'";
        }

        $payments = $this->db->query("
            select
                sp.*,
                s.Supplier_Code,
                s.Supplier_Name,
                s.Supplier_Mobile,
                ba.account_name,
                ba.account_number,
                ba.bank_name,
                case sp.SPayment_TransactionType
                when 'CR' then 'Received'
                    when 'CP' then 'Paid'
                end as transaction_type,
                case sp.SPayment_Paymentby
                    when 'bank' then concat('Bank - ', ba.account_name, ' - ', ba.account_number, ' - ', ba.bank_name)
                    else 'Cash'
                end as payment_by,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_supplier_payment sp
            left join tbl_bank_accounts ba on ba.account_id = sp.account_id
            left join tbl_supplier s on s.Supplier_SlNo = sp.SPayment_customerID
            left join tbl_user ua on ua.User_SlNo = sp.AddBy
            left join tbl_user ud on ud.User_SlNo = sp.DeletedBy
            where sp.status = '$status'
            and sp.branch_id = ? $clauses
            order by sp.SPayment_id desc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($payments);
    }

    public function addSupplierPayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);

            $payment = (array)$paymentObj;
            $payment['SPayment_invoice'] = $this->mt->generateSupplierPaymentCode();
            $payment['status'] = 'a';
            $payment['Addby'] = $this->session->userdata("userId");
            $payment['AddTime'] = date('Y-m-d H:i:s');
            $payment['last_update_ip'] = get_client_ip();
            $payment['branch_id'] = $this->session->userdata("BRANCHid");

            $this->db->insert('tbl_supplier_payment', $payment);
            $paymentId = $this->db->insert_id();

            $res = ['success' => true, 'message' => 'Payment added successfully', 'paymentId' => $paymentId];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function updateSupplierPayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);
            $paymentId = $paymentObj->SPayment_id;

            $payment = (array)$paymentObj;
            unset($payment['SPayment_id']);
            $payment['UpdateBy'] = $this->session->userdata("userId");
            $payment['UpdateTime'] = date('Y-m-d H:i:s');
            $payment['last_update_ip'] = get_client_ip();

            $this->db->where('SPayment_id', $paymentObj->SPayment_id)->update('tbl_supplier_payment', $payment);

            $res = ['success' => true, 'message' => 'Payment updated successfully', 'paymentId' => $paymentId];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function deleteSupplierPayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $payment = array(
                'status'         => 'd',
                "DeletedBy"      => $this->session->userdata("userId"),
                "DeletedTime"    => date("Y-m-d H:i:s"),
                "last_update_ip" => get_client_ip()
            );
            $this->db->set($payment)->where('SPayment_id', $data->paymentId)->update('tbl_supplier_payment');

            $res = ['success' => true, 'message' => 'Payment deleted successfully'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }
}
