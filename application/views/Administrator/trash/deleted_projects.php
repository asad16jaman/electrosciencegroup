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
</style>
<div id="Project">
    <div class="row">
        <div class="col-md-12" style="margin: 0;">
            <fieldset class="scheduler-border scheduler-search">
                <legend class="scheduler-border">Deleeted Project List</legend>
                <div class="control-group">
                    <form class="form-inline" @submit.prevent="getProjects">
                        <div class="form-group">
                            <label>Search Type</label>
                            <select class="form-select" style="height: 26px;padding:0 6px;width:150px;" v-model="searchType">
                                <option value="">All</option>
                                <option value="division">By Division</option>
                            </select>
                        </div>

                        <div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'division' ? '' : 'none'}">
                            <label>Division</label>
                            <v-select v-bind:options="divisions" v-model="selectedDivision" label="name"></v-select>
                        </div>

                        <div class="form-group" style="margin-top: -1px;">
                            <input type="submit" value="Search">
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="row" style="display:none;" v-bind:style="{display: projects.length > 0 ? '' : 'none'}">
        <div class="col-md-12 text-right">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>
        <div class="col-md-12">
            <div class="table-responsive" id="reportContent">
                <table class="table table-bordered table-hover" id="projectTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" @click="allCheck">
                            </th>
                            <th>Sl</th>
                            <th>Project Code</th>
                            <th>Project Name</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Thana</th>
                            <th>Added By</th>
                            <th>Deleted By</th>
                            <th>Deleted Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(project, sl) in projects">
                            <td>
                                <input type="checkbox" v-model="project.checkStatus">
                            </td>
                            <td>{{ sl + 1 }}</td>
                            <td>{{ project.code }}</td>
                            <td>{{ project.name }}</td>
                            <td>{{ project.division_name }}</td>
                            <td>{{ project.district_name }}</td>
                            <td>{{ project.thana_name }}</td>
                            <td>{{ project.added_by }}</td>
                            <td>{{ project.deleted_by }}</td>
                            <td>{{ project.DeletedTime | dateFormat('DD-MM-YYYY, h:mm:ss a') }}</td>
                        </tr>
                        <tr v-if="projects.filter(item => item.checkStatus == true).length > 0">
                            <td colspan="10" style="text-align: right;">
                                <button type="button" @click="storeProject" style="margin: 0;" class="btn btn-success btn-sm">Store Project</button>
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
        el: '#Project',
        data() {
            return {
                searchType: '',
                projects: [],
                selectedProject: null,
                divisions: [],
                selectedDivision: null
            }
        },
        filters: {
            dateFormat(dt, format) {
                return moment(dt).format(format);
            },
        },
        created() {
            this.getDivisions();
        },
        methods: {
            getDivisions() {
				axios.get('/get_divisions').then(res => {
					this.divisions = res.data;
				})
			},
            getProjects() {
                let divisionId = '';
                if (this.searchType == 'division' && this.selectedDivision != null) {
                    divisionId = this.selectedDivision.id;
                }

                let data = {
                    divisionId: divisionId,
                    status: 'd'
                }
                axios.post('/get_projects', data).then(res => {
                    this.projects = res.data.map(item => {
                        item.checkStatus = false;
                        return item;
                    });
                })
            },
            allCheck() {
                if (event.target.checked) {
                    this.projects = this.projects.map(item => {
                        item.checkStatus = true;
                        return item;
                    })
                } else {
                    this.projects = this.projects.map(item => {
                        item.checkStatus = false;
                        return item;
                    })
                }
            },
            storeProject() {
                let projects = this.projects.filter(item => item.checkStatus == true);
                if (projects.length == 0) {
                    Swal.fire({
                        icon: "error",
                        text: "Select project",
                    });
                    return;
                }
                axios.post('/restore_project', {
                        projects: projects
                    })
                    .then(res => {
                        alert(res.data);
                        this.getProjects();
                    })
            },
            async print() {
                let reportContent = `
					<div class="container">
                        <div class="row">
                            <div class="col-xs-12">
                                <h3 style="text-align:center">Deleted Projects List</h3>
                            </div>
                        </div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportContent').innerHTML}
							</div>
						</div>
					</div>
				`;

                var reportWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}, left=0, top=0`);
                reportWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
				`);

                reportWindow.document.body.innerHTML += reportContent;

                reportWindow.focus();
                await new Promise(resolve => setTimeout(resolve, 1000));
                reportWindow.print();
                reportWindow.close();
            }
        }
    })
</script>