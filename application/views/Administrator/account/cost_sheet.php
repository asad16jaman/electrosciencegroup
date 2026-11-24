<style>
    .v-select {
        float: right;
        min-width: 200px;
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

    #searchForm select {
        padding: 0;
        border-radius: 4px;
    }

    #searchForm .form-group {
        margin-right: 5px;
    }

    #searchForm * {
        font-size: 13px;
    }
</style>

<div id="costSheet">
    <div class="row" style="margin:0;">
        <fieldset class="scheduler-border scheduler-search">
            <legend class="scheduler-border">Search Expense & Income Sheet</legend>
            <div class="control-group">
                <div class="col-md-12">
                    <form class="form-inline" id="searchForm" @submit.prevent="getTransactions">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select class="form-select" style="margin: 0;width:130px;height:26px;" v-model="filter.transactionType">
                                <option value="">All</option>
                                <option value="received">Received</option>
                                <option value="paid">Payment</option>
                            </select>
                        </div>

                        <div class="form-group" style="display:none;" v-bind:style="{display: projects.length > 0 ? '' : 'none'}">
                            <label>Project</label>
                            <v-select v-bind:options="projects" v-model="selectedProject" label="name" @input="onChangeProject"></v-select>
                        </div>

                        <div class="form-group">
                            <label for="">From</label>
                            <input type="date" class="form-control" v-model="filter.dateFrom">
                        </div>

                        <div class="form-group">
                            <label for="">To</label>
                            <input type="date" class="form-control" v-model="filter.dateTo">
                        </div>

                        <div class="form-group">
                            <input type="submit" value="Search">
                        </div>
                    </form>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="row" v-if="transactions.length > 0" style="margin:0; display:none;" v-bind:style="{display: transactions.length > 0 ? '' : 'none'}">
        <div class="col-md-12 text-right">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>
        <div class="col-md-12">
            <div class="table-responsive" id="printContent">
                <table class="table table-bordered table-condensed table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2">Date</th>
                            <th :colspan="accounts.length">
                                Classification of Cost
                            </th>
                            <th rowspan="2">Total Cost</th>
                        </tr>

                        <tr>
                            <th v-for="account in accounts" :key="account.Acc_SlNo">
                                {{ account.Acc_Name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="date in transactionDates":key="date">
                            <td>
                                {{ date }}
                            </td>
                            <td
                                v-for="account in accounts"
                                :key="account.Acc_SlNo"
                                class="text-right"
                            >
                                {{ formatNumber(getAccountAmount(date, account.Acc_SlNo)) }}
                            </td>
                            <td class="text-right">
                                <strong>
                                    {{ formatNumber(getDateTotal(date)) }}
                                </strong>
                            </td>
                        </tr>

                        <tr style="font-weight:bold;background:#f5f5f5;">
                            <td class="text-center">
                                Grand Total
                            </td>
                            <td
                                v-for="account in accounts"
                                :key="'grand-' + account.Acc_SlNo"
                                class="text-right"
                            >
                                {{ formatNumber(getAccountTotal(account.Acc_SlNo)) }}
                            </td>
                            <td class="text-right">
                                {{ formatNumber(getGrandTotal()) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
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
        el: '#costSheet',
        data() {
            return {
                filter: {
                    transactionType: '',
                    projectId: null,
                    accountId: null,
                    dateFrom: moment().format('YYYY-MM-DD'),
                    dateTo: moment().format('YYYY-MM-DD')
                },
                accounts: [],
                selectedAccount: null,
                projects: [],
                selectedProject: null,
                transactions: [],
                transactionDates: []
            }
        },
        created() {
            this.getAccounts();
            this.getProjects();
            this.getTransactions();
        },
        methods: {
            getAccounts() {
                axios.get('/get_accounts').then(res => {
                    this.accounts = res.data;
                })
            },

            onChangeAccount() {
                if (this.selectedAccount == null || this.selectedAccount.Acc_SlNo == undefined) {
                    this.filter.accountId = null;
                    return;
                }
                this.filter.accountId = this.selectedAccount.Acc_SlNo;
            },

            getProjects() {
				axios.get('/get_projects')
                .then(res => {
					this.projects = res.data.map(pro => {
						pro.name = `${pro.code} - ${pro.name}`;
						return pro;
					});
				})
			},

            onChangeProject() {
                if (this.selectedProject == null || this.selectedProject.id == undefined) {
                    this.filter.projectId = null;
                    return;
                }
                this.filter.projectId = this.selectedProject.id;
            },

            getTransactions() {
                axios.post('/get_cost_sheet_data', this.filter)
                    .then(res => {
                        this.transactions = res.data;
                        this.makeDates();
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },

            makeDates() {
                this.transactionDates = [];
                if (
                    !this.filter.dateFrom ||
                    !this.filter.dateTo
                ) {
                    return;
                }

                let currentDate = moment(this.filter.dateFrom);
                let endDate = moment(this.filter.dateTo);
                while (currentDate.isSameOrBefore(endDate, 'day')) {

                    this.transactionDates.push(
                        currentDate.format('YYYY-MM-DD')
                    );
                    currentDate.add(1, 'day');
                }
            },

            getAccountAmount(date, accountId) {
                let total = 0;
                this.transactions.forEach(transaction => {
                    if (
                        transaction.Tr_date == date &&
                        transaction.Acc_SlID == accountId
                    ) {
                        total += parseFloat(transaction.amount) || 0;
                    }
                });
                return total;
            },

            getDateTotal(date) {
                let total = 0;
                this.accounts.forEach(account => {
                    total += this.getAccountAmount(
                        date,
                        account.Acc_SlNo
                    );
                });
                return total;
            },

            getAccountTotal(accountId) {
                let total = 0;
                this.transactionDates.forEach(date => {
                    total += this.getAccountAmount(
                        date,
                        accountId
                    );
                });

                return total;
            },

            getGrandTotal() {
                let total = 0;
                this.transactionDates.forEach(date => {
                    total += this.getDateTotal(date);
                });

                return total;
            },

            formatNumber(value) {
                return Number(value || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },
                    
            async print() {
                let dateText = "";
                if (this.filter.dateFrom != null && this.filter.dateTo != null) {
                    dateText = `Statement from <strong>${this.filter.dateFrom}</strong>  to <strong>${this.filter.dateTo}</strong>`;
                }
                let printContent = `
                    <div class="container">
                        <h4 style="text-align:center">Daily Cost Sheet</h4>
                        <div class="row">
                            <div class="col-xs-6 col-xs-offset-6 text-right">
                                ${dateText}
                            </div>
                        </div>
                    </div>
                    <div class="container">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#printContent').innerHTML}
							</div>
						</div>
                    </div>
                `;

                let printWindow = window.open('', '', `width=${screen.width}, height=${screen.height}`);
                printWindow.document.write(`
                    <?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
                `);

                printWindow.document.body.innerHTML += printContent;
                printWindow.focus();
                await new Promise(r => setTimeout(r, 1000));
                printWindow.print();
                printWindow.close();
            }
        }
    })
</script>