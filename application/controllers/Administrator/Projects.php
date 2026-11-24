<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->branch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model("Model_myclass", "mmc", TRUE);
        $this->load->model('Model_table', "mt", TRUE);
        $this->load->model('Billing_model');
    }

    public function index()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Project Entry";
        $data['code'] = $this->mt->generateProjectCode();
        $data['content'] = $this->load->view('Administrator/project/create', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getDivisions(){
        $divisions = $this->db->query("select * from tbl_divisions")->result();
        echo json_encode($divisions);
    }

    public function getDistricts(){
        $data = json_decode($this->input->raw_input_stream);
        $clauses = "";
        if(isset($data->divisionId) && $data->divisionId != null){
            $clauses .= " and d.division_id = $data->divisionId";
        }

        $districts = $this->db->query("
            select 
                d.* 
            from tbl_districts d
            where 1 = 1
            $clauses
        ")->result();

        echo json_encode($districts);
    }

    public function getPoliceStations() {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = "";

        if(isset($data->districtId) && $data->districtId != null){
            $clauses .= " and ps.district_id = $data->districtId";
        }
        if(isset($data->type) && $data->type == 'metropolitan'){
            $clauses .= " and ps.type = '$data->type'";
        } 

        $policeStations = $this->db->query("
            select 
                ps.* 
            from tbl_police_stations ps
            where 1 = 1 
            $clauses
        ")->result();

        echo json_encode($policeStations);
    }

    public function get() 
    {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = "";
        $status = " and p.status = 'a'";
        if (isset($data->status) && $data->status != '') {
            $status = " and p.status = '$data->status'";
        }

        if(isset($data->divisionId) && $data->divisionId != null){
            $clauses .= " and p.division_id = $data->divisionId";
        }
        if(isset($data->districtId) && $data->districtId != null){
            $clauses .= " and p.district_id = $data->districtId";
        }
        if(isset($data->thanaId) && $data->thanaId != null){
            $clauses .= " and p.thana_id = $data->thanaId";
        } 

        if (isset($data->name) && $data->name != '') {
            $search = $this->db->escape_like_str($data->name);
            $clauses .= " and (p.code like '%{$search}%' or p.name like '%{$search}%')";
        }

        $projects = $this->db->query("
            select 
                p.*,
                d.name as division_name,
                dis.name as district_name,
                ps.name as thana_name,
                ua.User_Name as added_by,
                ud.User_Name as deleted_by
            from tbl_projects p
            left join tbl_divisions d on d.id = p.division_id
            left join tbl_districts dis on dis.id = p.district_id
            left join tbl_police_stations ps on ps.id = p.thana_id
            left join tbl_user ua on ua.User_SlNo = p.add_by
            left join tbl_user ud on ud.User_SlNo = p.delete_by
            where p.branch_id = ?
            $status
            $clauses
            order by p.id desc
        ", [$this->branch])->result();

        echo json_encode($projects);
    }

    public function store()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $projectObj = json_decode($this->input->raw_input_stream);

            $projectNameCount = $this->db->query("select * from tbl_projects where name = ?", $projectObj->name)->num_rows();
            if ($projectNameCount > 0) {
                $res = ['success' => false, 'message' => 'Project name already exists'];
                echo json_encode($res);
                exit;
            }

            $projectCodeCount = $this->db->query("select * from tbl_projects where code = ?", $projectObj->code)->num_rows();
            if ($projectCodeCount > 0) {
                $res = ['success' => false, 'message' => 'Product code already exists'];
                echo json_encode($res);
                exit;
            }

            $project                   = (array)$projectObj;
            $project['status']         = 'a';
            $project['add_by']         = $this->session->userdata("userId");
            $project['add_time']       = date('Y-m-d H:i:s');
            $project['last_update_ip'] = get_client_ip();
            $project['branch_id']      = $this->branch;

            $this->db->insert('tbl_projects', $project);

            $res = ['success' => true, 'message' => 'Project added successfully', 'projectId' => $this->mt->generateProjectCode()];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function update()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $projectObj = json_decode($this->input->raw_input_stream);

            $projectNameCount = $this->db->query("select * from tbl_projects where name = ? and id != ?", [$projectObj->name, $projectObj->id])->num_rows();
            if ($projectNameCount > 0) {
                $res = ['success' => false, 'message' => 'Project name already exists'];
                echo json_encode($res);
                exit;
            }

            $projectCodeCount = $this->db->query("select * from tbl_projects where code = ? and id != ?", [$projectObj->code, $projectObj->id])->num_rows();
            if ($projectCodeCount > 0) {
                $res = ['success' => false, 'message' => 'Project code already exists'];
                echo json_encode($res);
                exit;
            }
            
            $project = (array)$projectObj;
            unset($project['id']);
            $project['update_by']       = $this->session->userdata("userId");
            $project['update_time']     = date('Y-m-d H:i:s');
            $project['last_update_ip']  = get_client_ip();

            $this->db->where('id', $projectObj->id)->update('tbl_projects', $project);

            $res = ['success' => true, 'message' => 'Project updated successfully', 'projectId' => $this->mt->generateProjectCode()];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function delete()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $rules = array(
                'status'         => 'd',
                'delete_by'      => $this->session->userdata("userId"),
                'delete_time'    => date('Y-m-d H:i:s'),
                'last_update_ip' => get_client_ip()
            );
            $this->db->set($rules)->where('id', $data->projectId)->update('tbl_projects');

            $res = ['success' => true, 'message' => 'Project deleted successfully'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function ledger()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Project Ledger";
        $data['content'] = $this->load->view('Administrator/project/ledger', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getLedger()
    {
        $data = json_decode($this->input->raw_input_stream);

        if (empty($data->fromDate)) {
            $previousBalance = 0;
        } else {
            $previousBalance = $this->mt->getProjectSummary($data->projectId, $data->fromDate)->balance;
        }

        $ledger = $this->db->query("
            /* Cash In */
            select 
                sm.SaleMaster_SlNo as id,
                sm.SaleMaster_SaleDate as date,
                concat('Sale - ', sm.SaleMaster_InvoiceNo) as description,
                sm.SaleMaster_PaidAmount as in_amount,
                0.00 as out_amount
            from tbl_salesmaster sm 
            left join tbl_customer c on c.Customer_SlNo = sm.SalseCustomer_IDNo
            where sm.status = 'a'
            and sm.project_id = '$data->projectId'
            and sm.branch_id = '$this->branch'
            and sm.SaleMaster_SaleDate between '$data->fromDate' and '$data->toDate'
            
            UNION
            select 
                ct.Tr_SlNo as id,
                ct.Tr_date as date,
                concat('Cash in - ', acc.Acc_Name, ' - ', ct.Tr_Description) as description,
                ct.In_Amount as in_amount,
                0.00 as out_amount
            from tbl_cashtransaction ct
            join tbl_account acc on acc.Acc_SlNo = ct.Acc_SlID
            where ct.status = 'a'
            and ct.project_id = '$data->projectId'
            and ct.branch_id = '$this->branch'
            and ct.Tr_Type = 'In Cash'
            and ct.Tr_date between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                pr.PurchaseReturn_SlNo as id,
                pr.PurchaseReturn_ReturnDate as date,
                concat('Purchase - ', pr.PurchaseMaster_InvoiceNo, ' - ', pr.PurchaseReturn_Description) as description,
                pr.PurchaseReturn_ReturnAmount as in_amount,
                0.00 as out_amount
            from tbl_purchasereturn pr 
            left join tbl_purchasemaster pm on pm.PurchaseMaster_InvoiceNo = pr.PurchaseMaster_InvoiceNo
            where pr.status = 'a'
            and pm.project_id = '$data->projectId'
            and pr.branch_id = '$this->branch'
            and pr.PurchaseReturn_ReturnDate between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                bt.transaction_id as id,
                bt.transaction_date as date,
                concat('Bank Deposit - ', ba.account_name, ' - ', ba.account_number) as description,
                bt.amount as in_amount,
                0.00 as out_amount
            from tbl_bank_transactions bt
            join tbl_bank_accounts ba on ba.account_id = bt.account_id
            where bt.status = 1
            and bt.project_id = '$data->projectId'
            and bt.branch_id = '$this->branch'
            and bt.transaction_type = 'deposit'
            and bt.transaction_date between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                pp.id,
                pp.date,
                concat('Project Payment Receive - ', pp.invoice) as description,
                pp.amount as in_amount,
                0.00 as out_amount
            from tbl_project_payments pp
            where pp.status = 'a'
            and pp.project_id = '$data->projectId'
            and pp.branch_id = '$this->branch'
            and pp.type = 'CR'
            and pp.date between '$data->fromDate' and '$data->toDate'
            
            /* Cash out */
            
            UNION
            select 
                pm.PurchaseMaster_SlNo as id,
                pm.PurchaseMaster_OrderDate as date,
                concat('Purchase - ', pm.PurchaseMaster_InvoiceNo, ' - ', ifnull(s.Supplier_Name, pm.supplierName), ' (', ifnull(s.Supplier_Code, 'Cash Supplier'), ')') as description,
                0.00 as in_amount,
                pm.PurchaseMaster_TotalAmount as out_amount
            from tbl_purchasemaster pm 
            left join tbl_supplier s on s.Supplier_SlNo = pm.Supplier_SlNo
            where pm.status = 'a'
            and pm.project_id = '$data->projectId'
            and pm.branch_id = '$this->branch'
            and pm.PurchaseMaster_OrderDate between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                sr.SaleReturn_SlNo as id,
                sr.SaleReturn_ReturnDate as date,
                concat('Sale Return - ', sr.SaleMaster_InvoiceNo, ' - ', sr.SaleReturn_Description) as description,
                0.00 as in_amount,
                sr.SaleReturn_ReturnAmount as out_amount
            from tbl_salereturn sr 
            join tbl_salesmaster sm on sm.SaleMaster_InvoiceNo  = sr.SaleMaster_InvoiceNo
            where sr.status = 'a'
            and sm.SaleMaster_SaleType = 'project'
            and sm.project_id = '$data->projectId'
            and sr.branch_id = '$this->branch'
            and sr.SaleReturn_ReturnDate between '$data->fromDate' and '$data->toDate'
            
            UNION
            select 
                ct.Tr_SlNo as id,
                ct.Tr_date as date,
                concat('Cash out - ', acc.Acc_Name, ' - ', ct.Tr_Description) as description,
                0.00 as in_cash,
                ct.Out_Amount as out_amount
            from tbl_cashtransaction ct
            join tbl_account acc on acc.Acc_SlNo = ct.Acc_SlID
            where ct.Tr_Type = 'Out Cash'
            and ct.status = 'a'
            and ct.project_id = '$data->projectId'
            and ct.branch_id = '$this->branch'
            and ct.Tr_date between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                bt.transaction_id as id,
                bt.transaction_date as date,
                concat('Bank Withdraw - ', ba.account_name, ' - ', ba.account_number) as description,
                0.00 as in_amount,
                bt.amount as out_amount
            from tbl_bank_transactions bt
            join tbl_bank_accounts ba on ba.account_id = bt.account_id
            where bt.status = 1
            and bt.project_id = '$data->projectId'
            and bt.branch_id = '$this->branch'
            and bt.transaction_type = 'withdraw'
            and bt.transaction_date between '$data->fromDate' and '$data->toDate'

            UNION
            select 
                pp.id,
                pp.date,
                concat('Project Payment - ', pp.invoice) as description,
                0.00 as in_amount,
                pp.amount as out_amount
            from tbl_project_payments pp
            where pp.status = 'a'
            and pp.project_id = '$data->projectId'
            and pp.branch_id = '$this->branch'
            and pp.type = 'CP'
            and pp.date between '$data->fromDate' and '$data->toDate'

            order by date, id
        ")->result();

        $ledger = array_map(function ($ind, $row) use ($previousBalance, $ledger) {
            $row->balance = (($ind == 0 ? $previousBalance : $ledger[$ind - 1]->balance) + $row->in_amount) - $row->out_amount;
            return $row;
        }, array_keys($ledger), $ledger);

        $res['previousBalance'] = $previousBalance;
        $res['ledger'] = $ledger;

        echo json_encode($res);
    }

    public function chairmanReport()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Chairman Report";
        $data['content'] = $this->load->view('Administrator/project/chairmanReport', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getChairmanReport()
    {
        $data = json_decode($this->input->raw_input_stream);
        $groupBy = isset($data->groupBy) ? $data->groupBy : 'division';

        $groups = [
            'division' => ['field' => 'p.division_id', 'name' => "ifnull(d.name, 'Unassigned')", 'join' => 'left join tbl_divisions d on d.id = p.division_id'],
            'district' => ['field' => 'p.district_id', 'name' => "ifnull(d.name, 'Unassigned')", 'join' => 'left join tbl_districts d on d.id = p.district_id'],
            'thana'    => ['field' => 'p.thana_id', 'name' => "ifnull(d.name, 'Unassigned')", 'join' => 'left join tbl_police_stations d on d.id = p.thana_id'],
            'project'  => ['field' => 'p.id', 'name' => 'p.name', 'join' => ''],
        ];

        if (!isset($groups[$groupBy])) {
            $groupBy = 'division';
        }

        $group = $groups[$groupBy];
        $report = $this->db->query("
            select
                {$group['field']} as group_id,
                {$group['name']} as group_name,
                count(p.id) as project_count,
                ifnull(sum(sales.amount), 0) as sale_amount,
                ifnull(sum(purchases.amount), 0) as purchase_amount,
                ifnull(sum(cash_in.amount), 0) as cash_in,
                ifnull(sum(cash_out.amount), 0) as cash_out,
                ifnull(sum(sales.amount), 0) + ifnull(sum(cash_in.amount), 0)
                    - ifnull(sum(purchases.amount), 0) - ifnull(sum(cash_out.amount), 0) as profit_loss
            from tbl_projects p
            {$group['join']}
            left join (
                select project_id, sum(SaleMaster_TotalSaleAmount) as amount
                from tbl_salesmaster
                where status = 'a' and branch_id = ? and project_id is not null
                group by project_id
            ) sales on sales.project_id = p.id
            left join (
                select project_id, sum(PurchaseMaster_TotalAmount) as amount
                from tbl_purchasemaster
                where status = 'a' and branch_id = ? and project_id is not null
                group by project_id
            ) purchases on purchases.project_id = p.id
            left join (
                select project_id, sum(In_Amount) as amount
                from tbl_cashtransaction
                where status = 'a' and Tr_Type = 'In Cash' and branch_id = ? and project_id is not null
                group by project_id
            ) cash_in on cash_in.project_id = p.id
            left join (
                select project_id, sum(Out_Amount) as amount
                from tbl_cashtransaction
                where status = 'a' and Tr_Type = 'Out Cash' and branch_id = ? and project_id is not null
                group by project_id
            ) cash_out on cash_out.project_id = p.id
            where p.status = 'a' and p.branch_id = ?
            group by {$group['field']}, {$group['name']}
            order by group_name
        ", [$this->branch, $this->branch, $this->branch, $this->branch, $this->branch])->result();

        echo json_encode($report);
    }

    public function payment()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Project Payment";
        $data['content'] = $this->load->view('Administrator/project/payment', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getDue()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->projectId) && $data->projectId != null) {
            $clauses .= " and p.id = '$data->projectId'";
        }

        if (isset($data->districtId) && $data->districtId != null) {
            $clauses .= " and p.district_id = '$data->districtId'";
        }

        $dueResult = $this->mt->projectDue($clauses);

        echo json_encode($dueResult);
    }

    public function addPayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);

            $payment = (array)$paymentObj;
            $payment['invoice'] = $this->mt->generateProjectPaymentCode();
            $payment['status'] = 'a';
            $payment['add_by'] = $this->session->userdata("userId");
            $payment['add_time'] = date('Y-m-d H:i:s');
            $payment['branch_id'] = $this->session->userdata("BRANCHid");
            $payment['last_update_ip'] = get_client_ip();

            $this->db->insert('tbl_project_payments', $payment);
            $paymentId = $this->db->insert_id();

            $res = ['success' => true, 'message' => 'Payment added successfully', 'paymentId' => $paymentId];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function updatePayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);
            $paymentId = $paymentObj->id;

            $payment = (array)$paymentObj;
            unset($payment['id']);
            $payment['update_by'] = $this->session->userdata("userId");
            $payment['update_time'] = date('Y-m-d H:i:s');
            $payment['last_update_ip'] = get_client_ip();

            $this->db->where('id', $paymentObj->id)->update('tbl_project_payments', $payment);

            $res = ['success' => true, 'message' => 'Payment updated successfully', 'paymentId' => $paymentId];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function deletePayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $payment = array(
                'status' => 'd',
                'last_update_ip' => get_client_ip(),
            );
            $this->db->set($payment)->where('id', $data->paymentId)->update('tbl_project_payments');

            $res = ['success' => true, 'message' => 'Payment deleted successfully'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function getPayment()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        $status = "a";
        if (isset($data->status) && $data->status != '') {
            $status = $data->status;
        }
        if (isset($data->paymentType) && $data->paymentType != '' && $data->paymentType == 'received') {
            $clauses .= " and pp.type = 'CR'";
        }
        if (isset($data->paymentType) && $data->paymentType != '' && $data->paymentType == 'paid') {
            $clauses .= " and pp.type = 'CP'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and pp.date between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->projectId) && $data->projectId != '' && $data->projectId != null) {
            $clauses .= " and pp.project_id = '$data->projectId'";
        }

        $payments = $this->db->query("
            select
                pp.*,
                p.code as project_code,
                p.name as project_name,
                ba.account_name,
                ba.account_number,
                ba.bank_name,
                case pp.type
                    when 'CR' then 'Received'
                    when 'CP' then 'Paid'
                end as transaction_type,
                case pp.payment_by
                    when 'bank' then concat('Bank - ', ba.account_name, ' - ', ba.account_number, ' - ', ba.bank_name)
                    when 'By Cheque' then 'Cheque'
                    else 'Cash'
                end as paymentBy,
                ua.User_Name as added_by
            from tbl_project_payments pp
            join tbl_projects p on p.id = pp.project_id
            left join tbl_bank_accounts ba on ba.account_id = pp.account_id
            left join tbl_user ua on ua.User_SlNo = pp.add_by
            where pp.status = '$status'
            and pp.branch_id = ? $clauses
            order by pp.id desc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($payments);
    }

    public function invoice($id = Null)
    {
        $data['title'] = "Projct Payment Reports";
        if ($id != 'pr') {
            $pid["PamentID"] = $id;
            $this->session->set_userdata($pid);
        }
        $data['content'] = $this->load->view('Administrator/project/invoice', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }
}
