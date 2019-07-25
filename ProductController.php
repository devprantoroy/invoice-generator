<?php

namespace App\Http\Controllers;

use App\General;
use App\Product;
use App\ProductCategory;
use App\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct(){
        $gnl = General::first();
        date_default_timezone_set($gnl->timezone);
    }

    public function searchProduct(Request $request){
        $keyWord = $request->product_name;
        $p = Product::owner()->company()->whereHas('cat',function ($q) use ($keyWord){
            $q ->where('name', 'LIKE','%'.$keyWord.'%');
        })->orwhere('name', 'LIKE','%'.$keyWord.'%')->get();

        if (count($p) > 0){
            $auto = $p->map(function ($u){
               return [
                   'id' => $u->id,
                   'label' => $u->name,
                   'buy_price' => '',
                   'qty' => 1,
                   'amt' => '',
               ] ;
            });
            return $auto;
        }
        return response()->json(['success' => false]);

    }

    public function purchaseIndex(){
        $data['pt'] = "Purchase Product";
        $data['supplier'] = Supplier::owner()->company()->get();
        $data['product'] = Product::owner()->company()->get();
        $data['invoice_no'] = rand('111111111','999999999');
        return view('admin.supply.purchase',$data);
    }

    public function supplierIndex(){
        $data['pt'] = "Manage Supplier";
        $data['items'] = Supplier::owner()->company()->paginate(15);
        return view('admin.supply.supply',$data);
    }

    public function supplierStore(Request $request){
        $request->validate(['name' => 'required|max:191|unique:suppliers', 'details' => 'required|max:5000']);
        Supplier::create($request->all());
        return back()->with('message', 'Create Successfully');
    }

    public function supplierUpdate(Request $request,Supplier $supplier){
        $request->validate([
            'name' => 'required|max:191|unique:suppliers,name,'.$supplier->id,
            'details' => 'required|max:5000'
        ]);
        $supplier->update($request->all());

        return back()->with('message', 'Update Successfully');
    }

    public function supplierDel(Supplier $supplier){
        $supplier->delete();
        return back()->with('message', 'Delete Successfully');
    }

    public function index(){
        $data['pt'] = "Manage Product";
        $data['items'] = Product::owner()->company()->paginate(15);
        $data['cat_items'] = ProductCategory::owner()->company()->get();
        return view('admin.product.product',$data);
    }

    public function catStore(Request $request){
        $request->validate(['name' => 'required|max:191|unique:product_categories']);
        ProductCategory::create($request->all());
        return back()->with('message', 'Create Successfully');
    }

    public function catDel(ProductCategory $cat){
        $cat->delete();
        return back()->with('message', 'Delete Successfully');
    }

    public function catUpdate(Request $request,ProductCategory $cat){
        $request->validate(['name' => 'required|max:191|unique:product_categories,name,'.$cat->id]);
        $cat->update(['name' => $request->name]);
        return back()->with('message', 'Update Successfully');
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|max:191|unique:products',
            'cat_id' => 'required|integer',
            'selling_price' => 'required|min:0',
            'unit' => 'required',
        ]);
        Product::create($request->all());
        return back()->with('message', 'Create Successfully');
    }

    public function update(Request $request, Product $product){
        $request->validate([
            'name' => 'required|max:191|unique:products,name,'.$product->id,
            'cat_id' => 'required|integer',
            'selling_price' => 'required|min:0',
            'unit' => 'required',
        ]);
        $product->update([
            'name' => $request->name,
            'cat_id' => $request->cat_id,
            'selling_price' => $request->selling_price,
            'unit' => $request->unit,
        ]);
        return back()->with('message', 'Update Successfully');
    }

    public function destroy(Product $product){
        $product->delete();
        return back()->with('message', 'Delete Successfully');
    }
}
