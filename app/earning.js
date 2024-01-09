import * as Item from './item.js';
import * as View from './util/view.js';
import {Adapter} from './util/adapter.js';

console.log('earringJS start');


var reqData="";
if(req.major){
    reqData+="&major="+req.major;
}

if(req.year){
    reqData+="&year="+req.year;
}


if(req.month){
    reqData+="&month="+req.month;
}

console.log('reqData',reqData);

const main_view=View.findById('main_view');
const main_pb=View.findById('main_pb');

const total_earning=View.findById('total_earning');
const total_cost=View.findById('total_cost');
const net_earning=View.findById('net_earning');
const payment_container=View.findById('payment_container');
const cost_container=View.findById('cost_container');

const total_payment=View.findById('total_payment');
const btnNextPaymentList=View.findById('btnNextPaymentList');
const btnPrevPaymentList=View.findById('btnPrevPaymentList');
const btnLastPaymentList=View.findById('btnLastPaymentList');
const btnFirstPaymentList=View.findById('btnFirstPaymentList');
const payment_row_counter=View.findById('payment_row_counter');
const payment_offset=View.findById('payment_offset');


const total_cost_list=View.findById('total_cost_list');
const btnNextCostList=View.findById('btnNextCostList');
const btnPrevCostList=View.findById('btnPrevCostList');
const btnLastCostList=View.findById('btnLastCostList');
const btnFirstCostList=View.findById('btnFirstCostList');
const cost_row_counter=View.findById('cost_row_counter');
const cost_offset=View.findById('cost_offset');
const ui_project_sale_of_month_container=View.findById('project_sale_of_month_container');
const ui_project_sale_of_year_container=View.findById('project_sale_of_year_container');

//overview
const ui_subscriber=View.findById('subscriber');
const ui_subscriber_totay=View.findById('subscriber_today');
const ui_total_sale_today=View.findById('total_sale_today');
const ui_subscriber_all_time=View.findById('subscriber_all_time');
const ui_total_Sale_all_time=View.findById('total_Sale_all_time');
const ui_total_sale_current_month=View.findById('total_sale_current_month');
const ui_subscriber_current_year=View.findById('subscriber_current_year');
const ui_total_sale_current_year=View.findById('total_sale_current_year');
const ui_previous_month_amount=View.findById('previous_month_amount');


//cost adding
const ui_msg_box_success=View.findById('msg_box_success');
const ui_msg_box_fail=View.findById('msg_box_fail');
const ui_pb_cost_adding=View.findById('pb_cost_adding');
const ui_input_cost_title=View.findById('input_cost_title');
const ui_input_cost_amount=View.findById('input_cost_amount');
const ui_btn_add_cost=View.findById('btn_add_cost');
const ui_fail_msg=View.findById('fail_msg');
const ui_success_msg=View.findById('success_msg');

// date selector
const ui_year_container=View.findById('year_container');
const ui_month_container=View.findById('month_container');
const ui_year_selector=View.findById('year_selector');
const ui_month_selector=View.findById('month_selector');



let paymentAdapter,costAdapter;

