export function paymentItem(payment){
    return `
    <tr id="pay_${payment.id}">										
        <td>${payment.learner_name} </td>	
        <td>${payment.learner_phone}</td>	
        <td>${payment.amount}</td>
        <td>${payment.date}</td>
        <td class="text-center"> 
            <span onclick="delP()" id="edit-${payment.id}" title="Edit" class="gray-s"><i class="uil uil-edit-alt"></i></span>
        </td>

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
                <span id="edit-${cost.id}" title="Edit" class="gray-s"><i class="uil uil-edit-alt"></i></span>
            </td>

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
        </div>
    `;
}   