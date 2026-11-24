<div id="sizes">
	<form @submit.prevent="saveData">
		<div class="row" style="margin: 0;">
			<fieldset class="scheduler-border">
				<legend class="scheduler-border">Size Entry Form</legend>
				<div class="control-group">
					<div class="col-xs-12 col-md-6 col-md-offset-3">
						<div class="form-group clearfix">
							<label class="control-label col-xs-4 col-md-4">Size Name:</label>
							<div class="col-xs-8 col-md-8">
								<input type="text" class="form-control" v-model="size.Size_Name">
							</div>
						</div>
						<?php if (buttonAction('entry') || buttonAction('update')): ?>
							<div class="form-group clearfix">
								<div class="col-xs-12 col-md-12 text-right">
									<input type="button" class="btnReset" value="Reset" @click="resetForm">
									<input type="submit" class="btnSave" value="Save">
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</fieldset>
		</div>
	</form>

	<div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="sizes" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.sl }}</td>
							<td>{{ row.Size_Name }}</td>
							<td>
								<?php if (buttonAction('update')): ?>
									<i class="btnEdit fa fa-pencil" @click="editSize(row)"></i>
								<?php endif; ?>
								<?php if (buttonAction('delete')): ?>
									<i class="btnDelete fa fa-trash" @click="deleteSize(row.Size_SlNo)"></i>
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
		el: '#sizes',
		data() {
			return {
				size: {
					Size_SlNo: 0,
					Size_Name: '',
				},
				sizes: [],

				columns: [{
						label: 'Sl',
						field: 'sl',
						align: 'center'
					},
					{
						label: 'Size Name',
						field: 'Size_Name',
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
		created() {
			this.getSizes();
		},
		methods: {
			getSizes() {
				axios.get('/get_sizes').then(res => {
					this.sizes = res.data.map((item, index) => {
						item.sl = index + 1;
						return item;
					});
				})
			},
			saveData() {
				if (this.size.Size_Name == '') {
					Swal.fire({
						icon: "error",
						text: "Size name is empty!",
					});
					return;
				}
				let url = '/add_size';
				if (this.size.Size_SlNo != 0) {
					url = '/update_size';
				}

				axios.post(url, this.size).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.status) {
						this.resetForm();
						this.getSizes();
					}
				})
			},
			editSize(size) {
				let keys = Object.keys(this.size);
				keys.forEach(key => {
					this.size[key] = size[key];
				})
			},
			deleteSize(sizeId) {
				if (confirm('Are you sure?')) {
					axios.post('/delete_size', {
						sizeId: sizeId
					}).then(res => {
						let r = res.data;
						alert(r.message);
						if (r.status) {
							this.getSizes();
						}
					})
				}
			},
			resetForm() {
				this.size = {
					Size_SlNo: 0,
					Size_Name: '',
				}
			}
		}
	})
</script>