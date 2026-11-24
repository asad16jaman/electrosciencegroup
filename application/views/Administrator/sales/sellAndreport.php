<div id="salesInvoice">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<pos-invoice v-if="company_profile.print_type == 3" v-bind:pos_id="salesId"></pos-invoice>
			<sales-invoice v-if="company_profile.print_type == 1 || company_profile.print_type == 2" v-bind:sales_id="salesId"></sales-invoice>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/components/salesInvoice.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/components/posInvoice.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/JsBarcode.all.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/qrcode.min.js"></script>
<script>
	new Vue({
		el: '#salesInvoice',
		components: {
			salesInvoice,
			posInvoice
		},
		data(){
			return {
				salesId: parseInt('<?php echo $salesId;?>'),
				company_profile: {},
			}
		},

		created() {
			this.getCompanyProfile();
		},

		methods: {
			getCompanyProfile() {
				axios.get('/get_company_profile').then(res => {
					this.company_profile = res.data;
				})
			},
		},
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

