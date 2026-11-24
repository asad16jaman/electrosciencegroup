<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css" />
<div id="qrcodeInvoice">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <sales-invoice v-bind:sales_id="salesId"></sales-invoice>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/components/qrcodeInvoice.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/JsBarcode.all.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/qrcode.min.js"></script>
<script>
    new Vue({
        el: '#qrcodeInvoice',
        components: {
            qrcodeInvoice
        },
        data() {
            return {
                salesId: parseInt('<?php echo $salesId; ?>')
            }
        }
    })

    function generateBarcode(data, format = "CODE128", displayValue = false) {
        const canvas = document.createElement("canvas");
        JsBarcode(canvas, data, {
            format: format,
            width: 1.5,
            height: 50,
            fontSize: 12,
            margin: 0,
            displayValue: displayValue
        });
        return canvas.toDataURL();
    }

    function generateQRCode(data) {
        const canvas = document.createElement("canvas");
        QRCode.toCanvas(canvas, data, {
            width: 80,
            height: 80
        });
        return canvas.toDataURL();
    }
</script>