fetchThePage();
setYears();
setMonths();

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
    ajax.open("GET","api/pages/earning.php?"+reqData,true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    console.log(data);
    const earings=data.earnings;
    const projectCosts=data.projectCosts;

    let netEarning=earings.total-projectCosts.total_cost;

    View.setText(total_earning,earings.total);
    View.setText(total_cost,projectCosts.total_cost);
    View.setText(ui_total_sale_current_month,earings.total);
    

    View.setText(net_earning,netEarning);

    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);

    var payments=earings.payments;
    View.setText(ui_subscriber,payments.length);
    View.setText(total_payment,earings.total);

    if(payments){
        paymentAdapter=new Adapter(payments,payment_container,Item.paymentItem,20);
        paymentAdapter.firstPage((info)=>{
            View.setText(payment_row_counter,info);
        });
    }else{
        payment_container.innerHTML=`
            <tr>
                <td  colspan="5">
                     <div style="text-align:center; padding:15px; width=100%;"> No payment </div>
                </td>
            </tr>
               
        `;
    }

    View.setText(total_cost_list,projectCosts.total_cost);
    let costs=projectCosts.costs;
    if(costs){
        costAdapter=new Adapter(costs,cost_container,Item.cost,10);
            costAdapter.firstPage((info)=>{
            View.setText(cost_row_counter,info);
        });
    }else{
         cost_container.innerHTML=`
            <tr>
                <td  colspan="4">
                     <div style="text-align:center; padding:15px; width=100%;"> No Cost </div>
                </td>
            </tr>
               
        `;
    }

    var salesOfYear=data.saleOfYear;
    if(salesOfYear) setSaleOfYearChar(salesOfYear);

    var saleOfMonth=data.saleOfMonth;
    var saleOfLastMonth=data.saleOfLastMonth;
    if(saleOfMonth){
        console.log('saleOfMonths ',saleOfMonth);
        setSaleOfMonthChart(saleOfMonth,saleOfLastMonth);
    }
   

    var saleOfDay=data.saleOfDay;
    if(saleOfDay.total_amount!=null){
        View.setText(ui_total_sale_today,saleOfDay.total_amount);
        View.setText(ui_subscriber_totay,saleOfDay.total_subscriber);
    }else{
        View.setText(ui_total_sale_today,0);
        View.setText(ui_subscriber_totay,0);
    }
   
    var saleOfAllTime=data.saleOfAllTime;
    if(saleOfAllTime.amount!=null){
        View.setText(ui_total_Sale_all_time,saleOfAllTime.amount);
        View.setText(ui_subscriber_all_time,saleOfAllTime.subscriber);
    }else{
        View.setText(ui_total_Sale_all_time,0);
        View.setText(ui_subscriber_all_time,0);
    }

    var totalSaleOfCurrentYear=data.totalSaleOfCurrentYear;
    if(totalSaleOfCurrentYear.amount!=null){
        View.setText(ui_total_sale_current_year,totalSaleOfCurrentYear.amount);
        View.setText(ui_subscriber_current_year,totalSaleOfCurrentYear.subscriber);
    }else{
        View.setText(ui_total_sale_current_year,0);
        View.setText(ui_subscriber_current_year,0);
    }

}

payment_offset.addEventListener("change",function(){
    console.log('input value',payment_offset.value);

    paymentAdapter.setOffsetRange(parseInt(payment_offset.value),(info)=>{
        View.setText(payment_row_counter,info);
    });
});

ui_month_selector.addEventListener("change",function(){
    req.month=ui_month_selector.value;
    reqData+="&month="+req.month;

    View.setVisibility(main_view,false);
    View.setVisibility(main_pb,true);

    fetchThePage();
});

ui_year_selector.addEventListener("change",function(){

    req.year=ui_year_selector.value;
    reqData+="&year="+req.year;
    View.setVisibility(main_view,false);
    View.setVisibility(main_pb,true);

    fetchThePage();
});

btnFirstPaymentList.addEventListener("click",function(){
    paymentAdapter.firstPage((info)=>{
        View.setText(payment_row_counter,info);
    })
})

btnLastPaymentList.addEventListener("click",function(){
    paymentAdapter.lastPage((info)=>{
        View.setText(payment_row_counter,info);
    })
});

btnPrevPaymentList.addEventListener("click",function(){
    paymentAdapter.prevPage((info)=>{
         View.setText(payment_row_counter,info);
    });
});

btnNextPaymentList.addEventListener("click", function(){
    paymentAdapter.nextPage((info)=>{
         View.setText(payment_row_counter,info);
    });
   
});


cost_offset.addEventListener("change",function(){
    console.log('input value',cost_offset.value);

    costAdapter.setOffsetRange(parseInt(cost_offset.value),(info)=>{
        View.setText(cost_row_counter,info);
    });
});

btnFirstCostList.addEventListener("click",function(){
    costAdapter.firstPage((info)=>{
        View.setText(cost_row_counter,info);
    })
})

btnLastCostList.addEventListener("click",function(){
    costAdapter.lastPage((info)=>{
        View.setText(cost_row_counter,info);
    })
});

btnPrevCostList.addEventListener("click",function(){
    costAdapter.prevPage((info)=>{
        View.setText(cost_row_counter,info);
    });
});

btnNextCostList.addEventListener("click", function(){
    costAdapter.nextPage((info)=>{
        View.setText(cost_row_counter,info);
    });
   
});


ui_btn_add_cost.addEventListener("click", function(){
    addNewCost();
});


