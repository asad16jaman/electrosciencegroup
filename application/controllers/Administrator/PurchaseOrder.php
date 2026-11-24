<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class PurchaseOrder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->brunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }

        $this->load->model('Model_table', "mt", TRUE);
        $this->load->helper('form');
    }

    public function index()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Purchase Order Entey";

        $invoice = $this->mt->generatePurchaseInvoice();

        $data['purchaseId'] = 0;
        $data['invoice'] = $invoice;
        $data['content'] = $this->load->view('Administrator/purchaseOrder/create', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function edit($purId)
    {
        $data['title'] = "Purchase Update";
        $data['purchaseId'] = $purId;
        $data['invoice'] = $this->db->query("select PurchaseMaster_InvoiceNo from tbl_purchasemaster where PurchaseMaster_SlNo = ?", $purId)->row()->PurchaseMaster_InvoiceNo;
        $data['content'] = $this->load->view('Administrator/purchaseOrder/create', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function store()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);

            if ($data->purchase->purchaseFor != $this->session->userdata("BRANCHid")) {
                $res = ['success' => false, 'message' => 'You have already changed your branch.', 'branch_status' => false];
                echo json_encode($res);
                exit;
            }

            $invoice = $data->purchase->invoice;
            $invoiceCount = $this->db->query("select * from tbl_purchasemaster where PurchaseMaster_InvoiceNo = ?", $invoice)->num_rows();
            if ($invoiceCount != 0) {
                $invoice = $this->mt->generatePurchaseInvoice();
            }

            $supplierId = $data->purchase->supplierId;
            if (isset($data->supplier) && $data->supplier->Supplier_Type == 'N') {
                $supplier = (array)$data->supplier;
                unset($supplier['Supplier_SlNo']);
                unset($supplier['Supplier_Type']);
                unset($supplier['display_name']);

                $mobile_count = $this->db->query("select * from tbl_supplier where Supplier_Mobile = ? and branch_id = ?", [$data->supplier->Supplier_Mobile, $this->session->userdata("BRANCHid")])->row();

                if ($data->supplier->Supplier_Type == 'N' && empty($mobile_count)) {
                    $supplier['Supplier_Code']     = $this->mt->generateSupplierCode();
                    $supplier['Supplier_Type']     = 'retail';
                    $supplier['status']            = 'a';
                    $supplier['AddBy']             = $this->session->userdata("userId");
                    $supplier['AddTime']           = date('Y-m-d H:i:s');
                    $supplier['last_update_ip']    = get_client_ip();
                    $supplier['branch_id'] = $this->session->userdata('BRANCHid');

                    $this->db->insert('tbl_supplier', $supplier);
                    $supplierId = $this->db->insert_id();
                } else {
                    $res = ['success' => false, 'message' => 'Supplier mobile number already exist!'];
                    echo json_encode($res);
                    exit;
                }
            }

            $purchase = array(
                'PurchaseMaster_InvoiceNo'      => $invoice,
                'project_id'                    => $data->purchase->project_id,
                'PurchaseMaster_OrderDate'      => $data->purchase->purchaseDate,
                'PurchaseMaster_PurchaseFor'    => $data->purchase->purchaseFor,
                'supplierType'                  => 'retail',
                'Supplier_SlNo'                 => $supplierId,
                'PurchaseMaster_TotalAmount'    => $data->purchase->total,
                'PurchaseMaster_DiscountAmount' => $data->purchase->discount,
                'PurchaseMaster_Tax'            => $data->purchase->vat,
                'PurchaseMaster_Freight'        => $data->purchase->freight,
                'PurchaseMaster_SubTotalAmount' => $data->purchase->subTotal,
                'PurchaseMaster_PaidAmount'     => $data->purchase->paid,
                'PurchaseMaster_DueAmount'      => $data->purchase->due,
                'previous_due'                  => $data->purchase->previousDue,
                'PurchaseMaster_Description'    => $data->purchase->note,
                'is_order'                      => 'yes',
                'status'                        => 'p',
                'AddBy'                         => $this->session->userdata("userId"),
                'AddTime'                       => date('Y-m-d H:i:s'),
                'last_update_ip' => get_client_ip(),
                'branch_id'       => $this->session->userdata('BRANCHid')
            );

            $this->db->insert('tbl_purchasemaster', $purchase);
            $purchaseId = $this->db->insert_id();

            foreach ($data->cartProducts as $product) {
                $purchaseDetails = array(
                    'PurchaseMaster_IDNo' => $purchaseId,
                    'Product_IDNo' => $product->productId,
                    'order_quantity' => $product->quantity,
                    'PurchaseDetails_TotalQuantity' => 0,
                    'PurchaseDetails_Rate' => $product->purchaseRate,
                    'PurchaseDetails_TotalAmount' => $product->total,
                    'status' => 'p',
                    'AddBy' => $this->session->userdata("userId"),
                    'AddTime' => date('Y-m-d H:i:s'),
                    'last_update_ip' => get_client_ip(),
                    'branch_id' => $this->session->userdata('BRANCHid')
                );

                $this->db->insert('tbl_purchasedetails', $purchaseDetails);
            }
            $this->db->trans_commit();
            $res = ['success' => true, 'message' => 'Purchase Order Success!', 'purchaseId' => $purchaseId];
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function invoicePrint($purchaseId)
    {
        $data['title'] = "Purchase Order Invoice";
        $data['purchaseId'] = $purchaseId;
        $data['content'] = $this->load->view('Administrator/purchaseOrder/invoicePrint', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function update()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();

            $data = json_decode($this->input->raw_input_stream);
            $purchaseId = $data->purchase->purchaseId;

            $supplierId = $data->purchase->supplierId;
            if (isset($data->supplier) && $data->supplier->Supplier_Type == 'N') {
                $supplier = (array)$data->supplier;
                unset($supplier['Supplier_SlNo']);
                unset($supplier['Supplier_Type']);
                unset($supplier['display_name']);

                $mobile_count = $this->db->query("select * from tbl_supplier where Supplier_Mobile = ? and branch_id = ?", [$data->supplier->Supplier_Mobile, $this->session->userdata("BRANCHid")])->row();

                if ($data->supplier->Supplier_Type == 'N' && empty($mobile_count)) {
                    $supplier['Supplier_Code']     = $this->mt->generateSupplierCode();
                    $supplier['Supplier_Type']     = 'retail';
                    $supplier['status']            = 'a';
                    $supplier['AddBy']             = $this->session->userdata("userId");
                    $supplier['AddTime']           = date('Y-m-d H:i:s');
                    $supplier['last_update_ip']    = get_client_ip();
                    $supplier['branch_id'] = $this->session->userdata('BRANCHid');

                    $this->db->insert('tbl_supplier', $supplier);
                    $supplierId = $this->db->insert_id();
                } else {
                    $res = ['success' => false, 'message' => 'Supplier mobile number already exist!'];
                    echo json_encode($res);
                    exit;
                }
            }

            $purchase = array(
                'project_id' => $data->purchase->project_id,
                'PurchaseMaster_InvoiceNo' => $data->purchase->invoice,
                'PurchaseMaster_OrderDate' => $data->purchase->purchaseDate,
                'PurchaseMaster_PurchaseFor' => $data->purchase->purchaseFor,
                'supplierType' => 'retail',
                'Supplier_SlNo' => $supplierId,
                'PurchaseMaster_TotalAmount' => $data->purchase->total,
                'PurchaseMaster_DiscountAmount' => $data->purchase->discount,
                'PurchaseMaster_Tax' => $data->purchase->vat,
                'PurchaseMaster_Freight' => $data->purchase->freight,
                'PurchaseMaster_SubTotalAmount' => $data->purchase->subTotal,
                'PurchaseMaster_PaidAmount' => $data->purchase->paid,
                'PurchaseMaster_DueAmount' => $data->purchase->due,
                'previous_due' => $data->purchase->previousDue,
                'PurchaseMaster_Description' => $data->purchase->note,
                'is_order' => 'yes',
                'UpdateBy' => $this->session->userdata("userId"),
                'UpdateTime' => date('Y-m-d H:i:s'),
                'last_update_ip' => get_client_ip(),
                'branch_id' => $this->session->userdata('BRANCHid')
            );

            $this->db->where('PurchaseMaster_SlNo', $purchaseId);
            $this->db->update('tbl_purchasemaster', $purchase);

            $this->db->where("PurchaseMaster_IDNo", $purchaseId)->delete("tbl_purchasedetails");
            foreach ($data->cartProducts as $product) {
                $purchaseDetails = array(
                    'PurchaseMaster_IDNo' => $purchaseId,
                    'Product_IDNo' => $product->productId,
                    'order_quantity' => $product->quantity,
                    'PurchaseDetails_TotalQuantity' => 0,
                    'PurchaseDetails_Rate' => $product->purchaseRate,
                    'PurchaseDetails_TotalAmount' => $product->total,
                    'status' => 'p',
                    'AddBy' => $this->session->userdata("userId"),
                    'AddTime' => date('Y-m-d H:i:s'),
                    'UpdateBy' => $this->session->userdata("userId"),
                    'UpdateTime' => date('Y-m-d H:i:s'),
                    'last_update_ip' => get_client_ip(),
                    'branch_id' => $this->session->userdata('BRANCHid')
                );

                $this->db->insert('tbl_purchasedetails', $purchaseDetails);
            }

            $this->db->trans_commit();
            $res = ['success' => true, 'message' => 'Purchase Order Update Success!', 'purchaseId' => $purchaseId];
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function record()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Purchase Order Record";
        $data['content'] = $this->load->view('Administrator/purchaseOrder/record', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function get()
    {
        $data = json_decode($this->input->raw_input_stream);
        $branchId = $this->session->userdata('BRANCHid');

        $clauses = "";
        $limit = "";
        if (isset($data->status) && $data->status != '') {
            $clauses .= " and pm.status = '$data->status'";
        } else {
            $clauses .= " and pm.status != 'd'";
        }
        if (isset($data->name) && $data->name != '') {
            $clauses .= " and pm.PurchaseMaster_InvoiceNo like '$data->name%'";
        }
        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and pm.PurchaseMaster_OrderDate between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and pm.project_id = '$data->projectId'";
        }

        if (isset($data->supplierId) && $data->supplierId != '') {
            $clauses .= " and pm.Supplier_SlNo = '$data->supplierId'";
        }

        if (isset($data->forSearch) && $data->forSearch != '') {
            $limit .= "limit 20";
        }

        $purchaseIdClause = "";
        if (isset($data->purchaseId) && $data->purchaseId != null) {
            $purchaseIdClause = " and pm.PurchaseMaster_SlNo = '$data->purchaseId'";

            $res['purchaseDetails'] = $this->db->query("
                select
                    pd.*,
                    (select pd.order_quantity - pd.PurchaseDetails_TotalQuantity) as remaining_quantity,
                    p.Product_Name,
                    p.Product_Code,
                    p.ProductCategory_ID,
                    p.Product_SellingPrice,
                    pc.ProductCategory_Name,
                    u.Unit_Name
                from tbl_purchasedetails pd 
                join tbl_product p on p.Product_SlNo = pd.Product_IDNo
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                join tbl_unit u on u.Unit_SlNo = p.Unit_ID
                where pd.PurchaseMaster_IDNo = '$data->purchaseId'
                and pd.status != 'd'
            ")->result();
        }
        $purchases = $this->db->query("
            select
                concat(pm.PurchaseMaster_InvoiceNo, ' - ', ifnull(s.Supplier_Name, pm.supplierName)) as invoice_text,
                pm.*,
                ifnull(pm.Supplier_SlNo, '') as Supplier_SlNo,
                ifnull(s.Supplier_Code, 'Cash Supplier') as Supplier_Code,
                ifnull(s.Supplier_Name, pm.supplierName) as Supplier_Name,
                ifnull(s.Supplier_Mobile, pm.supplierMobile) as Supplier_Mobile,
                ifnull(s.Supplier_Address, pm.supplierAddress) as Supplier_Address,
                s.Supplier_Email,
                p.code as project_code,
                p.name as project_name,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_purchasemaster pm
            left join tbl_projects p on p.id = pm.project_id
            left join tbl_supplier s on s.Supplier_SlNo = pm.Supplier_SlNo
            left join tbl_user ua on ua.User_SlNo = pm.AddBy
            left join tbl_user ud on ud.User_SlNo = pm.DeletedBy
            where pm.branch_id = ?
            and pm.is_order = 'yes'
            $purchaseIdClause 
            $clauses
            order by pm.PurchaseMaster_SlNo desc
            $limit
        ", $branchId)->result();

        $res['purchases'] = $purchases;
        echo json_encode($res);
    }

    public function details()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->supplierId) && $data->supplierId != '') {
            $clauses .= " and s.Supplier_SlNo = '$data->supplierId'";
        }

        if (isset($data->productId) && $data->productId != '') {
            $clauses .= " and p.Product_SlNo = '$data->productId'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and pm.project_id = '$data->projectId'";
        }

        if (isset($data->categoryId) && $data->categoryId != '') {
            $clauses .= " and pc.ProductCategory_SlNo = '$data->categoryId'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and pm.PurchaseMaster_OrderDate between '$data->dateFrom' and '$data->dateTo'";
        }

        $saleDetails = $this->db->query("
            select 
                pd.*,
                p.Product_Name,
                pc.ProductCategory_Name,
                pm.PurchaseMaster_InvoiceNo,
                pm.PurchaseMaster_OrderDate,
                ifnull(s.Supplier_Code, 'Cash Supplier') as Supplier_Code,
                ifnull(s.Supplier_Name, pm.supplierName) as Supplier_Name,
                ifnull(s.Supplier_Mobile, pm.supplierMobile) as Supplier_Mobile
            from tbl_purchasedetails pd
            left join tbl_product p on p.Product_SlNo = pd.Product_IDNo
            left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            join tbl_purchasemaster pm on pm.PurchaseMaster_SlNo = pd.PurchaseMaster_IDNo
            left join tbl_supplier s on s.Supplier_SlNo = pm.Supplier_SlNo
            where pd.status != 'd'
            and pm.is_order = 'yes'
            and pd.branch_id = ?
            $clauses
        ", $this->brunch)->result();

        echo json_encode($saleDetails);
    }

    public function getRecord()
    {
        $data = json_decode($this->input->raw_input_stream);
        $branchId = $this->session->userdata("BRANCHid");
        $clauses = "";
        if (isset($data->status) && $data->status != '') {
            $clauses .= " and pm.status = '$data->status'";
        } else {
            $clauses .= " and pm.status != 'd'";
        }
        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and pm.PurchaseMaster_OrderDate between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->userFullName) && $data->userFullName != '') {
            $clauses .= " and pm.AddBy = '$data->userFullName'";
        }

        if (isset($data->projectId) && $data->projectId != '') {
            $clauses .= " and pm.project_id = '$data->projectId'";
        }

        if (isset($data->supplierId) && $data->supplierId != '') {
            $clauses .= " and pm.Supplier_SlNo = '$data->supplierId'";
        }

        $purchases = $this->db->query("
            select 
                pm.*,
                ifnull(s.Supplier_Code, 'Cash Supplier') as Supplier_Code,
                ifnull(s.Supplier_Name, pm.supplierName) as Supplier_Name,
                ifnull(s.Supplier_Mobile, pm.supplierMobile) as Supplier_Mobile,
                ifnull(s.Supplier_Address, pm.supplierAddress) as Supplier_Address,
                br.Branch_name,
                p.code as project_code,
                p.name as project_name,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_purchasemaster pm
            left join tbl_projects p on p.id = pm.project_id
            left join tbl_supplier s on s.Supplier_SlNo = pm.Supplier_SlNo
            left join tbl_branch br on br.branch_id = pm.branch_id
            left join tbl_user ua on ua.User_SlNo = pm.AddBy
            left join tbl_user ud on ud.User_SlNo = pm.DeletedBy
            where pm.branch_id = ?
            and pm.is_order = 'yes'
            $clauses
        ", $branchId)->result();

        foreach ($purchases as $purchase) {
            $purchase->purchaseDetails = $this->db->query("
                select 
                    pd.*,
                    (select pd.order_quantity - pd.PurchaseDetails_TotalQuantity) as remaining_quantity,
                    p.Product_Name,
                    pc.ProductCategory_Name
                from tbl_purchasedetails pd
                join tbl_product p on p.Product_SlNo = pd.Product_IDNo
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                where pd.PurchaseMaster_IDNo = ?
                and pd.status != 'd'
            ", $purchase->PurchaseMaster_SlNo)->result();
        }

        echo json_encode($purchases);
    }

    public function  delete()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $purchase = $this->db->select('*')->where('PurchaseMaster_SlNo', $data->purchaseId)->get('tbl_purchasemaster')->row();
            if ($purchase->status != 'p') {
                $res = ['success' => false, 'message' => 'Purchase Order not found'];
                echo json_encode($res);
                exit;
            }

            $returnCount = $this->db->query("select * from tbl_purchasereturn pr where pr.PurchaseMaster_InvoiceNo = ? and pr.status = 'a'", $purchase->PurchaseMaster_InvoiceNo)->num_rows();
            if ($returnCount != 0) {
                $res = ['success' => false, 'message' => 'Unable to delete. Purchase return found'];
                echo json_encode($res);
                exit;
            }


            $purchase = array(
                'status' => 'd',
                'DeletedBy' => $this->session->userdata('userId'),
                'DeletedTime' => date("Y-m-d H:i:s"),
                'last_update_ip' => get_client_ip(),
            );
            $this->db->set($purchase)->where('PurchaseMaster_IDNo', $data->purchaseId)->update('tbl_purchasedetails');
            $this->db->set($purchase)->where('PurchaseMaster_SlNo', $data->purchaseId)->update('tbl_purchasemaster');

            $res = ['success' => true, 'message' => 'Successfully Purchase Order Deleted!'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function invoice()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Purchase Order Invoices";
        $data['content'] = $this->load->view('Administrator/purchaseOrder/invoice', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function partialDelivery($orderId = 0)
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['title'] = "Purchase Order Partial Delivery";
        $data['orderId'] = $orderId;

        $data['content'] = $this->load->view('Administrator/purchaseOrder/delivery', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getPurchaseForDelivery()
    {
        $data = json_decode($this->input->raw_input_stream);
        $purchaseDetails = $this->db->query("
            select 
                pd.*,
                pd.PurchaseDetails_Rate,
                p.Product_Code,
                p.Product_Name,
                pc.ProductCategory_Name,
                (select pd.order_quantity - pd.PurchaseDetails_TotalQuantity) as remaining_quantity,
                0 as delivery_quantity
            from tbl_purchasedetails pd
            join tbl_purchasemaster pm on pm.PurchaseMaster_SlNo = pd.PurchaseMaster_IDNo
            join tbl_product p on p.Product_SlNo = pd.Product_IDNo
            left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            where pm.PurchaseMaster_SlNo = ?
            and pd.status != 'd'
        ", $data->purchaseId)->result();

        echo json_encode($purchaseDetails);
    }

    public function addDelivery()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);

            foreach ($data->cart as $product) {
                $deliveryDetails = array(
                    'date' => $data->partialDelivery->date,
                    'pur_detail_id' => $product->PurchaseDetails_SlNo,
                    'product_id' => $product->Product_IDNo,
                    'quantity' => $product->delivery_quantity,
                    'total' => $product->delivery_amount,
                    'note' => $data->partialDelivery->note,
                    'status' => 'a',
                    'add_by' => $this->session->userdata("userId"),
                    'add_time' => date('Y-m-d H:i:s'),
                    'branch_id' => $this->session->userdata('BRANCHid'),
                    "last_update_ip" => get_client_ip()
                );

                $this->db->insert('tbl_purchase_delivery_logs', $deliveryDetails);

                // update purchase details
                if ($product->status == 'p') {
                    $this->db->query("
                        update tbl_purchasedetails 
                        set PurchaseDetails_TotalQuantity = PurchaseDetails_TotalQuantity + ?,
                        status = ? 
                        where PurchaseDetails_SlNo = ?
                    ", [$product->delivery_quantity, 'a', $product->PurchaseDetails_SlNo]);
                } else {
                    $this->db->query("
                        update tbl_purchasedetails 
                        set PurchaseDetails_TotalQuantity = PurchaseDetails_TotalQuantity + ?
                        where PurchaseDetails_SlNo = ?
                    ", [$product->delivery_quantity, $product->PurchaseDetails_SlNo]);
                }

                // stock update
                $inventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$product->Product_IDNo, $this->session->userdata('BRANCHid')])->num_rows();
                if ($inventoryCount == 0) {
                    $inventory = array(
                        'product_id' => $product->Product_IDNo,
                        'purchase_quantity' => $product->delivery_quantity,
                        'branch_id' => $this->session->userdata('BRANCHid')
                    );

                    $this->db->insert('tbl_currentinventory', $inventory);
                } else {
                    $this->db->query("
                    update tbl_currentinventory 
                    set purchase_quantity = purchase_quantity + ? 
                    where product_id = ?
                    and branch_id = ?
                ", [$product->delivery_quantity, $product->Product_IDNo, $this->session->userdata('BRANCHid')]);
                }
            }

            // update purchase master
            $purchase = $this->db->query("select * from tbl_purchasemaster where PurchaseMaster_SlNo = ?", $data->invoice->PurchaseMaster_SlNo)->row();
            if ($purchase->status == 'p') {
                $this->db->query("
                        update tbl_purchasemaster 
                        set status = ? 
                        where PurchaseMaster_SlNo = ?
                    ", ['a', $data->invoice->PurchaseMaster_SlNo]);
            }

            // Check if all purchase details are fully delivered
            $remaining = $this->db->query("
                SELECT COUNT(*) AS total
                FROM tbl_purchasedetails
                WHERE PurchaseMaster_IDNo = ?
                AND order_quantity != PurchaseDetails_TotalQuantity
            ", [$data->invoice->PurchaseMaster_SlNo])->row();

            if ($remaining->total == 0) {
                $this->db->query("
                    UPDATE tbl_purchasemaster
                    SET is_delivered = 'yes'
                    WHERE PurchaseMaster_SlNo = ?
                ", [$data->invoice->PurchaseMaster_SlNo]);
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

        $data['title'] = "Purchase Delivery History";
        $data['content'] = $this->load->view('Administrator/purchaseOrder/deliveryLogs', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getDeliveryLogs()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->purchaseId) && $data->purchaseId != '') {
            $clauses .= " and pm.PurchaseMaster_SlNo = '$data->purchaseId'";
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
                pd.PurchaseDetails_Rate,
                p.Product_Code,
                p.Product_Name,
                0 as delivery_quantity
            from tbl_purchase_delivery_logs dl
            join tbl_purchasedetails pd on pd.PurchaseDetails_SlNo = dl.pur_detail_id
            join tbl_purchasemaster pm on pm.PurchaseMaster_SlNo = pd.PurchaseMaster_IDNo
            join tbl_product p on p.Product_SlNo = dl.product_id
            where dl.status = 'a'
            and dl.branch_id = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($logs);
    }
}
