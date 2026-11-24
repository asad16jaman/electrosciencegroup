<style scoped>
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

    #branchDropdown .vs__actions button {
        display: none;
    }

    #branchDropdown .vs__actions .open-indicator {
        height: 15px;
        margin-top: 7px;
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

    input[disabled=disabled] {
        color: #ffff !important;
    }

    .btnEntry,
    .btnEntry:hover,
    .btnEntry:focus {
        background: green !important;
    }

    tr th,
    tr td {
        vertical-align: middle !important;
    }

    .product-image {
        width: 75px;
        height: 75px;
        border: 1px solid #d5d5d5;
        border-radius: 5px;
        padding: 1px;
        margin-top: 5px;
    }

    .firstCard {
        padding: 0;
        background: #e3e3e366;
        height: 630px;
        border-top: 5px solid #9b6d65;
    }

    .secondCard {
        padding: 0;
        background: #eefe;
        height: 630px;
        border-top: 5px solid #d15b47;
    }

    .thirdCard {
        padding: 0;
        background: #eefe;
        height: 100px;
        border-bottom: 5px solid #d15b47;
    }

    @media screen and (max-width: 1000px) and (min-width: 300px) {
        .firstCard {
            height: auto !important;
        }

        .secondCard {
            height: auto !important;
        }

        .thirdCard {
            height: auto !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.add('sidebar', 'responsive', 'ace-save-state', 'sidebar-fixed', 'menu-min');
        }
    });
</script>


