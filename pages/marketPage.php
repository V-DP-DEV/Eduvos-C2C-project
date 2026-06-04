<?php
require_once __DIR__ . '/../bootstrap.php';

try {

    $categories = Db::usingDbConnection(function () {
        return Category::all();
    });

    $cities = Db::usingDbConnection(function () {
        return City::all();
    });
    $userId=$_GET['userId']??"";
    $sessionId= $_SESSION['id']??"";
    $user=null;
    if($userId!==""){
        $user = Db::usingDbConnection(function () use ($userId) {
        return User::find($userId);
    });
        
    }

} catch (PDOException $e) {
    Db::handleException($e);
    echo "<h1>Something went wrong. Try again later!</h1>";
    exit;
} catch (Exception $e) {
    echo "<h1>Something went wrong. Try again later!</h1>";
    exit;
}
?>

<main>
    <aside class="filters" id="filtersPanel">
        <div class="filters-inner">
                <h3>Filters</h3>

                <label>
                    Category
                    <select id="categoryId" name="categoryId">
                    <option value="">Any</option>
                    <?php foreach($categories as $category): ?>
                    	<option value="<?=$category->getId()?>"><?=htmlspecialchars($category->getName())?></option>
                    <?php endforeach;?>
					</select>
                </label>

                <label>
                    Min Price
                    <input type="number" id="minPrice">
                </label>

                <label>
                    Max Price
                    <input type="number" id="maxPrice">
                </label>

                <label>
                    City
                    <select id="citySelect">
                    <option value="">Any</option>
                    <?php foreach($cities as $city): ?>
                    	<option value="<?=$city->getId()?>"><?=htmlspecialchars($city->getName())?></option>
                    <?php endforeach;?>
					</select>
                </label>

                <label>
                    Suburb
                    <select id="suburbId"></select>
                </label>
        	</div>
            </aside>
    <div class="page">
	<div class="divblock">
		<div class="page-header">
            <div class="left">
                <h1>
                	<?php if($userId==$sessionId && $userId!==""): ?>
                		My listing
                    <?php elseif($user): ?>
                    	<?= $user->getUsername() ?>
                	<?php else:?>
                    	Market
					<?php endif;?>
                </h1>
                <span id="resultCount"></span>
            </div>

            <div class="right">
                <input id="search" type="search" placeholder="Search...">
                <select id="sort">
                	<option value="id_desc">Newest</option>
            		<option value="price_asc">Price ↑</option>
            		<option value="price_desc">Price ↓</option>
                </select>
                <button id="filterToggle">Filters</button>
            </div>
        </div>
		<div class="gridContainer" id="productContainer">
		</div>
	</div>
    </div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {createUserSummaryElement} from '/pages/createUserSummaryElement.js';
 	
    let isUserListings = false;
    
function createProduct(product) {
    const div = document.createElement("div");
    div.className = "product";
    div.dataset.id = product.id;

    const img = document.createElement("img");
    img.src = `actions/image.php?id=${product.productImg.imageId}`;
    img.alt = product.title;

    const h3 = document.createElement("h3");
    h3.textContent = product.title;

    const price = document.createElement("p");
    price.textContent = "R" +product.price;
	
    
    div.appendChild(img);
    div.appendChild(h3);
    div.appendChild(price);
    
    if(!isUserListings){
    	const userSummary = createUserSummaryElement(product.userSummary);

    	const location = document.createElement("div");
    	location.textContent = `${product.suburb.city.name}, ${product.suburb.name}`;    
        
        div.appendChild(userSummary);
    	div.appendChild(location);
    }
	
    return div;
}    
    
    
const btn = document.getElementById("filterToggle");
const filtersElement = document.querySelector(".filters");

    
    
    
btn.addEventListener("click", () => {
    filtersElement.classList.toggle("open");
});    
    
document.addEventListener("click", function (e) {
    const product = e.target.closest(".product");

    if (product) {
        const id = product.dataset.id;
        window.location.href = "index.php?page=productPage&id=" + id;
    }
}); 
    
document.getElementById("categoryId").addEventListener("change", (e) => {
    setFilter("category", e.target.value);
    applyFilters();
});

// Min Price
document.getElementById("minPrice").addEventListener("input", (e) => {
    setFilter("minPrice", e.target.value);
    applyFilters();
});

// Max Price
document.getElementById("maxPrice").addEventListener("input", (e) => {
    setFilter("maxPrice", e.target.value);
    applyFilters();
});

// City
document.getElementById("citySelect").addEventListener("change", (e) => {
    setFilter("cityId", e.target.value);

    // Optional: clear suburb when city changes
    setFilter("suburbId", null);
    document.getElementById("suburbId").value = "";

    applyFilters();
});

// Suburb
document.getElementById("suburbId").addEventListener("change", (e) => {
    setFilter("suburbId", e.target.value);

    // Optional: clear city if suburb used
    setFilter("cityId", null);
    document.getElementById("citySelect").value = "";

    applyFilters();
});
    
// Search
document.getElementById("search").addEventListener("input", (e) => {
    setFilter("search", e.target.value);
    applyFilters();
});

// Sort
document.getElementById("sort").addEventListener("change", (e) => {
    const [field, dir] = e.target.value.split("_");

    setFilter("orderBy", field);
    setFilter("dir", dir);

    applyFilters();
});
    
    
    
const filters = {sort:"id",dir:"ASC",lastId:"0"};

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("userId");
	
    if (userId) {
        isUserListings=true;
        setFilter("userId", userId);
    }
	console.log('asda')
    applyFilters();
});

function applyFilters() {
    const parent = document.getElementById("productContainer");
    const mapToErrorFields={}
    parent.innerHTML=""
        request({url:"actions/getProducts.php?",data:filters,method:'GET',mapToErrorFields:mapToErrorFields, onSuccess(result){
            result.data.forEach((product)=>{
                parent.append(createProduct(product));
            })
        }})  
}    
    
function setFilter(key, value) {
    if (value === null || value === "") {
        delete filters[key];
    } else {
        filters[key] = value;
    }
}
    

const citySelect = document.getElementById('citySelect');
    const suburbSelect = document.getElementById('suburbId');
    getSuburbs(citySelect.value,suburbSelect);
    citySelect.addEventListener('change',async function(event){
        const value = event.target.value;
        getSuburbs(value,suburbSelect);
    });
    
    async function getSuburbs(cityVal,suburbE){
        const value = cityVal;
        if(value){
            const response = await fetch(`/actions/getSuburbs.php?cityId=${value}`);
            const data = await response.json();
            if(data.success===false){
                console.log('fah');
            }
            console.log(data);
            suburbE.innerHTML = "";
            const option = document.createElement("option");
    			option.value = "";
    			option.textContent = "Any";
    			suburbE.appendChild(option);
            data.data.suburbs.forEach(suburb => {
    			const option = document.createElement("option");
    			option.value = suburb.id;
    			option.textContent = suburb.name;
    			suburbE.appendChild(option);
  			});
        }
        else{
            suburbE.innerHTML = "";
            const option = document.createElement("option");
    			option.value = "";
    			option.textContent = "Any";
    			suburbE.appendChild(option);
        }
    }
</script>

