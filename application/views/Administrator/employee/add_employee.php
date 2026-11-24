<?php
$serial = "E1000";
$query = $this->db->query("SELECT * FROM tbl_employee order by Employee_ID desc limit 1");
$result = $query->row();
if (@$result->Employee_ID != null) {
	$serial = $result->Employee_ID;
}
$serial = explode("E", $serial);
if ($serial[1] >= 9) {
	$serial = $serial['1'];
	$autoserial = $serial + 1;
	$generateCode = "E" . $autoserial;
} else {
	$serial = $serial[1];
	$autoserial = $serial + 1;
	$generateCode = "E0" . $autoserial;
}
?>
<style>
	.v-select {
		margin-bottom: 5px;
		background: #fff;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
		border: none;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}

	.add-button {
		padding: 2.5px;
		width: 100%;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
		cursor: pointer;
		border-radius: 3px;
	}

	.add-button:hover {
		background-color: #41add6;
		color: white;
	}

	#employee label {
		font-size: 13px;
	}

	#employee select {
		border-radius: 3px;
	}

	#employee .add-button {
		padding: 2.5px;
		width: 100%;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
		cursor: pointer;
		border-radius: 3px;
	}

	#employee .add-button:hover {
		background-color: #41add6;
		color: white;
	}

	#employee input[type="file"] {
		display: none;
	}

	#employee .custom-file-upload {
		border: 1px solid #ccc;
		display: inline-block;
		padding: 5px 12px;
		cursor: pointer;
		margin-top: 5px;
		background-color: #298db4;
		border: none;
		color: white;
	}

	#employee .custom-file-upload:hover {
		background-color: #41add6;
	}

	#employeeImage {
		height: 100%;
	}
