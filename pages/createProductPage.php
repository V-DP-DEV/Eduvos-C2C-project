<?php
require_once __DIR__ . "/../bootstrap.php";

try {

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
		<h1>Create product</h1>
		<form action="actions/signup.php" method="post" id="myForm">
			<div class="inputGroup">
				<label for ="title" >Title</label>
				<input type="text" name="title" id="title" required>
				<span class="error" id="eTitle"></span>
			</div>
			<div class="inputGroup">
				<label for ="price" >Price</label>
				<input type="number" name="price" id="price" required>
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
                <textarea name="description" id="description"></textarea>
                <span class="error" id="eDescription"></span>
			</div>
			<div class="inputGroup">
				<label for ="images[]" class="fileButton">Upload images</label>
				<input type="file" name="images[]" id="images[]" required multiple hidden>
                <span class="error" id="eImages"></span>
			</div>
                   
           	<div class="scrollImgs" id="scrollImgs">
                </div>
            <div class="buttonContainer">
                <input class="wideButton" type="submit" value="Create">
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

function updatePreview() {
    previewContainer.innerHTML = "";

    filesArray.forEach((file, index) => {
        const reader = new FileReader();

        reader.onload = function(e) {
            const wrapper = document.createElement("div");
			wrapper.className = "imagePreview";

			const img = document.createElement("img");
			img.src = e.target.result;
			img.className = "imagePreviewImg";

			const btn = document.createElement("button");
			btn.innerText = "x";
			btn.className = "imagePreviewRemove";

            btn.onclick = () => {
                filesArray.splice(index, 1);
                syncInputFiles();
                updatePreview();
            };

            wrapper.appendChild(img);
            wrapper.appendChild(btn);
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

const form = document.getElementById('myForm');
	
	form.addEventListener('submit', async function(event){
        const fd = new FormData();
        document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
		fd.append("title", document.getElementById("title").value);
		fd.append("price", document.getElementById("price").value);
		fd.append("description", document.getElementById("description").value);
        fd.append("categoryId", document.getElementById("categoryId").value);

		filesArray.forEach(file => {
    		fd.append("images[]", file); // use the same name as your input
		});
		event.preventDefault();
        const mapToErrorFields={'title':'eTitle','description':'eDescription','price':'ePrice','categoryId':'eCategoryId','images':'eImages'};
        const successMessage ="Producted created";
        const redirect = "https://myc2c.gamer.gd/index.php?page=myListings";
        request({url:'actions/createProduct.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect})
        
	});

</script>