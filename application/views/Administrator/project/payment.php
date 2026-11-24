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

	#projectPayment label {
		font-size: 13px;
	}

	#projectPayment select {
		border-radius: 3px;
		padding: 0;
	}

	.add-button {
		padding: 2.8px;
		width: 100%;
		background-color: #d15b47;
		display: block;
		text-align: center;
		color: white;
		cursor: pointer;
		border-radius: 3px;
	}

	.add-button:hover {
		color: white;
	}
</style>
<div id="projectPayment">
	<div class="row">
		<div class="col-md-12" style="margin:0;">
			<fieldset class="scheduler-border">
				<legend class="scheduler-border">Project Payment Form</legend>
				<div class="control-group">
					<form @submit.prevent="savePayment">
						<div class="row">
							<div class="col-md-5 col-md-offset-1">
								<div class="form-group">
									<label class="col-md-4 control-label">Transaction Type</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<select class="form-control" v-model="payment.type" required>
											<option value=""></option>
											<option value="CR">Receive</option>
											<option value="CP">Payment</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">Payment Type</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<select class="form-control" v-model="payment.payment_by" required>
											<option value="cash">Cash</option>
											<option value="bank">Bank</option>
										</select>
									</div>
								</div>
								<div class="form-group" style="display:none;" v-bind:style="{display: payment.payment_by == 'bank' ? '' : 'none'}">
									<label class="col-md-4 control-label">Bank Account</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<v-select v-bind:options="filteredAccounts" v-model="selectedAccount" label="display_text" placeholder="Select account"></v-select>
									</div>
								</div>

								<div class="form-group">
									<label class="col-md-4 control-label no-padding-right"> Project </label>
									<label class="col-md-1">:</label>
									<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
										<div style="width: 86%;">
											<v-select v-bind:options="projects" style="margin: 0;" label="display_name" v-model="selectedProject" v-on:input="getProjectDue" @search="onSearchProject"></v-select>
										</div>
										<div style="width: 13%;margin-left:2px;">
											<a href="<?= base_url('project') ?>" class="add-button" target="_blank" title="Add New Project"><i class="fa fa-plus" aria-hidden="true"></i></a>
										</div>
									</div>
								</div>

								<div class="form-group">
									<label class="col-md-4 control-label">Due</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<input type="text" class="form-control" v-model="payment.previous_due" disabled>
									</div>
								</div>
							</div>

							<div class="col-md-5">
								<div class="form-group">
									<label class="col-md-4 control-label">Payment Date</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<input type="date" style="margin-bottom: 4px;" class="form-control" v-model="payment.date" required @change="getProjectPayments" v-bind:disabled="userType == 'u' ? true : false">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">Description</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<input type="text" class="form-control" v-model="payment.note">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">Amount</label>
									<label class="col-md-1">:</label>
									<div class="col-md-7">
										<input type="number" step="any" min="0" class="form-control" v-model="payment.amount" required>
									</div>
								</div>
								<?php if (buttonAction('entry') || buttonAction('update')): ?>
									<div class="form-group">
										<div class="col-md-7 col-md-offset-5 text-right">
											<input type="button" @click="resetForm" class="btnReset" value="Reset">
											<input type="submit" :disabled="paymentProgress" class="btnSave" value="Save">
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</form>
				</div>
			</fieldset>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="payments" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.invoice }}</td>
							<td>{{ row.date }}</td>
							<td>{{ row.project_name }}</td>
							<td>{{ row.type }}</td>
							<td>{{ row.payment_by }}</td>
							<td>{{ row.amount }}</td>
							<td>{{ row.note }}</td>
							<td>{{ row.added_by }}</td>
							<td>
								<i class="fa fa-file" style="margin-right: 5px;font-size: 14px;cursor: pointer;" @click="window.location = `/projectPaymentInvoice/${row.id}`"></i>
								<?php if (buttonAction('update')): ?>
									<i class="btnEdit fa fa-pencil" @click="editPayment(row)"></i>
								<?php endif; ?>
								<?php if (buttonAction('delete')): ?>
									<i class="btnDelete fa fa-trash" @click="deletePayment(row.id)"></i>
								<?php endif; ?>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page" style="margin-bottom: 50px;"></datatable-pager>
			</div>
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
		el: '#projectPayment',
		data() {
			return {
				payment: {
					id: 0,
					invoice: null,
					type: 'CR',
					payment_by: 'cash',
					project_id: null,
					account_id: null,
					date: moment().format('YYYY-MM-DD'),
					amount: 0,
					note: '',
					previous_due: 0
				},
				payments: [],
				projects: [],
				selectedProject: {
                    id: '',
					name: '',
					display_name: 'Select Project'
				},
				accounts: [],
				selectedAccount: null,
				paymentProgress: false,
				userType: '<?php echo $this->session->userdata("accountType"); ?>',

				columns: [{
						label: 'Transaction Id',
						field: 'invoice',
						align: 'center'
					},
					{
						label: 'Date',
						field: 'date',
						align: 'center'
					},
					{
						label: 'Project Name',
						field: 'project_name',
						align: 'center'
					},
					{
						label: 'Transaction Type',
						field: 'type',
						align: 'center'
					},
					{
						label: 'Payment by',
						field: 'payment_by',
						align: 'center'
					},
					{
						label: 'Amount',
						field: 'amount',
						align: 'center'
					},
					{
						label: 'Description',
						field: 'note',
						align: 'center'
					},
					{
						label: 'Saved By',
						field: 'add_by',
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
				filter: ''
			}
		},
		computed: {
			filteredAccounts() {
				let accounts = this.accounts.filter(account => account.status == '1');
				return accounts.map(account => {
					account.display_text = `${account.account_name} - ${account.account_number} (${account.bank_name})`;
					return account;
				})
			},
		},
		created() {
			this.getProjects();
			this.getAccounts();
			this.getProjectPayments();
		},
		methods: {
			getProjectPayments() {
				let data = {
					dateFrom: this.payment.date,
					dateTo: this.payment.date
				}
				axios.post('/get_project_payments', data).then(res => {
					this.payments = res.data;
				})
			},

			async getProjects() {
				await axios.get('/get_projects')
                .then(res => {
					this.projects = res.data.map(pro => {
						pro.display_name = `${pro.code} - ${pro.name}`;
						return pro;
					});
				})
			},

			getProjectDue() {
				if (this.selectedProject == null || this.selectedProject.id == '') {
					return;
				}

				axios.post('/get_project_due', {
					projectId: this.selectedProject.id
				}).then(res => {
					this.payment.previous_due = res.data[0].dueAmount;
				})
			},

			async onSearchProject(val, loading) {
				if (val.length > 2) {
					loading(true);
					await axios.post('/get_projects', { name: val }).then(res => {
						this.projects = res.data.map(pro => {
							pro.display_name = `${pro.code} - ${pro.name}`;
							return pro;
						});
						loading(false);
					});
				} else {
					loading(false);
					await this.getProjects();
				}
			},

			getAccounts() {
				axios.get('/get_bank_accounts')
					.then(res => {
						this.accounts = res.data;
					})
			},
			savePayment() {
				if (this.payment.payment_by == 'bank') {
					if (this.selectedAccount == null) {
						alert('Select an account');
						return;
					} else {
						this.payment.account_id = this.selectedAccount.account_id;
					}
				} else {
					this.payment.account_id = null;
				}
				if (this.selectedProject == null || this.selectedProject.id == undefined) {
					alert('Select Project');
					return;
				}

				this.payment.project_id = this.selectedProject.id;

				let url = '/add_project_payment';
				if (this.payment.id != 0) {
					url = '/update_project_payment';
				}
				this.paymentProgress = true;
				axios.post(url, this.payment).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.resetForm();
						this.paymentProgress = false;
						this.getProjectPayments();
						let invoiceConfirm = confirm('Do you want to view invoice?');
						if (invoiceConfirm == true) {
							window.open('/projectPaymentInvoice/' + r.paymentId, '_blank');
						}
					}
				})
			},

			editPayment(payment) {
				let keys = Object.keys(this.payment);
				keys.forEach(key => {
					this.payment[key] = payment[key];
				})

				this.selectedProject = {
					id: payment.project_id,
					name: payment.project_name,
					display_name: `${payment.project_code} - ${payment.project_name}`
				}

				if (payment.payment_by == 'bank') {
					this.selectedAccount = {
						account_id: payment.account_id,
						account_name: payment.account_name,
						account_number: payment.account_number,
						bank_name: payment.bank_name,
						display_text: `${payment.account_name} - ${payment.account_number} (${payment.bank_name})`
					}
				}
			},

			deletePayment(paymentId) {
				let deleteConfirm = confirm('Are you sure?');
				if (deleteConfirm == false) {
					return;
				}
				axios.post('/delete_project_payment', {
					paymentId: paymentId
				}).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.getProjectPayments();
					}
				})
			},

			resetForm() {
                this.payment = {
					id: 0,
					invoice: null,
					type: 'CR',
					payment_by: 'cash',
					account_id: null,
					date: moment().format('YYYY-MM-DD'),
					amount: 0,
					note: '',
					previous_due: 0
				}

				this.selectedProject = {
                    id: '',
                    name: '',
					display_name: 'Select Project'
				}
				this.selectedAccount = null;

                this.paymentProgress = false;
			}
		}
	})
</script>