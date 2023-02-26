export function paymentItem(payment){
    return `
    <tr id="pay_${payment.id}">										
        <td>${payment.learner_name} </td>	
        <td>${payment.learner_phone}</td>	
        <td>${payment.amount}</td>
        <td>${payment.date}</td>

        <td class="text-center">
            <span
            onclick="deletePayment(
                ${payment.id},
                '${payment.learner_name}',
                '${payment.learner_phone}',
                ${payment.amount},
                '${payment.date}')" 
            
            title="Delete" class="gray-s"><i class="uil uil-trash-alt"></i></span>
        </td>
    </tr>
    `;
  
}

export function cost(cost){
    return `
        <tr id="cost_${cost.id}">										
            <td>${cost.title}</td>
            <td>${cost.amount}</td>	
            <td>${cost.date}</td>	

            <td class="text-center">
                <span 
                onclick="deleteCost(${cost.id},'${cost.title}',${cost.amount},'${cost.date}')" 
                title="Delete" class="gray-s"><i class="uil uil-trash-alt"></i></span>
            </td>
        </tr>
    `;
    
}

export function projectEarning(project){
    return `
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="earning.php?path=${project.project_name}&major=${project.keyword}">
                <div class="card_dash">
                    <div class="card_dash_left">
                        <h5>${project.project_name} </h5> 
                        <h2>${project.total_sale} MMK</h2>
                        <div class="last-earning"><h6>${project.last_sale} MMK</h6></div>
                    </div>
                    <div class="card_dash_right">
                        <img src="${project.icon}" alt="">
                    </div>
                </div>
            </a>
        </div>
    `;
}  

export function transaction(transaction){
    return `
        <tr id="transaction_${transaction.id}">										
            <td>${transaction.title} </td>	
            <td>${transaction.type==0? 'IN' :'OUT'}</td>	
            <td>${transaction.amount}</td>	
            <td>${transaction.current_balance}</td>
            <td>${transaction.date}</td>

            <td class="text-center">
                <span
                onclick="deleteTransaction(
                ${transaction.id},
                'transaction_${transaction.id}')" 
                
                title="Delete" class="gray-s"><i class="uil uil-trash-alt"></i></span>
            </td>
        </tr>
    `;
}

export function comfirmDialogue(msg,id){
    return `
    <div id="modal_comfirm${id}" class="modal">
            <!-- Modal content -->
        <div class="modal-content" style="width:50%;margin:auto;margin-top:70px;">
            
            <h4 style="margin: 30px;">${msg}</h4>
            <br><br>

            <div style="margin:30px; padding-left:10%;padding-right:10%;">
                <button class="btn btn-primary" id="modalCanel${id}" style="float:left">Cancel</button>
                <button class="btn btn-danger" id="modalComfirm${id}" style="float:right">Delete</button>
            </div>

        </div>

    </div>
    `;
}