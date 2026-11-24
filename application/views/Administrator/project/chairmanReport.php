<style>
    #chairmanReport .report-tabs { margin-bottom: 12px; }
    #chairmanReport .report-tabs .btn { margin-right: 4px; }
    #chairmanReport .report-filter { float: right; width: 210px; }
    #chairmanReport .summary-cards { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    #chairmanReport .summary-card { border: 1px solid #b9cfe3; border-radius: 4px; min-width: 210px; padding: 15px 10px; background: #f5fbff; color: #2d5b87; }
    #chairmanReport .summary-card.purchase, #chairmanReport .summary-card.cash-out { background: #fff6f6; border-color: #e5b7b7; color: #a94442; }
    #chairmanReport .summary-card.cash-in, #chairmanReport .summary-card.profit { background: #f1fbf3; border-color: #b8d9bd; color: #397a45; }
    #chairmanReport .summary-card.loss { background: #fff6f6; border-color: #e5b7b7; color: #a94442; }
    #chairmanReport .summary-card .card-label { font-size: 12px; font-weight: bold; text-transform: uppercase; }
    #chairmanReport .summary-card .amount { float: right; font-size: 16px; font-weight: bold; }
    #chairmanReport .table > thead > tr > th,
    #chairmanReport .table > tfoot > tr > th { background: #281653; color: #fff; text-align: center; vertical-align: middle; }
    #chairmanReport .table > tbody > tr > td { vertical-align: middle; }
</style>

<div id="chairmanReport">
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12">
            <div class="summary-cards">
                <div class="summary-card">
                    <span class="card-label"><i class="fa fa-building"></i> Total Project</span>
                    <span class="amount">{{ total('project_count') }}</span>
                </div>
                <div class="summary-card cash-in">
                    <span class="card-label"><i class="fa fa-money"></i> Total Sale</span>
                    <span class="amount">{{ total('sale_amount') | decimal }}</span>
                </div>
                <div class="summary-card purchase">
                    <span class="card-label"><i class="fa fa-shopping-cart"></i> Total Purchase</span>
                    <span class="amount">{{ total('purchase_amount') | decimal }}</span>
                </div>
                <div class="summary-card cash-in">
                    <span class="card-label"><i class="fa fa-arrow-down"></i> Cash In</span>
                    <span class="amount">{{ total('cash_in') | decimal }}</span>
                </div>
                <div class="summary-card cash-out">
                    <span class="card-label"><i class="fa fa-arrow-up"></i> Cash Out</span>
                    <span class="amount">{{ total('cash_out') | decimal }}</span>
                </div>
                <div class="summary-card" :class="total('profit_loss') < 0 ? 'loss' : 'profit'">
                    <span class="card-label"><i class="fa fa-line-chart"></i> Profit / Loss</span>
                    <span class="amount">{{ total('profit_loss') | decimal }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="report-tabs">
                <input type="text" class="form-control input-sm report-filter" v-model="searchText"
                    :placeholder="'Filter ' + groupLabel + '...'">
                <button v-for="tab in tabs" type="button" class="btn btn-sm"
                    :class="groupBy == tab.value ? 'btn-primary' : 'btn-default'"
                    @click="changeGroup(tab.value)">
                    <i :class="tab.icon"></i> {{ tab.label }}
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="reportTable">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 50px">Sl</th>
                            <th>{{ groupLabel }} Name</th>
                            <th>Project</th>
                            <th>Sale Amount</th>
                            <th>Purchase Amount</th>
                            <th>Cash In</th>
                            <th>Cash Out</th>
                            <th>Profit / Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in filteredReport" :key="row.group_id">
                            <td class="text-center">{{ index + 1 }}</td>
                            <td>{{ row.group_name }}</td>
                            <td class="text-center">{{ row.project_count }}</td>
                            <td class="text-right">{{ row.sale_amount | decimal }}</td>
                            <td class="text-right">{{ row.purchase_amount | decimal }}</td>
                            <td class="text-right">{{ row.cash_in | decimal }}</td>
                            <td class="text-right">{{ row.cash_out | decimal }}</td>
                            <td class="text-right">{{ row.profit_loss | decimal }}</td>
                        </tr>
                        <tr v-if="filteredReport.length == 0">
                            <td colspan="8" class="text-center">No records found</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="filteredReport.length">
                        <tr style="font-weight: bold;">
                            <td colspan="2" class="text-right">Grand Total:</td>
                            <td>{{ total('project_count') }}</td>
                            <td class="text-right">{{ total('sale_amount') | decimal }}</td>
                            <td class="text-right">{{ total('purchase_amount') | decimal }}</td>
                            <td class="text-right">{{ total('cash_in') | decimal }}</td>
                            <td class="text-right">{{ total('cash_out') | decimal }}</td>
                            <td class="text-right">{{ total('profit_loss') | decimal }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script>
    new Vue({
        el: '#chairmanReport',
        data: {
            groupBy: 'division',
            report: [],
            searchText: '',
            tabs: [
                { value: 'division', label: 'Division Wise', icon: 'fa fa-map' },
                { value: 'district', label: 'District Wise', icon: 'fa fa-map-o' },
                { value: 'thana', label: 'Thana Wise', icon: 'fa fa-location-arrow' },
                { value: 'project', label: 'Project Wise', icon: 'fa fa-building' }
            ]
        },
        computed: {
            groupLabel() {
                let tab = this.tabs.find(tab => tab.value == this.groupBy);
                return tab ? tab.label.replace(' Wise', '') : 'Division';
            },
            filteredReport() {
                let keyword = this.searchText.trim().toLowerCase();
                if (!keyword) return this.report;
                return this.report.filter(row => String(row.group_name || '').toLowerCase().includes(keyword));
            }
        },
        created() {
            this.getReport();
        },
        methods: {
            changeGroup(groupBy) {
                this.groupBy = groupBy;
                this.searchText = '';
                this.getReport();
            },
            getReport() {
                axios.post('/get_chairman_report', { groupBy: this.groupBy })
                    .then(res => this.report = res.data)
                    .catch(() => this.report = []);
            },
            total(column) {
                return this.filteredReport.reduce((sum, row) => sum + parseFloat(row[column] || 0), 0);
            }
        },
        filters: {
            decimal(value) {
                return parseFloat(value || 0).toFixed(2);
            }
        }
    });
</script>
