import * as Item from './item.js';
import * as View from './util/view.js';
import {Adapter} from './util/adapter.js';

const main_view=View.findById('main_view');
const main_pb=View.findById('main_pb');

const ui_table_container=View.findById('table_container');
const ui_row_counter=View.findById('row_counter');
const ui_offset = View.findById('offset');
const btnFirstList=View.findById('btnFirstList');
const btnPrevList=View.findById('btnPrevList');
const btnNextList=View.findById('btnNextList');
const btnLastList=View.findById('btnLastList');

const ui_modal_container=View.findById('modal_container');

let myAdapter;

fetchThePage();

function fetchThePage(){

    var ajax=new XMLHttpRequest();
   
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            loadUI(JSON.parse(ajax.responseText));
        }else{
            console.log('somethine wrong');
        }
    };
    ajax.open("GET","api/pages/approve-payment.php?",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    console.log(data);
    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);

    var payments=data.payments;
    if(payments){
        myAdapter =new Adapter(payments,ui_table_container,Item.pendingPayment,20);
        myAdapter.firstPage((info)=>{
            View.setText(ui_row_counter,info);
        });
    }
}



ui_offset.addEventListener("change",()=>{
    myAdapter.setOffsetRange(parseInt(ui_offset.value),(info)=>{
       View.setText(ui_row_counter,info);
    });
});

btnFirstList.addEventListener("click",()=>{
    myAdapter.firstPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});

btnNextList.addEventListener("click",()=>{
    myAdapter.nextPage((info)=>{
          View.setText(ui_row_counter,info);
    });
});

btnPrevList.addEventListener("click",()=>{
    myAdapter.prevPage((info)=>{
         View.setText(ui_row_counter,info);
    });
});

btnLastList.addEventListener("click",()=>{
    myAdapter.lastPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});


let item_id;
function approvePayment(id,view_id,screenshot){
    
    ui_modal_container.innerHTML=Item.paymentApproveDialog(id,screenshot);

    $('#modal_comfirm'+id).modal('show');
    $("#modalCanel"+id).click(function () {});
    item_id=id;
   
    $("#modalComfirm"+id).click(function () {

      
        var view=document.getElementById(view_id);
        view.setAttribute('style','background:#f00;color:white;');

        var ajax=new XMLHttpRequest();
        ajax.onload =function(){
            if(ajax.status==200 || ajax.readyState==4){

                console.log(ajax.responseText);
                fetchThePage();
            }
        };
        ajax.open("POST","api/payments/approve.php",true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.send("id="+item_id);

    });

}

export{approvePayment}