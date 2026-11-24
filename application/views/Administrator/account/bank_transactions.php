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

    .button {
        width: 25px;
        height: 25px;
        border: none;
        color: white;
    }

    .active-button {
        background-color: rgb(252, 89, 89);
    }

    .transaction-deposit {
        background-color: #f0f4f0;
    }

    .transaction-withdraw {
        background-color: #fff4f4;
    }
</style>

<div id="bankTransactions">

    <div class="row" style="margin: 0;">
        <fieldset class="scheduler-border">
            <legend class="scheduler-border">Bank Transaction Form</legend>
            <div class="control-group">
                <div class="col-md-6 col-md-offset-1">
                    <form action="" class="form-horizontal" @submit.prevent="saveTransaction">
                        <div class="form-group">
                            <label for="" class="control-label col-md-4">Transaction Date</label>
                            <div class="col-md-8">
                                <input type="date" style="margin-bottom: 4px;" class="form-control" v-model="transaction.transaction_date" required @change="getTransactions">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4 control-label"> Project </label>
                            <div class="col-xs-8" style="margin-bottom:5px;">
                                <v-select v-bind:options="projects" style="margin: 0;" label="name" v-model="selectedProject"></v-select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="control-label col-md-4">Account</label>
                            <div class="col-md-8">
                                <v-select v-bind:options="filteredAccounts" v-model="selectedAccount" label="display_text" placeholder="Select account" @input="getBankBalance"></v-select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="control-label col-md-4">Transaction Type</label>
                            <div class="col-md-8">
                                <select class="form-control" v-model="transaction.transaction_type" required style="padding:0px;">
                                    <option value="">Select Type</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="withdraw">Withdraw</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="control-label col-md-4">Amount</label>
                            <div class="col-md-8">
                                <input type="number" min="0" step="any" class="form-control" v-model="transaction.amount" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="control-label col-md-4">Note</label>
                            <div class="col-md-8">
                                <textarea class="form-control" v-model="transaction.note"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label no-padding-right"> Image </label>
                            <div class="col-sm-8">
                                <input type="file" class="form-control" @change="onChangeDocument" ref="image" style="padding: 1px 12px !important;">
                                <span></span>
                                <img  :src="imageUrl" style="width:80px; height:70px; margin-top:5px;" v-if="imageUrl">
                            </div>
                        </div>

                        <?php if (buttonAction('entry') || buttonAction('update')): ?>
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4 text-right">
                                    <input type="button" @click="resetForm" class="btnReset" value="Reset">
                                    <input type="submit" value="Save Transaction" v-bind:disabled="onProgress ? true : false" class="btnSave">
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="col-md-2 col-md-offset-1 text-center" style="display:none;" v-bind:style="{display: selectedAccount == null || selectedAccount.account_id == undefined ? 'none' : ''}">
                    <div style="width: 100%;min-height: 150px;padding:15px 5px;background: #eeeeee;border: 1px solid #cdcdcd;margin-top: 15px;">
                        <i class="fa fa-dollar fa-2x"></i>
                        <h5>Current Balance</h5>
                        <h3 style="color: green;">{{ accountBalance }}</h3>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="row">
        <div class="col-md-12 form-inline">
            <div class="form-group">
                <label for="filter" class="sr-only">Filter</label>
                <input type="text" class="form-control" v-model="filter" placeholder="Filter">
            </div>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <datatable :columns="columns" :data="transactions" :filter-by="filter">
                    <template scope="{ row }">
                        <tr v-bind:class="[row.transaction_type == 'deposit' ? 'transaction-deposit' : 'transaction-withdraw']">
                            <td>{{ row.transaction_date }}</td>
                            <td>{{ row.project_name ?? 'N/A' }}</td>
                            <td>{{ row.account_name }}</td>
                            <td>{{ row.account_number }}</td>
                            <td>{{ row.bank_name }}</td>
                            <td>{{ row.transaction_type }}</td>
                            <td>{{ row.note }}</td>
                            <td>{{ row.amount }}</td>
                            <td>{{ row.AddBy }}</td>
                            <td>
                                <a v-if="row.image" :href="`/${row.image}`" :download="row.image" type="button" title="Download transaction image">
                                    <i class="btnEdit fa fa-download"></i>
                                </a>
                                <i v-if="row.image" class="btnEdit fa fa-eye" @click="viewTransactionImage(row)" title="View transaction image"></i>
                                <?php if (buttonAction('update')): ?>
                                    <i class="btnEdit fa fa-pencil" @click="editTransaction(row)"></i>
                                <?php endif; ?>
                                <?php if (buttonAction('delete')): ?>
                                    <i class="btnDelete fa fa-trash" @click="removeTransaction(row)"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </template>
                </datatable>
                <datatable-pager v-model="page" type="abbreviated" :per-page="per_page"></datatable-pager>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionImageModal" tabindex="-1" role="dialog" aria-labelledby="transactionImageModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="transactionImageModalLabel">Transaction Image</h4>
                </div>
                <div class="modal-body text-center">
                    <img v-if="selectedTransactionImage" :src="selectedTransactionImage" class="img-responsive center-block" style="max-height: 70vh;" alt="Transaction image">
                </div>
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
        el: '#bankTransactions',
        data() {
            return {
                transaction: {
                    transaction_id: 0,
                    project_id: '',
                    account_id: '',
                    transaction_date: moment().format('YYYY-MM-DD'),
                    transaction_type: '',
                    amount: '',
                    note: ''
                },
                imageFile: null,
				imageUrl: '',
                selectedTransactionImage: '',
                transactions: [],
                columns: [{
                        label: 'Transaction Date',
                        field: 'transaction_date',
                        align: 'center'
                    },
                    {
                        label: 'Project Name',
                        field: 'project_name',
                        align: 'center'
                    },
                    {
                        label: 'Account Name',
                        field: 'account_name',
                        align: 'center'
                    },
                    {
                        label: 'Account Number',
                        field: 'account_number',
                        align: 'center'
                    },
                    {
                        label: 'Bank Name',
                        field: 'bank_name',
                        align: 'center'
                    },
                    {
                        label: 'Transaction Type',
                        field: 'transaction_type',
                        align: 'center'
                    },
                    {
                        label: 'Note',
                        field: 'note',
                        align: 'center'
                    },
                    {
                        label: 'Amount',
                        field: 'amount',
                        align: 'center'
                    },
                    {
                        label: 'Saved By',
                        field: 'AddBy',
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
                accounts: [],
                selectedAccount: null,
                projects: [],
                selectedProject: null,
                accountBalance: null,
                onProgress: false
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
            this.getTransactions();
        },
        methods: {
            getProjects() {
				axios.get('/get_projects')
                .then(res => {
					this.projects = res.data.map(pro => {
						pro.name = `${pro.code} - ${pro.name}`;
						return pro;
					});
				})
			},

            getAccounts() {
                axios.get('/get_bank_accounts')
                    .then(res => {
                        this.accounts = res.data;
                    })
            },

            onChangeDocument() {
				if(event.target.files.length > 0){
					this.imageFile = event.target.files[0];
					this.imageUrl = URL.createObjectURL(this.imageFile);
				} else {
					this.imageFile = null;
					this.imageUrl = '';
				}
            },

            getTransactions() {
                let data = {
                    dateFrom: this.transaction.transaction_date,
                    dateTo: this.transaction.transaction_date
                }
                axios.post('/get_bank_transactions', data)
                    .then(res => {
                        this.transactions = res.data;
                    })
            },

            saveTransaction() {
                if (this.selectedAccount == null) {
                    alert('Select an Account');
                    return;
                }

                this.transaction.account_id = this.selectedAccount.account_id;
                this.transaction.project_id = this.selectedProject ? this.selectedProject.id : null;

                let fd = new FormData();
				fd.append('transaction', JSON.stringify(this.transaction));
                fd.append('transaction_image', this.imageFile);

                let url = '/add_bank_transaction';
                if (this.transaction.transaction_id != 0) {
                    url = '/update_bank_transaction';
                }

                this.onProgress = true;
                axios.post(url, fd)
                    .then(res => {
                        let r = res.data;
                        alert(r.message);
                        if (r.success) {
                            this.resetForm();
                            this.getTransactions();
                            this.onProgress = false;
                        }
                    })
                    .catch(error => {
                        if (error.response) {
                            alert(`${error.response.status}, ${error.response.statusText}`)
                        }
                    })
            },

            editTransaction(transaction) {
                let keys = Object.keys(this.transaction);
                keys.forEach(key => this.transaction[key] = transaction[key]);
                this.selectedAccount = {
                    account_id: transaction.account_id,
                    account_number: transaction.account_number,
                    bank_name: transaction.bank_name,
                    display_text: `${transaction.account_number} (${transaction.bank_name})`
                }

                if(transaction.project_id != null && transaction.project_id != '') {
                    this.selectedProject = this.projects.find(pro => pro.id == transaction.project_id);
                } else {
                    this.selectedProject = null;
                }

                this.imageUrl = transaction.image != '' && transaction.image != null ? `/${transaction.image}` : '/uploads/no_image.jpg';
            },

            viewTransactionImage(transaction) {
                this.selectedTransactionImage = `/${transaction.image}`;
                $('#transactionImageModal').modal('show');
            },

            removeTransaction(transaction) {
                let confirmation = confirm('Are you sure?');
                if (confirmation == false) {
                    return;
                }

                axios.post('/remove_bank_transaction', transaction)
                    .then(res => {
                        let r = res.data;
                        alert(r.message);
                        if (r.success) {
                            this.getTransactions();
                        }
                    })
                    .catch(error => {
                        if (error.response) {
                            alert(`${error.response.status}, ${error.response.statusText}`)
                        }
                    })
            },

            getBankBalance() {
                if (this.selectedAccount == null || this.selectedAccount.account_id == undefined) {
                    return;
                }

                axios.post('/get_bank_balance', {
                    accountId: this.selectedAccount.account_id
                }).then(res => {
                    this.accountBalance = res.data[0].balance;
                })
            },

            resetForm() {
                this.transaction = {
                    transaction_id: 0,
                    project_id: '',
                    account_id: '',
                    transaction_date: moment().format('YYYY-MM-DD'),
                    transaction_type: '',
                    amount: '',
                    note: ''
                }
                this.selectedAccount = null;
                this.selectedProject = null;
                this.imageUrl = '';
                this.imageFile = null;
            }
        }
    })
</script>