</style>
<div class="row" id="employee">
	<form @submit.prevent="saveEmployee">
		<div class="col-md-6">
			<fieldset class="scheduler-border scheduler-search" style="height: auto;">
				<legend class="scheduler-border">Job Information</legend>
				<div class="control-group">
					<div class="form-group">
						<label class="col-md-4 control-label" for="Product_id"> Employee ID </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" name="Employeer_id" v-model="employee.Employee_ID" id="Employeer_id" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label" for="Product_id"> Bio ID </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" name="bio_id" id="bio_id" v-model="employee.bio_id" autocomplete="off" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_name"> Employee Name </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="em_name" name="em_name" v-model="employee.Employee_Name" autocomplete="off" placeholder="Employee Name .." class="form-control" />
							<div id="em_name_" class="col-md-12"></div>
						</div>
						<label class="col-md-1 em_name" style="display:none;color: red;padding-left: 0px;margin-left: -8px;">required</label>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_Depertment"> Department </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6" style="display: flex;align-items:center;">
							<div style="width: 88%;">
								<v-select :options="departments" v-model="selectedDepartment" label="Department_Name"></v-select>
							</div>
							<div style="width:12%;margin-left:2px;">
								<a href="<?= base_url('depertment') ?>" title="Add New Department" style="margin-top:-3px;" class="add-button" target="_blank"><i class="fa fa-plus" aria-hidden="true"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_Designation"> Designation </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6" style="display: flex;align-items:center;">
							<div style="width: 88%;">
								<v-select :options="designations" v-model="selectedDesignation" label="Designation_Name"></v-select>
							</div>
							<div style="width:12%;margin-left:2px;">
								<a href="<?= base_url('designation') ?>" title="Add New Designation" style="margin-top:-3px;" class="add-button" target="_blank"><i class="fa fa-plus" aria-hidden="true"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_Joint_date">Joint Date</label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input class="form-control" style="margin-bottom:4px;border-radius: 5px !important;" name="em_Joint_date" id="em_Joint_date" type="date" v-model="employee.Employee_JoinDate" />
						</div>
						<label class="col-md-1 em_Joint_date" style="display:none;color: red;padding-left: 0px;margin-left: -8px;">required</label>
					</div>


					<div class="form-group">
						<label class="col-md-4 control-label" for="salary_range">Salary Range</label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="number" min="0" step="any" id="salary_range" name="salary_range" v-model="employee.salary_range" class="form-control" />
							<div id="salary_range_" class="col-md-12"></div>
						</div>
						<label class="col-md-1 salary_range" style="display:none;color: red;padding-left: 0px;margin-left: -8px;">required</label>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="status"> Activation status</label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<select class="form-control" name="status" id="status" v-model="employee.status">
								<option value="a"> Active </option>
								<option value="p"> Deactive </option>
							</select>
						</div>
						<label class="col-md-1 status" style="display:none;color: red;padding-left: 0px;margin-left: -8px;">required</label>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="col-md-6">
			<fieldset class="scheduler-border scheduler-search">
				<legend class="scheduler-border">Contact Information</legend>
				<div class="control-group">

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_Present_address"> Present Address </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="em_Present_address" name="em_Present_address" v-model="employee.Employee_PrasentAddress" placeholder="Present Address" autocomplete="off" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_Permanent_address"> Permanent Address </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="em_Permanent_address" name="em_Permanent_address" v-model="employee.Employee_PermanentAddress" placeholder="Parmanent Address" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_contact"> Contact No </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="em_contact" name="em_contact" v-model="employee.Employee_ContactNo" placeholder="Contact No" autocomplete="off" class="form-control" />
						</div>
						<label class="col-md-1 em_contact" style="display:none;color: red;padding-left: 0px;margin-left: -8px;">required</label>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="ec_email"> E-mail </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="ec_email" name="ec_email" v-model="employee.Employee_Email" placeholder="E-mail" autocomplete="off" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="nid"> NID </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="nid" name="nid" v-model="employee.Employee_NID" placeholder="NID" autocomplete="off" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_reference"> Reference </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<textarea id="em_reference" rows="3" name="em_reference" v-model="employee.Employee_Reference" placeholder="Reference" class="form-control" autocomplete="off"></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for=""> </label>
						<label class="col-md-1 control-label"></label>
						<div class="col-md-6 text-right">
							<?php if (buttonAction('entry') || buttonAction('update')): ?>
								<button type="submit" name="btnSubmit" :title="employee.Employee_SlNo == '' ? 'Save' : 'Update'" class="btnSave">
									{{ employee.Employee_SlNo == '' ? 'Save' : 'Update' }}
									<i class="ace-icon fa fa-arrow-right icon-on-right"></i>
								</button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col-md-6">
			<fieldset class="scheduler-border scheduler-search">
				<legend class="scheduler-border">Personal Information</legend>
				<div class="control-group">

					<div class="form-group">
						<label class="col-md-4 control-label" for="form-field-1"> Father's Name </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="em_father" name="em_father" v-model="employee.Employee_FatherName" placeholder="Father's Name" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="form-field-1"> Mother's Name </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input type="text" id="mother_name" name="mother_name" v-model="employee.Employee_MotherName" placeholder="Mother's Name" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="form-field-1"> Gender </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<select class="chosen-select form-control" name="Gender" v-model="employee.Employee_Gender" id="Gender">
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="em_dob">Date of Birth</label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<input class="form-control" name="em_dob" id="em_dob" type="date" style="border-radius: 5px !important; margin-bottom:4px;" v-model="employee.Employee_BirthDate" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label" for="Marital"> Marital status </label>
						<label class="col-md-1 control-label">:</label>
						<div class="col-md-6">
							<select class="chosen-select form-control" name="Marital" id="Marital" v-model="employee.Employee_MaritalStatus">
								<option value="unmarried">Unmarried</option>
								<option value="married">Married</option>
							</select>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="col-md-6">
			<fieldset class="scheduler-border scheduler-search" style="height: 175px;">
				<legend class="scheduler-border">Picture & Document Section</legend>
				<div class="form-group clearfix">
					<div class="col-md-4" style="display: flex;align-items:center;flex-direction:column;">
						<div style="width: 100px;height:100px;border: 1px solid #ccc;overflow:hidden;">
							<img id="employeeImage" v-if="imageUrl == '' || imageUrl == null" src="/assets/no_image.gif">
							<img id="employeeImage" v-if="imageUrl != '' && imageUrl != null" v-bind:src="imageUrl">
						</div>
						<div style="text-align:center;">
							<label class="custom-file-upload">
								<input type="file" @change="previewImage" />
								Select Image
							</label>
						</div>
					</div>

					<div class="col-md-8">
						<label class="custom-file-upload">
							<input
								type="file"
								multiple
								accept="image/*"
								@change="selectDocuments"
							/>
							Select Documents
						</label>

						<div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">

							<div
								v-for="(file, index) in documents"
								:key="index"
								style="
									width:80px;
									height:80px;
									position:relative;
									border:1px solid #ccc;
									border-radius:4px;
									overflow:hidden;
									background:#f5f5f5;
								"
							>

								<!-- Image -->
								<img
									:src="file.preview"
									style="
										width:100%;
										height:100%;
										object-fit:cover;
									"
								>

								<!-- Remove Button -->
								<button
									type="button"
									@click="removeDocument(index)"
									style="
										position:absolute;
										top:2px;
										right:2px;
										width:22px;
										height:22px;
										padding:0;
										border:none;
										border-radius:50%;
										background:red;
										color:white;
										font-size:14px;
										line-height:20px;
										cursor:pointer;
									"
								>
									×
								</button>

							</div>

						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</form>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#employee',
		data() {
			return {
				employee: {
					Employee_SlNo: '',
					Employee_ID: "<?= $generateCode; ?>",
					bio_id: '',
					Department_ID: '',
					Designation_ID: '',
					Employee_Name: '',
					Employee_JoinDate: moment().format('YYYY-MM-DD'),
					Employee_Gender: 'Male',
					Employee_BirthDate: moment().format('YYYY-MM-DD'),
					Employee_NID: '',
					Employee_ContactNo: '',
					Employee_Email: '',
					Employee_MaritalStatus: 'unmarried',
					Employee_FatherName: '',
					Employee_MotherName: '',
					Employee_PrasentAddress: '',
					Employee_PermanentAddress: '',
					salary_range: 0,
					Employee_Reference: '',
					status: 'a'
				},
				imageUrl: '',
				selectedFile: null,
				documents: [],
				deletedDocuments: [],
				departments: [],
				selectedDepartment: null,
				designations: [],
				selectedDesignation: null,
			}
		},
		created() {
			this.getDesignations();
			this.getDepartments();
			if (parseInt("<?= $employeeId; ?>") > 0) {
				this.getEmployee();
			}
		},
		methods: {
			selectDocuments(event) {
				const files = Array.from(event.target.files);
				const remaining = 5 - this.documents.length;

				if (remaining <= 0) {
					Swal.fire({
						icon: 'warning',
						text: 'Maximum 5 documents allowed. Please remove an image first.'
					});
					event.target.value = '';
					return;
				}

				const selectedFiles = files.slice(0, remaining);

				selectedFiles.forEach(file => {
					if (!file.type.startsWith('image/')) {
						return;
					}

					const reader = new FileReader();

					reader.onload = (e) => {

						this.documents.push({
							id: null,
							image: null,
							file: file,
							preview: e.target.result
						});

					};

					reader.readAsDataURL(file);
				});

				if (files.length > remaining) {

					Swal.fire({
						icon: 'warning',
						text: `Only ${remaining} more image${remaining > 1 ? 's' : ''} can be added.`
					});

				}

				event.target.value = '';
			},
			removeDocument(index) {
				const document = this.documents[index];
				if (document.id) {
					this.documents.splice(index, 1);
					if (!this.deletedDocuments) {
						this.deletedDocuments = [];
					}
					this.deletedDocuments.push(document.id);
				} else {
					this.documents.splice(index, 1);
				}
			},
			getDepartments() {
				axios.get("/get_departments")
					.then(res => {
						this.departments = res.data;
					})
			},
			getDesignations() {
				axios.get("/get_designations")
					.then(res => {
						this.designations = res.data;
					})
			},

			saveEmployee() {
				if (this.employee.Employee_Name == '') {
					Swal.fire({
						icon: "error",
						text: "Name required!",
					});
					return;
				}
				if (this.selectedDepartment == null) {
					Swal.fire({
						icon: "error",
						text: "Department required!",
					});
					return;
				}
				if (this.selectedDesignation == null) {
					Swal.fire({
						icon: "error",
						text: "Designation required!",
					});
					return;
				}
				if (this.employee.Employee_ContactNo == '') {
					Swal.fire({
						icon: "error",
						text: "Contact number required!",
					});
					return;
				}
				this.employee.Department_ID = this.selectedDepartment.Department_SlNo
				this.employee.Designation_ID = this.selectedDesignation.Designation_SlNo
				let formdata = new FormData();
				formdata.append('data', JSON.stringify(this.employee));
				formdata.append('image', this.selectedFile);
				// documents
				this.documents.forEach((document, index) => {
					formdata.append('documents[]', document.file);
				});
				// Deleted documents
				formdata.append('deletedDocuments', JSON.stringify(this.deletedDocuments));

				let url = "/employeeInsert";
				if (this.employee.Employee_SlNo != '') {
					url = "/employeeUpdate";
				}
				axios.post(url, formdata)
					.then(res => {
						if (res.data.success) {
							alert(res.data.message);
							location.replace("/employee");
						}
					})
			},

			getEmployee() {
				axios.post('/get_employees', {
						employeeId: "<?= $employeeId; ?>"
					})
					.then(res => {
						let employee = res.data[0];
						Object.keys(this.employee).forEach(item => {
							this.employee[item] = employee[item];
						})
						if (employee.Employee_Pic_thum == null || employee.Employee_Pic_thum == '') {
							this.imageUrl = null;
						} else {
							this.imageUrl = '/'+employee.Employee_Pic_thum;
						}

						// Existing documents
						axios.post('/get_employee_documents', {
							employeeId: this.employee.Employee_SlNo
						})
						.then(response => {
							let res = response.data;
							if(res.success) {
								this.documents = res.documents.map(item => {
									return {
										id: item.id,
										image: item.image,
										preview: '/'+item.image
									};
	
								});
							} else {
								this.documents = [];
							}
						});

						setTimeout(() => {
							this.selectedDepartment = this.departments.find(item => item.Department_SlNo == employee.Department_ID);
							this.selectedDesignation = this.designations.find(item => item.Designation_SlNo == employee.Designation_ID);
						}, 1000);

					})

			},

			previewImage(event) {
				const WIDTH = 200;
				const HEIGHT = 200;
				if (event.target.files[0]) {
					let reader = new FileReader();
					reader.readAsDataURL(event.target.files[0]);
					reader.onload = (ev) => {
						let img = new Image();
						img.src = ev.target.result;
						img.onload = async e => {
							let canvas = document.createElement('canvas');
							canvas.width = WIDTH;
							canvas.height = HEIGHT;
							const context = canvas.getContext("2d");
							context.drawImage(img, 0, 0, canvas.width, canvas.height);
							let new_img_url = context.canvas.toDataURL(event.target.files[0].type);
							this.imageUrl = new_img_url;
							const resizedImage = await new Promise(rs => canvas.toBlob(rs, 'image/jpeg', 1))
							this.selectedFile = new File([resizedImage], event.target.files[0].name, {
								type: resizedImage.type
							});
						}
					}
				} else {
					event.target.value = '';
				}
			},
		},
	});
</script>