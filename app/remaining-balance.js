import * as Item from './item.js';
import * as View from './util/view.js';
import {Adapter} from './util/adapter.js';

var reqData="";

if(req.year){
    reqData+="&year="+req.year;
}


if(req.month){
    reqData+="&month="+req.month;
}

console.log('reqData',reqData);

const main_view=View.findById('main_view');
const main_pb=View.findById('main_pb');

const ui_kht_balance=View.findById('kht_balance');
const ui_mhk_balance=View.findById('mhk_balance');

const ui_transaction_contrainer_kht=View.findById('transaction_contrainer_kht');
const ui_transaction_contrainer_mhk=View.findById('transaction_contrainer_mhk');
const ui_row_counter_min=View.findById('row_counter_min');
const ui_row_counter_kaung=View.findById('row_counter_kaung');

// add transaction
const ui_btn_add_transaction=View.findById('btn_add_transaction');
const ui_input_transc_title=View.findById('input_transc_title');
const ui_input_transc_amount=View.findById('input_transc_amount');
const ui_input_transc_type=View.findById('input_transc_type');
const ui_input_staff_id=View.findById('input_staff_id');
const ui_msg_box_fail=View.findById('msg_box_fail');
const ui_msg_box_success=View.findById('msg_box_success');
const ui_fail_msg=View.findById('fail_msg');
const ui_success_msg=View.findById('success_msg');
const ui_pb_trans_adding=View.findById('pb_trans_adding');

// balance transfer
const ui_msg_box_success_tx=View.findById('msg_box_success_tx');
const ui_msg_box_fail_tx=View.findById('msg_box_fail_tx');
const ui_success_msg_tx=View.findById('success_msg_tx');
const ui_fail_msg_tx=View.findById('fail_msg_tx');
const ui_input_trans_from=View.findById('input_trans_from');
const ui_input_trans_to=View.findById('input_trans_to');
const ui_input_transfer_amount=View.findById('input_transfer_amount');
const ui_btn_balance_transfer=View.findById('btn_balance_transfer');
const ui_pb_balance_transfer=View.findById('pb_balance_transfer');

// date time selector
const ui_year_container=View.findById('year_container');
const ui_month_container=View.findById('month_container');

const ui_modal_container=View.findById('modal_container');

let kaungAdapter,minAdapter;


fetchThePage();

function fetchThePage(){


    var ajax=new XMLHttpRequest();
    console.log('req data',reqData);
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            loadUI(JSON.parse(ajax.responseText));
        }else{
            console.log('somethine wrong');
        }
    };
    ajax.open("GET","api/pages/remaining-balance.php?"+reqData,true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    console.log(data);
    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);
    setMonths();
    setYears();

    View.setText(ui_kht_balance,data.kaung_balance);
    View.setText(ui_mhk_balance,data.min_balance);

    var kaung_transactions=data.kaung_transactions
    var min_transactions=data.min_transactions;

    if(kaung_transactions){
        kaungAdapter =new Adapter(kaung_transactions,ui_transaction_contrainer_kht,Item.transaction,10);
        kaungAdapter.firstPage((info)=>{
            View.setText(ui_row_counter_kaung,info);
        });
    }

    if(min_transactions){
        minAdapter=new Adapter(min_transactions,ui_transaction_contrainer_mhk,Item.transaction,10);
        minAdapter.firstPage((info)=>{
             View.setText(ui_row_counter_min,info);
        })
    }

}

