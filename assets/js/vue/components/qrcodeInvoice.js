const qrcodeInvoice = Vue.component("sales-invoice", {
  template: `
          <div>
              <div class="row">
                <div class="col-xs-12" style="margin: 0;display: flex;">
                    <div style="border-right: 2px solid gray;padding-left: 0;width:20%;display: flex;align-items: center;padding:6px;">
                        <img :src="'/' + currentBranch.Company_Logo_thum" style="height: 70px;" alt="Logo" />
                    </div>
                    <div style="padding-top:20px;width:65%;padding-left: 10px;">
                        <strong style="font-size:18px !important">{{currentBranch.Company_Name }}</strong><br>
                        <p style="white-space:pre-line;">{{currentBranch.Repot_Heading }}</p>
                    </div>
                    <div style="display: flex;align-items: center;justify-content: end;border-left: 2px solid gray;padding-right: 0;width:20%;">
                        <p style="text-align:center;margin:0;margin-top: 15px;padding-left: 8px;">
                            <img :src="sales.barcodeImg" /><br>
                            inv{{sales.SaleMaster_InvoiceNo}}
                        </p>                                                    
                    </div>
                </div>
            </div>
              
              <div id="invoiceContent">
                  <div class="row">
                      <div class="col-xs-12 text-center">
                          <div _h098asdh>
                              {{currentBranch.InvoiceHeder}}
                          </div>
                      </div>
                  </div>
                  <div class="row" style="margin:0;">
                      <div class="col-xs-12" style="border: 2px solid gray;padding:5px;margin:0 0 10px 0;border-radius: 5px;">
                          <div class="col-xs-7" style="padding-left:0;">
                              <table style="width:100%;">
                                  <tr>
                                      <td style="width:90px;"><strong>Customer ID</strong></td>
                                      <td style="font-weight:900;">:</td>
                                      <td style="padding-left:5px !important;">{{ sales.Customer_Code }}</td>
                                  </tr>
                                  <tr>
                                      <td style="width:90px;"><strong>Name</strong></td>
                                      <td style="font-weight:900;">:</td>
                                      <td style="padding-left:5px !important;">{{ sales.Customer_Name }}</td>
                                  </tr>
                                  <tr>
                                      <td style="width:90px;"><strong>Address</strong></td>
                                      <td style="font-weight:900;">:</td>
                                      <td style="padding-left:5px !important;">{{ sales.Customer_Address }}</td>
                                  </tr>
                                  <tr>
                                      <td style="width:90px;"><strong>Mobile</strong></td>
                                      <td style="font-weight:900;">:</td>
                                      <td style="padding-left:5px !important;">{{ sales.Customer_Mobile }}</td>
                                  </tr>
                              </table>
                          </div>
                          <div class="col-xs-5 text-right" style="padding-right:0;">
                              <strong>Sales by:</strong> {{ sales.added_by }}<br>
                              <strong>Invoice No.:</strong> {{ sales.SaleMaster_InvoiceNo }}<br>
                              <strong>Sales Date:</strong> {{ sales.SaleMaster_SaleDate | formatDateTime('DD-MM-YYYY') }} {{ sales.AddTime | formatDateTime('h:mm a') }}<br>
                              <strong v-if="sales.employee_id">Employee:</strong> {{ sales.Employee_Name }}
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-xs-12">
                          <table class="cartTable" _a584de>
                              <thead>
                                  <tr>
                                      <td style="text-align:center;width:4%;border-left:2px solid gray !important;">Sl.</td>
                                      <td style="width:50%;text-align:center;">Description</td>
                                      <td style="text-align:center;">Quantity</td>
                                      <td style="text-align:center;">Unit</td>
                                      <td style="text-align:center;">Unit Price</td>
                                      <td style="text-align:right;border-right:2px solid gray !important;">Total</td>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr v-for="(product, sl) in cart">
                                      <td style="border-left:2px solid gray !important;text-align:center;">{{ sl + 1 }}</td>
                                      <td style="text-align:left;">{{ product.Product_Name }} - {{ product.Product_Code }}</td>
                                      <td style="text-align:center;">{{ product.SaleDetails_TotalQuantity }}</td>
                                      <td style="text-align:center;">{{ product.Unit_Name }}</td>
                                      <td style="text-align:right;">{{ product.SaleDetails_Rate }}</td>
                                      <td align="right" style="border-right:2px solid gray !important;">{{ product.SaleDetails_TotalAmount }}</td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="border-left:2px solid gray !important;text-align:center;font-weight:700;">Total</td>
                                      <td style="text-align:center;font-weight:700;">{{ cart.reduce((prev, curr) => {return prev + parseFloat(curr.SaleDetails_TotalQuantity)}, 0) }}</td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray !important;font-weight:700;">{{ cart.reduce((prev, curr) => {return prev + parseFloat(curr.SaleDetails_TotalAmount)}, 0).toFixed(2) }}</td>
                                  </tr>
                                  <tr>
                                      <td colspan="6" style="padding:1px !important;border-bottom:none;border-left:none;border-right:none;background:none !important;"></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="border:none;background:none !important;" rowspan="7">
                                          <table style="width:75%;margin-bottom:10px !important;margin-top:5px;display:none;" class="pull-left" :style="{display: sales.bankPaid > 0 ? '' : 'none'}">
                                              <tr>
                                                  <td style="text-align:center;font-weight:700;background:none !important;">Sl.</td>
                                                  <td style="text-align:center;font-weight:700;background:none !important;">Bank</td>
                                                  <td style="text-align:right;font-weight:700;background:none !important;">Amount</td>
                                              </tr>
                                              <tr>
                                                  <td style="background:none !important;">1</td>
                                                  <td style="background:none !important;">{{sales.account_name}} - {{sales.account_number}} - {{sales.bank_name}}</td>
                                                  <td style="background:none !important;text-align:right;">{{sales.bankPaid}}</td>
                                              </tr>
                                          </table>
                                          <table style="width:75%;margin-bottom:15px !important;display:none;" class="pull-left" :style="{display: currentBranch.dueStatus == 'true' ? '' : 'none'}">
                                              <tr>
                                                  <td style="background:none !important;">Previous Due:</td>                                                
                                                  <td style="text-align:right;background:none !important;">{{ sales.SaleMaster_Previous_Due == null ? '0.00' : sales.SaleMaster_Previous_Due  }}</td>
                                              </tr>
                                              <tr>
                                                  <td style="background:none !important;">Current Due:</td>                                                
                                                  <td style="text-align:right;background:none !important;">{{ sales.SaleMaster_DueAmount == null ? '0.00' : sales.SaleMaster_DueAmount  }}</td>
                                              </tr>
                                              <tr>
                                                  <td style="background:none !important;"><strong>Total Due:</strong></td>                                                
                                                  <td style="text-align:right;font-weight:700;background:none !important;">{{ parseFloat(parseFloat(sales.SaleMaster_Previous_Due) + parseFloat(sales.SaleMaster_DueAmount == null ? 0.00 : sales.SaleMaster_DueAmount)).toFixed(2) }}</td>
                                              </tr>
                                          </table>
                                          <div class="row">
                                              <div class="col-xs-12">
                                                  <strong>In Word: </strong> {{ withDecimal(sales.SaleMaster_TotalSaleAmount) }}<br><br>
                                                  <p style="margin:0;display:none;" :style="{display: sales.SaleMaster_Description || currentBranch.InvoiceNote ? '' : 'none'}">
                                                      <strong>Note: </strong>
                                                      {{ sales.SaleMaster_Description ? sales.SaleMaster_Description : currentBranch.InvoiceNote }}
                                                  </p>
                                              </div>
                                          </div>
                                      </td>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;border-top:2px solid gray;background:none !important;"><strong>Sub Total:</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;border-top:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_SubTotalAmount).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;background:none !important;"><strong>Discount: (-)</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_TotalDiscountAmount).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;background:none !important;"><strong>Vat: (+)</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_TaxAmount).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;background:none !important;"><strong>Transport Cost: (+)</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_Freight).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;background:none !important;"><strong>Total:</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_TotalSaleAmount).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;background:none !important;"><strong>Paid:</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_PaidAmount).toFixed(2) }}</strong></td>
                                  </tr>
                                  <tr>
                                      <td colspan="2" style="text-align:right;border-left:2px solid gray;border-bottom:2px solid gray;background:none !important;"><strong>Due:</strong></td>
                                      <td colspan="3" style="text-align:right;border-right:2px solid gray;border-bottom:2px solid gray;background:none !important;"><strong>{{ parseFloat(sales.SaleMaster_DueAmount).toFixed(2) }}</strong></td>
                                  </tr>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
              <div class="container" :style="cart.length > 15 ? 'margin-top:50px;' : 'position:fixed;bottom:15px;width:100%;left: 0;'">
                <div class="row" style="border-bottom:1px solid #ccc;margin-bottom:5px;padding-bottom:6px;">
                    <table style="width:100%;">
                        <tr>
                            <td style="text-align:left;width:33.33%;">
                                <span style="text-decoration:overline;">Received by</span>
                            </td>
                            <td style="text-align:center;width:33.33%;">
                                <img :src="sales.qrcodeImg" />
                            </td>
                            <td style="text-align:right;width:33.33%;"><span style="text-decoration:overline;">Authorized Signature</span></td>
                        </tr>
                    </table>
                </div>

                <div class="row" style="font-size:12px;">
                    <div class="col-xs-6">
                        Print Date: {{toDay()}}, Printed by: {{ sales.added_by }}
                    </div>
                    <div class="col-xs-6 text-right">
                        Developed by: Link-Up Technologoy, Contact no: 01911978897
                    </div>
                </div>
            </div>
          </div>
      `,
  props: ["sales_id"],
  data() {
    return {
      sales: {
        SaleMaster_InvoiceNo: null,
        SalseCustomer_IDNo: null,
        SaleMaster_SaleDate: null,
        Customer_Name: null,
        Customer_Address: null,
        Customer_Mobile: null,
        SaleMaster_TotalSaleAmount: null,
        SaleMaster_TotalDiscountAmount: null,
        SaleMaster_TaxAmount: null,
        SaleMaster_Freight: null,
        SaleMaster_SubTotalAmount: null,
        SaleMaster_PaidAmount: null,
        SaleMaster_DueAmount: null,
        SaleMaster_Previous_Due: null,
        SaleMaster_Description: null,
        AddBy: null,
      },
      cart: [],
      style: null,
      companyProfile: {},
      currentBranch: {},
    };
  },
  filters: {
    formatDateTime(dt, format) {
      return dt == "" || dt == null ? "" : moment(dt).format(format);
    },
  },
  created() {
    this.setStyle();
    this.getSales();
  },
  methods: {
    async getSales() {
      await axios
        .post("/get_sales_qrcode", { salesId: this.sales_id })
        .then((res) => {
          this.sales = res.data.sales.map((item) => {
            item.barcodeImg = generateBarcode(item.SaleMaster_InvoiceNo);
            item.qrcodeImg = generateQRCode(
              location.origin + "/sales_invoice_print/" + item.SaleMaster_SlNo
            );
            return item;
          })[0];
          this.cart = res.data.saleDetails;
          this.getCurrentBranch(this.sales.branch_id);
        });
    },
    getCurrentBranch(branchId) {
      axios.get("/get_current_branch_qrcode/" + branchId).then((res) => {
        this.currentBranch = res.data;
      });
    },
    toDay(){
        return moment().format("DD-MM-YYYY h:mm a");
    },
    setStyle() {
      this.style = document.createElement("style");
      this.style.innerHTML = `
                  div[_h098asdh]{
                      /*background-color:#e0e0e0;*/
                      font-weight: bold;
                      font-size:15px;
                      margin-bottom:15px;
                      padding: 5px;
                      border-top: 1px dotted #454545;
                      border-bottom: 1px dotted #454545;
                  }
                  div[_d9283dsc]{
                      padding-bottom:25px;
                      border-bottom: 1px solid #ccc;
                      margin-bottom: 15px;
                  }
                  table[_a584de]{
                      border-collapse: separate;
                      border-spacing: 0;
                      width: 100%;
                      border-radius: 5px;
                      overflow: hidden;
                  }
                  table[_a584de] thead{
                      font-weight:bold;
                      background-color: #b8b8bd !important;                                   
                  }
                  table[_a584de] thead td{
                      padding: 5px 3px !important;
                      vertical-align: middle;
                      border-top: 2px solid gray;                                        
                  }
                  table[_a584de] td{
                      padding: 1px 2px;
                      border: 1px solid gray;
                      varticale-align: middle;
                      font-size: 12px;
                  }
                  table[_t92sadbc2]{
                      width: 100%;
                  }
                  table[_t92sadbc2] td{
                      padding: 1px 2px;
                  }
  
                  .cartTable tbody tr:nth-child(odd) td {
                      background-color: #ffffff;
                  }
                      
                  .cartTable tbody tr:nth-child(even) td {
                          background-color: #ebebebad;
                  }
              `;
      document.head.appendChild(this.style);
    },
    withDecimal(n) {
      n = n == undefined ? 0 : parseFloat(n).toFixed(2);
      var nums = n.toString().split(".");
      var whole = this.convertNumberToWords(nums[0]);
      if (nums.length == 2 && nums[1] > 0) {
        var fraction = this.convertNumberToWords(nums[1]);
        return (
          whole +
          this.currentBranch.Currency_Name +
          " " +
          fraction +
          this.currentBranch.SubCurrency_Name +
          " only"
        );
      } else {
        return whole + this.currentBranch.Currency_Name + " only";
      }
    },
    convertNumberToWords(amount) {
      var words = new Array();
      words[0] = "";
      words[1] = "One";
      words[2] = "Two";
      words[3] = "Three";
      words[4] = "Four";
      words[5] = "Five";
      words[6] = "Six";
      words[7] = "Seven";
      words[8] = "Eight";
      words[9] = "Nine";
      words[10] = "Ten";
      words[11] = "Eleven";
      words[12] = "Twelve";
      words[13] = "Thirteen";
      words[14] = "Fourteen";
      words[15] = "Fifteen";
      words[16] = "Sixteen";
      words[17] = "Seventeen";
      words[18] = "Eighteen";
      words[19] = "Nineteen";
      words[20] = "Twenty";
      words[30] = "Thirty";
      words[40] = "Forty";
      words[50] = "Fifty";
      words[60] = "Sixty";
      words[70] = "Seventy";
      words[80] = "Eighty";
      words[90] = "Ninety";
      amount = amount.toString();
      var atemp = amount.split(".");
      var number = atemp[0].split(",").join("");
      var n_length = number.length;
      var words_string = "";
      if (n_length <= 9) {
        var n_array = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0);
        var received_n_array = new Array();
        for (var i = 0; i < n_length; i++) {
          received_n_array[i] = number.substr(i, 1);
        }
        for (var i = 9 - n_length, j = 0; i < 9; i++, j++) {
          n_array[i] = received_n_array[j];
        }
        for (var i = 0, j = 1; i < 9; i++, j++) {
          if (i == 0 || i == 2 || i == 4 || i == 7) {
            if (n_array[i] == 1) {
              n_array[j] = 10 + parseInt(n_array[j]);
              n_array[i] = 0;
            }
          }
        }
        value = "";
        for (var i = 0; i < 9; i++) {
          if (i == 0 || i == 2 || i == 4 || i == 7) {
            value = n_array[i] * 10;
          } else {
            value = n_array[i];
          }
          if (value != 0) {
            words_string += words[value] + " ";
          }
          if (
            (i == 1 && value != 0) ||
            (i == 0 && value != 0 && n_array[i + 1] == 0)
          ) {
            words_string += "Crores ";
          }
          if (
            (i == 3 && value != 0) ||
            (i == 2 && value != 0 && n_array[i + 1] == 0)
          ) {
            words_string += "Lakhs ";
          }
          if (
            (i == 5 && value != 0) ||
            (i == 4 && value != 0 && n_array[i + 1] == 0)
          ) {
            words_string += "Thousand ";
          }
          if (
            i == 6 &&
            value != 0 &&
            n_array[i + 1] != 0 &&
            n_array[i + 2] != 0
          ) {
            words_string += "Hundred and ";
          } else if (i == 6 && value != 0) {
            words_string += "Hundred ";
          }
        }
        words_string = words_string.split("  ").join(" ");
      }
      return words_string;
    }
  },
});
