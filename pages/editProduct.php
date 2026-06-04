<?php
require_once __DIR__ . "/../bootstrap.php";

$productId = $_GET['id'] ?? "";

if ($productId === "") {
    echo "<h1>No verification specified</h1>";
    exit;
}

try {

    $product = Db::usingDbConnection(function () use ($productId) {
        return Product::withImgAndReviews($productId);
    });

    if (!$product) {
        echo "<h1>This is not a valid product request</h1>";
        exit;
    }

    if ($product->getUserId() !== ($_SESSION["id"] ?? null)) {
        echo "<h1>This is not your product</h1>";
        exit;
    }
	
    $hasActiverOrder = Db::usingDbConnection(function () use ($product) {
        return $product->hasActiveOrder();
    });
    if ($hasActiverOrder) {
        echo "<h1>Cant be edited when a valid order is in process</h1>";
        exit;
    }

    $categories = Db::usingDbConnection(function () {
        return Category::all();
    });

} catch (PDOException $e) {
    Db::handleException($e);
    echo "<h1>Something went wrong when trying to retrieve your data. Try again later!</h1>";
    exit;
} catch (Exception $e) {
    echo "<h1>Something went wrong when trying to retrieve your data. Try again later!</h1>";
    exit;
}
?>
<main>
	<div class="formContainer" id="myForm">
		<h1>Edit product</h1>
		<form action="actions/signup.php" method="post" id="myForm">
			<div class="inputGroup">
				<label for ="title" >Title</label>
				<input type="text" name="title" id="title" value="<?= htmlspecialchars($product->getTitle())?>" required>
				<span class="error" id="eTitle"></span>
			</div>
			<div class="inputGroup">
				<label for ="price" >Price</label>
				<input type="number" name="price" id="price" value="<?= htmlspecialchars($product->getPrice())?>"  required>
                <span class="error" id="ePrice"></span>
			</div>
            <div class="inputGroup">
                <label for ="categoryId" >Category</label>
				<select id="categoryId" name="categoryId">
                    <?php foreach($categories as $category): ?>
                    	<option value="<?=$category->getId()?>"><?=htmlspecialchars($category->getName())?></option>
                    <?php endforeach;?>
				</select>
                <span class="error" id="eCategoryId"></span>
            </div>
			<div class="inputGroup">
				<label for ="description" >Description</label>
                <textarea name="description" id="description" ><?= htmlspecialchars($product->getDescription())?></textarea>
                <span class="error" id="eDescription"></span>
			</div>
			<div class="inputGroup">
				<label for ="images[]" class="fileButton">Upload images</label>
				<input type="file" name="images[]" id="images[]" multiple hidden>
                <span class="error" id="eImages"></span>
			</div>
                   
           	<div class="scrollImgs" id="scrollImgs">
            </div>
            <div class="buttonContainerCenter">
				<input class="wideButton" type="submit" value="Edit">
            </div>
		</form>
                    
	</div>
</main>
<script type="module">
import {request} from '/pages/apiClient.js';

document.querySelectorAll('.error').forEach(el => {
   		el.classList.add('hidden');
	});

const textarea = document.getElementById("description");
textarea.style.height = "auto";                 // reset height
textarea.style.height = textarea.scrollHeight + "px"; // set new height
    
    
textarea.addEventListener("input", () => {
  textarea.style.height = "auto";                 // reset height
  textarea.style.height = textarea.scrollHeight + "px"; // set new height
});    
    
const fileInput = document.getElementById("images[]");
const previewContainer = document.getElementById("scrollImgs");



let filesArray = [];
let existingImages = [];   
    
<?php foreach($product->getImgs() as $image):?>
    existingImages.push(<?= $image->getImageId()?>);

           	<?php endforeach;?>    
updatePreview();    
    // existing DB images

fileInput.addEventListener("change", (e) => {
    const newFiles = Array.from(e.target.files);

    newFiles.forEach(file => {
        if (file.type === "image/png" || file.type === "image/jpeg") {
            filesArray.push(file);
        } else {
            alert("Only PNG and JPEG images are allowed.");
        }
    });

    updatePreview();
});
    
function createWrapper(src) {
    const wrapper = document.createElement("div");
	wrapper.className = "imagePreview";

	const img = document.createElement("img");
	img.src = src;
	img.className = "imagePreviewImg";

	const btn = document.createElement("button");
	btn.innerText = "x";
	btn.className = "imagePreviewRemove";

   wrapper.appendChild(img);
   wrapper.appendChild(btn);
   return wrapper;
}

function updatePreview() {
    previewContainer.innerHTML = "";
    
    existingImages.forEach((imgId, index) => {
        const wrapper = createWrapper("actions/image.php?id="+imgId);

        const btn = wrapper.querySelector("button");

        btn.onclick = () => {
            existingImages.splice(index, 1);
            updatePreview();
        };

        previewContainer.appendChild(wrapper);
    });

    filesArray.forEach((file, index) => {
        const reader = new FileReader();

        reader.onload = function (e) {
            const wrapper = createWrapper(e.target.result);

            const btn = wrapper.querySelector("button");

            btn.onclick = () => {
                filesArray.splice(index, 1);
                syncInputFiles();
                updatePreview();
            };

            previewContainer.appendChild(wrapper);
        };

        reader.readAsDataURL(file);
    });

    syncInputFiles();
}

function syncInputFiles() {
    const dataTransfer = new DataTransfer();

    filesArray.forEach(file => {
        dataTransfer.items.add(file);
    });

    fileInput.files = dataTransfer.files;
}

document.getElementById("categoryId").value=<?= $product->getCategoryId()?>;    
    
const form = document.getElementById('myForm');
	
	form.addEventListener('submit', async function(event){
        event.preventDefault();
        
        document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
        
        const fd = new FormData();
		fd.append("title", document.getElementById("title").value);
		fd.append("price", document.getElementById("price").value);
		fd.append("description", document.getElementById("description").value);
        fd.append("categoryId", document.getElementById("categoryId").value);
        fd.append("existingImages", JSON.stringify(existingImages));
        fd.append("id",<?= $productId?>)
		filesArray.forEach(file => {
    		fd.append("images[]", file); // use the same name as your input
		});
        const mapToErrorFields={'title':'eTitle','description':'eDescription','price':'ePrice','categoryId':'eCategoryId','images':'eImages'};
        const successMessage ="Product edited!";
        request({url:'actions/editProduct.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage})
        
	});

</script>