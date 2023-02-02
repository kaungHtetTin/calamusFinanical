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

const main_view=View.findById('main_view1');
const main_pb=View.findById('main_pb1');

const ui_current_total=View.findById('current_total');
const ui_last_month_total=View.findById('last_month_total');
const ui_project_container=View.findById('project_container');

let projectAdapter;


fetchThePage();
function fetchThePage(){
    console.log('load the dashboard page');
    var ajax=new XMLHttpRequest();
    ajax.onload =function(){
        if(ajax.status==200 || ajax.readyState==4){
            loadUI(JSON.parse(ajax.responseText));
        }else{
            console.log('somethine wrong');
        }
    };
    ajax.open("GET","api/pages/dashboard.php?"+reqData,true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send();
}

function loadUI(data){
    console.log('response',data);
    View.setVisibility(main_view,true);
    View.setVisibility(main_pb,false);
    
    View.setText(ui_current_total,data.total_sale.current+" MMK");
    View.setText(ui_last_month_total,data.total_sale.last+" MMK");

    
    projectAdapter=new Adapter(data.projects,ui_project_container,Item.projectEarning,20);
    projectAdapter.firstPage((info)=>{
        console.log(info);
    });

    setSaleOfYearChar(data.saleOfYear);
}



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

    var ctx = document.getElementById('saleOfYear');
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