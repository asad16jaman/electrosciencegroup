<style>
	.v-select {
		margin-bottom: 5px;
		background: #fff;
		border-radius: 3px;
	}

	.v-select.open .dropdown-toggle {
		border-bottom: 1px solid #ccc;
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

	#projects label {
		font-size: 13px;
	}

	#projects select {
		border-radius: 3px;
	}

	#projects .add-button {
		padding: 2.5px;
		width: 100%;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
		cursor: pointer;
		border-radius: 3px;
	}

	#projects .add-button:hover {
		background-color: #41add6;
		color: white;
	}

	#projects input[type="file"] {
		display: none;
	}

	#projects .custom-file-upload {
		border: 1px solid #ccc;
		display: inline-block;
		padding: 5px 12px;
		cursor: pointer;
		margin-top: 5px;
		background-color: #298db4;
		border: none;
		color: white;
	}

	#projects .custom-file-upload:hover {
		background-color: #41add6;
	}

	tr td {
		vertical-align: middle !important;
	}
</style>
<div id="Project">
	<form @submit.prevent="saveProject">
		<div class="row" style="margin: 0;">
			<div class="col-md-12 col-xs-12 no-padding-left">
				<fieldset class="scheduler-border">
					<legend class="scheduler-border">Project Entry Form</legend>
					<div class="control-group">
						<div class="col-md-6">
							<div class="form-group clearfix">
								<label class="control-label col-md-4">Project ID</label>
								<div class="col-md-7">
									<input type="text" class="form-control" v-model="project.code">
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Project Name</label>
								<div class="col-md-7">
									<input type="text" class="form-control" v-model="project.name" placeholder="Enter Name" required>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Select Division</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 100%;">
										<v-select v-bind:options="divisions" style="margin:0;" v-model="selectedDivision" label="name" @input="DivisionOnChange"></v-select>
									</div>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Select District</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 100%;">
										<v-select v-bind:options="districts" style="margin:0;" id="district" v-model="selectedDistrict" label="name" @input="districtOnChange"></v-select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group clearfix">
								<label class="control-label col-md-4">Select Thana</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 100%;">
										<v-select v-bind:options="thanas" style="margin:0;" id="thana" v-model="selectedThana" label="name"></v-select>
									</div>
								</div>
							</div>
							<div class="form-group clearfix">
								<label class="control-label col-md-4">Description</label>
								<div class="col-md-7">
									<textarea name="note" id="note" class="form-control" v-model="project.note" placeholder="Enter Description"></textarea>
								</div>
							</div>
							<div class="form-group clearfix">
								<div class="col-md-11 text-right">
									<?php if (buttonAction('entry') || buttonAction('update')): ?>
										<input type="button" @click="clearForm" class="btnReset" value="Reset">
										<input type="submit" class="btnSave" value="Save">
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</form>

	<div class="row">
		<div class="col-md-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="projects" :filter-by="filter">
					<template scope="{ row }">
						<tr :style="{background: row.status == 'p' ? '#fdda9b' : ''}">
							<td>{{ row.sl }}</td>
							<td>{{ row.code }}</td>
							<td style="text-align: left;padding-left:3px;">{{ row.name }}</td>
							<td>{{ row.division_name }}</td>
							<td>{{ row.district_name }}</td>
							<td>{{ row.thana_name }}</td>
							<td>{{ row.note }}</td>
							<td>
								<?php if (buttonAction('update')): ?>
									<i class="btnEdit fa fa-pencil" @click="editProject(row)"></i>
								<?php endif; ?>
								<?php if (buttonAction('delete')): ?>
									<i class="btnDelete fa fa-trash" @click="deleteProject(row.id)"></i>
								<?php endif; ?>
								<i @click="window.open(`/Administrator/projects/barcodeGenerate/${row.id}`, '_blank')" class="btnBarcode fa fa-barcode"></i>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page"></datatable-pager>
			</div>
		</div>
	</div>

	<!-- modal form -->
	<div class="modal formModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm" role="document">
			<form @submit.prevent="saveModalData($event)">
				<div class="modal-content">
					<div class="modal-header" style="display: flex;align-items: center;justify-content: space-between;">
						<h5 class="modal-title" v-html="modalTitle"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" style="padding-top: 0;">
						<div class="form-group">
							<label for="">Name</label>
							<input type="text" :name="formInput" v-model="fieldValue" class="form-control" autocomplete="off" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btnReset" data-dismiss="modal">Close</button>
						<button type="submit" class="btnSave">Save</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#Project',
		data() {
			return {
				project: {
					id: '',
					code: "<?php echo $code; ?>",
					name: '',
					division_id: null,
					district_id: null,
					thana_id: null,
					note: ''
				},

				projects: [],
				divisions: [],
				selectedDivision: null,
				districts: [],
				selectedDistrict: null,
				thanas: [],
				selectedThana: null,

				columns: [{
						label: 'Sl.',
						field: 'sl',
						align: 'center',
						filterable: false
					},
					{
						label: 'Project Id',
						field: 'code',
						align: 'center',
						filterable: false
					},
					{
						label: 'Project Name',
						field: 'name',
						align: 'center'
					},
					{
						label: 'Division',
						field: 'division_name',
						align: 'center'
					},
					{
						label: 'District',
						field: 'district_name',
						align: 'center'
					},
					{
						label: 'Thana',
						field: 'thana_name',
						align: 'center'
					},
					{
						label: 'Description',
						field: 'note',
						align: 'center'
					},
					{
						label: 'Action',
						align: 'center',
						filterable: false
					}
				],
				page: 1,
				per_page: 100,
				filter: '',

				formInput: '',
				url: '',
				modalTitle: '',
				fieldValue: ''
			}
		},
		created() {
			this.getDivisions();
			this.getProjects();
		},
		methods: {
			getDivisions() {
				axios.get('/get_divisions').then(res => {
					this.divisions = res.data;
				})
			},
			async DivisionOnChange() {
				if (!this.selectedDivision) {
					this.districts = [];
					this.thanas = [];
					this.selectedDistrict = null;
					this.selectedThana = null;
					return;
				}

				this.selectedDistrict = null;
				this.selectedThana = null;
				this.thanas = [];

				await this.getDistricts();
				if (this.project.id == 0) {
					document.querySelector("#district [type='search']").focus();
				}
			},
			getDistricts() {
				if (!this.selectedDivision) {
					this.districts = [];
					return Promise.resolve();
				}

				return axios.post('/get_districts', {
					divisionId: this.selectedDivision.id
				}).then(res => {
					this.districts = res.data;
				});
			},
			async districtOnChange() {
				if (!this.selectedDistrict) {
					this.thanas = [];
					this.selectedThana = null;
					return;
				}

				this.selectedThana = null;

				await this.getThanas();
				if (this.project.id == 0) {
					document.querySelector("#thana [type='search']").focus();
				}
			},
			getThanas() {
				if (!this.selectedDistrict) {
					this.thanas = [];
					return Promise.resolve();
				}

				return axios.post('/get_police_stations', {
					districtId: this.selectedDistrict.id
				}).then(res => {
					this.thanas = res.data;
				});
			},
			getProjects() {
				axios.get('/get_projects')
					.then(res => {
						this.projects = res.data.map((item, index) => {
							item.sl = index + 1;
							return item;
						});
					})
			},

			saveProject() {
				if (this.selectedDivision == null) {
					Swal.fire({
						icon: "error",
						text: "Select Division",
					});
					return;
				}
				if (this.selectedDistrict == null) {
					Swal.fire({
						icon: "error",
						text: "Select District",
					});
					return;
				}
				if (this.selectedThana == null) {
					Swal.fire({
						icon: "error",
						text: "Select Thana",
					});
					return;
				}

				this.project.division_id = this.selectedDivision.id;
				this.project.district_id = this.selectedDistrict.id;
				this.project.thana_id = this.selectedThana.id;

				let url = '/add_project';
				if (this.project.id != 0) {
					url = '/update_project';
				}
				axios.post(url, this.project)
					.then(res => {
						let r = res.data;
						alert(r.message);
						if (r.success) {
							this.clearForm();
							this.project.code = r.projectId;
							this.getProjects();
						}
					})

			},
			async editProject(project) {

				Object.keys(this.project).forEach(key => {
					this.project[key] = project[key];
				});

				this.selectedDivision = this.divisions.find(
					d => d.id == project.division_id
				);

				await this.getDistricts();

				this.selectedDistrict = this.districts.find(
					d => d.id == project.district_id
				);

				await this.getThanas();

				this.selectedThana = this.thanas.find(
					t => t.id == project.thana_id
				);
			},
			deleteProject(projectId) {
				let deleteConfirm = confirm('Are you sure?');
				if (deleteConfirm == false) {
					return;
				}
				axios.post('/delete_project', {
					projectId: projectId
				}).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.getProjects();
					}
				})
			},

			clearForm() {
				this.project = {
					id: '',
					code: "<?php echo $this->mt->generateProjectCode(); ?>",
					name: '',
					division_id: null,
					district_id: null,
					thana_id: null,
					note: ''
				};

				this.selectedDivision = null;
				this.selectedDistrict = null;
				this.selectedThana = null;

				this.districts = [];
				this.thanas = [];
			}
		}
	})
</script>