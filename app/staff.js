import * as Item from './item.js';
import * as View from './util/view.js';
import {Adapter} from './util/adapter.js';

const main_view=View.findById('main_view');
const main_pb=View.findById('main_pb');

//table
const ui_staff_container=View.findById('staff_container');
const ui_row_counter=View.findById('row_counter');
const ui_offset = View.findById('offset');
const btnFirstList=View.findById('btnFirstList');
const btnPrevList=View.findById('btnPrevList');
const btnNextList=View.findById('btnNextList');
const btnLastList=View.findById('btnLastList');

//filter
const ui_project_selector=View.findById('project_selector');
const ui_status_selector=View.findById('status_selector');



let staffAdapter,selected_project='all',selected_status='all',staffs;
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
    ajax.open("GET","api/pages/staffs.php?",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    console.log(data);
    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);


    staffs=data.staffs;

    staffAdapter =new Adapter(staffs,ui_staff_container,Item.staff,20);
        staffAdapter.firstPage((info)=>{
            View.setText(ui_row_counter,info);
    });


}

ui_offset.addEventListener("change",()=>{
    staffAdapter.setOffsetRange(parseInt(ui_offset.value),(info)=>{
       View.setText(ui_row_counter,info);
    });
});

btnFirstList.addEventListener("click",()=>{
    staffAdapter.firstPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});

btnNextList.addEventListener("click",()=>{
    staffAdapter.nextPage((info)=>{
          View.setText(ui_row_counter,info);
    });
});

btnPrevList.addEventListener("click",()=>{
    staffAdapter.prevPage((info)=>{
         View.setText(ui_row_counter,info);
    });
});

btnLastList.addEventListener("click",()=>{
    staffAdapter.lastPage((info)=>{
          View.setText(ui_row_counter,info);
    })
});

ui_project_selector.addEventListener("change",()=>{
    selected_project=ui_project_selector.value;
    filterStaffs(selected_project,selected_status);
});

ui_status_selector.addEventListener("change",()=>{
    selected_status=ui_status_selector.value;
    filterStaffs(selected_project,selected_status);
});

function filterStaffs(project,status){
    var filteredResults;
    if(project=='all'){
        filteredResults=staffs;
    }else{
        filteredResults=staffs.filter(checkProject);
    }

    if(status!='all'){
        filteredResults=filteredResults.filter(checkStatus);
    }

    staffAdapter =new Adapter(filteredResults,ui_staff_container,Item.staff,20);
        staffAdapter.firstPage((info)=>{
            View.setText(ui_row_counter,info);
    });

}

function checkProject(staff){
    return staff.project==ui_project_selector.value || staff.project=='all';
}

function checkStatus(staff){
    return staff.present==ui_status_selector.value ;
}