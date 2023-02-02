<div id="myModal" class="modal">
		<!-- Modal content -->
    <div class="modal-content" style="width:50%;margin:auto;margin-top:70px;">
        
        <h4 style="margin: 30px;">Do you really want to delete this payment?</h4>

        <table class="table ucp-table earning__table" border="0.5">
            <thead class="thead-s">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Date</th>		
                                    
                </tr>
            </thead>
            <tbody>
                <tr>										
                    <td id="modal_name"></td>	
                    <td id="modal_phone"></td>	
                    <td id="modal_amount"></td>	
                    <td id="modal_date"></td>
                    
                </tr>
            </tbody>
        </table>

        <div style="margin:30px; padding-left:10%;padding-right:10%;">
            <button class="btn btn-primary" id="modalCanel" style="float:left">Cancel</button>
            <button class="btn btn-danger" id="modalDelete" style="float:right">Delete</button>
        </div>

    </div>

</div>