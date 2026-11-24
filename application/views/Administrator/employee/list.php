<div class="row">
    <div class="col-xs-12">
        <div class="table-responsive">
            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>

                        <th>Photo</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th class="hidden-480">Designation</th>
                        <th>Contact No</th>
                        <th>status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (isset($employes) && $employes) {
                        foreach ($employes as $row) {
                    ?>
                            <tr>
                                <td>
                                    <?php if ($row->Employee_Pic_thum) { ?>
                                        <a href="<?php echo base_url() . $row->Employee_Pic_thum; ?>">
                                            <img src="<?php echo base_url() . $row->Employee_Pic_thum; ?>" alt="" style="width:35px;height:35px;border-radius:5px; padding:2px;">
                                        </a>
                                    <?php } else { ?>
                                        <img src="<?php echo base_url() . 'uploads/no_image.jpg' ?>" alt="" style="width:35px;height:35px;border-radius:5px; padding:2px;">
                                    <?php } ?>
                                </td>
                                <td><?php echo $row->Employee_ID; ?></td>
                                <td class="hidden-480"><?php echo $row->Employee_Name; ?></td>
                                <td><?php echo $row->Designation_Name; ?></td>

                                <td class="hidden-480"><?php echo $row->Employee_ContactNo; ?></td>
                                <td class="hidden-480"><?php if ($row->status == 'a') {
                                                            echo '<strong class="text-success">Active</strong>';
                                                        } else if ($row->status == 'p') {
                                                            echo '<strong class="text-danger">Deactive</strong>';
                                                        } ?></td>

                                <td>
                                    <div class="hidden-sm hidden-xs action-buttons">
                                        <!-- Details -->
                                        <a class="orange"
                                        href="<?php echo base_url(); ?>employeeDetails/<?php echo $row->Employee_SlNo; ?>"
                                        title="Employee Details">
                                            <i class="ace-icon fa fa-eye bigger-130"></i>
                                        </a>
                                        <!-- Documents -->
                                        <a class="green" href="javascript:void(0)" title="View Documents" onclick="viewEmployeeDocuments(<?php echo $row->Employee_SlNo; ?>)"><i class="ace-icon fa fa-file-image-o bigger-130"></i></a>
                                        
                                        <?php if (buttonAction('update')): ?>
                                            <a class="blue" href="<?php echo base_url(); ?>employee/<?php echo $row->Employee_SlNo; ?>">
                                                <i class="btnEdit ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (buttonAction('delete')): ?>
                                            <span onclick="deleted(<?php echo $row->Employee_SlNo; ?>)"><i class="btnDelete fa fa-trash-o"></i></span>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <!-- document view modal -->
            <div class="modal fade" id="employeeDocumentsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button"
                                    class="close"
                                    data-dismiss="modal">
                                &times;
                            </button>

                            <h4 class="modal-title">
                                Employee Documents
                            </h4>
                        </div>

                        <div class="modal-body">

                            <div id="documentLoading"
                                style="text-align:center; padding:50px; display:none;">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <br>
                                Loading...
                            </div>

                            <div id="noDocuments"
                                style="text-align:center; padding:50px; display:none;">
                                <i class="fa fa-file-o fa-3x"></i>
                                <br><br>
                                No documents found.
                            </div>

                            <div id="employeeDocumentCarousel"
                                class="carousel slide"
                                data-ride="carousel"
                                data-interval="false"
                                style="display:none;">

                                <div class="carousel-inner"
                                    id="employeeDocumentContainer">
                                </div>

                                <!-- Previous -->
                                <a class="left carousel-control"
                                href="#employeeDocumentCarousel"
                                data-slide="prev"
                                style="background:none;">

                                    <span class="glyphicon glyphicon-chevron-left"
                                        style="color:#000;"></span>

                                </a>

                                <!-- Next -->
                                <a class="right carousel-control"
                                href="#employeeDocumentCarousel"
                                data-slide="next"
                                style="background:none;">

                                    <span class="glyphicon glyphicon-chevron-right"
                                        style="color:#000;"></span>

                                </a>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function deleted(id) {
        var deletedd = id;
        var inputdata = 'deleted=' + deletedd;
        if (confirm("Are you sure, You want to delete this?")) {
            var urldata = "<?php echo base_url(); ?>employeeDelete";
            $.ajax({
                type: "POST",
                url: urldata,
                data: inputdata,
                success: function(data) {
                    alert("Delete Success");
                    location.reload();
                }
            });
        }
    }

    function viewEmployeeDocuments(employeeId) {

        $('#employeeDocumentContainer').html('');
        $('#employeeDocumentCarousel').hide();
        $('#noDocuments').hide();

        $('#documentLoading').show();

        $('#employeeDocumentsModal').modal('show');

        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>get_employee_documents",
            data: JSON.stringify({
                employeeId: employeeId
            }),
            contentType: "application/json",
            dataType: "json",

            success: function(response) {

                $('#documentLoading').hide();

                if (!response.success || !response.documents || response.documents.length === 0) {

                    $('#noDocuments').show();
                    return;
                }

                var container = $('#employeeDocumentContainer');

                container.html('');

                $.each(response.documents, function(index, document) {

                    var activeClass = index === 0 ? 'active' : '';

                    var documentUrl =
                        "<?php echo base_url(); ?>" +
                        document.image;

                    var html = `
                        <div class="item ${activeClass}">

                            <div style="
                                min-height:500px;
                                display:flex;
                                flex-direction:column;
                                justify-content:center;
                                align-items:center;
                                padding:20px;
                            ">

                                <h4 style="margin-bottom:15px;">
                                    ${document.Employee_Name+ ' - ' + document.Employee_ID || 'Document'}
                                </h4>

                                <img src="${documentUrl}"
                                    style="
                                        max-width:90%;
                                        max-height:400px;
                                        object-fit:contain;
                                        border:1px solid #ddd;
                                        padding:5px;
                                        background:#fff;
                                    "
                                    onerror="this.style.display='none';">

                                <div style="margin-top:20px;">

                                    <a href="${documentUrl}"
                                    download
                                    class="btn btn-success">
                                        <i class="fa fa-download"></i>
                                        Download
                                    </a>

                                </div>

                            </div>

                        </div>
                    `;

                    container.append(html);
                });

                $('#employeeDocumentCarousel').show();

                $('#employeeDocumentCarousel').carousel(0);
            },

            error: function(xhr) {

                $('#documentLoading').hide();

                $('#noDocuments')
                    .html(
                        '<i class="fa fa-warning fa-3x text-danger"></i>' +
                        '<br><br>' +
                        'Failed to load documents.'
                    )
                    .show();
            }
        });
    }
</script>


<script type="text/javascript">
    function active(id) {
        var deletedd = id;
        var inputdata = 'deleted=' + deletedd;
        if (confirm("Are you sure, You want to active this?")) {
            var urldata = "<?php echo base_url(); ?>employeeActive";
            $.ajax({
                type: "POST",
                url: urldata,
                data: inputdata,
                success: function(data) {
                    alert("Delete Success");
                    location.reload();
                }
            });
        }
    }
</script>