ui_btn_add_transaction.addEventListener("click",()=>{
    var title=ui_input_transc_title.value;
    var amount=ui_input_transc_amount.value;
    var type=ui_input_transc_type.value;
    var staff_id=ui_input_staff_id.value;

    View.setVisibility(ui_msg_box_fail,false);
    View.setVisibility(ui_msg_box_success,false);

    if(title==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please enter title");
        return;
    }
    if(amount==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please enter amount");
        return;
    }

    if(isNaN(amount)){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Amount must be number");
        return;
    }

    if(type==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please select the transaction type");
        return;
    }

    if(staff_id==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please select the owner");
        return;
    }

    View.setVisibility(ui_pb_trans_adding,true);
    var ajax=new XMLHttpRequest();
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            console.log(ajax.responseText);
            var response=JSON.parse(ajax.responseText);
            if(response.status=="success"){
                View.setVisibility(ui_msg_box_success,true);
                View.setText(ui_success_msg,response.msg);
                ui_input_transc_title.value="";
                ui_input_transc_amount.value="";
                fetchThePage()
            }else{
                View.setVisibility(ui_msg_box_fail,true);
                View.setText(ui_fail_msg,response.msg);
            }

        }else{
            View.setVisibility(ui_msg_box_fail,true)
            View.setText(ui_fail_msg,"An unexpected error occurred!");
        }
        View.setVisibility(ui_pb_trans_adding,false);
    };
    ajax.open("POST","api/funds/add.php",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send(`title=${title}&amount=${amount}&type=${type}&staff_id=${staff_id}`);

    
});


ui_btn_balance_transfer.addEventListener("click",()=>{
    var trans_from=ui_input_trans_from.value;
    var trans_to=ui_input_trans_to.value;
    var trans_amount=ui_input_transfer_amount.value;


    View.setVisibility(ui_msg_box_fail_tx,false);
    View.setVisibility(ui_msg_box_success_tx,false);

    if(trans_to==trans_from){
        View.setVisibility(ui_msg_box_fail_tx,true);
        View.setText(ui_fail_msg_tx,"Please check FROM and TO input");
        return;
    }

    if(trans_from==""){
        View.setVisibility(ui_msg_box_fail_tx,true);
        View.setText(ui_fail_msg_tx,"Please select FROM input");
        return;
    }

    if(trans_to==""){
        View.setVisibility(ui_msg_box_fail_tx,true);
        View.setText(ui_fail_msg_tx,"Please select TO input");
        return;
    }

    if(trans_amount==""){
        View.setVisibility(ui_msg_box_fail_tx,true);
        View.setText(ui_fail_msg_tx,"Please enter tranferring amount");
        return;
    }

    if(isNaN(trans_amount)){
        View.setVisibility(ui_msg_box_fail_tx,true);
        View.setText(ui_fail_msg_tx,"The transferring amount must be number");
        return;
    }

    View.setVisibility(ui_pb_balance_transfer,true);
    var ajax=new XMLHttpRequest();
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            console.log(ajax.responseText);
            var response=JSON.parse(ajax.responseText);
            if(response.status=="success"){

                View.setVisibility(ui_msg_box_success_tx,true);
                View.setText(ui_success_msg_tx,response.msg);
                ui_input_transfer_amount.value="";
                fetchThePage()
            }else{
                View.setVisibility(ui_msg_box_fail,true);
                View.setText(ui_fail_msg,response.msg);
            }

        }else{
            View.setVisibility(ui_msg_box_fail,true)
            View.setText(ui_fail_msg,"An unexpected error occurred!");
        }
        View.setVisibility(ui_pb_balance_transfer,false);
    };
    ajax.open("POST","api/funds/balance-transfer.php",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send(`to=${trans_to}&amount=${trans_amount}&from=${trans_from}`);


});

let transaction_id;
function deleteTransaction(id,view_id){

    ui_modal_container.innerHTML=Item.comfirmDialogue('Do you really want to delete this transaction',id);

    $('#modal_comfirm'+id).modal('show');
    $("#modalCanel"+id).click(function () {});
    transaction_id=id;
   
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
        ajax.open("POST","api/funds/delete.php",true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.send("id="+transaction_id);

    });

}


function setYears(){
    let currentYear= new Date().getFullYear();
    for(var i=currentYear;i>=2022;i--){
        ui_year_container.innerHTML+=`
            <div class="item" data-value="${i}">${i}</div>
        `;
    }
}

function setMonths(){
    let months =[
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec"
        ];

    for(var i=0;i<months.length;i++){
        ui_month_container.innerHTML+=`
              <div class="item" data-value="${(i+1)}">${months[i]}</div>
        `;
    }    
  
}

export{deleteTransaction};