function addNewCost(){
    var title=ui_input_cost_title.value;
    var amount=ui_input_cost_amount.value;

   
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

    View.setVisibility(ui_pb_cost_adding,true);
    
    var ajax=new XMLHttpRequest();
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            console.log(ajax.responseText);
            var response=JSON.parse(ajax.responseText);
            if(response.status=="success"){
                View.setVisibility(ui_msg_box_success,true);
                View.setText(ui_success_msg,response.msg);
                ui_input_cost_title.value="";
                ui_input_cost_amount.value="";
                fetchThePage()
            }else{
                View.setVisibility(ui_msg_box_fail,true);
                View.setText(ui_fail_msg,response.msg);
            }

        }else{
            View.setVisibility(ui_msg_box_fail,true)
            View.setText(ui_fail_msg,"An unexpected error occurred!");
        }
        View.setVisibility(ui_pb_cost_adding,false);
    };
    ajax.open("POST","api/costs/add.php",true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send(`title=${title}&amount=${amount}&major=${req.major}`);

}

let paymentListId;
function deletePayment(id,name,phone,currentAmount,date){
    $('#modal_name').text(name);
    $('#modal_phone').text(phone);
    $('#modal_amount').text(currentAmount);
    $('#modal_date').text(date);

    $('#myModal').modal('show');
    $("#modalCanel").click(function () {
        
    });
    paymentListId=id;
    $("#modalDelete").click(function () {
        var payment=document.getElementById("pay_"+paymentListId);

        payment.setAttribute('style','background:#f00;color:white;');

        var ajax=new XMLHttpRequest();
        ajax.onload =function(){
            if(ajax.status==200 || ajax.readyState==4){

                console.log(ajax.responseText);
                fetchThePage();
            }
        };
        ajax.open("POST","api/payments/delete.php",true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.send("id="+paymentListId);

    });
}

let costListId;
function deleteCost(id,title,amount,date){
            
    $('#modal_cost_title').text(title);
    $('#modal_cost_amount').text(amount);
    $('#modal_cost_date').text(date);

    $('#delete_cost_model').modal('show');
    $("#modalCanel_cost").click(function () {
        
    });
    costListId=id;

    $("#modalDelete_cost").click(function () {
     
       
        var cost=document.getElementById("cost_"+costListId);

        cost.setAttribute('style','background:#f00;color:white;');

        var ajax=new XMLHttpRequest();
        ajax.onload =function(){
            if(ajax.status==200 || ajax.readyState==4){
                console.log(ajax.responseText);
                fetchThePage();
            }
        };
        ajax.open("POST","api/costs/delete.php",true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.send("id="+costListId);

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

function daysInThisMonth() {
  var now = new Date();
  var currentMonth;
  if(req.month){
    currentMonth=req.month;
  }else {
    currentMonth=now.getMonth();
  }
  return new Date(now.getFullYear(), currentMonth+1, 0).getDate();
}

//sale of years
function setSaleOfYearChar(sales){

    var data=[];

    for(var i=0;i<12;i++){
        var month=i+1;
        var current_sale=sales.filter(sale=>sale.month==month);
        if(current_sale.length>0){
            data[i]=current_sale[0].amount;
        }else{
            data[i]=0;
        }
        
    }
    clearChart(ui_project_sale_of_year_container,'project_sale_of_year');
    var ctx = document.getElementById('project_sale_of_year');
   
 
    if (ctx !== null) {
        var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: "line",

        // The data for our dataset
        data: {
            labels: [
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
            ],
            datasets: [
            {
                label: "",
                backgroundColor: "transparent",
                borderColor: "rgb(237, 42, 38)",
                data,
                lineTension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: "rgba(255,255,255,1)",
                pointHoverBackgroundColor: "rgba(255,255,255,1)",
                pointBorderWidth: 2,
                pointHoverRadius: 8,
                pointHoverBorderWidth: 1
            }
            ]
        },

        // Configuration options go here
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
            display: false
            },
            layout: {
            padding: {
                right: 10
            }
            },
            scales: {
            xAxes: [
                {
                gridLines: {
                    display: false
                }
                }
            ],
            yAxes: [
                {
                gridLines: {
                    display: true,
                    color: "#efefef",
                    zeroLineColor: "#efefef",
                },
                ticks: {
                    callback: function(value) {
                    var ranges = [
                        { divider: 1e6, suffix: "M" },
                        { divider: 1e4, suffix: "k" }
                    ];
                    function formatNumber(n) {
                        for (var i = 0; i < ranges.length; i++) {
                        if (n >= ranges[i].divider) {
                            return (
                            (n / ranges[i].divider).toString() + ranges[i].suffix
                            );
                        }
                        }
                        return n;
                    }
                    return formatNumber(value);
                    }
                }
                }
            ]
            },
            tooltips: {
            callbacks: {
                title: function(tooltipItem, data) {
                return data["labels"][tooltipItem[0]["index"]];
                },
                label: function(tooltipItem, data) {
                return "MMK " + data["datasets"][0]["data"][tooltipItem["index"]];
                }
            },
            responsive: true,
            intersect: false,
            enabled: true,
            titleFontColor: "#333",
            bodyFontColor: "#686f7a",
            titleFontSize: 12,
            bodyFontSize: 14,
            backgroundColor: "rgba(256,256,256,0.95)",
            xPadding: 20,
            yPadding: 10,
            displayColors: false,
            borderColor: "rgba(220, 220, 220, 0.9)",
            borderWidth: 2,
            caretSize: 10,
            caretPadding: 15
            }
        }
        });
    }
}



function setSaleOfMonthChart(sales,lastSales){

    var data=[]; // current month;
    var data2=[];  // previous month
    var previous_month_amount=0;
    var now=new Date();

    for(var i=0;i<daysInThisMonth();i++){
        var day=i+1;
        var current_sale=sales.filter(sale=>sale.day==day);
        if(current_sale.length>0){
            data[i]=current_sale[0].amount;
        }else{
            data[i]=0;
        }

        if(lastSales){
            var last_sale=lastSales.filter(sale=>sale.day==day);
            if(last_sale.length>0){
                data2[i]=last_sale[0].amount;
                if(day<=now.getDate()) previous_month_amount+=parseInt(data2[i]);
            }else{
                data2[i]=0;
            }
        }else{
            data2[i]=0;
        }
        
    }

    View.setText(ui_previous_month_amount,previous_month_amount);

    var dayLabels=[];
    for(var i=0;i<daysInThisMonth();i++){
        dayLabels[i]=i+1;
    }
    
    clearChart(ui_project_sale_of_month_container,'project_sale_of_month');
    
    var dual = document.getElementById("project_sale_of_month");
  
    if (dual !== null) {
        var urChart = new Chart(dual, {
        type: "line",
        data: {
            labels: dayLabels,
            datasets: [
            {
                label: "Old",
                pointRadius: 4,
                pointBackgroundColor: "rgba(255,255,255,1)",
                pointBorderWidth: 2,
                fill: false,
                backgroundColor: "transparent",
                borderWidth: 2,
                borderColor: "#ed2a26",
                data: data
            },
            {
                label: "New",
                fill: false,
                pointRadius: 4,
                pointBackgroundColor: "rgba(255,255,255,1)",
                pointBorderWidth: 2,
                backgroundColor: "transparent",
                borderWidth: 2,
                borderColor: "rgba(255, 230, 0, 0.3)",
                data: data2
            }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
            display: false
            },
            layout: {
            padding: {
                right: 10
            }
            },
            scales: {
            xAxes: [
                {
                gridLines: {
                    display: false
                }
                }
            ],
            yAxes: [
                {
                gridLines: {
                    display: true,
                    color: "#efefef",
                    zeroLineColor: "#efefef",
                },
                ticks: {
                    callback: function(value) {
                    var ranges = [
                        { divider: 1e6, suffix: "M" },
                        { divider: 1e4, suffix: "k" }
                    ];
                    function formatNumber(n) {
                        for (var i = 0; i < ranges.length; i++) {
                        if (n >= ranges[i].divider) {
                            return (
                            (n / ranges[i].divider).toString() + ranges[i].suffix
                            );
                        }
                        }
                        return n;
                    }
                    return formatNumber(value);
                    }
                }
                }
            ]
            },
            tooltips: {
            callbacks: {
                title: function(tooltipItem, data) {
                return data["labels"][tooltipItem[0]["index"]];
                },
                label: function(tooltipItem, data) {
                return "MMK " + data["datasets"][0]["data"][tooltipItem["index"]];
                }
            },
            responsive: true,
            intersect: false,
            enabled: true,
            titleFontColor: "#333",
            bodyFontColor: "#686f7a",
            titleFontSize: 12,
            bodyFontSize: 14,
            backgroundColor: "rgba(256,256,256,0.95)",
            xPadding: 20,
            yPadding: 10,
            displayColors: false,
            borderColor: "rgba(220, 220, 220, 0.9)",
            borderWidth: 2,
            caretSize: 10,
            caretPadding: 15
            }
        }
        });
    }

}


function clearChart(container,chartId){
    container.innerHTML=`
        <canvas id="${chartId}" class="chartjs"></canvas>
    `;
}

export{deletePayment,deleteCost};