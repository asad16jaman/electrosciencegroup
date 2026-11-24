<div id="purchaseOrderInvoice">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<purchase-order-invoice v-bind:purchase_id="purchaseId"></purchase-order-invoice>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/components/purchaseOrderInvoice.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script>
	new Vue({
		el: '#purchaseOrderInvoice',
		components: {
			purchaseOrderInvoice
		},
		data(){
			return {
				purchaseId: parseInt('<?php echo $purchaseId;?>')
			}
		}
	})
</script>

