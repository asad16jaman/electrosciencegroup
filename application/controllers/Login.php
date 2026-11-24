<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("model_myclass", "mmc", TRUE);
		$this->load->model('model_table', "mt", TRUE);
		$this->load->model('Billing_model');
		$this->load->helper('JWT');
		$this->load->helper('my_helper');
	}
	public function index()
	{
		$data['title'] = "Login";
		$this->load->view('login/login', $data);
		$this->session->sess_destroy();
		session_unset();
		session_write_close();
	}

	public function brach_access($id)
	{
		$data['branch_id'] = $id;
		$this->load->view('Administrator/branch_access', $data);
	}

	function procedure()
	{

		$user = $this->input->post('user_name');
		$pass = md5($this->input->post('password'));
		$query = $this->db->query("SELECT u.User_SlNo, u.User_ID, u.FullName, u.User_Name, u.userBranch_id, u.UserType, u.image_name as user_image, u.status AS userstatus, br.branch_id, br.Branch_name, br.Branch_sales FROM tbl_user AS u LEFT JOIN tbl_branch AS br ON br.branch_id = u.userBranch_id where br.status = 'a' and u.User_Name = ? AND u.User_Password = ?", [$user, $pass]);

		$data = $query->row();

		if (isset($data)) {

			if ($data->userstatus == 'a') {

				$company = $this->db->select(['Company_Logo_org', 'Currency_Name'])->get('tbl_company')->row();

				$this->db->insert(
					'tbl_user_activity',
					[
						'user_id' 		=>	$data->User_SlNo,
						'ip_address' 	=>	get_client_ip(),
						'login_time' 	=>	date("Y-m-d H:i:s"),
						'status' 		=>	'a',
						'branch_id' 	=>	$data->userBranch_id,
					]
				);

				$sdata['user_activity_id'] = $this->db->insert_id();

				$sdata['userId'] = $data->User_SlNo;
				$sdata['BRANCHid'] = $data->userBranch_id;
				$sdata['old_branch_id'] = $data->userBranch_id;
				$sdata['FullName'] = $data->FullName;
				$sdata['User_Name'] = $data->User_Name;
				$sdata['user_image'] = $data->user_image;
				$sdata['accountType'] = $data->UserType;
				$sdata['userBrunch'] = $data->Branch_sales;
				$sdata['Branch_name'] = $data->Branch_name;
				$sdata['Brunch_image'] = $company->Company_Logo_org;
				$sdata['Currency_Name'] = $company->Currency_Name;
				$sdata['progressBar'] = true;
				$this->session->set_userdata($sdata);
				redirect('Administrator/');
			} else {
				$sdata['message'] = "Sorry your are deactivated";
				$this->load->view('login/login', $sdata);
			}
		} else {
			$sdata['message'] = "Invalid User name or Password";
			$this->load->view('login/login', $sdata);
		}
	}


	public function forgotpassword()
	{
		$data['title'] = "Forgot Password";
		$this->load->view('ForgotPassword', $data);
	}

	public function logout()
	{
		$this->db->where('id', $this->session->userdata("user_activity_id"));
		$this->db->update('tbl_user_activity', ['logout_time' => date("Y-m-d H:i:s")]);

		$this->session->unset_userdata('user_activity_id');
		$this->session->unset_userdata('userId');
		$this->session->unset_userdata('User_Name');
		$this->session->unset_userdata('accountType');
		$this->session->unset_userdata('module');

		$this->session->sess_destroy();
		session_unset();
		session_write_close();

		redirect("Login");
	}

	public function userLogin()
	{
		$res = ['success' => false, 'message' => ''];

		$postData = json_decode($this->input->raw_input_stream);

		$user = $postData->username;
		$pass = md5($postData->password);

		$query = $this->db->query("SELECT u.User_SlNo, u.User_ID, u.FullName, u.User_Name, u.userBranch_id, u.UserType, u.image_name as user_image, u.status AS userstatus, br.branch_id, br.Branch_name, br.Branch_sales FROM tbl_user AS u LEFT JOIN tbl_branch AS br ON br.branch_id = u.userBranch_id where br.status = 'a' and u.User_Name = ? AND u.User_Password = ?", [$user, $pass]);
		$data = $query->row();
		if (isset($data)) {

			if ($data->userstatus == 'a') {
				$encData['userId'] = $data->User_SlNo;
				$encData['BRANCHid'] = $data->userBranch_id;
				$encData['FullName'] = $data->FullName;
				$encData['User_Name'] = $data->User_Name;

				$resData['userId'] = $data->User_SlNo;
				$resData['fullName'] = $data->FullName;
				$resData['username'] = $data->User_Name;
				$resData['branchId'] = $data->userBranch_id;
				$resData['branchName'] = $data->Branch_name;

				$token = JWT::encode($encData, 'secret1234');

				$res = [
					'success' => true,
					'message' => 'Login success',
					'data' => $resData,
					'token' => $token
				];
			}
		} else {
			$res = ['success' => false, 'message' => 'Invalid login'];
		}

		echo json_encode($res);
	}


	public function saleInvoicePrint($saleId)
	{
		$data['title'] = "Sales Invoice";
		$data['salesId'] = $saleId;
		$this->load->view('Administrator/sales/qrcode_invoice', $data);
	}

	public function getCurrentBranch($branchId)
	{
		$branch = $this->Billing_model->company_branch_profile($branchId);
		echo json_encode($branch);
	}

	public function getSales()
	{
		$data = json_decode($this->input->raw_input_stream);
		$clauses = "";
		$limit = "";
		$status = "a";
		if (isset($data->status) && $data->status != '') {
			$status = $data->status;
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
                    p.Product_Code,
                    p.Product_Name,
                    pc.ProductCategory_Name,
                    u.Unit_Name
                from tbl_saledetails sd
                join tbl_product p on p.Product_SlNo = sd.Product_IDNo
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                join tbl_unit u on u.Unit_SlNo = p.Unit_ID
                where sd.SaleMaster_IDNo = ?
                and sd.status = '$status'
            ", $data->salesId)->result();

			$res['saleDetails'] = $saleDetails;
		}
		$sales = $this->db->query("
            select 
            concat(sm.SaleMaster_InvoiceNo, ' - ', ifnull(c.Customer_Name, sm.customerName)) as invoice_text,
            sm.*,
            ifnull(c.Customer_Code, 'Cash Customer') as Customer_Code,
            ifnull(c.Customer_Name, sm.customerName) as Customer_Name,
            ifnull(c.Customer_Mobile, sm.customerMobile) as Customer_Mobile,
            ifnull(c.Customer_Address, sm.customerAddress) as Customer_Address,
            c.Customer_Type,
            ba.account_name,
            ba.account_number,
            ba.bank_name,
            e.Employee_Name,
            e.Employee_ID,
            br.Branch_name,
            ua.User_Name as added_by,
            ud.User_Name as deleted_by
            from tbl_salesmaster sm
            left join tbl_customer c on c.Customer_SlNo = sm.SalseCustomer_IDNo
            left join tbl_bank_accounts ba on ba.account_id = sm.accountId
            left join tbl_employee e on e.Employee_SlNo = sm.employee_id
            left join tbl_user ua on ua.User_SlNo = sm.AddBy
            left join tbl_user ud on ud.User_SlNo = sm.DeletedBy
            left join tbl_branch br on br.branch_id = sm.branch_id
            where sm.status = '$status'
            $clauses
            order by sm.SaleMaster_SlNo desc
            $limit
        ")->result();

		$res['sales'] = $sales;

		echo json_encode($res);
	}
}
