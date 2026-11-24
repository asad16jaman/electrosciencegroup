<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class WorkOrder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->sbrunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model('Billing_model');
        $this->load->library('cart');
        $this->load->model('Model_table', "mt", TRUE);
        $this->load->helper('form');
        $this->load->model('SMS_model', 'sms', true);

        if (!$this->db->field_exists('SaleMaster_InvoiceText', 'tbl_salesmaster')) {
            if ($this->db->field_exists('invoice_text', 'tbl_salesmaster')) {
                $this->db->query("ALTER TABLE tbl_salesmaster CHANGE invoice_text SaleMaster_InvoiceText TEXT NULL");
            } else {
                $this->db->query("ALTER TABLE tbl_salesmaster ADD SaleMaster_InvoiceText TEXT NULL AFTER SaleMaster_Description");
            }
        }
    }

    public function index()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['title'] = "Product Work Order";

        $invoice = $this->mt->generateSalesInvoice();
        $data['salesId'] = 0;
        $data['invoice'] = $invoice;
        $data['content'] = $this->load->view('Administrator/workOrder/create', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function edit($salesId)
    {
        $data['title'] = "Work Order Update";
        $sales = $this->db->query("select * from tbl_salesmaster where SaleMaster_SlNo = ?", $salesId)->row();
        $data['salesId'] = $salesId;
        $data['invoice'] = $sales->SaleMaster_InvoiceNo;
        $data['content'] = $this->load->view('Administrator/workOrder/create', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function store()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);
            $isProjectOrder = isset($data->sales->salesType) && $data->sales->salesType == 'project';

            if ($data->sales->salesFrom != $this->session->userdata("BRANCHid")) {
                $res = ['success' => false, 'message' => 'You have already changed your branch.', 'branch_status' => false];
                echo json_encode($res);
                exit;
            }
            $invoice = $data->sales->invoiceNo;
            $invoiceCount = $this->db->query("select * from tbl_salesmaster where SaleMaster_InvoiceNo = ?", $invoice)->num_rows();
            if ($invoiceCount != 0) {
                $invoice = $this->mt->generateSalesInvoice();
            }

            $customerId = $data->sales->customerId;

            if (!$isProjectOrder && isset($data->customer) && $data->customer->Customer_Type == 'N') {
                $customer = (array)$data->customer;
                unset($customer['Customer_SlNo']);
                unset($customer['display_name']);
                unset($customer['Customer_Type']);
                $mobile_count = $this->db->query("select * from tbl_customer where Customer_Mobile = ? and branch_id = ?", [$data->customer->Customer_Mobile, $this->session->userdata("BRANCHid")])->row();

                if ($data->customer->Customer_Type == 'N' && empty($mobile_count)) {
                    $customer['Customer_Code'] = $this->mt->generateCustomerCode();
                    $customer['Customer_Type'] = $data->sales->salesType;
                    $customer['Customer_Credit_Limit'] = $data->sales->total;
                    $customer['status'] = 'a';
                    $customer['AddBy'] = $this->session->userdata("userId");
                    $customer['AddTime'] = date("Y-m-d H:i:s");
                    $customer['last_update_ip'] = get_client_ip();
                    $customer['branch_id'] = $this->session->userdata("BRANCHid");
                    $this->db->insert('tbl_customer', $customer);
                    $customerId = $this->db->insert_id();
                }else {
                    $res = ['success' => false, 'message' => 'Customer mobile number already exist!'];
                    echo json_encode($res);
                    exit;
                }
            }

            $sales = array(
                'SaleMaster_InvoiceNo'           => $invoice,
                'project_id'                     => $data->sales->project_id,
                'employee_id'                    => $data->sales->employeeId,
                'SaleMaster_SaleDate'            => $data->sales->salesDate,
                'customerType'                   => $data->sales->salesType,
                'SalseCustomer_IDNo'             => $customerId,
                'SaleMaster_SaleType'            => $data->sales->salesType,
                'SaleMaster_TotalSaleAmount'     => $data->sales->total,
                'SaleMaster_TotalDiscountAmount' => $data->sales->discount,
                'SaleMaster_TaxAmount'           => $data->sales->vat,
                'SaleMaster_Freight'             => $data->sales->transportCost,
                'SaleMaster_SubTotalAmount'      => $data->sales->subTotal,
                'accountId'                      => empty($data->sales->accountId) ? NULL : $data->sales->accountId,
                'cashPaid'                       => $data->sales->cashPaid,
                'bankPaid'                       => $data->sales->bankPaid,
                'SaleMaster_PaidAmount'          => $data->sales->paid,
                'SaleMaster_DueAmount'           => $data->sales->due,
                'SaleMaster_Previous_Due'        => $data->sales->previousDue,
                'SaleMaster_Description'         => $data->sales->note,
                'SaleMaster_InvoiceText'         => $data->sales->invoiceText ?? '',
                'is_order'                       => 'yes',
                'status'                         => 'p',
                "AddBy"                          => $this->session->userdata("userId"),
                'AddTime'                        => date("Y-m-d H:i:s"),
                'last_update_ip'                 => get_client_ip(),
                'branch_id'                      => $this->session->userdata("BRANCHid")
            );

            $this->db->insert('tbl_salesmaster', $sales);

            $salesId = $this->db->insert_id();

            foreach ($data->cart as $cartProduct) {
                $saleDetails = array(
                    'SaleMaster_IDNo'           => $salesId,
                    'Product_IDNo'              => $cartProduct->productId,
                    'order_quantity'            => $cartProduct->quantity,
                    'store_quantity'            => 0,
                    'SaleDetails_TotalQuantity' => 0,
                    'Purchase_Rate'             => $cartProduct->purchaseRate,
                    'SaleDetails_Rate'          => $cartProduct->salesRate,
                    'SaleDetails_Tax'           => $cartProduct->vat,
                    'SaleDetails_TotalAmount'   => $cartProduct->total,
                    'is_service'                => $cartProduct->is_service,
                    'status'                    => 'p',
                    'AddBy'                     => $this->session->userdata("userId"),
                    'AddTime'                   => date('Y-m-d H:i:s'),
                    'last_update_ip'            => get_client_ip(),
                    'branch_id'                 => $this->session->userdata('BRANCHid')
                );

                $this->db->insert('tbl_saledetails', $saleDetails);
            }

            //Send sms
            if (!$isProjectOrder && !empty($data->customer->Customer_Mobile) && preg_match("/(^(01){1}[3456789]{1}(\d){8})$/", trim($data->customer->Customer_Mobile)) == 1) {
                $currentDue = $data->sales->previousDue + ($data->sales->total - $data->sales->paid);
                $customerInfo = $this->db->query("select * from tbl_customer where Customer_SlNo = ?", $customerId)->row();
                if (!empty($customerInfo)) {
                    $sendToName = $customerInfo->owner_name != '' ? $customerInfo->owner_name : $customerInfo->Customer_Name;
                } else {
                    $sendToName = $data->customer->Customer_Name;
                }
                $currency = $this->session->userdata('Currency_Name');

                $message = "Dear {$sendToName},\nYour invoice No. {$invoice}\nBill is {$currency} {$data->sales->total}\nReceived {$currency} {$data->sales->paid}\nCurrent due {$currency} {$currentDue}";
                $recipient = $data->customer->Customer_Mobile;
                $this->sms->sendSms($recipient, $message);
            }

            $this->db->trans_commit();
            $res = ['success' => true, 'message' => 'Work Order Success', 'salesId' => $salesId];
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function update()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);
            $isProjectOrder = isset($data->sales->salesType) && $data->sales->salesType == 'project';

            $salesId = $data->sales->salesId;
            $customerId = $data->sales->customerId;

            if (!$isProjectOrder && isset($data->customer) && $data->customer->Customer_Type == 'N') {
                $customer = (array)$data->customer;
                unset($customer['Customer_SlNo']);
                unset($customer['display_name']);
                unset($customer['Customer_Type']);
                $mobile_count = $this->db->query("select * from tbl_customer where Customer_Mobile = ? and branch_id = ?", [$data->customer->Customer_Mobile, $this->session->userdata("BRANCHid")])->row();

                if ($data->customer->Customer_Type == 'N' && empty($mobile_count)) {
                    $customer['Customer_Code'] = $this->mt->generateCustomerCode();
                    $customer['Customer_Type'] = $data->sales->salesType;
                    $customer['Customer_Credit_Limit'] = $data->sales->total;
                    $customer['status'] = 'a';
                    $customer['AddBy'] = $this->session->userdata("userId");
                    $customer['AddTime'] = date("Y-m-d H:i:s");
                    $customer['last_update_ip'] = get_client_ip();
                    $customer['branch_id'] = $this->session->userdata("BRANCHid");
                    $this->db->insert('tbl_customer', $customer);
                    $customerId = $this->db->insert_id();
                }else {
                    $res = ['success' => false, 'message' => 'Customer mobile number already exist!'];
                    echo json_encode($res);
                    exit;
                }
            }

            $sales = array(
                'project_id'                     => $data->sales->project_id,
                'employee_id'                    => $data->sales->employeeId,
                'SaleMaster_SaleDate'            => $data->sales->salesDate,
                'SaleMaster_SaleType'            => $data->sales->salesType,
                'customerType'                   => $data->sales->salesType,
                'SalseCustomer_IDNo'             => $customerId,
                'SaleMaster_TotalSaleAmount'     => $data->sales->total,
                'SaleMaster_TotalDiscountAmount' => $data->sales->discount,
                'SaleMaster_TaxAmount'           => $data->sales->vat,
                'SaleMaster_Freight'             => $data->sales->transportCost,
                'SaleMaster_SubTotalAmount'      => $data->sales->subTotal,
                'accountId'                      => empty($data->sales->accountId) ? NULL : $data->sales->accountId,
                'cashPaid'                       => $data->sales->cashPaid,
                'bankPaid'                       => $data->sales->bankPaid,
                'SaleMaster_PaidAmount'          => $data->sales->paid,
                'SaleMaster_DueAmount'           => $data->sales->due,
                'SaleMaster_Previous_Due'        => $data->sales->previousDue,
                'SaleMaster_Description'         => $data->sales->note,
                'SaleMaster_InvoiceText'         => $data->sales->invoiceText ?? '',
                "UpdateBy"                       => $this->session->userdata("userId"),
                'UpdateTime'                     => date("Y-m-d H:i:s"),
                'last_update_ip'                 => get_client_ip(),
                "branch_id"                      => $this->session->userdata("BRANCHid")
            );

            $this->db->where('SaleMaster_SlNo', $salesId);
            $this->db->update('tbl_salesmaster', $sales);

            $this->db->query("delete from tbl_saledetails where SaleMaster_IDNo = ?", $salesId);

            foreach ($data->cart as $cartProduct) {
                $saleDetails = array(
                    'SaleMaster_IDNo'           => $salesId,
                    'Product_IDNo'              => $cartProduct->productId,
                    'order_quantity'            => $cartProduct->quantity,
                    'store_quantity'            => 0,
                    'SaleDetails_TotalQuantity' => 0,
                    'Purchase_Rate'             => $cartProduct->purchaseRate,
                    'SaleDetails_Rate'          => $cartProduct->salesRate,
                    'SaleDetails_Tax'           => $cartProduct->vat,
                    'SaleDetails_TotalAmount'   => $cartProduct->total,
                    'is_service'                => $cartProduct->is_service,
                    'status'                    => 'p',
                    'UpdateBy'                  => $this->session->userdata("userId"),
                    'UpdateTime'                => date('Y-m-d H:i:s'),
                    'last_update_ip'            => get_client_ip(),
                    'branch_id'                 => $this->session->userdata("BRANCHid")
                );

                $this->db->insert('tbl_saledetails', $saleDetails);
            }
            $this->db->trans_commit();
            $res = ['success' => true, 'message' => 'Work Order Updated', 'salesId' => $salesId];
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function delete()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $saleId = $data->saleId;

            $sale = $this->db->select('*')->where('SaleMaster_SlNo', $saleId)->get('tbl_salesmaster')->row();
            if ($sale->status != 'p') {
                $res = ['success' => false, 'message' => 'Work Order not found'];
                echo json_encode($res);
                exit;
            }

            $returnCount = $this->db->query("select * from tbl_salereturn sr where sr.SaleMaster_InvoiceNo = ? and sr.status = 'a'", $sale->SaleMaster_InvoiceNo)->num_rows();

            if ($returnCount != 0) {
                $res = ['success' => false, 'message' => 'Unable to delete. return found'];
                echo json_encode($res);
                exit;
            }

            $sale = array(
                'status' => 'd',
                'DeletedBy' => $this->session->userdata('userId'),
                'DeletedTime' => date('Y-m-d H:i:s'),
                'last_update_ip' => get_client_ip(),
            );

            $this->db->set($sale)->where('SaleMaster_IDNo', $saleId)->update('tbl_saledetails');
            $this->db->set($sale)->where('SaleMaster_SlNo', $saleId)->update('tbl_salesmaster');
            $res = ['success' => true, 'message' => 'Work Order Deleted'];

        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function get()
    {
        $data = json_decode($this->input->raw_input_stream);
        $branchId = $this->session->userdata("BRANCHid");
        $clauses = "";
        $limit = "";

        if (isset($data->status) && $data->status != '') {
            $clauses .= " and sm.status = '$data->status'";
        } else {
            $clauses .= " and sm.status != 'd'";
        }

        if (isset($data->name) && $data->name != '') {
            $clauses .= " or sm.SaleMaster_InvoiceNo like '$data->name%'";
        }
        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sm.SaleMaster_SaleDate between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->userId) && $data->userId != '') {
            $clauses .= " and sm.AddBy = '$data->userId'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and sm.project_id = '$data->projectId'";
        }

        if (isset($data->customerId) && $data->customerId != '') {
            $clauses .= " and sm.SalseCustomer_IDNo = '$data->customerId'";
        }

        if (isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and sm.employee_id = '$data->employeeId'";
        }

        if (isset($data->customerType) && $data->customerType != '') {
            $clauses .= " and sm.customerType = '$data->customerType'";
        }

        if (isset($data->forSearch) && $data->forSearch != '') {
            $limit .= "limit 20";
        }

        if (isset($data->salesId) && $data->salesId != 0 && $data->salesId != '') {
            $clauses .= " and sm.SaleMaster_SlNo = '$data->salesId'";
            $saleDetails = $this->db->query("
                select 
                    sd.*,
                    (select sd.order_quantity - ifnull(sd.SaleDetails_TotalQuantity, 0)) as remaining_quantity,
                    p.Product_Code,
                    p.Product_Name,
                    pc.ProductCategory_Name,
                    u.Unit_Name
                from tbl_saledetails sd
                join tbl_product p on p.Product_SlNo = sd.Product_IDNo
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                join tbl_unit u on u.Unit_SlNo = p.Unit_ID
                where sd.SaleMaster_IDNo = ?
                and sd.status != 'd'
            ", $data->salesId)->result();

            $res['saleDetails'] = $saleDetails;
        }
        $sales = $this->db->query("
            select 
                concat(
                    sm.SaleMaster_InvoiceNo,
                    ' - ',
                    case
                        when sm.SaleMaster_SaleType = 'project'
                            then ifnull(p.name, '')
                        else
                            ifnull(c.Customer_Name, sm.customerName)
                    end,
                    case when ifnull(sm.SaleMaster_InvoiceText, '') != '' then concat(' - ', sm.SaleMaster_InvoiceText) else '' end
                ) as invoice_text,
                sm.*,
                ifnull(c.Customer_Code, 'Cash Customer') as Customer_Code,
                ifnull(c.Customer_Name, sm.customerName) as Customer_Name,
                ifnull(c.Customer_Mobile, sm.customerMobile) as Customer_Mobile,
                ifnull(c.Customer_Address, sm.customerAddress) as Customer_Address,
                ifnull(c.Customer_Type, sm.customerType) as Customer_Type,
                p.code as project_code,
                p.name as project_name,
                concat_ws(', ', ps.name, dis.name, d.name) as project_location,
                ba.account_name,
                ba.account_number,
                ba.bank_name,
                e.Employee_Name,
                e.Employee_ID,
                br.Branch_name,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_salesmaster sm
            left join tbl_projects p on p.id = sm.project_id
            left join tbl_divisions d on d.id = p.division_id
            left join tbl_districts dis on dis.id = p.district_id
            left join tbl_police_stations ps on ps.id = p.thana_id
            left join tbl_customer c on c.Customer_SlNo = sm.SalseCustomer_IDNo
            left join tbl_bank_accounts ba on ba.account_id = sm.accountId
            left join tbl_employee e on e.Employee_SlNo = sm.employee_id
            left join tbl_user ua on ua.User_SlNo = sm.AddBy
            left join tbl_user ud on ud.User_SlNo = sm.DeletedBy
            left join tbl_branch br on br.branch_id = sm.branch_id
            where sm.branch_id = ?
            and sm.is_order = 'yes'
            $clauses
            order by sm.SaleMaster_SlNo desc
            $limit
        ", $branchId)->result();

        $res['sales'] = $sales;

        echo json_encode($res);
    }

    function record()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Work Order Record";
        $data['content'] = $this->load->view('Administrator/workOrder/record', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getRecord()
    {
        $data = json_decode($this->input->raw_input_stream);
        $branchId = $this->session->userdata("BRANCHid");
        $clauses = "";

        if (isset($data->status) && $data->status != '') {
            $clauses .= " and sm.status = '$data->status'";
        } else {
            $clauses .= " and sm.status != 'd'";
        }
        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sm.SaleMaster_SaleDate between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->userId) && $data->userId != '') {
            $clauses .= " and sm.AddBy = '$data->userId'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and sm.project_id = '$data->projectId'";
        }

        if (isset($data->customerId) && $data->customerId != '') {
            $clauses .= " and sm.SalseCustomer_IDNo = '$data->customerId'";
        }

        if (isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and sm.employee_id = '$data->employeeId'";
        }

        $sales = $this->db->query("
            select 
                sm.*,
                ifnull(c.Customer_Code, 'Cash Customer') as Customer_Code,
                ifnull(c.Customer_Name, sm.customerName) as Customer_Name,
                ifnull(c.Customer_Mobile, sm.customerMobile) as Customer_Mobile,
                ifnull(c.Customer_Address, sm.customerAddress) as Customer_Address,
                ifnull(c.Customer_Type, sm.customerType) as Customer_Type,
                p.code as project_code,
                p.name as project_name,
                e.Employee_Name,
                br.Branch_name,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_salesmaster sm
            left join tbl_projects p on p.id = sm.project_id
            left join tbl_customer c on c.Customer_SlNo = sm.SalseCustomer_IDNo
            left join tbl_employee e on e.Employee_SlNo = sm.employee_id
            left join tbl_branch br on br.branch_id = sm.branch_id
            left join tbl_user ua on ua.User_SlNo = sm.AddBy
            left join tbl_user ud on ud.User_SlNo = sm.DeletedBy
            where sm.branch_id = '$branchId'
            and sm.is_order = 'yes'
            $clauses
            order by sm.SaleMaster_SlNo desc
        ")->result();

        foreach ($sales as $sale) {
            $sale->saleDetails = $this->db->query("
                select 
                    sd.*,
                    (select sd.order_quantity - ifnull(sd.SaleDetails_TotalQuantity, 0)) as remaining_quantity,
                    p.Product_Name,
                    pc.ProductCategory_Name
                from tbl_saledetails sd
                join tbl_product p on p.Product_SlNo = sd.Product_IDNo
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                where sd.SaleMaster_IDNo = ?
                and sd.status != 'd'
            ", $sale->SaleMaster_SlNo)->result();
        }

        echo json_encode($sales);
    }

    public function getDetails()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->customerId) && $data->customerId != '') {
            $clauses .= " and c.Customer_SlNo = '$data->customerId'";
        }

        if($this->session->userdata('accountType') != 'm') {
            $clauses .= " and sm.AddBy = '$this->userId'";
        }

        if (isset($data->productId) && $data->productId != '') {
            $clauses .= " and p.Product_SlNo = '$data->productId'";
        }

        if (isset($data->categoryId) && $data->categoryId != '') {
            $clauses .= " and pc.ProductCategory_SlNo = '$data->categoryId'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and sm.project_id = '$data->projectId'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sm.SaleMaster_SaleDate between '$data->dateFrom' and '$data->dateTo'";
        }

        $saleDetails = $this->db->query("
            select 
                sd.*,
                p.Product_Code,
                p.Product_Name,
                p.ProductCategory_ID,
                pc.ProductCategory_Name,
                sm.SaleMaster_InvoiceNo,
                sm.SaleMaster_SaleDate,
                ifnull(c.Customer_Code, 'Cash Customer') as Customer_Code,
                ifnull(c.Customer_Name, sm.customerName) as Customer_Name,
                ifnull(c.Customer_Mobile, sm.customerMobile) as Customer_Mobile
            from tbl_saledetails sd
            left join tbl_product p on p.Product_SlNo = sd.Product_IDNo
            left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            join tbl_salesmaster sm on sm.SaleMaster_SlNo = sd.SaleMaster_IDNo
            left join tbl_customer c on c.Customer_SlNo = sm.SalseCustomer_IDNo
            where sd.status != 'd'
            and sm.branch_id = ?
            $clauses
        ", $this->sbrunch)->result();

        echo json_encode($saleDetails);
    }

    public function invoice()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Order Invoice";
        $data['content'] = $this->load->view('Administrator/workOrder/invoice', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function invoicePrint($saleId)
    {
        $data['title'] = "Order Invoice";
        $data['salesId'] = $saleId;
        $data['content'] = $this->load->view('Administrator/workOrder/invoicePrint', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function partialDelivery($orderId = 0)
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['title'] = "Work Order Partial Delivery";
        $data['orderId'] = $orderId;

        $data['content'] = $this->load->view('Administrator/workOrder/delivery', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getOrderForDelivery() 
    {
        $data = json_decode($this->input->raw_input_stream);
        $saleDetails = $this->db->query("
            select 
                sd.*,
                sd.SaleDetails_Rate,
                p.Product_Code,
                p.Product_Name,
                pc.ProductCategory_Name,
                (select sd.order_quantity - ifnull(sd.SaleDetails_TotalQuantity, 0)) as remaining_quantity,
                0 as store_delivery_quantity,
                0 as delivery_quantity
            from tbl_saledetails sd
            join tbl_salesmaster sm on sm.SaleMaster_SlNo = sd.SaleMaster_IDNo
            join tbl_product p on p.Product_SlNo = sd.Product_IDNo
            left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            where sm.SaleMaster_SlNo = ?
            and sd.status != 'd'
        ", $data->saleId)->result();

        echo json_encode($saleDetails);
    }

    public function addDelivery()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);

            foreach ($data->cart as $product) {
                $storeDeliveryQuantity = (float) ($product->store_delivery_quantity ?? 0);
                $deliveryQuantity = (float) ($product->delivery_quantity ?? 0);
                $totalDeliveryQuantity = $storeDeliveryQuantity + $deliveryQuantity;
                $availableQuantity = (float) $this->db->query(
                    "select order_quantity - ifnull(SaleDetails_TotalQuantity, 0) as remaining_quantity
                     from tbl_saledetails where SaleDetails_SlNo = ? and status != 'd'",
                    [$product->SaleDetails_SlNo]
                )->row()->remaining_quantity;

                if ($storeDeliveryQuantity < 0 || $deliveryQuantity < 0 || $totalDeliveryQuantity <= 0 || $totalDeliveryQuantity > $availableQuantity) {
                    throw new Exception('Delivery quantity is not valid');
                }

                $deliveryDetails = array(
                    'date' => $data->partialDelivery->date,
                    'sale_detail_id' => $product->SaleDetails_SlNo,
                    'product_id' => $product->Product_IDNo,
                    'store_quantity' => $storeDeliveryQuantity,
                    'quantity' => $totalDeliveryQuantity,
                    'total' => $totalDeliveryQuantity * (float) $product->SaleDetails_Rate,
                    'note' => $data->partialDelivery->note,
                    'status' => 'a',
                    'add_by' => $this->session->userdata("userId"),
                    'add_time' => date('Y-m-d H:i:s'),
                    'branch_id' => $this->session->userdata('BRANCHid'),
                    "last_update_ip" => get_client_ip()
                );

                $this->db->insert('tbl_order_delivery_logs', $deliveryDetails);

                // update sale details
                if($product->status == 'p') {
                    $this->db->query("
                        update tbl_saledetails 
                        set store_quantity = ifnull(store_quantity, 0) + ?,
                        SaleDetails_TotalQuantity = ifnull(SaleDetails_TotalQuantity, 0) + ?,
                        status = ? 
                        where SaleDetails_SlNo = ?
                    ", [$storeDeliveryQuantity, $totalDeliveryQuantity, 'a', $product->SaleDetails_SlNo]);
                } else {
                    $this->db->query("
                        update tbl_saledetails 
                        set store_quantity = ifnull(store_quantity, 0) + ?,
                        SaleDetails_TotalQuantity = ifnull(SaleDetails_TotalQuantity, 0) + ?
                        where SaleDetails_SlNo = ?
                    ", [$storeDeliveryQuantity, $totalDeliveryQuantity, $product->SaleDetails_SlNo]);
                }

                // stock update
                $this->db->query("
                    update tbl_currentinventory 
                    set sales_quantity = sales_quantity + ? 
                    where product_id = ?
                    and branch_id = ?
                ", [$totalDeliveryQuantity, $product->Product_IDNo, $this->session->userdata('BRANCHid')]);
            }

            // update sale master
            $sale = $this->db->query("select * from tbl_salesmaster where SaleMaster_SlNo = ?", $data->invoice->SaleMaster_SlNo)->row();
            if($sale->status == 'p') {
                $this->db->query("
                        update tbl_salesmaster 
                        set status = ? 
                        where SaleMaster_SlNo = ?
                    ", ['a', $data->invoice->SaleMaster_SlNo]);
            }

            // Check if all sale details are fully delivered
            $remaining = $this->db->query("
                SELECT COUNT(*) AS total
                FROM tbl_saledetails
                WHERE SaleMaster_IDNo = ?
                AND order_quantity != ifnull(SaleDetails_TotalQuantity, 0)
            ", [$data->invoice->SaleMaster_SlNo])->row();

            if ($remaining->total == 0) {
                $this->db->query("
                    UPDATE tbl_salesmaster
                    SET is_delivered = 'yes'
                    WHERE SaleMaster_SlNo = ?
                ", [$data->invoice->SaleMaster_SlNo]);
            }

            $this->db->trans_commit();
            $res = ['success' => true, 'message' => 'Partial Delivery Success'];
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function deliveryLogs()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['title'] = "Work Order Delivery History";
        $data['content'] = $this->load->view('Administrator/workOrder/deliveryLogs', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getDeliveryLogs()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->saleId) && $data->saleId != '') {
            $clauses .= " and sm.SaleMaster_SlNo = '$data->saleId'";
        }

        if (isset($data->productId) && $data->productId != '') {
            $clauses .= " and dl.product_id = '$data->productId'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and dl.date between '$data->dateFrom' and '$data->dateTo'";
        }

        $logs = $this->db->query("
            select 
                dl.*,
                sd.SaleDetails_Rate,
                p.Product_Code,
                p.Product_Name,
                0 as delivery_quantity
            from tbl_order_delivery_logs dl
            join tbl_saledetails sd on sd.SaleDetails_SlNo = dl.sale_detail_id
            join tbl_salesmaster sm on sm.SaleMaster_SlNo = sd.SaleMaster_IDNo
            join tbl_product p on p.Product_SlNo = dl.product_id
            where dl.status = 'a'
            and dl.branch_id = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($logs);
    }
}
