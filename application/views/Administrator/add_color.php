<div id="colors">
	<form @submit.prevent="saveData">
		<div class="row" style="margin: 0;">
			<fieldset class="scheduler-border">
				<legend class="scheduler-border">Color Entry Form</legend>
				<div class="control-group">
					<div class="col-xs-12 col-md-6 col-md-offset-3">
						<div class="form-group clearfix">
							<label class="control-label col-xs-4 col-md-4">Color Name:</label>
							<div class="col-xs-8 col-md-8">
								<input type="text" class="form-control" v-model="color.Color_Name">
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
				<datatable :columns="columns" :data="colors" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.sl }}</td>
							<td>{{ row.Color_Name }}</td>
							<td>
								<?php if (buttonAction('update')): ?>
									<i class="btnEdit fa fa-pencil" @click="editColor(row)"></i>
								<?php endif; ?>
								<?php if (buttonAction('delete')): ?>
									<i class="btnDelete fa fa-trash" @click="deleteColor(row.Color_SlNo)"></i>
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
		el: '#colors',
		data() {
			return {
				color: {
					Color_SlNo: 0,
					Color_Name: '',
				},
				colors: [],

				columns: [{
						label: 'Sl',
						field: 'sl',
						align: 'center'
					},
					{
						label: 'Color Name',
						field: 'Color_Name',
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
			this.getColors();
		},
		methods: {
			getColors() {
				axios.get('/get_colors').then(res => {
					this.colors = res.data.map((item, index) => {
						item.sl = index + 1;
						return item;
					});
				})
			},
			saveData() {
				if (this.color.Color_Name == '') {
					Swal.fire({
						icon: "error",
						text: "Color name is empty!",
					});
					return;
				}
				let url = '/add_color';
				if (this.color.Color_SlNo != 0) {
					url = '/update_color';
				}

				axios.post(url, this.color).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.status) {
						this.resetForm();
						this.getColors();
					}
				})
			},
			editColor(color) {
				let keys = Object.keys(this.color);
				keys.forEach(key => {
					this.color[key] = color[key];
				})
			},
			deleteColor(colorId) {
				if (confirm('Are you sure?')) {
					axios.post('/delete_color', {
						colorId: colorId
					}).then(res => {
						let r = res.data;
						alert(r.message);
						if (r.status) {
							this.getColors();
						}
					})
				}
			},
			resetForm() {
				this.color = {
					Color_SlNo: 0,
					Color_Name: '',
				}
			}
		}
	})
</script>