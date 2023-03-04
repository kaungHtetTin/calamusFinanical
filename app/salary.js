import * as Item from './item.js';
import * as View from './util/view.js';
import {Adapter} from './util/adapter.js';

const main_view=View.findById('main_view');
const main_pb=View.findById('main_pb');

const ui_staff_name=View.findById('staff_name');

//table
const ui_table_container=View.findById('table_container');
const ui_row_counter=View.findById('row_counter');
const ui_offset = View.findById('offset');
const btnFirstList=View.findById('btnFirstList');
const btnPrevList=View.findById('btnPrevList');
const btnNextList=View.findById('btnNextList');
const btnLastList=View.findById('btnLastList');

const ui_year_container=View.findById('year_container');
const ui_year_selector=View.findById('year_selector');

//form
const ui_form_project_selector=View.findById('form_project_selector');
const ui_form_container=View.findById('form_container');
const ui_input_amount=View.findById('input_amount');
const ui_input_project=View.findById('input_project');
const ui_pay_from=View.findById('pay_from');
const ui_msg_box_fail=View.findById('msg_box_fail');
const ui_msg_box_success=View.findById('msg_box_success');
const ui_fail_msg=View.findById('fail_msg');
const ui_success_msg=View.findById('success_msg');
const ui_pb_form_adding=View.findById('pb_form_adding');
const ui_btn_add=View.findById('btn_add');

//modal
const ui_modal_container=View.findById('modal_container');


var reqData="";
reqData+="staff_id="+req.staff_id;

if(req.year){
    reqData+="&year="+req.year;
}

let salaryAdapter,staff;
fetchThePage();

function fetchThePage(){

    var ajax=new XMLHttpRequest();
   
    console.log('staff_id',req.staff_id);
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            console.log(ajax.responseText);
            loadUI(JSON.parse(ajax.responseText));
        }else{
            console.log('somethine wrong');
        }
    };
    ajax.open("GET","api/pages/salary.php?"+reqData,true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    
    setYears();
    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);


    var salaries=data.salaries;

    if(salaries){
        salaryAdapter =new Adapter(salaries,ui_table_container,Item.salary,20);
        salaryAdapter.firstPage((info)=>{
            View.setText(ui_row_counter,info);
        });
    }else{
        ui_table_container.innerHTML=`
            <tr>
                <td  colspan="4">
                     <div style="text-align:center; padding:15px; width=100%;"> No Salary Payment </div>
                </td>
            </tr>
        `;
        View.setText(ui_row_counter,"0-0 of 0");
    }


    staff=data.staff;
    View.setText(ui_staff_name,staff.name);

    

    if(staff.project!='all'){
        View.setVisibility(ui_form_project_selector,false);
        ui_form_container.setAttribute('style','height:300px');
        req.project=staff.project;
    }


}



ui_offset.addEventListener("change",()=>{
    salaryAdapter.setOffsetRange(parseInt(ui_offset.value),(info)=>{
       View.setText(ui_row_counter,info);
    });
});

btnFirstList.addEventListener("click",()=>{
    salaryAdapter.firstPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});

btnNextList.addEventListener("click",()=>{
    salaryAdapter.nextPage((info)=>{
          View.setText(ui_row_counter,info);
    });
});

btnPrevList.addEventListener("click",()=>{
    salaryAdapter.prevPage((info)=>{
         View.setText(ui_row_counter,info);
    });
});

btnLastList.addEventListener("click",()=>{
    salaryAdapter.lastPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});

ui_year_selector.addEventListener("change",function(){

    req.year=ui_year_selector.value;
    reqData+="&year="+req.year;
    View.setVisibility(main_view,false);
    View.setVisibility(main_pb,true);

    fetchThePage();
});


function setYears(){
    
    let currentYear= new Date().getFullYear();
    for(var i=currentYear;i>=2022;i--){
        ui_year_container.innerHTML+=`
            <div class="item" data-value="${i}">${i}</div>
        `;
    }
}

ui_btn_add.addEventListener("click",()=>{
    paySalary();
});


function paySalary(){
    var staff_id=staff.id;
    var amount=ui_input_amount.value;
    var project="";
    if(staff.project=='all'){
        project=ui_input_project.value;
    }else{
        project=staff.project;
    }

    var pay_from=ui_pay_from.value;


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

    if(project==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please select the Project");
        return;
    }

    if(pay_from==""){
        View.setVisibility(ui_msg_box_fail,true);
        View.setText(ui_fail_msg,"Please select the Pay From");
        return;
    }

    View.setVisibility(ui_pb_form_adding,true);
    var ajax=new XMLHttpRequest();
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            console.log(ajax.responseText);
            var response=JSON.parse(ajax.responseText);
            if(response.status=="success"){
                View.setVisibility(ui_msg_box_success,true);
                View.setText(ui_success_msg,response.msg);
                ui_input_amount.value="";
                fetchThePage()
            }else{
                View.setVisibility(ui_msg_box_fail,true);
                View.setText(ui_fail_msg,response.msg);
            }

        }else{
            View.setVisibility(ui_msg_box_fail,true)
            View.setText(ui_fail_msg,"An unexpected error occurred!");
        }
        View.setVisibility(ui_pb_form_adding,false);
    };
    ajax.open("POST","api/salaries/add.php",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send(`staff_id=${staff_id}&amount=${amount}&project=${project}&pay_from=${pay_from}`);

}

let deleting_item_id;
function deleteSalary(id,view_id){

    ui_modal_container.innerHTML=Item.comfirmDialogue('Do you really want to delete this salary payment',id);

    $('#modal_comfirm'+id).modal('show');
    $("#modalCanel"+id).click(function () {});
    deleting_item_id=id;
   
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
        ajax.open("POST","api/salaries/delete.php",true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.send("id="+deleting_item_id);

    });

}


export{deleteSalary};
