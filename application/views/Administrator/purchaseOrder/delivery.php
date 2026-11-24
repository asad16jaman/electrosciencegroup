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
            <legend class="scheduler-border">Partial Delivery</legend>
            <div class="control-group">
                <div class="form-group">
                    <label class="col-xs-1 control-label no-padding-right" for="purchaseInvoiceno"> Invoice no </label>
                    <div class="col-xs-3">
                        <v-select v-bind:options="invoices" style="width: 100%;" label="invoice_text" v-model="selectedInvoice" v-bind:disabled="partialDelivery.orderId == 0 ? false : true" @search="onSearchInvoice"></v-select>
                    </div>
                    <div class="col-xs-2">
                        <button type="button" style="padding: 1px 15px;" @click="getPurchaseDetailsForDelivery" v-bind:disabled="partialDelivery.orderId == 0 ? false : true">View</button>
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
            <h4 style="margin:0px;padding:0px;">Supplier Information</h4>
            Name: {{ selectedInvoice.Supplier_Name }}<br>
            Address: {{ selectedInvoice.Supplier_Address }}<br>
            Mobile: {{ selectedInvoice.Supplier_Mobile }}
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
                        <th>Purchase Rate</th>
                        <th>Purchase Total</th>
                        <th>Order Quantity</th>
                        <th>Already Delivery quantity</th>
                        <th>Remaining Delivery quantity</th>
                        <th>Delivery Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(product, sl) in cart" :style="{ backgroundColor: Number(product.remaining_quantity) === 0 ? '#d4edda' : '#f3b1b7' }">
                        <td>{{ sl + 1 }}</td>
                        <td style="text-align:left;padding-left:4px;">{{ product.Product_Code }} - {{ product.Product_Name }}</td>
                        <td>{{ product.PurchaseDetails_Rate }}</td>
                        <td>{{ product.PurchaseDetails_TotalAmount }}</td>
                        <td>{{ product.order_quantity }}</td>
                        <td>{{ product.PurchaseDetails_TotalQuantity }}</td>
                        <td>{{ product.remaining_quantity }}</td>
                        <td><input type="text" style="padding: 2px 5px;font-size: 12px;margin:2px 0;" v-model="product.delivery_quantity" v-on:input="productDeliveryTotal(sl)" :readonly="product.remaining_quantity == 0"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right;padding-top:15px;">Note</td>
                        <td colspan="2">
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
                purchaseDetails: [],
                onProgress: false,
            }
        },
        async created() {
            await this.getCategory();
            await this.getProducts();
            await this.getPurchases();
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
            getPurchases() {
                axios.post('/get_purchase_orders', {
                    forSearch: 'yes'
                }).then(res => {
                    this.invoices = res.data.purchases.filter(pur => pur.is_delivered == 'no');
                    if (this.partialDelivery.orderId != 0) {
                        this.selectedInvoice = this.invoices.find(
                            inv => inv.PurchaseMaster_SlNo == this.partialDelivery.orderId
                        );
                        this.getPurchaseDetailsForDelivery();
                    }
                });
            },
            async onSearchInvoice(val, loading) {
                if (val.length > 2) {
                    loading(true);
                    await axios.post("/get_purchase_orders", {
                            name: val,
                        })
                        .then(res => {
                            let r = res.data;
                            this.invoices = r.purchases
                            loading(false)
                        })
                } else {
                    loading(false)
                    await this.getPurchases();
                }
            },
            async getPurchaseDetailsForDelivery() {
                if (!this.selectedInvoice) {
                    Swal.fire({
                        icon: "error",
                        text: "Select invoice",
                    });
                    return;
                }
                await axios.post('/get_purchase_for_delivery', {
                    purchaseId: this.selectedInvoice.PurchaseMaster_SlNo
                }).then(res => {
                    this.cart = res.data;
                    this.cart2 = res.data;
                })
            },
            async productDeliveryTotal(ind) {
                this.onProgress = true;

                if (this.cart[ind].delivery_quantity > (this.cart[ind].order_quantity - this.cart[ind].PurchaseDetails_TotalQuantity)) {
                    Swal.fire({
                        icon: "error",
                        text: "Delivery quantity is not valid",
                    });
                    this.cart[ind].delivery_quantity = 0;
                    return;
                }

                this.cart[ind].delivery_amount = parseFloat(this.cart[ind].delivery_quantity) * parseFloat(this.cart[ind].PurchaseDetails_Rate);
                // this.calculateTotal();
                this.onProgress = false;
            },
            // calculateTotal() {
            //     this.partialDelivery.total = this.cart.reduce((prev, cur) => {
            //         return prev + (cur.delivery_amount ? parseFloat(cur.delivery_amount) : 0.00)
            //     }, 0);
            // },
            savePartialDelivery() {
                let filteredCart = this.cart.filter(product => product.delivery_quantity > 0 && product.PurchaseDetails_Rate > 0);

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
                axios.post('/add_purchase_delivery', data).then(async res => {
                    let r = res.data;
                    alert(r.message);
                    if (r.success) {
                        this.clearForm();
                        window.location = '/partialDelivery';
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
                this.purchaseDetails = [];
                this.onProgress = false;
            }
        }
    })
</script>