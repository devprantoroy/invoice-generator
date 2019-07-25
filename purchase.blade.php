@extends('admin.layout.master')
@section('style')
    <link rel="stylesheet" href="{{url('/')}}/assets/admin/css/jquery-ui.css">
@stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box dark">
                <div class="portlet-title">
                    <h4>
                       Purchase Product From Suppliers
                    </h4>
                </div>
                <div class="portlet-body" id="app">
                    <div class="row">
                        <div class="col-md-12">
                            <form method="POST" action="">
                                @csrf

                                    <div class="form-group row">
                                        <div class="col-md-4">
                                            <label>Select Supplier </label>
                                            <select class="form-control" data-live-search="true" name="supplier_id" required>
                                                @foreach($supplier as $data)
                                                    <option value="{{$data->id}}">{{$data->name}}</option>
                                                @endforeach
                                            </select>
                                            <a href="{{route('admin.supplier.index')}}">Add Supplier</a>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="col-md-12">Purchase Date</label>
                                            <div class="col-md-12">
                                                <div class="input-group date date-picker"  data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" class="form-control" required  name="purchase_date" autocomplete="off" >
                                                    <span class="input-group-btn">
                                                      <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                     </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Invoice No</label>
                                            <input type="number" name="invoice_no" value="{{$invoice_no}}"  class="form-control" required>
                                        </div>

                                        <div class="col-md-12 margin-top-10">
                                            <label>Detail /Note <small>(Optional)</small></label>
                                            <textarea name="details" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="row margin-top-40">

                                        <div class="form-group col-md-6 col-md-offset-3">
                                            <label>Search Product</label>
                                            <input type="text" name="search_product" autocomplete="off" id="search_product" @keyup="searchPro" v-model="searchProduct" class="form-control" required>
                                            <strong v-if="erroMsg" class="text-danger">No Product Found</strong>
                                        </div>

                                        <div v-if="appendVal.length > 0" class="col-md-12 margin-top-10">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover order-column">
                                                    <thead>
                                                        <tr>
                                                            <th width="20%">Product</th>
                                                            <th>Rate <small style="font-size: 10px">(Buying Price From Supplier)</small></th>
                                                            <th>Quantity</th>
                                                            <th>Total</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(data ,index) in appendVal">
                                                        <td>
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" :value="data.label" readonly>
                                                            </div>
                                                            <input type="hidden" name="product_id" :value="data.id">
                                                        </td>
                                                        <td>
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" v-model="data.buy_price" placeholder="Buying Price" required  name="buy_price[]">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" v-model="data.qty" placeholder="Quantity" required  name="qty[]">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" readonly v-if="numberValidation(data.buy_price) && numberValidation(data.qty)" v-model="data.amt = data.buy_price*data.qty"   placeholder="Amount" name="amt[]">
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <button type="button" @click="deleteEvent(index)" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="totalSum > 0">
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td colspan="2" class="text-center">
                                                           <h3> Grand Total : <strong>@{{ totalSum }} {{$general->currency}}</strong></h3>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>


                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary btn-block">Submit</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{url('/')}}/assets/admin/js/jquery-ui.js"></script>
    <script>
        var app = new Vue({
            el: "#app",
            data:{
                searchProduct: '',
                appendVal: [],
                erroMsg : false
            },
            computed:{
                totalSum(){
                   return this.appendVal.reduce(function(total, item){
                       if (item.qty !== '' && item.amt > 0 && item.qty > 0 ) {
                           return total + parseFloat(item.amt);
                       }
                       return 0;
                    },0);
                }
            },
            methods:{
                numberValidation(val){
                    if(!isNaN(val) &&  !isNaN(val) && Number(val)&& Number(val) && val > 0){return true}
                    return false;
                },
                deleteEvent(index){
                    this.$delete(this.appendVal, index);
                },
                searchPro(){
                    axios.post('{{route('search.product')}}', {product_name: this.searchProduct}).then(function (res) {
                        if (res.data.success !== false) {
                            app.erroMsg = false;
                            $( "#search_product" ).autocomplete({
                                source: res.data,
                                minLength: 1,
                                delay: 0,
                                select: function( event, ui ) {
                                    var length = app.appendVal.length;
                                    for(var i = 0; i < length; i++) {
                                        if(app.appendVal[i].id === ui.item.id){
                                            app.appendVal[i].qty = parseInt(app.appendVal[i].qty)+1;
                                            app.searchProduct = '';
                                            return 0;
                                        }
                                    }
                                    app.appendVal.push(ui.item);
                                    app.searchProduct = '';
                                }
                            });
                        }else {
                            app.erroMsg = true
                        }

                    })
                },
            }
        })
    </script>
@stop
