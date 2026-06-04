<?php
	$userId=$_SESSION['id'];
?>

<main>
    <div class="page">
		<div class="divblock">
            <div class="page-header">
            <div class="left">
                <h1>Your oders</h1>
            </div>

            <div class="right">
                <select id="state" name="state">
                    <option value="">Any state</option>
                	<option value="cancelled">Cancelled</option>
            		<option value="paid">Paid</option>
                    <option value="received">Received</option>
            		<option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="tableContainer">
		<table id="myTable">
            <thead>
				<tr>
						<th>Product name</th>
						<th>Seller name</th>
						<th>Price</th>
						<th>status</th>
						<th>Action</th>
				</tr>
            </thead>
            <tbody id="rows">
            </tbody>
		</table>
        </div>
		</div>
    </div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {createUserSummaryElement} from '/pages/createUserSummaryElement.js';

    const filters = {};
	    
	function createOrderRow(order) {
    const tr = document.createElement("tr");
    tr.dataset.id = order.id;

    // Title
    const tdTitle = document.createElement("td");
    tdTitle.textContent = order.title;

    // Seller summary
    const tdSeller = document.createElement("td");
    tdSeller.appendChild(createUserSummaryElement(order.sellerSummary));

    // Price
    const tdPrice = document.createElement("td");
    tdPrice.textContent = "R" +order.price;

    // State
    const tdState = document.createElement("td");
    tdState.textContent = order.state;

    // Actions
    const tdActions = document.createElement("td");

    if (order.state === "paid") {
        const btnReceive = document.createElement("button");
        btnReceive.dataset.action = "receive";
        btnReceive.textContent = "Receive";

        const btnRefund = document.createElement("button");
        btnRefund.dataset.action = "refund";
        btnRefund.textContent = "Refund";

        tdActions.appendChild(btnReceive);
        tdActions.appendChild(btnRefund);

    } else if (order.state === "completed" && !order.hasReview) {
        const btnReview = document.createElement("button");
        btnReview.dataset.action = "review";
        btnReview.textContent = "Review";

        tdActions.appendChild(btnReview);
    }
    else{
        tdActions.textContent="None"
    }

    // assemble row
    tr.appendChild(tdTitle);
    tr.appendChild(tdSeller);
    tr.appendChild(tdPrice);
    tr.appendChild(tdState);
    tr.appendChild(tdActions);

    return tr;
}
    

    document.addEventListener("DOMContentLoaded", () => {
        const fd = new FormData();
        fd.append("userId",<?= $userId ?>);
        
        const mapToErrorFields={}
        request({url:'actions/checkTransactions.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields})
        
        
   	document.getElementById("state").addEventListener("change", (e) => {
    	setFilter("state", e.target.value);
        
	});    
	

        
    applyFilters();
});  
    
    
function applyFilters() {
    const parent =document.getElementById("rows");
	const mapToErrorFields={}
    parent.innerHTML ="";
        request({url:"actions/getMyOrders.php?",data:filters,method:'GET',mapToErrorFields:mapToErrorFields, onSuccess(result){
            
            result.data.forEach((order)=>{
                parent.append(createOrderRow(order));
            })
        }})  
}    
    
function setFilter(key, value) {
    if (value === null || value === "") {
        delete filters[key];
    } else {
        filters[key] = value;
    }
    applyFilters();
}
    
    
    
    const table = document.getElementById('myTable');
    table.addEventListener("click", async function (e){
        const row = e.target.closest('tr');
  		if (!row) return;
        
        
        const id= row.dataset.id;
        const action = e.target.dataset.action;
        console.log("You clicked received");
        switch(action){
            case 'receive' :
                console.log("You clicked received");
                updateRecieved(id);
                break;
            case 'refund':
                console.log("You clicked refund");
                updateCancelled(id);
                break;
            case 'review':
                window.location.href = 'index.php?page=leaveReview&orderId='+id;
                break;
        }
    });
    
    
    
    async function updateRecieved(id){
        if(!confirm("Do you want to mark this order as received?")){
            return;
        }
        const fd = new FormData();
        fd.append("orderId",id);
        
        const mapToErrorFields={}
        const successMessage = "Order marked as received!"
        request({url:'actions/receivedOrder.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,onSuccess:()=>{
                 location.reload()
                }})
    }
    
    async function updateCancelled(id){
        if(!confirm("Do you want to cancel this order?")){
            return;
        }
        
        const fd = new FormData();
        fd.append("orderId",id);
        
        const mapToErrorFields={}
        const successMessage="Order being cancelled! Check transactions"
        const redirect = "https://myc2c.gamer.gd/index.php?page=transactions";
        request({url:'actions/cancelOrder.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect})
    }
    
</script>