<?php
	$userId=$_SESSION['id'];
?>

<main>
    <div class="page">
		<div class="divblock">
            <div class="page-header">
            	<div class="left">
                	<h1>Transactions</h1>
            	</div>

            	<div class="right">
    				<select id="type" name="type">
                    	<option value="">Any type</option>
                		<option value="payment">Payment</option>
            			<option value="refund">Refund</option>
                		<option value="timeout_refund">Timeout refund</option>
                    	<option value="payout">Payout</option>
                	</select>
                	<select id="state" name="state">
                    	<option value="">Any state</option>
                		<option value="pending">Pending</option>
            			<option value="failed">Failed</option>
                    	<option value="success">Success</option>
                	</select>
    				<select id="sort" name="sort">
                    	<option value="id_desc">Newest</option>
                		<option value="id_asc">Oldest</option>
                	</select>
            	</div>
        	</div>
    	<div class="tableContainer">
		<table id="myTable">
            <thead>
				<tr>
                    	<th>Product</th>
                    	<th>Amount</th>
						<th>Type</th>
						<th>Status</th>
    					<th>Time</th>
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
    
    function createTransactionRow(transaction) {
    const tr = document.createElement("tr");
    tr.dataset.id = transaction.id;

    // Title
    const tdTitle = document.createElement("td");
    tdTitle.textContent = transaction.title;

    // Amount
    const tdAmount = document.createElement("td");
    tdAmount.textContent = "R" + transaction.amount;

    // Type
    const tdType = document.createElement("td");
    tdType.textContent = transaction.type;

    // Status
    const tdStatus = document.createElement("td");
    tdStatus.textContent = transaction.status;

    // Created At
    const tdCreatedAt = document.createElement("td");
    tdCreatedAt.textContent = transaction.createdAt;

    // Actions
    const tdActions = document.createElement("td");

    if (transaction.status === "pending") {
        const btn = document.createElement("button");
        btn.dataset.action = "refresh";
        btn.textContent = "Refresh";
        tdActions.appendChild(btn);
    }
    else{
        tdActions.textContent="None"
    }

    // append all
    tr.appendChild(tdTitle);
    tr.appendChild(tdAmount);
    tr.appendChild(tdType);
    tr.appendChild(tdStatus);
    tr.appendChild(tdCreatedAt);
    tr.appendChild(tdActions);

    return tr;
}
    
    document.addEventListener("DOMContentLoaded", () => {
        const fd = new FormData();
        fd.append("userId",<?= $userId ?>);
        
        const mapToErrorFields={}
        request({url:'actions/checkTransactions.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields})
        
        
   	document.getElementById("state").addEventListener("change", (e) => {
    	setFilter("status", e.target.value);
        
	});    
	document.getElementById("type").addEventListener("change", (e) => {
    	setFilter("type", e.target.value);
	}); 
    document.getElementById("sort").addEventListener("change", (e) => {
    	setFilter("sort", e.target.value);
        
	}); 

        
    applyFilters();
});  
    
    
function applyFilters() {
    const parent = document.getElementById("rows");
    const mapToErrorFields={}
    parent.innerHTML=""
        request({url:"actions/getTransactions.php?",data:filters,method:'GET',mapToErrorFields:mapToErrorFields, onSuccess(result){
            
            result.data.forEach((transaction)=>{
                parent.append(createTransactionRow(transaction));
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
            case 'refresh' :
                checkTransaction(id);
                break;
        }
    });
	async function checkTransaction(id){
        const fd = new FormData();
        fd.append("id",id);
        
        const mapToErrorFields={}
        request({url:'actions/checkTransactions.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,onSuccess:()=>{
                 location.reload()
                }})
    }
</script>