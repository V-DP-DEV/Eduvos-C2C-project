<?php
    require_once __DIR__ .'/../bootstrap.php';
	$orderId= $_GET['orderId'] ?? null;
	if($orderId == null){
		echo "<h1>Invalid product</h1>";
		exit;
	}
?>

<main>
	<div class="formContainer">
		<h1>Leave a review for</h1>
		<h2>Product</h2>
		<form id="myForm">
			<div class="inputGroup">
				<label for="rating">Rating</label>
				<div class ="stars" id="divMain">
					<span data-value="1">★</span>
					<span data-value="2">★</span>
					<span data-value="3">★</span>
					<span data-value="4">★</span>
					<span data-value="5">★</span>
				</div>
                <input type="hidden" name="rating" id="rating" value="0">
                <span class="error" id="eRating"></span>
			</div>
			<div class="inputGroup">
				<label for="communicationRating">Communication</label>
                <input type="hidden" name="communicationRating" id="communicationRating" value="0">
				<div class ="stars" id="divCommunication">
					<span data-value="1">★</span>
					<span data-value="2">★</span>
					<span data-value="3">★</span>
					<span data-value="4">★</span>
					<span data-value="5">★</span>
				</div>
                <span class="error" id="eCommunicationRating"></span>
                
			</div>
			<div class="inputGroup">
                <input type="hidden" name="conditionRating" id="conditionRating" value="0">
				<label for="conditionRating">Condition</label>
				<div class ="stars" id="divCondition">
					<span data-value="1">★</span>
					<span data-value="2">★</span>
					<span data-value="3">★</span>
					<span data-value="4">★</span>
					<span data-value="5">★</span>
				</div>
				<span class="error" id="eConditionRating"></span>
			</div>
			<div class="inputGroup">
				<label for="comments">Comments</label>
				<textarea name="comments" id="comments"></textarea>
                <span class="error" id="eComments"></span>
			</div>
			<div class="inputGroup">
				<label for="picture" >Picture <img class="reviewPreviewImage" src="/../images/addImage.png" alt="reviewImage" id="picturePreview" width="500px" height="500px"></label>
				<input type="file" name="picture" id="picture" hidden>
				
                <span class="error" id="ePhoto"></span>
			</div>
            <div class="buttonContainerCenter">
				<input class="wideButton" type="submit" value="Leave review">
            </div>
		</form>
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {addPreviewListener} from '/pages/previewImg.js';
    
    const textarea = document.getElementById("comments");
textarea.style.height = "auto";                 // reset height
textarea.style.height = textarea.scrollHeight + "px"; // set new height
    
    
textarea.addEventListener("input", () => {
  textarea.style.height = "auto";                 // reset height
  textarea.style.height = textarea.scrollHeight + "px"; // set new height
});     
    
    const fileInput = document.getElementById('picture');
	const imagePreview = document.getElementById('picturePreview');
	addPreviewListener(fileInput,imagePreview,imagePreview.src);
    
	setupStars('divMain','rating');
	setupStars('divCommunication','communicationRating');
	setupStars('divCondition','conditionRating');
    
    document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
    
	function setupStars(group,input){
		const stars = document.querySelectorAll('#' + group + ' span');
		const ratingInput = document.getElementById(input);
		stars.forEach(star => {
			star.addEventListener('click', function(){
				const value = star.dataset.value;
				ratingInput.value = value;
				stars.forEach(s => {
					s.classList.toggle('active', s.dataset.value <= value);
				});
			});
		});
	}
    
    const form = document.getElementById('myForm');
	
	form.addEventListener('submit', async function(event){
        event.preventDefault();
        document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
        const fd = new FormData(this);
        fd.append("orderId", <?= $orderId ?>);

		alert('Submitted');
        const mapToErrorFields={'rating':'eRating','commmunicationRating':'eCommmunicationRating','conditionRating':'eConditionRating','comments':'eComments'};
        const successMessage ="Review left succesfully";
        const redirect = "https://myc2c.gamer.gd/index.php?page=myOrders";
        request({url:'actions/leaveReview.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect})
        
	});
    
    
</script>