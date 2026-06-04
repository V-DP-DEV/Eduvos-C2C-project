<?php
	$userId=$_SESSION['id'];
?>

<main>
    <div class="page">
		<div class="divblock">
            <div class="page-header">
            <div class="left">
                <h1>Seller oders</h1>
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
						<th>Buyer name</th>
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

    // User summary
    const tdUser = document.createElement("td");
    tdUser.appendChild(createUserSummaryElement(order.userSummary));

    // Price
    const tdPrice = document.createElement("td");
    tdPrice.textContent = "R" + order.price;

    // State
    const tdState = document.createElement("td");
    tdState.textContent = order.state;

    // Actions
    const tdActions = document.createElement("td");

    if (order.state === "received") {
        const btnComplete = document.createElement("button");
        btnComplete.dataset.action = "complete";
        btnComplete.textContent = "Complete";

        tdActions.appendChild(btnComplete);
    }
    else{
        tdActions.textContent="None"
    }

    // assemble row
    tr.appendChild(tdTitle);
    tr.appendChild(tdUser);
    tr.appendChild(tdPrice);
    tr.appendChild(tdState);
    tr.appendChild(tdActions);

    return tr;
}
    
    document.addEventListener("DOMContentLoaded", () => {
   	document.getElementById("state").addEventListener("change", (e) => {
    	setFilter("state", e.target.value);
        
	});    
	const fd= new FormData();
    fd.append("userId",<?= $userId ?>);
        
    const mapToErrorFields={}
    request({url:'actions/checkTransactions.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields})
    applyFilters();
});  
    
    
function applyFilters() {
    const parent = document.getElementById("rows");
    parent.innerHTML=""
        request({url:"actions/getSellerOrders.php?",data:filters,method:'GET',mapToErrorFields:{}, onSuccess(result){
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
        switch(action){
            case 'complete' :
                console.log("You clicked completed");
                updateCompleted(id);
                break;
        }
    });
    
    async function updateCompleted(id){
        if(!confirm("Do you want to complete this order?")){
            return;
        }
        const fd = new FormData();
        fd.append("orderId",id);
        
        const mapToErrorFields={}
        const successMessage ="Order being completed! Check transactions";
        const redirect = "https://myc2c.gamer.gd/index.php?page=transactions";
        request({url:'actions/completeOrder.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect})
    }
    
</script>