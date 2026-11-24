<style>
    .v-select {
        float: right;
        min-width: 300px;
        background: #fff;
        margin-left: 5px;
        border-radius: 4px !important;
        margin-top: -1px;
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

    tr th,
    tr td {
        vertical-align: middle !important;
    }
</style>

<div class="row" id="partialDelivery">
    <div class="col-xs-12 col-md-12 col-lg-12">
        <fieldset class="scheduler-border scheduler-search">
            <legend class="scheduler-border">Work Order Partial Delivery</legend>
            <div class="control-group">
                <div class="form-group">
                    <label class="col-xs-1 control-label no-padding-right" for="saleInvoiceno"> Invoice no </label>
                    <div class="col-xs-3">
                        <v-select v-bind:options="invoices" label="invoice_text" v-model="selectedInvoice" v-bind:disabled="partialDelivery.orderId == 0 ? false : true" @search="onSearchInvoice"></v-select>
                    </div>
                    <div class="col-xs-2">
                        <button type="button" style="padding: 1px 15px;" @click="getOrderDetailsForDelivery" v-bind:disabled="partialDelivery.orderId == 0 ? false : true">View</button>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="col-xs-12 col-md-12 col-lg-12" v-if="cart2.length > 0" style="display:none" v-bind:style="{display: cart2.length > 0 ? '' : 'none'}">

        <div class="col-md-6">
            Delivery date: <input type="date" v-model="partialDelivery.date" v-bind:disabled="userType == 'u' ? true : false">
            <br>
        </div>
        <div class="col-md-6 text-right">
            <template v-if="selectedInvoice && (selectedInvoice.SaleMaster_SaleType == 'project' || selectedInvoice.Customer_Type == 'project' || selectedInvoice.customerType == 'project')">
                <h4 style="margin:0px;padding:0px;">Project Information</h4>
                Name: {{ selectedInvoice.project_name || 'N/A' }}<br>
                Location: {{ selectedInvoice.project_location || 'N/A' }}
            </template>
            <template v-else>
                <h4 style="margin:0px;padding:0px;">Customer Information</h4>
                Name: {{ selectedInvoice ? (selectedInvoice.Customer_Name || selectedInvoice.customerName || 'Cash Customer') : '' }}<br>
                Address: {{ selectedInvoice ? (selectedInvoice.Customer_Address || selectedInvoice.customerAddress || 'N/A') : '' }}<br>
                Mobile: {{ selectedInvoice ? (selectedInvoice.Customer_Mobile || selectedInvoice.customerMobile || 'N/A') : '' }}
            </template>
        </div>

        <div class="col-xs-12" style="margin-bottom: 4px;">
            <form class="form-inline">
                <div class="form-group clearfix">
                    <label for="">Category</label>
                    <v-select style="border: 1px solid #ccc;" :options="categories" v-model="selectedCategory" label="ProductCategory_Name" @input="filterCart"></v-select>
                </div>
                <div class="form-group clearfix">
                    <label for="">Product</label>
                    <v-select style="border: 1px solid #ccc;" :options="products" v-model="selectedProduct" label="display_text" @input="filterCart" @search="onSearchProduct"></v-select>
                </div>
            </form>
        </div>

        <div class="col-xs-12">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:5%;">Sl</th>
                        <th style="width: 30%;">Product</th>
                        <th>Sale Rate</th>
                        <th>Order Total</th>
                        <th>Order Quantity</th>
                        <th>Already Store Delivery Qty</th>
                        <th>Already Delivery quantity</th>
                        <th>Remaining Delivery quantity</th>
                        <th>Store Delivery Qty</th>
                        <th>Delivery Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(product, sl) in cart" :style="{ backgroundColor: Number(product.remaining_quantity) === 0 ? '#d4edda' : '#f3b1b7' }">
                        <td>{{ sl + 1 }}</td>
                        <td style="text-align:left;padding-left:4px;">{{ product.Product_Code }} - {{ product.Product_Name }}</td>
                        <td>{{ product.SaleDetails_Rate }}</td>
                        <td>{{ product.SaleDetails_TotalAmount }}</td>
                        <td>{{ product.order_quantity }}</td>
                        <td>{{ product.store_quantity || 0 }}</td>
                        <td>{{ (product.SaleDetails_TotalQuantity || 0) - (product.store_quantity || 0) }}</td>
                        <td>{{ product.remaining_quantity }}</td>
                        <td><input type="text" style="padding: 2px 5px;font-size: 12px;margin:2px 0;" v-model="product.store_delivery_quantity" v-on:input="productDeliveryTotal(sl)" :readonly="product.remaining_quantity == 0"></td>
                        <td><input type="text" style="padding: 2px 5px;font-size: 12px;margin:2px 0;" v-model="product.delivery_quantity" v-on:input="productDeliveryTotal(sl)" :readonly="product.remaining_quantity == 0"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align:right;padding-top:15px;">Note</td>
                        <td colspan="3">
                            <textarea style="width: 100%" v-model="partialDelivery.note"></textarea>
                        </td>
                        <td>
                            <?php if (buttonAction('entry') || buttonAction('update')): ?>
                                <button :disabled="onProgress ? true : false" style="margin-top: 5px;" class="btn btn-success" v-on:click="savePartialDelivery">Save Delivery</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
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
                cart: [],
                cart2: [],
                categories: [],
                selectedCategory: null,
                products: [],
                selectedProduct: null,
                partialDelivery: {
                    orderId: parseInt('<?php echo $orderId; ?>'),
                    date: moment().format('YYYY-MM-DD'),
                    note: ''
                },
                userType: '<?php echo $this->session->userdata("accountType"); ?>',
                saleDetails: [],
                onProgress: false,
            }
        },
        async created() {
            await this.getCategory();
            await this.getProducts();
            await this.getOrders();
        },
        methods: {
            filterCart() {
                if (this.selectedCategory == null && this.selectedProduct == null) {
                    this.cart = this.cart2;
                } else {
                    this.cart = this.cart2.filter(item => {
                        let categoryMatch = true;
                        let productMatch = true;
                        if (this.selectedCategory != null) {
                            categoryMatch = item.ProductCategory_Name == this.selectedCategory.ProductCategory_Name;
                        }
                        if (this.selectedProduct != null) {
                            productMatch = item.Product_Name == this.selectedProduct.Product_Name;
                        }
                        return categoryMatch && productMatch;
                    });
                }

            },
            getCategory() {
                axios.get("/get_categories")
                    .then(res => {
                        this.categories = res.data;
                    })
            },
            getProducts() {
                axios.post('/get_products', {
                    isService: 'false',
                    forSearch: 'yes'
                }).then(res => {
                    this.products = res.data;
                })
            },
            async onSearchProduct(val, loading) {
                if (val.length > 2) {
                    loading(true);
                    await axios.post("/get_products", {
                            name: val,
                            isService: 'false'
                        })
                        .then(res => {
                            let r = res.data;
                            this.products = r.filter(item => item.status == 'a' && item.is_service == 'false');
                            loading(false)
                        })
                } else {
                    loading(false)
                    await this.getProducts();
                }
            },
            getOrders() {
                axios.post('/get_work_orders', {
                    forSearch: 'yes'
                }).then(res => {
                    this.invoices = res.data.sales.filter(s => s.is_delivered == 'no');
                    if (this.partialDelivery.orderId != 0) {
                        this.selectedInvoice = this.invoices.find(
                            inv => inv.SaleMaster_SlNo == this.partialDelivery.orderId
                        );
                        if (this.selectedInvoice) {
                            this.getOrderDetailsForDelivery();
                        }
                    }
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
                    await this.getOrders();
                }
            },
            async getOrderDetailsForDelivery() {
                if (!this.selectedInvoice) {
                    Swal.fire({
                        icon: "error",
                        text: "Select invoice",
                    });
                    return;
                }
                await axios.post('/get_order_for_delivery', {
                    saleId: this.selectedInvoice.SaleMaster_SlNo
                }).then(res => {
                    // Reuse the invoice from get_work_orders, which includes customer fields.
                    this.cart = res.data;
                    this.cart2 = res.data;
                })
            },
            async productDeliveryTotal(ind) {
                this.onProgress = true;
                const product = this.cart[ind];
                const alreadyDelivered = Number(product.SaleDetails_TotalQuantity || 0);
                const storeDeliveryQuantity = Number(product.store_delivery_quantity || 0);
                const deliveryQuantity = Number(product.delivery_quantity || 0);
                const totalDeliveryQuantity = storeDeliveryQuantity + deliveryQuantity;
                const remainingQuantity = Number(product.order_quantity) - alreadyDelivered;

                if (storeDeliveryQuantity < 0 || deliveryQuantity < 0 || totalDeliveryQuantity > remainingQuantity) {
                    Swal.fire({
                        icon: "error",
                        text: "Delivery quantity is not valid",
                    });
                    product.store_delivery_quantity = 0;
                    product.delivery_quantity = 0;
                    this.onProgress = false;
                    return;
                }

                product.delivery_amount = totalDeliveryQuantity * Number(product.SaleDetails_Rate);
                this.onProgress = false;
            },

            savePartialDelivery() {
                let filteredCart = this.cart.filter(product =>
                    (Number(product.store_delivery_quantity || 0) + Number(product.delivery_quantity || 0)) > 0 &&
                    product.SaleDetails_Rate > 0
                );

                if (filteredCart.length == 0) {
                    Swal.fire({
                        icon: "error",
                        text: "No products to delivery",
                    });
                    return;
                }
                if (this.partialDelivery.date == null || this.partialDelivery.date == '') {
                    Swal.fire({
                        icon: "error",
                        text: "Enter date",
                    });
                    return;
                }

                let data = {
                    invoice: this.selectedInvoice,
                    partialDelivery: this.partialDelivery,
                    cart: filteredCart
                }


                this.onProgress = true;
                axios.post('/add_order_delivery', data).then(async res => {
                    let r = res.data;
                    alert(r.message);
                    if (r.success) {
                        this.clearForm();
                        window.location = '/workOrderDelivery';
                    }
                })
            },

            clearForm() {
                this.selectedInvoice = null;
                this.partialDelivery = {
                    orderId: 0,
                    date: moment().format('YYYY-MM-DD'),
                    note: ''
                }
                this.cart = [];
                this.saleDetails = [];
                this.onProgress = false;
            }
        }
    })
</script>