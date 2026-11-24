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

	#products label {
		font-size: 13px;
	}

	#products select {
		border-radius: 3px;
	}

	#products .add-button {
		padding: 2.5px;
		width: 100%;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
		cursor: pointer;
		border-radius: 3px;
	}

	#products .add-button:hover {
		background-color: #41add6;
		color: white;
	}

	#products input[type="file"] {
		display: none;
	}

	#products .custom-file-upload {
		border: 1px solid #ccc;
		display: inline-block;
		padding: 5px 12px;
		cursor: pointer;
		margin-top: 5px;
		background-color: #298db4;
		border: none;
		color: white;
	}

	#products .custom-file-upload:hover {
		background-color: #41add6;
	}

	#productImage {
		width: 100% !important;
		height: 100%;
	}

	tr td {
		vertical-align: middle !important;
	}
</style>
<div id="products">
	<form @submit.prevent="saveProduct">
		<div class="row" style="margin: 0;">
			<div class="col-md-10 col-xs-12 no-padding-left">
				<fieldset class="scheduler-border">
					<legend class="scheduler-border">Product Entry Form</legend>
					<div class="control-group">
						<div class="col-md-6">
							<div class="form-group clearfix">
								<label class="control-label col-md-4">Product Id:</label>
								<div class="col-md-7">
									<input type="text" class="form-control" v-model="product.Product_Code">
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Category:</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 88%;">
										<v-select v-bind:options="categories" style="margin:0;" v-model="selectedCategory" label="ProductCategory_Name"></v-select>
									</div>
									<div style="width:11%;margin-left:2px;">
										<span class="add-button" @click.prevent="modalOpen('/add_category', 'Add Category', 'ProductCategory_Name')"><i class="fa fa-plus"></i></span>
									</div>
								</div>
							</div>

							<div class="form-group clearfix" style="display: none;">
								<label class="control-label col-md-4">Brand:</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 88%;">
										<v-select v-bind:options="brands" style="margin:0;" v-model="selectedBrand" label="brand_name"></v-select>
									</div>
									<div style="width:11%;margin-left:2px;">
										<span class="add-button" @click.prevent="modalOpen('/add_brand', 'Add Brand', 'brand_name')"><i class="fa fa-plus"></i></span>
									</div>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Product Name:</label>
								<div class="col-md-7">
									<input type="text" class="form-control" v-model="product.Product_Name" required>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Unit:</label>
								<div class="col-md-7" style="display: flex;align-items:center;margin-bottom:5px;">
									<div style="width: 88%;">
										<v-select v-bind:options="units" style="margin:0;" v-model="selectedUnit" label="Unit_Name"></v-select>
									</div>
									<div style="width:11%;margin-left:2px;">
										<span class="add-button" @click.prevent="modalOpen('/add_unit', 'Add Unit', 'Unit_Name')"><i class="fa fa-plus"></i></span>
									</div>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">VAT:</label>
								<div class="col-md-7">
									<input type="number" min="0" step="any" class="form-control" v-model="product.vat">
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group clearfix">
								<label class="control-label col-md-4">Re-order level:</label>
								<div class="col-md-7">
									<input type="number" min="0" step="any" class="form-control" v-model="product.Product_ReOrederLevel" required>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Purchase Rate:</label>
								<div class="col-md-7">
									<input type="number" min="0" step="any" id="purchase_rate" class="form-control" v-model="product.Product_Purchase_Rate" required v-bind:disabled="product.is_service ? true : false">
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Sales Rate:</label>
								<div class="col-md-7">
									<input type="number" min="0" step="any" class="form-control" v-model="product.Product_SellingPrice" required>
								</div>
							</div>

							<div class="form-group clearfix">
								<label class="control-label col-md-4">Wholesale Rate:</label>
								<div class="col-md-7">
									<input type="number" min="0" step="any" class="form-control" v-model="product.Product_WholesaleRate" required>
								</div>
							</div>
							<div class="form-group clearfix">
								<label class="col-md-3">
									<input type="checkbox" v-model="product.is_service" @change="changeIsService">
									Is Service
								</label>
								<label class="col-md-3">
									<input type="checkbox" :false-value="`p`" :true-value="`a`" v-model="product.status">
									Is Active
								</label>
								<div class="col-md-5 text-right">
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
			<div class="col-md-2 col-xs-12 no-padding-right productImage">
				<fieldset class="scheduler-border bg-of-skyblue" style="height: 180px;">
					<legend class="scheduler-border">Image Upload</legend>
					<div class="control-group">
						<div class="form-group clearfix" style="display: flex;align-items:center;flex-direction:column;">
							<div style="width: 100px;height:100px;border: 1px solid #ccc;overflow:hidden;">
								<img id="productImage" v-if="imageSrc != '' && imageSrc != null" v-bind:src="imageSrc">
							</div>
							<div style="text-align:center;">
								<label class="custom-file-upload">
									<input type="file" @change="previewImage" />
									Select Image
								</label>
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
				<datatable :columns="columns" :data="products" :filter-by="filter">
					<template scope="{ row }">
						<tr :style="{background: row.status == 'p' ? '#fdda9b' : ''}">
							<td>{{ row.Product_Code }}</td>
							<td style="text-align: left;padding-left:3px;">{{ row.Product_Name }}</td>
							<td>{{ row.ProductCategory_Name }}</td>
							<td>{{ row.Product_Purchase_Rate }}</td>
							<td>{{ row.Product_SellingPrice }}</td>
							<td>{{ row.Product_WholesaleRate }}</td>
							<td>{{ row.vat }}</td>
							<td>{{ row.Unit_Name }}</td>
							<td>
								<span v-if="row.is_service == 'false'" class="badge badge-success">Product</span>
								<span v-else class="badge badge-warning">Service</span>
							</td>
							<td>
								<span v-if="row.status == 'a'" class="badge badge-success">Active</span>
								<span v-else class="badge badge-warning">Inactive</span>
							</td>
							<!--<td>-->
							<!--	<a :href="`/${row.image_name ? row.image_name : 'assets/no_image.gif'}`">-->
							<!--		<img :src="`/${row.image_name ? row.image_name : 'assets/no_image.gif'}`" style="width: 40px;height:40px;border-radius: 4px;border: 1px solid gray;" />-->
							<!--	</a>-->
							<!--</td>-->
							<td>
								<?php if (buttonAction('update')): ?>
									<i class="btnEdit fa fa-pencil" @click="editProduct(row)"></i>
								<?php endif; ?>
								<?php if (buttonAction('delete')): ?>
									<i class="btnDelete fa fa-trash" @click="deleteProduct(row.Product_SlNo)"></i>
								<?php endif; ?>
								<i @click="window.open(`/Administrator/products/barcodeGenerate/${row.Product_SlNo}`, '_blank')" class="btnBarcode fa fa-barcode"></i>
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
		el: '#products',
		data() {
			return {
				product: {
					Product_SlNo: '',
					Product_Code: "<?php echo $productCode; ?>",
					Product_Name: '',
					ProductCategory_ID: '',
					brand: '',
					Product_ReOrederLevel: 0,
					Product_Purchase_Rate: 0,
					Product_SellingPrice: 0,
					Product_WholesaleRate: 0,
					Unit_ID: '',
					vat: 0,
					is_service: false,
					status: 'a',
				},
				imageSrc: "/assets/no_image.gif",
				selectedFile: "",
				products: [],
				categories: [],
				selectedCategory: null,
				brands: [],
				selectedBrand: null,
				units: [],
				selectedUnit: null,

				columns: [{
						label: 'Product Id',
						field: 'Product_Code',
						align: 'center',
						filterable: false
					},
					{
						label: 'Product Name',
						field: 'Product_Name',
						align: 'center'
					},
					{
						label: 'Category',
						field: 'ProductCategory_Name',
						align: 'center'
					},
					{
						label: 'Purchase Price',
						field: 'Product_Purchase_Rate',
						align: 'center'
					},
					{
						label: 'Sales Price',
						field: 'Product_SellingPrice',
						align: 'center'
					},
					{
						label: 'Wholesale Price',
						field: 'Product_WholesaleRate',
						align: 'center'
					},
					{
						label: 'VAT',
						field: 'vat',
						align: 'center'
					},
					{
						label: 'Unit',
						field: 'Unit_Name',
						align: 'center'
					},
					{
						label: 'Type',
						field: 'is_service',
						align: 'center'
					},
					{
						label: 'Status',
						field: 'status',
						align: 'center'
					},
					// 	{
					// 		label: 'Image',
					// 		field: 'image_name',
					// 		align: 'center',
					// 		filterable: false
					// 	},
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
			this.getCategories();
			this.getBrands();
			this.getUnits();
			this.getProducts();
		},
		methods: {
			changeIsService() {
				if (this.product.is_service) {
					this.product.Product_Purchase_Rate = 0;
				}
			},
			getCategories() {
				axios.get('/get_categories').then(res => {
					this.categories = res.data;
				})
			},
			getBrands() {
				axios.get('/get_brands').then(res => {
					this.brands = res.data;
				})
			},
			getUnits() {
				axios.get('/get_units').then(res => {
					this.units = res.data;
				})
			},
			getProducts() {
				axios.post('/get_products', {
					withPstatus: 'yes'
				}).then(res => {
					this.products = res.data;
				})
			},
			saveProduct() {
				if (this.selectedCategory == null) {
					Swal.fire({
						icon: "error",
						text: "Select category",
					});
					return;
				}
				if (this.selectedUnit == null) {
					Swal.fire({
						icon: "error",
						text: "Select unit",
					});
					return;
				}
				if (this.selectedBrand != null) {
					this.product.brand = this.selectedBrand.brand_SiNo;
				}
				this.product.ProductCategory_ID = this.selectedCategory.ProductCategory_SlNo;
				this.product.Unit_ID = this.selectedUnit.Unit_SlNo;

				let fd = new FormData();
				fd.append('data', JSON.stringify(this.product));
				fd.append('image', this.selectedFile);

				let url = '/add_product';
				if (this.product.Product_SlNo != 0) {
					url = '/update_product';
				}
				axios.post(url, fd)
					.then(res => {
						let r = res.data;
						alert(r.message);
						if (r.success) {
							this.clearForm();
							this.product.Product_Code = r.productId;
							this.getProducts();
						}
					})

			},
			editProduct(product) {
				let keys = Object.keys(this.product);
				keys.forEach(key => {
					this.product[key] = product[key];
				})
				this.product.is_service = product.is_service == 'true' ? true : false;
				this.selectedCategory = {
					ProductCategory_SlNo: product.ProductCategory_ID,
					ProductCategory_Name: product.ProductCategory_Name
				}

				this.selectedUnit = {
					Unit_SlNo: product.Unit_ID,
					Unit_Name: product.Unit_Name
				}
				if (product.image_name == null || product.image_name == '') {
					this.imageSrc = "/assets/no_image.gif";
				} else {
					this.imageSrc = product.image_name;
				}
			},
			deleteProduct(productId) {
				let deleteConfirm = confirm('Are you sure?');
				if (deleteConfirm == false) {
					return;
				}
				axios.post('/delete_product', {
					productId: productId
				}).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.getProducts();
					}
				})
			},
			clearForm() {
				this.product = {
					Product_SlNo: '',
					Product_Code: "",
					Product_Name: '',
					ProductCategory_ID: '',
					brand: '',
					Product_ReOrederLevel: 0,
					Product_Purchase_Rate: 0,
					Product_SellingPrice: 0,
					Product_WholesaleRate: 0,
					Unit_ID: '',
					vat: 0,
					is_service: false,
					status: 'a'
				};
				this.imageSrc = "/assets/no_image.gif";
				this.selectedFile = "";
				this.product.Product_Code = "<?php echo $this->mt->generateProductCode(); ?>";
			},

			previewImage(event) {
				const WIDTH = 150;
				const HEIGHT = 150;
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
							this.imageSrc = new_img_url;
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

			// modal data store
			modalOpen(url, title, txt) {
				$(".formModal").modal("show");
				this.formInput = txt;
				this.url = url;
				this.modalTitle = title;
			},

			saveModalData(event) {
				let filter = {}
				if (this.formInput == "ProductCategory_Name") {
					filter.ProductCategory_Name = this.fieldValue;
					filter.ProductCategory_Description = "";
				}
				if (this.formInput == "brand_name") {
					filter.brand_name = this.fieldValue;
				}
				if (this.formInput == "Unit_Name") {
					filter.Unit_Name = this.fieldValue;
				}

				axios.post(this.url, filter)
					.then(res => {
						if (this.formInput == "ProductCategory_Name") {
							this.getCategories();
						}
						if (this.formInput == "brand_name") {
							this.getBrands();
						}
						if (this.formInput == "Unit_Name") {
							this.getUnits();
						}

						$(".formModal").modal('hide');
						this.formInput = '';
						this.url = "";
						this.modalTitle = '';
						this.fieldValue = '';
					})
			},
		}
	})
</script>