<style>
.v-select {
		float: right;
		min-width: 250px;
		background: #fff;
		margin-left: 5px;
		border-radius: 4px !important;
		margin-top: -2px;
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
</style>

<div class="row" id="partialDelivery">
    <div class="col-xs-12 col-md-12 col-lg-12">
        <fieldset class="scheduler-border scheduler-search">
            <legend class="scheduler-border">Work Order Delivery History</legend>
            <div class="control-group">
                <div class="col-md-12">
					<form class="form-inline" id="searchForm" @submit.prevent="getDeliveryLogs">
						<div class="form-group">
							<label>Invoice No</label>
							<v-select v-bind:options="invoices" label="invoice_text" v-model="selectedInvoice" @search="onSearchInvoice"></v-select>
						</div>

						<div class="form-group">
							<label for="byDate" style="font-weight: bold;color: #ff2c82;">&nbsp;<input type="checkbox" name="byDate" id="byDate" v-model="byDate"> By Date &nbsp;</label>
						</div>

						<div class="form-group" style="display: none;" v-bind:style="{display: byDate == true ? '' : 'none'}">
							<label for="">From</label>
							<input type="date" class="form-control" v-model="dateFrom">
						</div>

						<div class="form-group" style="display: none;" v-bind:style="{display: byDate == true ? '' : 'none'}">
							<label for="">To</label>
							<input type="date" class="form-control" v-model="dateTo">
						</div>

						<div class="form-group" style="margin-top: -1px;">
                            <input type="submit" value="View">
						</div>
					</form>
				</div>
            </div>
        </fieldset>
    </div>

    <div class="col-xs-12 col-md-12 col-lg-12" v-if="deliveryLogs.length > 0" style="display:none" v-bind:style="{display: deliveryLogs.length > 0 ? '' : 'none'}">
        <div class="table-responsive">
            <br>
            <div class="col-md-6" style="margin-bottom: 10px;">
                <h4 style="margin:0px;padding:0px;">Order Information</h4>
                Invoice: {{ selectedInvoice.SaleMaster_InvoiceNo }}<br>
                Order Date: {{ selectedInvoice.SaleMaster_SaleDate }}
                <br>
            </div>
            <div class="col-md-6 text-right" style="margin-bottom: 10px;">
                <h4 style="margin:0px;padding:0px;">Customer Information</h4>
                Name: {{ selectedInvoice.Customer_Name }}<br>
                Mobile: {{ selectedInvoice.Customer_Mobile }}<br>
                <span v-if="selectedInvoice.Customer_Address != ''">Address: {{ selectedInvoice.Customer_Address }}</span>
            </div>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:5%;">Sl</th>
                        <th>Date</th>
                        <th>Product Name</th>
                        <th>Sale Rate</th>
                        <th>Store Qty</th>
                        <th>Delivery Qty</th>
                        <th>Total Quantity</th>
                        <th>Total</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(product, sl) in deliveryLogs">
                        <td>{{ sl + 1 }}</td>
                        <td>{{ product.date | formatDateTime('DD-MM-YYYY') }}</td>
                        <td style="text-align:left;padding-left:4px;">{{ product.Product_Code }} - {{ product.Product_Name }}</td>
                        <td>{{ product.SaleDetails_Rate }}</td>
                        <td>{{ product.store_quantity }}</td>
                        <td>{{ product.quantity - product.store_quantity }}</td>
                        <td>{{ product.quantity }}</td>
                        <td>{{ product.total }}</td>
                        <td>{{ product.note }}</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td colspan="4">Total</td>
                        <td>{{ deliveryLogs.reduce((prev, curr) => {return prev + parseFloat(curr.store_quantity)}, 0) | decimal }}</td>
                        <td>{{ deliveryLogs.reduce((prev, curr) => {return prev + parseFloat(curr.quantity - curr.store_quantity)}, 0) | decimal }}</td>
                        <td>{{ deliveryLogs.reduce((prev, curr) => {return prev + parseFloat(curr.quantity)}, 0) | decimal }}</td>
                        <td>{{ deliveryLogs.reduce((prev, curr) => {return prev + parseFloat(curr.total)}, 0) | decimal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#partialDelivery',
        data() {
            return {
                invoices: [],
                selectedInvoice: null,
                dateFrom: moment().format('YYYY-MM-DD'),
				dateTo: moment().format('YYYY-MM-DD'),
                deliveryLogs: [],
                byDate: false,
            }
        },

        filters: {
            formatDateTime(dt, format) {
                return dt == "" || dt == null ? "" : moment(dt).format(format);
            },

            decimal(val) {
				return parseFloat(val).toFixed(2);
			}
        },

        async created() {
            await this.getWorkOrders(); 
        },

        methods: {
            getWorkOrders() {
                axios.post('/get_work_orders', { 
                    forSearch: 'yes' 
                }).then(res => {
                    this.invoices = res.data.sales.filter(pur => pur.is_order == 'yes');
                });
            },

            async onSearchInvoice(val, loading) {
                if (val.length > 2) {
                    loading(true);
                    await axios.post("/get_work_orders", {
                            name: val,
                        })
                        .then(res => {
                            let r = res.data;
                            this.invoices = r.sales
                            loading(false)
                        })
                } else {
                    loading(false)
                    await this.getWorkOrders();
                }
            },

            getDeliveryLogs() {
                if (!this.selectedInvoice) {
                    Swal.fire({
                        icon: "error",
                        text: "Select invoice",
                    });
                    return;
                }
                let filter = {
                    saleId: this.selectedInvoice.SaleMaster_SlNo,
                    dateFrom: this.byDate == true ? this.dateFrom : '',
					dateTo: this.byDate == true ? this.dateTo : ''
                }

                axios.post('/get_order_delivery_logs', filter)
                .then(res => {
                    this.deliveryLogs = res.data;
                })
            }
        }
    })
</script>