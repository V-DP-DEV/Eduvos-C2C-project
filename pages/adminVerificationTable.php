<main>
    <div class="page">
	<div class="divblock">
		<div class="page-header">
            	<div class="left">
                	<h1>Verifications</h1>
            	</div>

            	<div class="right">
    				<select id="sort" name="sort">
                    	<option value="since_desc">Newest</option>
                		<option value="since_asc">Oldest</option>
                        <option value="avgReviews_asc">Highest reviews</option>
                		<option value="avgReviews_desc">Lowest reviews</option>
                	</select>
            	</div>
        	</div>
        <div class="tableContainer">
		<table>
            <thead>
                
			<tr>
				<th>Username</th>
				<th>Since</th>
				<th>Action</th>
			</tr>
            </thead>
            <tbody id="rows">
    		</tbody>
		</table>
        </div>
	</div>
    <div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {createUserSummaryElement} from '/pages/createUserSummaryElement.js';

    const filters = {};
    
    function createVerificationRow(verification) {
    const tr = document.createElement("tr");

    // User summary cell
    const tdUser = document.createElement("td");
    tdUser.appendChild(createUserSummaryElement(verification.userSummary));

    // Since cell
    const tdSince = document.createElement("td");
    tdSince.textContent = verification.userSummary.since;

    // View link cell
    const tdView = document.createElement("td");

    const link = document.createElement("a");
    link.href = `index.php?page=adminVerificationRequest&id=${verification.id}`;
    link.textContent = "View";

    tdView.appendChild(link);

    // assemble row
    tr.appendChild(tdUser);
    tr.appendChild(tdSince);
    tr.appendChild(tdView);

    return tr;
}
    
    document.addEventListener("DOMContentLoaded", () => {
        
    document.getElementById("sort").addEventListener("change", (e) => {
    	const [field, dir] = e.target.value.split("_");

    	setFilter("orderBy", field);
   		setFilter("dir", dir);
        
	}); 

        
    applyFilters();
});  
    
    
function applyFilters() {
    const parent = document.getElementById("rows");
     const mapToErrorFields={}
        request({url:"actions/getVerifications.php?",data:filters,method:'GET',mapToErrorFields:mapToErrorFields, onSuccess(result){
            parent.innerHTML=""
            result.data.forEach((request)=>{
                parent.append(createVerificationRow(request));
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
    
</script>