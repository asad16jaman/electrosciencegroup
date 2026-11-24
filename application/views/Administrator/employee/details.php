<style>
    .employee-container {
        background: #fff;
        font-family: Arial, sans-serif;
        max-width: 1000px;
        font-size: 14px;
        margin: 20px auto;
    }

    .employee-header {
        text-align: center;
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .employee-header h2 {
        margin: 5px 0;
        font-weight: bold;
    }

    .employee-photo {
        width: 120px;
        height: 140px;
        object-fit: cover;
        border: 1px solid #ccc;
        padding: 3px;
    }

    .section-title {
        background: #f5f5f5;
        border-left: 4px solid #428bca;
        padding: 8px 12px;
        margin-top: 20px;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 16px;
    }

    .info-table {
        width: 100%;
    }

    .info-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    .info-label {
        width: 20%;
        font-weight: bold;
    }

    .info-value {
        width: 30%;
    }

    .document-box {
        display: inline-block;
        width: 180px;
        margin: 10px;
        vertical-align: top;
        text-align: center;
    }

    .document-box img {
        width: 170px;
        height: 130px;
        object-fit: contain;
        border: 1px solid #ddd;
        padding: 3px;
    }

    .document-name {
        margin-top: 5px;
        font-weight: bold;
    }

    .print-button {
        margin-bottom: 15px;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #reportContent,
        #reportContent * {
            visibility: visible !important;
        }

        #reportContent {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            max-width: 1000px !important;
            margin: 0 auto !important;
            background: #fff !important;
        }

        #reportContent .no-print {
            display: none !important;
        }

        .section-title {
            background: #f5f5f5 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .employee-header {
            border-bottom: 2px solid #333 !important;
        }

        .document-box {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .info-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .info-table td {
            padding: 7px 8px !important;
        }

        .employee-photo {
            width: 120px !important;
            height: 140px !important;
        }

        .document-box img {
            width: 170px !important;
            height: 130px !important;
        }

        .document-box a {
            text-decoration: none !important;
        }

        .document-box a::after {
            content: none !important;
        }

        .document-box a::before {
            content: none !important;
        }
    }
</style>
<div class="row">
    <div class="employee-container" id="reportContent">
        <!-- Buttons -->
        <div class="no-print print-button">
            <button type="button" class="btn btn-primary no-print" onclick="printEmployee()">
                <i class="fa fa-print"></i>
                Print
            </button>
    
            <button type="button" class="btn btn-default" onclick="window.history.back();">
                <i class="fa fa-arrow-left"></i>
                Back
            </button>
        </div>

        <!-- Header -->

        <div class="employee-header">
            <?php $photo = !empty($employee->Employee_Pic_org) ? base_url() . $employee->Employee_Pic_org :
            base_url() . 'uploads/no_image.jpg'; ?>

            <img src="<?php echo $photo; ?>" class="employee-photo" />

            <h2><?php echo htmlspecialchars($employee->Employee_Name); ?></h2>

            <div>
                Employee ID:
                <strong> <?php echo htmlspecialchars($employee->Employee_ID); ?> </strong>
            </div>
        </div>

        <!-- Basic Information -->

        <div class="section-title">Basic Information</div>

        <table class="info-table">
            <tr>
                <td class="info-label">Employee ID</td>
                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_ID); ?></td>
                <td class="info-label">Bio ID</td>
                <td class="info-value"><?php echo htmlspecialchars($employee->bio_id); ?></td>
            </tr>

            <tr>
                <td class="info-label">Employee Name</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_Name); ?></td>

                <td class="info-label">Gender</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_Gender); ?></td>
            </tr>

            <tr>
                <td class="info-label">Department</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Department_Name); ?></td>

                <td class="info-label">Designation</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Designation_Name); ?></td>
            </tr>

            <tr>
                <td class="info-label">Join Date</td>

                <td class="info-value">
                    <?php echo !empty($employee->Employee_JoinDate) ? date('d-m-Y',
                    strtotime($employee->Employee_JoinDate)) : ''; ?>
                </td>

                <td class="info-label">Birth Date</td>

                <td class="info-value">
                    <?php echo !empty($employee->Employee_BirthDate) ? date('d-m-Y',
                    strtotime($employee->Employee_BirthDate)) : ''; ?>
                </td>
            </tr>

            <tr>
                <td class="info-label">Gender</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_Gender); ?></td>

                <td class="info-label">Marital Status</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_MaritalStatus); ?></td>
            </tr>

            <tr>
                <td class="info-label">NID</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_NID); ?></td>

                <td class="info-label">Contact No</td>

                <td class="info-value"><?php echo htmlspecialchars($employee->Employee_ContactNo); ?></td>
            </tr>

            <tr>
                <td class="info-label">Email</td>

                <td colspan="3"><?php echo htmlspecialchars($employee->Employee_Email); ?></td>
            </tr>
        </table>

        <!-- Family Information -->

        <div class="section-title">Family Information</div>

        <table class="info-table">
            <tr>
                <td class="info-label">Father's Name</td>

                <td><?php echo htmlspecialchars($employee->Employee_FatherName); ?></td>

                <td class="info-label">Mother's Name</td>

                <td><?php echo htmlspecialchars($employee->Employee_MotherName); ?></td>
            </tr>
        </table>

        <!-- Address -->

        <div class="section-title">Address Information</div>

        <table class="info-table">
            <tr>
                <td class="info-label">Present Address</td>

                <td><?php echo nl2br(htmlspecialchars($employee->Employee_PrasentAddress)); ?></td>
            </tr>

            <tr>
                <td class="info-label">Permanent Address</td>

                <td><?php echo nl2br(htmlspecialchars($employee->Employee_PermanentAddress)); ?></td>
            </tr>
        </table>

        <!-- Employment Information -->

        <div class="section-title">Employment Information</div>

        <table class="info-table">
            <tr>
                <td class="info-label">Salary Range</td>

                <td><?php echo htmlspecialchars($employee->salary_range); ?></td>

                <td class="info-label">Reference</td>

                <td><?php echo htmlspecialchars($employee->Employee_Reference); ?></td>
            </tr>

            <tr>
                <td class="info-label">Status</td>

                <td colspan="3">
                    <?php if ($employee->status == 'a') { ?>

                    <strong style="color: green"> Active </strong>

                    <?php } elseif ($employee->status == 'p') { ?>

                    <strong style="color: red"> Deactive </strong>

                    <?php } ?>
                </td>
            </tr>
        </table>

        <!-- Documents -->

        <?php if (!empty($documents)) { ?>

        <div class="section-title">Employee Documents</div>

        <div style="text-align: center">
            <?php foreach ($documents as $document) { ?> <?php $documentUrl = base_url() . $document->image; ?>

            <div class="document-box">
                <a href="<?php echo $documentUrl; ?>" target="_blank">
                    <img src="<?php echo $documentUrl; ?>" alt="Document" />
                </a>

                <div class="document-name"><?php echo 'Document-' . $document->id; ?></div>
            </div>

            <?php } ?>
        </div>

        <?php } ?>
    </div>
</div>

<script>
    function printEmployee() {
        window.print();
    }
</script>