<div id="sales" class="row" style="margin: 0;">
    <div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0;">
        <fieldset class="scheduler-border entryFrom">
            <div class="control-group">
                <div class="row">
                    <div class="form-group">
                        <label class="col-xs-4 col-md-1 control-label no-padding-right"> Invoice no </label>
                        <div class="col-xs-8 col-md-2">
                            <input type="text" id="invoiceNo" class="form-control" v-model="sales.invoiceNo" readonly />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-4 col-md-1 control-label no-padding-right"> Sales By </label>
                        <div class="col-xs-8 col-md-2">
                            <v-select v-bind:options="employees" v-model="selectedEmployee" label="display_name" placeholder="Select Employee"></v-select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-4 col-md-1 control-label no-padding-right"> Sales From </label>
                        <div class="col-xs-8 col-md-2">
                            <v-select id="branchDropdown" v-bind:options="branches" label="Branch_name" v-model="selectedBranch" disabled></v-select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12 col-md-3">
                            <input class="form-control" id="salesDate" type="date" v-model="sales.salesDate" v-bind:disabled="userType == 'u' ? true : false" />
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="col-xs-12 col-md-6 col-lg-6 firstCard" style="margin-top: 6px;">
        <div class="card">
            <div class="card-header" style="padding-top: 10px;">
                <div class="col-xs-12 col-md-3">
                    <v-select v-bind:options="customers" label="display_name" v-model="selectedCustomer" v-on:input="customerOnChange" @search="onSearchCustomer"></v-select>
                </div>
                <div class="col-xs-12 col-md-3">
                    <input type="text" id="customerName" placeholder="Customer Name" class="form-control" v-model="selectedCustomer.Customer_Name" v-bind:disabled="selectedCustomer.Customer_Type == 'G' || selectedCustomer.Customer_Type == 'N' ? false : true" />
                </div>
                <div class="col-xs-12 col-md-3">
                    <input type="text" id="mobileNo" placeholder="Mobile No" class="form-control" v-model="selectedCustomer.Customer_Mobile" v-bind:disabled="selectedCustomer.Customer_Type == 'G' || selectedCustomer.Customer_Type == 'N' ? false : true" />
                </div>
                <div class="col-xs-12 col-md-3">
                    <input type="text" id="address" placeholder="Address" class="form-control" v-model="selectedCustomer.Customer_Address" v-bind:disabled="selectedCustomer.Customer_Type == 'G' || selectedCustomer.Customer_Type == 'N' ? false : true" />
                </div>
            </div>
            <div class="card-body" style="padding-top: 15px;">
                <div class="col-xs-12 col-md-12" style="margin-bottom: 4px;">
                    <div class="input-group" style="width: 100%;">
                        <input type="text" class="form-control" style="height: 28px;" placeholder="Scan or enter barcode" v-model="barcodeInput" @change.enter="onBarcodeEnter">
                        <span class="input-group-addon">
                            <i class="fa fa-barcode"></i>
                        </span>
                    </div>
                </div>
                <div class="col-xs-12 col-md-12" style="height: 480px;overflow-y:auto;border-bottom: 1px solid #dbd9d9;">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width:6%;color:#000;">Sl</th>
                                <th style="color:#000;">Code</th>
                                <th style="width:40%;color:#000;">Product</th>
                                <th style="color:#000;">Category</th>
                                <th style="width:10%;color:#000;">Qty</th>
                                <th style="color:#000;">Rate</th>
                                <th style="color:#000;">Total</th>
                                <th style="color:#000;">Action</th>
                            </tr>
                        </thead>
                        <tbody style="display:none;" v-bind:style="{display: cart.length > 0 ? '' : 'none'}">
                            <tr v-for="(product, sl) in cart" :style="{background: product.is_service == 'true' ? '#ffecc0' : ''}">
                                <td>{{ sl + 1 }}</td>
                                <td>{{ product.productCode }}</td>
                                <td style="text-align: left;padding-left:3px;">{{ product.name }}</td>
                                <td>{{ product.categoryName }}</td>
                                <td>
                                    <div class="input-group" style="width: 120px;">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-xs btn-default" @click="product.quantity = Math.max(1, parseFloat(product.quantity) - 1); quantityRateChange()" style="padding:0px 6px;">-</button>
                                        </span>
                                        <input type="text" v-model="product.quantity" class="form-control text-center" @input="quantityRateChange" />
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-xs btn-default" @click="product.quantity = parseFloat(product.quantity) + 1; quantityRateChange()" style="padding:0px 6px;">+</button>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" min="0" step="any" class="form-control" v-model="product.salesRate" style="height:27px;margin:0;padding: 0 5px; width: 120px; text-align: center;" @input="quantityRateChange" />
                                </td>
                                <td>{{ product.total }}</td>
                                <td><a href="" v-on:click.prevent="removeFromCart(sl)"><i class="fa fa-trash"></i></a></td>
                            </tr>
                        </tbody>
                        <tbody v-if="cart.length == 0" style="display:none;" v-bind:style="{display: cart.length == 0 ? '' : 'none'}">
                            <tr>
                                <td colspan="8" style="text-align: center; color: #999;">No products in cart</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-xs-12 col-md-12">
                    <div class="form-group" style="display: flex;align-items:center;">
                        <label class="col-xs-2 col-md-2">Note</label>
                        <div class="col-xs-6 col-md-7" style="border-left: 1px solid #cdcdcd;border-right: 1px solid #cdcdcd;">
                            <textarea class="form-control" style="font-size:13px;margin-top:3px;height: 66px;" placeholder="Note" v-model="sales.note"></textarea>
                        </div>
                        <div class="col-xs-4 col-md-3 text-center">
                            <span style="font-size: 25px;font-weight: 700;color: green;" v-text="sales.total"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6 col-lg-6 secondCard" style="margin-top: 6px;">
        <div class="card">
            <div class="card-header" style="padding: 5px;">
                <div class="col-xs-12 col-md-6">
                    <v-select :options="categories" v-model="selectedCategory" label="ProductCategory_Name" @input="onChangeCategory" placeholder="Select Category"></v-select>
                </div>
                <div class="col-xs-12 col-md-6">
                    <v-select :options="brands" v-model="selectedBrand" label="brand_name" @input="onChangeBrand" placeholder="Select Brand"></v-select>
                </div>
                <div class="col-xs-12 col-md-12">
                    <input type="text" class="form-control" placeholder="Product Name" style="padding: 8px 6px !important; height: 32px;" @input="onSearchProduct" />
                </div>
            </div>
            <div class="card-body" style="padding: 5px;">
                <div class="col-xs-12 col-md-12">
                    <div style="height: 530px;overflow-y:auto;" class="row custom-scrollbar">
                        <div class="col-md-3 col-xs-6" style="margin-bottom: 6px;display:none;" :style="{display: products.length > 0 ? '' : 'none'}" v-for="(product, index) in products" :key="index">
                            <div class="product-card"
                                @click="addToCart(product)"
                                :style="{
                                    position: 'relative',
                                    cursor: 'pointer',
                                    height: '165px',
                                    borderRadius: '5px',
                                    border: '1px solid #c1c1c1',
                                    padding: '4px',
                                    background: cart.find(p => p.productId == product.Product_SlNo) ? '#ffecc0' : '#fff',
                                    textAlign: 'center',
                                    display: 'flex',
                                    alignItems: 'center',
                                    flexDirection: 'column'
                                }">
                                <p style="margin: 0px; position: absolute; left: 0px; bottom: 0px; padding: 1px; font-size: 11px; width: 100%; text-align: center; background: #d3d3d3;">
                                    Stock: <span :style="{color: product.current_quantity > 0 ? 'green' : 'red'}" v-text="product.current_quantity"></span>
                                </p>
                                <div class="product-image">
                                    <img :src="`/${product.image_name ? product.image_name : 'assets/no_image.gif'}`" style="width: 100%;" alt="Product Image" />
                                </div>
                                <div class="product-details">
                                    <p :title="product.Product_Name" class="product-name" style="font-size: 11px;margin-bottom: 3px;font-weight: 700;" v-text="product.Product_Name.length > 38 ? product.Product_Name.substring(0, 38) + '...' : product.Product_Name"></p>
                                    <p class="product-price" style="font-size: 11px;margin-bottom: 4px;font-weight: 700;" v-text="product.Product_SellingPrice"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-12 col-lg-12 thirdCard" style="margin-top: 6px;">
        <div class="card">
            <div class="card-body" style="padding: 5px;">
                <div class="col-xs-12 col-md-3 no-padding">
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">SubTotal</label>
                        <div class="col-xs-8 col-md-8">
                            <input type="number" min="0" step="any" class="form-control" v-model="sales.subTotal" readonly />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">Discount</label>
                        <div class="col-xs-4 col-md-4" style="display: flex;align-items:center;">
                            <input type="number" min="0" step="any" id="discountPercent" class="form-control" v-model="discountPercent" v-on:input="calculateTotal" />
                            <span style="margin-top: -4px;font-size: 16px;">%</span>
                        </div>
                        <div class="col-xs-4 col-md-4">
                            <input type="number" min="0" step="any" id="discountPercent" class="form-control" v-model="discountPercent" v-on:input="calculateTotal" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">Vat</label>
                        <div class="col-xs-4 col-md-4" style="display: flex;align-items:center;">
                            <input type="number" min="0" step="any" id="vatPercent" class="form-control" v-model="vatPercent" v-on:input="calculateTotal" />
                            <span style="margin-top: -4px;font-size: 16px;">%</span>
                        </div>
                        <div class="col-xs-4 col-md-4">
                            <input type="number" min="0" step="any" id="vat" class="form-control" :readonly="vatPercent > 0 ? false : true" v-model="sales.vat" v-on:input="calculateTotal" />
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3 no-padding">
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">Trans.Cost</label>
                        <div class="col-xs-8 col-md-8">
                            <input type="number" min="0" step="any" id="transportCost" class="form-control" v-model="sales.transportCost" v-on:input="calculateTotal" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">Total</label>
                        <div class="col-xs-8 col-md-8">
                            <input type="number" id="total" class="form-control" v-model="sales.total" readonly />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">Due</label>
                        <div class="col-xs-8 col-md-4">
                            <input type="number" min="0" step="any" id="due" class="form-control" v-model="sales.due" readonly />
                        </div>
                        <div class="col-xs-8 col-md-4">
                            <input type="number" min="0" step="any" id="previousDue" class="form-control" v-model="sales.previousDue" readonly style="color:red;" />
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3 no-padding">
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">CashPaid</label>
                        <div class="col-xs-8 col-md-8">
                            <input type="number" min="0" step="any" id="cashPaid" class="form-control" v-model="sales.cashPaid" v-on:input="calculateTotal" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-xs-4 col-md-4">BankPaid</label>
                        <div class="col-xs-8 col-md-8">
                            <input type="number" min="0" step="any" id="bankPaid" class="form-control" v-model="sales.bankPaid" v-on:input="calculateTotal" />
                        </div>
                    </div>
                    <div class="form-group" style="display: none;" :style="{display: sales.bankPaid > 0 ? '' : 'none'}" v-if="sales.bankPaid > 0">
                        <label for="" class="col-xs-4 col-md-4">Bank Account</label>
                        <div class="col-xs-8 col-md-8">
                            <v-select :options="banks" v-model="selectedBank" label="display_name" style="border-radius: 4px;"></v-select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3 no-padding">
                    <div class="form-group">
                        <div class="col-xs-12 col-md-12">
                            <?php if (buttonAction('entry') || buttonAction('update')): ?>
                                <button @click="saveSales" :disabled="saleOnProgress" style="display: flex ; align-items: center; justify-content: center; width: 100%; height: 80px; border: 1px solid #ccc; border-radius: 5px; background: green; font-size: 40px; color: white;" type="button">Sale</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
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
        el: '#sales',
        data() {
            return {
                sales: {
                    salesId: 0,
                    invoiceNo: '<?php echo $invoice; ?>',
                    salesBy: '<?php echo $this->session->userdata("FullName"); ?>',
                    salesType: 'retail',
                    salesFrom: '',
                    salesDate: moment().format('YYYY-MM-DD'),
                    customerId: '',
                    employeeId: null,
                    accountId: null,
                    subTotal: 0,
                    discount: 0,
                    vatPercent: 0,
                    vat: 0,
                    transportCost: 0,
                    total: 0,
                    cashPaid: 0,
                    bankPaid: 0,
                    paid: 0,
                    previousDue: 0,
                    due: 0,
                    note: '',
                },
                vatPercent: 0,
                discountPercent: 0,
                cart: [],
                employees: [],
                selectedEmployee: null,
                banks: [],
                selectedBank: null,
                branches: [],
                selectedBranch: {
                    branch_id: "<?php echo $this->session->userdata('BRANCHid'); ?>",
                    Branch_name: "<?php echo $this->session->userdata('Branch_name'); ?>"
                },
                customers: [],
                selectedCustomer: {
                    Customer_SlNo: '',
                    Customer_Code: '',
                    Customer_Name: 'Cash Customer',
                    display_name: 'Cash Customer',
                    Customer_Mobile: '',
                    Customer_Address: '',
                    Customer_Type: 'G'
                },
                oldCustomerId: null,
                oldPreviousDue: 0,
                categories: [],
                selectedCategory: null,
                brands: [],
                selectedBrand: null,
                products: [],
                selectedProduct: {
                    Product_SlNo: '',
                    display_text: 'Select Product',
                    Product_Name: '',
                    Unit_Name: '',
                    quantity: 0,
                    Product_Purchase_Rate: '',
                    Product_SellingPrice: 0,
                    vat: 0,
                    total: 0
                },
                barcodeInput: '',
                productPurchaseRate: '',
                productStockText: '',
                productStock: '',
                saleOnProgress: false,
                sales_due_on_update: 0,
                keyPressed: '',
                click: false,
                userType: '<?php echo $this->session->userdata("accountType"); ?>'
            }
        },
        async created() {
            await this.getBrand();
            await this.getCategory();
            await this.getEmployees();
            await this.getBank();
            await this.getBranches();
            await this.getCustomers();
            this.getProducts();

            if (this.sales.salesId != 0) {
                await this.getSales();
            }
        },
        methods: {
            getEmployees() {
                axios.get('/get_employees').then(res => {
                    this.employees = res.data.map(item => {
                        item.display_name = `${item.Employee_Name} - ${item.Employee_ID}`;
                        return item;
                    });
                })
            },
            getBank() {
                axios.get('/get_bank_accounts').then(res => {
                    this.banks = res.data.map(item => {
                        item.display_name = `${item.account_name} - ${item.account_number} - ${item.bank_name}`;
                        return item;
                    });
                })
            },
            getBrand() {
                axios.get('/get_brands').then(res => {
                    this.brands = res.data;
                })
            },
            getCategory() {
                axios.get('/get_categories').then(res => {
                    this.categories = res.data;
                })
            },
            getBranches() {
                axios.get('/get_branches').then(res => {
                    this.branches = res.data;
                })
            },
            async getCustomers() {
                await axios.post('/get_customers', {
                    customerType: this.sales.salesType,
                    forSearch: 'yes'
                }).then(res => {
                    this.customers = res.data;
                    this.customers.unshift({
                        Customer_SlNo: '',
                        Customer_Code: '',
                        Customer_Name: 'Cash Customer',
                        display_name: 'Cash Customer',
                        Customer_Mobile: '',
                        Customer_Address: '',
                        Customer_Type: 'G'
                    }, {
                        Customer_SlNo: '',
                        Customer_Code: '',
                        Customer_Name: '',
                        display_name: 'New Customer',
                        Customer_Mobile: '',
                        Customer_Address: '',
                        Customer_Type: 'N'
                    })
                })
            },
            async onSearchCustomer(val, loading) {
                if (val.length > 2) {
                    loading(true);
                    await axios.post("/get_customers", {
                            name: val,
                        })
                        .then(res => {
                            let r = res.data;
                            this.customers = r.filter(item => item.status == 'a')
                            loading(false)
                        })
                } else {
                    loading(false)
                    await this.getCustomers();
                }
            },
            onChangeCategory() {
                if (this.selectedCategory == null) {
                    this.getProducts();
                } else {
                    this.onSearchProduct();
                }
            },
            onChangeBrand() {
                if (this.selectedBrand == null) {
                    this.getProducts();
                } else {
                    this.onSearchProduct();
                }
            },
            getProducts() {
                axios.post('/get_total_stock', {
                    forSearch: 'yes'
                }).then(res => {
                    if (this.sales.salesType == 'wholesale') {
                        this.products = res.data.stock.filter((product) => product.Product_WholesaleRate > 0);
                        this.products.map((product) => {
                            return product.Product_SellingPrice = product.Product_WholesaleRate;
                        })
                    } else {
                        this.products = res.data.stock;
                    }
                })
            },
            async onSearchProduct() {
                if (event.target.value != '') {
                    await axios.post("/get_total_stock", {
                            name: event.target.value,
                            categoryId: this.selectedCategory ? this.selectedCategory.ProductCategory_SlNo : '',
                            brandId: this.selectedBrand ? this.selectedBrand.brand_SiNo : ''
                        })
                        .then(res => {
                            let r = res.data.stock;
                            if (this.sales.salesType == 'wholesale') {
                                this.products = r.filter(item => item.status == 'a' && item.Product_WholesaleRate > 0).map((product) => {
                                    product.Product_SellingPrice = product.Product_WholesaleRate;
                                    return product;
                                });
                            } else {
                                this.products = r.filter(item => item.status == 'a');
                            }
                        })
                } else {
                    this.getProducts();
                }
            },
            onSalesTypeChange() {
                this.selectedCustomer = {
                    Customer_SlNo: '',
                    Customer_Code: '',
                    Customer_Name: 'Cash Customer',
                    display_name: 'Cash Customer',
                    Customer_Mobile: '',
                    Customer_Address: '',
                    Customer_Type: 'G'
                }
                this.getCustomers();

                this.clearProduct();
                this.getProducts();
            },
            async customerOnChange() {
                this.sales.previousDue = 0;
                if (this.selectedCustomer == null) {
                    this.selectedCustomer = {
                        Customer_SlNo: '',
                        Customer_Code: '',
                        Customer_Name: 'Cash Customer',
                        display_name: 'Cash Customer',
                        Customer_Mobile: '',
                        Customer_Address: '',
                        Customer_Type: 'G'
                    }
                    return
                }
                if (this.selectedCustomer.Customer_SlNo == '') {
                    return;
                }

                if (this.sales.salesId != 0 && this.oldCustomerId != parseInt(this.selectedCustomer.Customer_SlNo)) {
                    let changeConfirm = confirm('Changing customer will set previous due to current due amount. Do you really want to change customer?');
                    if (changeConfirm == false) {
                        return;
                    }
                } else if (this.sales.salesId != 0 && this.oldCustomerId == parseInt(this.selectedCustomer.Customer_SlNo)) {
                    this.sales.previousDue = this.oldPreviousDue;
                    return;
                }

                await this.getCustomerDue();

                this.calculateTotal();
            },
            async getCustomerDue() {
                await axios.post('/get_customer_due', {
                    customerId: this.selectedCustomer.Customer_SlNo
                }).then(res => {
                    if (res.data.length > 0) {
                        this.sales.previousDue = res.data[0].dueAmount;
                    } else {
                        this.sales.previousDue = 0;
                    }
                })
            },
            async onBarcodeEnter() {
                if (this.barcodeInput == '') {
                    Swal.fire({
                        icon: "error",
                        text: "Barcode field is empty",
                    });
                    return;
                }
                let product = await axios.post("/get_total_stock", {
                    name: this.barcodeInput,
                }).then(res => {
                    let r = res.data.stock;
                    if (this.sales.salesType == 'wholesale') {
                        return r.filter(item => item.status == 'a' && item.Product_WholesaleRate > 0).map((product) => {
                            product.Product_SellingPrice = product.Product_WholesaleRate;
                            return product;
                        });
                    } else {
                        return r.filter(item => item.status == 'a');
                    }
                })
                if (product.length == 0) {
                    Swal.fire({
                        icon: "error",
                        text: "Product not found",
                    });
                    return;
                }
                product = product[0];
                product.quantity = product.quantity || 1;
                let prod = {
                    productId: product.Product_SlNo,
                    productCode: product.Product_Code,
                    categoryName: product.ProductCategory_Name,
                    name: product.Product_Name,
                    salesRate: product.Product_SellingPrice,
                    vat: product.vat,
                    is_service: product.is_service,
                    quantity: product.quantity,
                    total: parseFloat(product.quantity * product.Product_SellingPrice).toFixed(2),
                    purchaseRate: product.Product_Purchase_Rate
                }

                if (prod.productId == '') {
                    document.querySelector("#product [type='search']").focus();
                    return;
                }
                if (prod.quantity == 0 || product.quantity == '') {
                    Swal.fire({
                        icon: "error",
                        text: "Enter quantity",
                    });
                    return;
                }

                if (parseFloat(prod.quantity) > parseFloat(this.productStock) && prod.is_service == 'false') {
                    Swal.fire({
                        icon: "error",
                        text: "Stock unavailable",
                    });
                    return;
                }

                let cartInd = this.cart.find(p => p.productId == prod.productId);
                if (cartInd) {
                    prod.quantity = parseFloat(cartInd.quantity) + parseFloat(prod.quantity);
                    prod.total = parseFloat(cartInd.quantity * cartInd.salesRate).toFixed(2);
                    this.cart.splice(this.cart.findIndex(p => p.productId == prod.productId), 1);
                }

                this.cart.unshift(prod);
                this.clearProduct();
                this.barcodeInput = "";
                this.calculateTotal();
            },
            async addToCart(product = null) {
                product.quantity = product.quantity || 1;
                let prod = {
                    productId: product.Product_SlNo,
                    productCode: product.Product_Code,
                    categoryName: product.ProductCategory_Name,
                    name: product.Product_Name,
                    salesRate: product.Product_SellingPrice,
                    vat: product.vat,
                    is_service: product.is_service,
                    quantity: product.quantity,
                    total: parseFloat(product.quantity * product.Product_SellingPrice).toFixed(2),
                    purchaseRate: product.Product_Purchase_Rate
                }

                if (prod.productId == '') {
                    document.querySelector("#product [type='search']").focus();
                    return;
                }
                if (prod.quantity == 0 || product.quantity == '') {
                    Swal.fire({
                        icon: "error",
                        text: "Enter quantity",
                    });
                    return;
                }

                if (parseFloat(prod.quantity) > parseFloat(this.productStock) && prod.is_service == 'false') {
                    Swal.fire({
                        icon: "error",
                        text: "Stock unavailable",
                    });
                    return;
                }

                let cartInd = this.cart.find(p => p.productId == prod.productId);
                if (cartInd) {
                    prod.quantity = parseFloat(cartInd.quantity) + parseFloat(prod.quantity);
                    prod.total = parseFloat(cartInd.quantity * cartInd.salesRate).toFixed(2);
                    this.cart.splice(this.cart.findIndex(p => p.productId == prod.productId), 1);
                }

                this.cart.unshift(prod);
                this.clearProduct();
                this.calculateTotal();
            },
            quantityRateChange() {
                this.cart = this.cart.map(item => {
                    if (item.quantity == '' || item.quantity == 0) {
                        item.quantity = 1
                    }
                    if (item.salesRate == '') {
                        item.salesRate = 0;
                    }
                    item.total = parseFloat(parseFloat(item.salesRate) * parseFloat(item.quantity)).toFixed(2);
                    return item;
                })
                this.calculateTotal();
            },
            removeFromCart(ind) {
                this.cart.splice(ind, 1);
                this.calculateTotal();
            },
            clearProduct() {
                this.productStock = '';
                this.productStockText = '';
                this.keyPressed = '';
                this.click = false;
            },
            calculateTotal() {
                this.sales.subTotal = this.cart.reduce((prev, curr) => {
                    return prev + parseFloat(curr.total)
                }, 0).toFixed(2);
                if (event.target.id != 'transportCost') {
                    this.sales.vat = this.cart.reduce((prev, curr) => {
                        return +prev + +(curr.total * (curr.vat / 100))
                    }, 0);
                }
                if (event.target.id == 'discountPercent') {
                    this.sales.discount = ((parseFloat(this.sales.subTotal) * parseFloat(this.discountPercent)) / 100).toFixed(2);
                } else {
                    this.discountPercent = (parseFloat(this.sales.discount) / parseFloat(this.sales.subTotal) * 100).toFixed(2);
                }
                this.sales.total = (parseFloat(this.sales.subTotal) - parseFloat(this.sales.discount)).toFixed(2);
                if (event.target.id == 'vatPercent') {
                    this.sales.vat = ((parseFloat(this.sales.total) * parseFloat(this.vatPercent)) / 100).toFixed(2);
                } else if (event.target.id == 'vat') {
                    this.vatPercent = (parseFloat(this.sales.vat) / parseFloat(this.sales.total) * 100).toFixed(2);
                } else {
                    this.vatPercent = (parseFloat(this.sales.vat) / parseFloat(this.sales.total) * 100).toFixed(2);
                }
                this.sales.total = parseFloat(parseFloat(this.sales.vat) + parseFloat(this.sales.total) + parseFloat(this.sales.transportCost)).toFixed(2);

                var totalPaid = parseFloat(parseFloat(this.sales.cashPaid) + parseFloat(this.sales.bankPaid)).toFixed(2);
                if (event.target.id != 'cashPaid' && event.target.id != 'bankPaid' && event.target.id != 'bank_charge') {
                    this.sales.cashPaid = this.sales.total;
                    this.sales.due = 0;
                } else if (event.target.id == 'cashPaid') {
                    this.sales.due = parseFloat(parseFloat(this.sales.total) - parseFloat(totalPaid)).toFixed(2);
                } else if (event.target.id == 'bankPaid' || event.target.id == 'bank_charge') {
                    this.sales.due = parseFloat(parseFloat(this.sales.total) - parseFloat(totalPaid)).toFixed(2);
                }
            },
            async saveSales() {
                this.sales.accountId = this.selectedBank ? this.selectedBank.account_id : '';
                this.sales.paid = parseFloat(parseFloat(this.sales.cashPaid) + parseFloat(this.sales.bankPaid)).toFixed(2);
                if (this.keyPressed == 'Enter' && !this.click) {
                    this.click = true;
                    return;
                }
                if (this.selectedCustomer == null) {
                    Swal.fire({
                        icon: "error",
                        text: "Select Customer",
                    });
                    return;
                }
                if (this.cart.length == 0) {
                    Swal.fire({
                        icon: "error",
                        text: "Cart is empty",
                    });
                    return;
                }

                let url = "/add_sales";
                if (this.sales.salesId != 0) {
                    url = "/update_sales";
                }
                if (parseFloat(this.selectedCustomer.Customer_Credit_Limit) < (parseFloat(this.sales.due) + parseFloat(this.sales.previousDue))) {
                    Swal.fire({
                        icon: "error",
                        text: `Customer credit limit (${this.selectedCustomer.Customer_Credit_Limit}) exceeded`,
                    });
                    return;
                }

                if (parseFloat(this.sales.due) > 0 && this.selectedCustomer.Customer_Type == 'G') {
                    Swal.fire({
                        icon: "error",
                        text: `Cash Customer can not due`,
                    });
                    return;
                }
                if (parseFloat(this.sales.paid) > parseFloat(this.sales.total)) {
                    Swal.fire({
                        icon: "error",
                        text: `Paid amount can not greater than total amount`,
                    });
                    return;
                }
                if (parseFloat(this.sales.bankPaid) > 0 && this.selectedBank == null) {
                    Swal.fire({
                        icon: "error",
                        text: `Please select account`,
                    });
                    return;
                }

                if (this.selectedEmployee != null && this.selectedEmployee.Employee_SlNo != null) {
                    this.sales.employeeId = this.selectedEmployee.Employee_SlNo;
                } else {
                    this.sales.employeeId = null;
                }
                this.sales.salesFrom = this.selectedBranch.branch_id;
                this.sales.customerId = this.selectedCustomer.Customer_SlNo;

                let data = {
                    sales: this.sales,
                    cart: this.cart,
                    customer: this.selectedCustomer
                }
                this.saleOnProgress = true;
                axios.post(url, data).then(async res => {
                    let r = res.data;
                    if (r.success) {
                        let conf = confirm(`${r.message}, Do you want to view invoice?`);
                        if (conf) {
                            window.open('/sale_invoice_print/' + r.salesId, '_blank');
                            await new Promise(r => setTimeout(r, 1000));
                            window.location = '/pos';
                        } else {
                            window.location = '/pos';
                        }
                    } else {
                        alert(r.message);
                        this.saleOnProgress = false;
                        if (r.branch_status == false) {
                            location.reload();
                        }
                    }
                })
            },
            async getSales() {
                await axios.post('/get_sales', {
                    salesId: this.sales.salesId
                }).then(res => {
                    let r = res.data;
                    let sales = r.sales[0];
                    this.sales.salesBy = sales.AddBy;
                    this.sales.salesFrom = sales.branch_id;
                    this.sales.salesDate = sales.SaleMaster_SaleDate;
                    this.sales.salesType = sales.SaleMaster_SaleType;
                    this.sales.customerId = sales.SalseCustomer_IDNo;
                    this.sales.employeeId = sales.Employee_SlNo;
                    this.sales.subTotal = sales.SaleMaster_SubTotalAmount;
                    this.sales.discount = sales.SaleMaster_TotalDiscountAmount;
                    this.sales.vat = sales.SaleMaster_TaxAmount;
                    this.sales.transportCost = sales.SaleMaster_Freight;
                    this.sales.total = sales.SaleMaster_TotalSaleAmount;
                    this.sales.cashPaid = sales.cashPaid;
                    this.sales.bankPaid = sales.bankPaid;
                    this.sales.paid = sales.SaleMaster_PaidAmount;
                    this.sales.previousDue = sales.SaleMaster_Previous_Due;
                    this.sales.due = sales.SaleMaster_DueAmount;
                    this.sales.note = sales.SaleMaster_Description;

                    this.oldCustomerId = sales.SalseCustomer_IDNo;
                    this.oldPreviousDue = sales.SaleMaster_Previous_Due;
                    this.sales_due_on_update = sales.SaleMaster_DueAmount;

                    this.vatPercent = parseFloat(this.sales.vat) * 100 / parseFloat(this.sales.subTotal);
                    this.discountPercent = parseFloat(this.sales.discount) * 100 / parseFloat(this.sales.subTotal);

                    this.selectedEmployee = {
                        Employee_SlNo: sales.employee_id,
                        Employee_Name: sales.Employee_Name,
                        display_name: `${sales.Employee_Name} - ${sales.Employee_ID}`
                    }

                    if (this.sales.bankPaid > 0) {
                        this.selectedBank = {
                            account_id: sales.accountId,
                            display_name: `${sales.account_name} - ${sales.account_number} - ${sales.bank_name}`
                        }
                    }

                    this.selectedCustomer = {
                        Customer_SlNo: sales.SalseCustomer_IDNo == null ? '' : sales.SalseCustomer_IDNo,
                        Customer_Code: sales.Customer_Code,
                        Customer_Name: sales.Customer_Name,
                        display_name: sales.customerType == 'G' ? 'Cash Customer' : `${sales.Customer_Code} - ${sales.Customer_Name}`,
                        Customer_Mobile: sales.Customer_Mobile,
                        Customer_Address: sales.Customer_Address,
                        Customer_Type: sales.customerType
                    }

                    r.saleDetails.forEach(product => {
                        let cartProduct = {
                            productCode: product.Product_Code,
                            productId: product.Product_IDNo,
                            categoryName: product.ProductCategory_Name,
                            name: product.Product_Name,
                            salesRate: product.SaleDetails_Rate,
                            vat: product.SaleDetails_Tax,
                            is_service: product.is_service,
                            quantity: product.SaleDetails_TotalQuantity,
                            total: product.SaleDetails_TotalAmount,
                            purchaseRate: product.Purchase_Rate,
                        }

                        this.cart.push(cartProduct);
                    })
                })
            }
        }
    })
</script>