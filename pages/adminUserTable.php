<main>
    <aside class="filters" id="filtersPanel">
        <div class="filters-inner">
                <h3>Filters</h3>

                <label>
                    Verified
                    <select id="verified">
                    <option value="">Any</option>
    				<option value="1">Verified</option>
					<option value="0">Not verified</option>
					</select>
                </label>

                <label>
                    Banned
                    <select id="banned">
                    <option value="">Any</option>
    				<option value="1">Banned</option>
					<option value="0">Unbanned</option>
					</select>
                </label>
        	</div>
            </aside>
     <div class="page">
	<div class="divblock">
		<div class="page-header">
            <div class="left">
                <h1>Users</h1>
            </div>

            <div class="right">
                <input id="search" placeholder="Search..." type="search">
                <select id="sort">
                	<option value="id_desc">Newest</option>
    				<option value="id_asc">Oldest</option>
            		<option value="avgRating_desc">Highest rating</option>
                </select>
                <button id="filterToggle">Filters</button>
            </div>
        </div>
    	<div class="tableContainer">
		<table id="myTable">
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
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {createUserSummaryElement} from '/pages/createUserSummaryElement.js';
function createUserRow(userSummary) {
    const tr = document.createElement("tr");
    tr.dataset.id = userSummary.id;

    // TD 1: user summary
    const td1 = document.createElement("td");
    td1.appendChild(createUserSummaryElement(userSummary));

    // TD 2: since
    const td2 = document.createElement("td");

    // Unverify button (only if verified)
    if (userSummary.since) {
        td2.textContent=userSummary.since;
    }
    
    // TD 3: actions
    const td3 = document.createElement("td");

    // Unverify button (only if verified)
    if (userSummary.verified) {
        const btnUnverify = document.createElement("button");
        btnUnverify.dataset.action = "removeVerification";
        btnUnverify.textContent = "Unverify";
        td3.appendChild(btnUnverify);
    }

    // Ban / Unban logic
    const btnBanToggle = document.createElement("button");

    if (!userSummary.banned) {
        btnBanToggle.dataset.action = "ban";
        btnBanToggle.textContent = "Ban";
    } else {
        btnBanToggle.dataset.action = "unban";
        btnBanToggle.textContent = "Unban";
    }

    td3.appendChild(btnBanToggle);

    // assemble row
    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);

    return tr;
}



const btn = document.getElementById("filterToggle");
const filtersElement = document.querySelector(".filters");

btn.addEventListener("click", () => {
    filtersElement.classList.toggle("open");
});    

document.getElementById("verified").addEventListener("change", (e) => {
    setFilter("verified", e.target.value);
    applyFilters();
});

document.getElementById("banned").addEventListener("change", (e) => {
    setFilter("banned", e.target.value);
    applyFilters();
});
  
const filters = {sort:"id",dir:"ASC",lastId:"0"};
applyFilters();

function applyFilters() {
    const parent = document.getElementById("rows");
    const mapToErrorFields={}
        request({url:"actions/getUsers.php?",data:filters,method:'GET',mapToErrorFields:mapToErrorFields, onSuccess(result){
            parent.innerHTML ="";
            result.data.forEach((user)=>{
                parent.append(createUserRow(user));
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

// Sort
document.getElementById("sort").addEventListener("change", (e) => {
    const [field, dir] = e.target.value.split("_");

    setFilter("orderBy", field);
    setFilter("dir", dir);

    applyFilters();
});

document.getElementById("search").addEventListener("input", (e) => {
    setFilter("search", e.target.value);
    applyFilters();
});

const table = document.getElementById('myTable');
    table.addEventListener("click", async function (e){
        const row = e.target.closest('tr');
  		if (!row) return;
        
        
        const id= row.dataset.id;
        
        const action = e.target.dataset.action;
        switch(action){
            case 'ban' :
                updateBanned(id,1);
                break;
            case 'unban' :
                updateBanned(id,0);
                break;
            case 'removeVerification':
                removeVerification(id);
                break;
        }
    });
    
	async function removeVerification(id){
        const fd = new FormData();
        fd.append("id",id);
        
        const mapToErrorFields={}
        const successMessage ="Verification remove";
        request({url:'actions/removeVerification.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage, onSuccess:()=>{
            location.reload()
        }})
    }
	
    async function updateBanned(id,banned){
        if(banned){
            if(!confirm("Do you want to ban this user?")){
            	return;
        	}
        }
        else{
            if(!confirm("Do you want to unban this user?")){
            	return;
        	}
        }
        const fd = new FormData();
        fd.append("userId",id);
        fd.append("isBanned",banned);
        
        const mapToErrorFields={}
        const successMessage ="Ban status updated";
        request({url:'actions/updateBanStatusUser.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,onSuccess:()=>{
            location.reload()
        }})
    }
</script>

    
    
    