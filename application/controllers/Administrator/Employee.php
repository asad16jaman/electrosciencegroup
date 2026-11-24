<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->brunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model('Billing_model');
        $this->load->model("Model_myclass", "mmc", TRUE);
        $this->load->model('Model_table', "mt", TRUE);

        $vars['branch_info'] = $this->Billing_model->company_branch_profile($this->brunch);
        $this->load->vars($vars);
    }

    public function index()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee";
        $data['employeeId'] = 0;
        $data['content'] = $this->load->view('Administrator/employee/add_employee', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getEmployees()
    {
        $data = json_decode($this->input->raw_input_stream);

        $statusClause = " and e.status != 'd'";
        if (isset($data->with_deactive) && $data->with_deactive != '') {
            $statusClause = " and e.status != 'd'";
        }
        if (isset($data->employeeId) && $data->employeeId != '') {
            $statusClause = " and e.Employee_SlNo = '$data->employeeId'";
        }

        $employees = $this->db->query("
            SELECT 
                e.*,
                dp.Department_Name,
                ds.Designation_Name,
                concat(e.Employee_Name, ' - ', e.Employee_ID) as display_name
            from tbl_employee e 
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            where e.branch_id = ?
            $statusClause
            order by Employee_ID asc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($employees);
    }

    public function getMonths()
    {
        $months = $this->db->query(
            "SELECT * from tbl_month
            order by month_id desc
        "
        )->result();

        echo json_encode($months);
    }

    public function getEmployeePayments()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and e.Employee_SlNo = '$data->employeeId'";
        }

        if (isset($data->month) && $data->month != '') {
            $clauses .= " and ep.month_id = '$data->month'";
        }

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and ep.payment_date between '$data->dateFrom' and '$data->dateTo'";
        }

        $payments = $this->db->query("
            select 
                ep.*,
                e.Employee_Name,
                e.Employee_ID,
                e.salary_range,
                dp.Department_Name,
                ds.Designation_Name,
                m.month_name

            from tbl_employee_payment ep
            join tbl_employee e on e.Employee_SlNo = ep.Employee_SlNo
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            join tbl_month m on m.month_id = ep.month_id

            where ep.branch_id = ?
            and ep.status = 'a'
            $clauses
            order by ep.employee_payment_id desc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($payments);
    }

    public function getSalarySummary()
    {

        $data = json_decode($this->input->raw_input_stream);

        $yearMonth = date("Ym", strtotime($data->monthName));

        $summary = $this->db->query("
            select 
                e.*,
                dp.Department_Name,
                ds.Designation_Name,
                (
                    select ifnull(sum(ep.payment_amount), 0) from tbl_employee_payment ep
                    where ep.Employee_SlNo = e.Employee_SlNo
                    and ep.status = 'a'
                    and ep.month_id = " . $data->monthId . "
                    and ep.branch_id = " . $this->session->userdata('BRANCHid') . "
                ) as paid_amount,
                
                (
                    select ifnull(sum(ep.deduction_amount), 0) from tbl_employee_payment ep
                    where ep.Employee_SlNo = e.Employee_SlNo
                    and ep.status = 'a'
                    and ep.month_id = " . $data->monthId . "
                    and ep.branch_id = " . $this->session->userdata('BRANCHid') . "
                ) as deducted_amount,
                
                (
                    select e.salary_range - (paid_amount + deducted_amount)
                ) as due_amount
                
            from tbl_employee e 
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            where e.status = 'a'
            and " . $yearMonth . " >= extract(YEAR_MONTH from e.Employee_JoinDate)
            and e.branch_id = " . $this->session->userdata('BRANCHid') . "
        ")->result();

        echo json_encode($summary);
    }

    public function getPayableSalary()
    {
        $data = json_decode($this->input->raw_input_stream);

        $payableAmount = $this->db->query("
            select 
            (e.salary_range - ifnull(sum(ep.payment_amount + ep.deduction_amount), 0)) as payable_amount
            from tbl_employee_payment ep
            join tbl_employee e on e.Employee_SlNo = ep.Employee_SlNo
            where ep.status = 'a'
            and ep.month_id = ?
            and ep.Employee_SlNo = ?
            and ep.branch_id = ?        
        ", [$data->monthId, $data->employeeId, $this->brunch])->row()->payable_amount;

        echo $payableAmount;
    }

    //Designation
    public function designation()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Add Designation";
        $data['content'] = $this->load->view('Administrator/employee/designation', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getDesignations()
    {
        $designations = $this->db->query("select * from tbl_designation where status = 'a'")->result();
        echo json_encode($designations);
    }

    public function insert_designation()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $designation = array(
                "Designation_Name" => $data->Designation_Name,
                "status"          => 'a',
                "AddBy"           => $this->session->userdata("userId"),
                "AddTime"         => date("Y-m-d H:i:s"),
                "last_update_ip"         => get_client_ip(),

            );
            $this->db->insert('tbl_designation', $designation);
            $res = ['status' => true, 'message' => "Designation added successfull"];
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }


    public function designationupdate()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $designation = array(
                "Designation_Name" => $data->Designation_Name,
                "UpdateBy"         => $this->session->userdata("userId"),
                "UpdateTime"       => date("Y-m-d H:i:s"),
                "last_update_ip"         => get_client_ip(),
            );
            $this->db->where('Designation_SlNo', $data->Designation_SlNo);
            $this->db->update('tbl_designation', $designation);
            $res = ['status' => true, 'message' => "Designation Update"];
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }

    public function designationdelete()
    {
        $data = json_decode($this->input->raw_input_stream);
        $designation = array(
            'status'         => 'd',
            "DeletedBy"      => $this->session->userdata("userId"),
            "DeletedTime"    => date("Y-m-d H:i:s"),
            "last_update_ip" => get_client_ip(),
        );
        $this->db->where('Designation_SlNo', $data->designationId);
        $this->db->update('tbl_designation', $designation);
        echo json_encode(['status' => true, 'message' => 'Designation delete successfully']);
    }

    public function depertment()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Add Depertment";
        $data['content'] = $this->load->view('Administrator/employee/depertment', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }


    public function getDepartments()
    {
        $departments = $this->db->query("select * from tbl_department where status = 'a'")->result();
        echo json_encode($departments);
    }

    public function insert_depertment()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $department = array(
                "Department_Name" => $data->Department_Name,
                "status"          => 'a',
                "AddBy"           => $this->session->userdata("userId"),
                "AddTime"         => date("Y-m-d H:i:s"),
                "last_update_ip"         => get_client_ip(),

            );
            $this->db->insert('tbl_department', $department);
            $res = ['status' => true, 'message' => "Department added successfull"];
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }

    public function depertmentupdate()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $department = array(
                "Department_Name" => $data->Department_Name,
                "UpdateBy"                    => $this->session->userdata("userId"),
                "UpdateTime"                  => date("Y-m-d H:i:s"),
                "last_update_ip"         => get_client_ip(),
            );
            $this->db->where('Department_SlNo', $data->Department_SlNo);
            $this->db->update('tbl_department', $department);
            $res = ['status' => true, 'message' => "Department Update"];
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }

    public function depertmentdelete()
    {
        $data = json_decode($this->input->raw_input_stream);
        $department = array(
            'status'         => 'd',
            "DeletedBy"      => $this->session->userdata("userId"),
            "DeletedTime"    => date("Y-m-d H:i:s"),
            "last_update_ip" => get_client_ip(),
        );
        $this->db->where('Department_SlNo', $data->departmentId);
        $this->db->update('tbl_department', $department);
        echo json_encode(['status' => true, 'message' => 'Department delete successfully']);
    }

    //^^^^^^^^^^^^^^^^^^^^
    public function emplists($status = 'all')
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee List";
        $data['employes'] = $this->HR_model->get_all_employee_list($status);
        $data['content'] = $this->load->view('Administrator/employee/list', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    // fancybox add 
    public function month()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = 'Month';
        $data['content'] = $this->load->view('Administrator/employee/month', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function insert_month()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $check = $this->db->query("select * from tbl_month where month_name = ? ", $data->month_name)->row();
            if (!empty($check)) {
                $res = ['status' => false, 'message' => "Month already exists"];
            } else {
                $month = array(
                    "month_name" => $data->month_name,
                );
                $this->db->insert('tbl_month', $month);
                $res = ['status' => true, 'message' => "Month added successfull"];
            }
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }


    public function updateMonth()
    {
        $res = ['status' => false];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $check = $this->db->query("select * from tbl_month where month_id != ? and month_name = ? ", [$data->month_id, $data->month_name])->row();
            if (!empty($check)) {
                $res = ['status' => false, 'message' => "Month already exists"];
            } else {
                $month = array(
                    "month_name" => $data->month_name,
                );
                $this->db->where('month_id', $data->month_id);
                $this->db->update('tbl_month', $month);
                $res = ['status' => true, 'message' => "Month Update"];
            }
        } catch (\Throwable $th) {
            $res = ['status' => false, 'message' => $th->getMessage()];
        }
        echo json_encode($res);
    }

    // Employee Insert
    public function employee_insert()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $employeeObj = json_decode($this->input->post('data'));

            if (!empty($employeeObj->Employee_ID)) {
                $codeCount = $this->db->query("SELECT * from tbl_employee where Employee_ID = '$employeeObj->Employee_ID' and branch_id = '$this->brunch'")->num_rows();
                if ($codeCount != 0) {
                    echo json_encode(['success' => false, 'message' => 'Employee ID Already Exist!']);
                    exit;
                }
            }

            if (!empty($employeeObj->bio_id)) {
                $bio_id_count = $this->db->query("SELECT * from tbl_employee where bio_id = '$employeeObj->bio_id' and branch_id = '$this->brunch'")->num_rows();
                if ($bio_id_count != 0) {
                    echo json_encode(['success' => false, 'message' => 'Bio ID Already Exist!']);
                    exit;
                }
            }
            $employee_count = $this->db->query(
                "SELECT * from tbl_employee 
                where Designation_ID = '$employeeObj->Designation_ID'
                and Department_ID = '$employeeObj->Department_ID'
                and Employee_Name = '$employeeObj->Employee_Name'
                and branch_id = '$this->brunch'
            "
            )->num_rows();
            if ($employee_count != 0) {
                echo json_encode(['success' => false, 'message' => 'Duplicate Employee!']);
                exit;
            }

            $employee = (array)$employeeObj;
            unset($employee['Employee_SlNo']);
            $employee["branch_id"]      = $this->brunch;
            $employee["AddBy"]          = $this->session->userdata("userId");
            $employee["AddTime"]        = date("Y-m-d H:i:s");
            $employee["last_update_ip"] = get_client_ip();
            $this->db->insert('tbl_employee', $employee);
            $employeeId = $this->db->insert_id();

            if (!empty($_FILES)) {
                $imagePath = $this->mt->uploadImage($_FILES, 'image', 'uploads/employees', $employeeObj->Employee_ID);
                $this->db->query("update tbl_employee e set e.Employee_Pic_org = ?, e.Employee_Pic_thum = ? where e.Employee_SlNo = ?", [$imagePath, $imagePath, $employeeId]);
            }

            // Employee Documents
            if (
                isset($_FILES['documents']) &&
                !empty($_FILES['documents']['name'][0])
            ) {
                $documentCount = count($_FILES['documents']['name']);

                // Maximum 5 documents
                $documentCount = min($documentCount, 5);

                for ($i = 0; $i < $documentCount; $i++) {

                    if (empty($_FILES['documents']['name'][$i])) {
                        continue;
                    }

                    // $_FILES structure
                    $documentFile = [
                        'name'     => $_FILES['documents']['name'][$i],
                        'type'     => $_FILES['documents']['type'][$i],
                        'tmp_name' => $_FILES['documents']['tmp_name'][$i],
                        'error'    => $_FILES['documents']['error'][$i],
                        'size'     => $_FILES['documents']['size'][$i]
                    ];

                    $fileData = [
                        'document' => $documentFile
                    ];

                    $documentPath = $this->mt->uploadImage($fileData, 'document', 'uploads/employees/documents', $employeeObj->Employee_ID . '_' . $i);

                    // Database insert
                    $documentData = [
                        'employee_id'    => $employeeId,
                        'image'          => $documentPath,
                        'add_by'         => $this->session->userdata("userId"),
                        'add_time'       => date("Y-m-d H:i:s"),
                        'last_update_ip' => get_client_ip(),
                        'branch_id'      => $this->brunch
                    ];

                    $this->db->insert('tbl_employee_documents', $documentData);
                }
            }

            $res = ['success' => true, 'message' => 'Save Success!'];
        } catch (\Throwable $th) {
            $res = ['success' => false, 'message' => $th->getMessage()];
        }

        echo json_encode($res);
    }

    public function employee_edit($id)
    {
        $data['title'] = "Edit Employee";
        $data['employeeId'] = $id;
        $data['content'] = $this->load->view('Administrator/employee/add_employee', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function employee_Update()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $employeeObj = json_decode($this->input->post('data'));

            if (!empty($employeeObj->Employee_ID)) {
                $codeCount = $this->db->query("SELECT * from tbl_employee where Employee_ID = '$employeeObj->Employee_ID' and branch_id = '$this->brunch' and Employee_SlNo != '$employeeObj->Employee_SlNo'")->num_rows();
                if ($codeCount != 0) {
                    echo json_encode(['success' => false, 'message' => 'Employee ID Already Exist!']);
                    exit;
                }
            }

            if (!empty($employeeObj->bio_id)) {
                $bio_id_count = $this->db->query("SELECT * from tbl_employee where bio_id = '$employeeObj->bio_id' and branch_id = '$this->brunch' and Employee_SlNo != '$employeeObj->Employee_SlNo'")->num_rows();
                if ($bio_id_count != 0) {
                    echo json_encode(['success' => false, 'message' => 'Bio ID Already Exist!']);
                    exit;
                }
            }

            $employee_count = $this->db->query(
                "SELECT * from tbl_employee 
            where Designation_ID = '$employeeObj->Designation_ID'
            and Department_ID = '$employeeObj->Department_ID'
            and Employee_Name = '$employeeObj->Employee_Name'
            and branch_id = '$this->brunch'
            and Employee_SlNo != '$employeeObj->Employee_SlNo'
        "
            )->num_rows();

            if ($employee_count != 0) {
                echo json_encode(['success' => false, 'message' => 'Duplicate Employee!']);
                exit;
            }
            $employee = (array)$employeeObj;
            $employeeId = $employeeObj->Employee_SlNo;

            unset($employee['Employee_SlNo']);
            $employee["branch_id"] = $this->session->userdata("BRANCHid");
            $employee["UpdateBy"] = $this->session->userdata("userId");
            $employee["UpdateTime"] = date("Y-m-d H:i:s");
            $employee["last_update_ip"] = get_client_ip();
            $this->db->where('Employee_SlNo', $employeeId)->update('tbl_employee', $employee);

            $employeeImage = $this->db->query("select * from tbl_employee c where c.Employee_SlNo = ?", $employeeId)->row();
            if (!empty($_FILES)) {
                $oldImgFile = $employeeImage->Employee_Pic_thum;
                if (file_exists($oldImgFile)) {
                    unlink($oldImgFile);
                }
                $imagePath = $this->mt->uploadImage($_FILES, 'image', 'uploads/employees', $employeeObj->Employee_ID);
                $this->db->query("update tbl_employee e set e.Employee_Pic_org = ?, e.Employee_Pic_thum = ? where e.Employee_SlNo = ?", [$imagePath, $imagePath, $employeeId]);
            }

            // delete old documents
            $deletedDocuments = json_decode($this->input->post('deletedDocuments'), true);

            if (!empty($deletedDocuments)) {

                foreach ($deletedDocuments as $documentId) {

                    $document = $this->db->query("
                        SELECT *
                        FROM tbl_employee_documents
                        WHERE id = ?
                        AND employee_id = ?
                        AND branch_id = ?
                    ", [
                        $documentId,
                        $employeeId,
                        $this->brunch
                    ])->row();

                    if ($document) {

                        if (!empty($document->image) && file_exists($document->image)) {
                            unlink($document->image);
                        }

                        $this->db
                            ->where('id', $documentId)
                            ->where('employee_id', $employeeId)
                            ->where('branch_id', $this->brunch)
                            ->delete('tbl_employee_documents');
                    }
                }
            }

            // New Documents Insert
            if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {

                $documentCount = count($_FILES['documents']['name']);
                $documentCount = min($documentCount, 5);
                for ($i = 0; $i < $documentCount; $i++) {

                    if (empty($_FILES['documents']['name'][$i])) {
                        continue;
                    }
                    // Single file structure
                    $documentFile = [
                        'name' => $_FILES['documents']['name'][$i],
                        'type' => $_FILES['documents']['type'][$i],
                        'tmp_name' =>  $_FILES['documents']['tmp_name'][$i],
                        'error' => $_FILES['documents']['error'][$i],
                        'size' => $_FILES['documents']['size'][$i]
                    ];

                    $fileData = [ 'document' => $documentFile];
                    $imagePath = $this->mt->uploadImage($fileData, 'document', 'uploads/employees/documents', $employeeObj->Employee_ID . '_' . $i);

                    // Insert document record
                    $documentData = [
                        'employee_id' => $employeeId,
                        'image' => $imagePath,
                        'add_by' => $this->session->userdata("userId"),
                        'add_time' => date("Y-m-d H:i:s"),
                        'last_update_ip' => get_client_ip(),
                        'branch_id' => $this->brunch
                    ];

                    $this->db->insert('tbl_employee_documents', $documentData);
                }
            }

            $res = ['success' => true, 'message' => 'Employee Update Success!'];
        } catch (\Throwable $th) {
            $res = ['success' => false, 'message' => $th->getMessage()];
        }

        echo json_encode($res);
    }

    public function employee_Delete()
    {
        $id = $this->input->post('deleted');

        // Employee profile image
        $employee = $this->db->query("
            SELECT Employee_Pic_org, Employee_Pic_thum
            FROM tbl_employee
            WHERE Employee_SlNo = ?
        ", [$id])->row();

        if ($employee) {

            // Profile image
            if (
                !empty($employee->Employee_Pic_thum) &&
                file_exists($employee->Employee_Pic_thum)
            ) {
                unlink($employee->Employee_Pic_thum);
            }

            if (
                !empty($employee->Employee_Pic_org) &&
                $employee->Employee_Pic_org != $employee->Employee_Pic_thum &&
                file_exists($employee->Employee_Pic_org)
            ) {
                unlink($employee->Employee_Pic_org);
            }
        }

        // Employee Documents
        $documents = $this->db->query("
            SELECT image
            FROM tbl_employee_documents
            WHERE employee_id = ?
        ", [$id])->result();

        foreach ($documents as $document) {

            $filePath = 'uploads/employees/documents/' . $document->image;

            if (
                !empty($document->image) &&
                file_exists($filePath)
            ) {
                unlink($filePath);
            }
        }

        // Delete document records
        $this->db->where('employee_id', $id)->delete('tbl_employee_documents');

        // Soft delete employee
        $rules = array(
            'status'         => 'd',
            'DeletedBy'      => $this->session->userdata("userId"),
            'DeletedTime'    => date("Y-m-d H:i:s"),
            'last_update_ip' => get_client_ip()
        );

        $this->db->set($rules)->where('Employee_SlNo', $id)->update('tbl_employee');
    }

    public function active()
    {
        $fld = 'Employee_SlNo';
        $id = $this->input->post('deleted');
        $this->mt->active("tbl_employee", $id, $fld);
    }

    public function employeesalarypayment()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee Salary Payment";
        $data['content'] = $this->load->view('Administrator/employee/employee_salary', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function selectEmployee()
    {
        $data['title'] = "Employee Salary Payment";
        $employee_id = $this->input->post('employee_id');
        $query = $this->db->query("SELECT `salary_range` FROM tbl_employee where Employee_SlNo='$employee_id'");
        $data['employee'] = $query->row();
        $this->load->view('Administrator/employee/ajax_employeey', $data);
    }

    public function addEmployeePayment()
    {
        $res = ['success' => false, 'message' => 'Nothing happened'];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);
            $payment = (array)$paymentObj;
            unset($payment['employee_payment_id']);
            $payment['status']         = 'a';
            $payment['AddBy']          = $this->session->userdata('userId');
            $payment['AddTime']        = date('Y-m-d H:i:s');
            $payment['last_update_ip'] = get_client_ip();
            $payment['branch_id']      = $this->brunch;

            $this->db->insert('tbl_employee_payment', $payment);
            $res = ['success' => true, 'message' => 'Employee payment added'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function employeesalaryreport()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee Salary Report";
        $data['content'] = $this->load->view('Administrator/employee/employee_salary_report', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function EmployeeSalary_list()
    {
        $datas['employee_id'] = $employee_id = $this->input->post('employee_id');
        $datas['month'] = $month = $this->input->post('month');

        $this->session->set_userdata($datas);

        $BRANCHid = $this->session->userdata("BRANCHid");

        if ($employee_id == 'All') {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.branch_id', $BRANCHid)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        } else {


            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.branch_id', $BRANCHid)
                ->where('tbl_employee.Employee_SlNo	', $employee_id)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        }

        $data['month'] = $month;
        $this->load->view('Administrator/employee/employee_salary_report_list', $data);
    }

    public function EmploeePaymentReportPrint()
    {
        $BRANCHid = $this->session->userdata("BRANCHid");

        $employee_id = $this->session->userdata('employee_id');
        $month = $this->session->userdata('month');

        if ($employee_id == 'All') {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.branch_id', $BRANCHid)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        } else {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.branch_id', $BRANCHid)
                ->where('tbl_employee.Employee_SlNo	', $employee_id)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        }

        $data['month'] = $month;
        $this->load->view('Administrator/employee/employee_salary_report_print', $data);
    }

    public function edit_employee_salary($id)
    {
        $data['title'] = "Edit Employee Salary";
        $BRANCHid = $this->session->userdata("BRANCHid");
        $query = $this->db->query("SELECT tbl_employee.*,tbl_employee_payment.*,tbl_month.*,tbl_designation.* FROM tbl_employee left join tbl_employee_payment on tbl_employee_payment.Employee_SlNo=tbl_employee.Employee_SlNo left join tbl_month on tbl_employee_payment.month_id=tbl_month.month_id left join tbl_designation on tbl_designation.Designation_SlNo=tbl_employee.Designation_ID where tbl_employee_payment.employee_payment_id='$id' AND tbl_employee_payment.branch_id='$BRANCHid'");
        $data['selected'] = $query->row();
        //echo "<pre>";print_r($data['selected']);exit;
        $data['content'] = $this->load->view('Administrator/employee/edit_employee_salary', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function updateEmployeePayment()
    {
        $res = ['success' => false, 'message' => 'Nothing happened'];
        try {
            $paymentObj = json_decode($this->input->raw_input_stream);
            $payment = (array)$paymentObj;
            unset($payment['employee_payment_id']);
            $payment['UpdateBy'] = $this->session->userdata('userId');
            $payment['UpdateTime'] = Date('Y-m-d H:i:s');
            $payment['last_update_ip'] = get_client_ip();

            $this->db->where('employee_payment_id', $paymentObj->employee_payment_id)->update('tbl_employee_payment', $payment);
            $res = ['success' => true, 'message' => 'Employee payment updated'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function deleteEmployeePayment()
    {
        $res = ['success' => false, 'message' => 'Nothing happened'];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $rules = array(
                'status'         => 'd',
                "DeletedBy"      => $this->session->userdata("userId"),
                "DeletedTime"    => date("Y-m-d H:i:s"),
                "last_update_ip" => get_client_ip()
            );
            $this->db->set($rules)->where('employee_payment_id', $data->paymentId)->update('tbl_employee_payment');
            $res = ['success' => true, 'message' => 'Employee payment deleted'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }


    //salary Payment
    public function employeePayment()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee Salary Payment";
        $data['content'] = $this->load->view('Administrator/employee/salary/payment', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function checkPaymentMonth()
    {
        $data = json_decode($this->input->raw_input_stream);
        $monthId = $data->month_id;

        $query = $this->db->query("SELECT month_id FROM tbl_employee_payment WHERE month_id = ? and branch_id = ? and status = 'a'", [$monthId, $this->session->userdata("BRANCHid")]);
        if ($query->num_rows() > 0) {
            echo json_encode(['success' => true]);
            exit();
        }
        echo json_encode(['success' => false]);
        exit();
    }

    public function getPayments()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and ep.payment_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->user_id) && $data->user_id != '') {
            $clauses .= " and ep.AddBy = '$data->user_id'";
        }

        if (isset($data->month_id) && $data->month_id != '') {
            $clauses .= " and ep.month_id = '$data->month_id'";
        }

        $payments = $this->db->query("
            SELECT ep.*,
            m.month_name,
            u.User_Name

            from tbl_employee_payment ep
            join tbl_month m on m.month_id = ep.month_id
            left join tbl_user u on u.User_SlNo = ep.AddBy

            where ep.status = 'a'
            and ep.branch_id = ?
            $clauses
            order by ep.id desc
        ", $this->session->userdata("BRANCHid"))->result();

        if (isset($data->details)) {
            foreach ($payments as $payment) {
                $payment->details = $this->db->query("
                    SELECT pd.*,
                    e.Employee_ID,
                    e.Employee_Name,
                    d.Department_Name,
                    de.Designation_Name

                    from tbl_employee_payment_details pd
                    join tbl_employee e on e.Employee_SlNo = pd.employee_id
                    left join tbl_department d on d.Department_SlNo = e.Department_ID
                    left join tbl_designation de on de.Designation_SlNo = e.Designation_ID

                    where pd.status = 'a'
                    and pd.payment_id = '$payment->id'
                ")->result();
            }
        }

        echo json_encode($payments);
    }

    public function getSalaryDetails()
    {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = "";

        if (isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and ep.payment_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->month_id) && $data->month_id != '') {
            $clauses .= " and ep.month_id = '$data->month_id'";
        }

        if (isset($data->employee_id) && $data->employee_id != '') {
            $clauses .= " and pd.employee_id = '$data->employee_id'";
        }

        $payments = $this->db->query(
            "SELECT pd.*,
            e.Employee_ID,
            e.Employee_Name,
            d.Department_Name,
            de.Designation_Name,
            m.month_name,
            ep.payment_date

            from tbl_employee_payment_details pd
            join tbl_employee_payment ep on ep.id = pd.payment_id
            join tbl_month m on m.month_id = ep.month_id
            join tbl_employee e on e.Employee_SlNo = pd.employee_id
            left join tbl_designation de on de.Designation_SlNo = e.Designation_ID
            left join tbl_department d on d.Department_SlNo = e.Department_ID
            
            where pd.status = 'a'
            and pd.branch_id = ?
            $clauses
        ",
            $this->session->userdata("BRANCHid")
        )->result();

        echo json_encode($payments);
    }

    public function saveSalaryPayment()
    {
        $res = ["success" => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $paymentObj = $data->payment;
            $employees = $data->employees;

            $payment = (array)$paymentObj;
            unset($payment['id']);
            $payment['AddBy'] = $this->session->userdata("userId");
            $payment['AddTime'] = date("Y-m-d H:i:s");
            $payment['branch_id'] = $this->session->userdata("BRANCHid");
            $payment['status'] = 'a';

            $this->db->insert('tbl_employee_payment', $payment);
            $payment_id = $this->db->insert_id();

            $total_payment_amount = 0;

            foreach ($employees as $emp) {
                $employee = [
                    'payment_id'     => $payment_id,
                    'employee_id'    => $emp->Employee_SlNo,
                    'salary'         => $emp->salary,
                    'benefit'        => $emp->benefit,
                    'deduction'      => $emp->deduction,
                    'net_payable'    => $emp->net_payable,
                    'payment'        => $emp->payment,
                    'comment'        => $emp->comment,
                    'AddBy'          => $this->session->userdata("userId"),
                    'AddTime'        => date("Y-m-d H:i:s"),
                    'last_update_ip' => get_client_ip(),
                    'branch_id'      => $this->session->userdata("BRANCHid"),
                    'status'         => 'a',
                ];

                $this->db->insert('tbl_employee_payment_details', $employee);

                $total_payment_amount += $emp->payment;
            }

            $this->db->where('id', $payment_id)->update('tbl_employee_payment', ['total_payment_amount' => $total_payment_amount]);

            $res = ["success" => true, 'message' => 'Payment Success'];

            echo json_encode($res);
        } catch (\Exception $e) {
            $res = ["success" => false, 'message' => $e->getMessage()];
        }
    }

    public function updateSalaryPayment()
    {
        $res = ["success" => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $paymentObj = $data->payment;
            $employees = $data->employees;
            $payment_id = $paymentObj->id;

            $payment = (array)$paymentObj;
            unset($payment['id']);
            $payment['UpdateBy'] = $this->session->userdata("userId");
            $payment['UpdateTime'] = date("Y-m-d H:i:s");

            $this->db->where('id', $payment_id);
            $this->db->update('tbl_employee_payment', $payment);

            $total_payment_amount = 0;

            foreach ($employees as $emp) {
                $employee = [
                    'payment_id'  => $payment_id,
                    'employee_id' => $emp->employee_id,
                    'salary'      => $emp->salary,
                    'benefit'     => $emp->benefit,
                    'deduction'   => $emp->deduction,
                    'net_payable' => $emp->net_payable,
                    'payment'     => $emp->payment,
                    'comment'     => $emp->comment,
                    'UpdateBy'    => $this->session->userdata("userId"),
                    'UpdateTime'  => date("Y-m-d H:i:s"),
                ];

                $this->db->where('id', $emp->id);
                $this->db->update('tbl_employee_payment_details', $employee);

                $total_payment_amount += $emp->payment;
            }

            $this->db->where('id', $payment_id)->update('tbl_employee_payment', ['total_payment_amount' => $total_payment_amount]);

            $res = ["success" => true, 'message' => 'Update Success'];

            echo json_encode($res);
        } catch (\Exception $e) {
            $res = ["success" => false, 'message' => $e->getMessage()];
        }
    }

    public function employeePaymentReport()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Employee Salary Payment Report";
        $data['content'] = $this->load->view('Administrator/employee/salary/payment_report', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function deletePayment()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);

            $details = $this->db->query(
                "SELECT pd.id from tbl_employee_payment_details pd
                    where pd.payment_id = '$data->paymentId'
                "
            )->result();

            $rules = array(
                'status'         => 'd',
                "DeletedBy"      => $this->session->userdata("userId"),
                "DeletedTime"    => date("Y-m-d H:i:s"),
                "last_update_ip" => get_client_ip()
            );
            foreach ($details as $detail) {
                $this->db->set($rules)->where('id', $detail->id)->update('tbl_employee_payment_details');
            }

            $this->db->set($rules)->where('id', $data->paymentId)->update('tbl_employee_payment');
            $res = ['success' => true, 'message' => 'Salary Payment deleted'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function getDocuments()
    {
       $data = json_decode($this->input->raw_input_stream);

        if (empty($data->employeeId)) {
            echo json_encode([
                'success' => false,
                'documents' => []
            ]);
            return;
        }

        $documents = $this->db->query("
            SELECT 
                ed.*,
                e.Employee_Name,
                e.Employee_ID
            FROM tbl_employee_documents ed
            JOIN tbl_employee e ON e.Employee_SlNo = ed.employee_id
            WHERE ed.employee_id = ?
            AND ed.branch_id = ?
            ORDER BY ed.id ASC
        ", [
            $data->employeeId,
            $this->brunch
        ])->result();

        echo json_encode([
            'success' => true,
            'documents' => $documents
        ]);
    }

    public function employeeDetails($employeeId = 0)
    {
        $access = $this->mt->userAccess();

        if (!$access) {
            redirect(base_url());
        }

        if (!$employeeId) {
            redirect(base_url('emplists'));
        }

        $employee = $this->db->query("
            SELECT
                e.*,
                dp.Department_Name,
                ds.Designation_Name
            FROM tbl_employee e

            LEFT JOIN tbl_department dp
                ON dp.Department_SlNo = e.Department_ID

            LEFT JOIN tbl_designation ds
                ON ds.Designation_SlNo = e.Designation_ID

            WHERE e.Employee_SlNo = ?
            AND e.branch_id = ?
            LIMIT 1
        ", [
            $employeeId,
            $this->brunch
        ])->row();

        if (!$employee) {
            show_404();
        }

        $documents = $this->db->query("
            SELECT *
            FROM tbl_employee_documents
            WHERE employee_id = ?
            AND branch_id = ?
            ORDER BY id ASC
        ", [
            $employeeId,
            $this->brunch
        ])->result();

        $data['title'] = 'Employee Details';
        $data['employee'] = $employee;
        $data['documents'] = $documents;
        $data['content'] = $this->load->view('Administrator/employee/details', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }
}
