<main>
	<div class="formContainer">
		<h1>Verification Request</h1>
		<form action="verificationRequest.php" method="post" id="myForm">
    		<div class="inputGroup">
    			<label for="idPhoto">ID Photo
                    <div class="imageBox cardImage">
                		<img src="/../images/addImage.png" alt="idPhoto" id="idPhotoPreview" class="portrait">
                	</div>
                </label>
				<input type="file" name="idPhoto" id="idPhoto" style="display:none">
                
                <span class="error" id="eIdPhoto"></span>
			</div>
			<div class="inputGroup">
				<label for="selfiePhoto">Selfie Photo
                    <div class="imageBox cardImage">
                		<img src="/../images/addImage.png" alt="selfiePhoto" id="selfiePhotoPreview" class="portrait">
                	</div></label>
				<input type="file" name="selfiePhoto" id="selfiePhoto" style="display:none">
                <span class="error" id="eSelfiePhoto"></span>
            </div>
            <div class="buttonContainerCenter">
            	<input class="wideButton" type="submit">
            </div>
		</form>
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
	import {addPreviewListener} from '/pages/previewImg.js';

	const fileInputId = document.getElementById('idPhoto');
	const imagePreviewId = document.getElementById('idPhotoPreview');
	addPreviewListener(fileInputId,imagePreviewId,imagePreviewId.src);

	const fileInputSelfie = document.getElementById('selfiePhoto');
	const imagePreviewSelfie = document.getElementById('selfiePhotoPreview');
	addPreviewListener(fileInputSelfie,imagePreviewSelfie,imagePreviewSelfie.src);

	const form = document.getElementById('myForm');
	
	document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});

	form.addEventListener('submit', async function(event){
		event.preventDefault();
        document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
        const mapToErrorFields={'idPhoto':'eIdPhoto','selfiePhoto':'eSelfiePhoto'}
        const successMessage="Request made"
       	const redirect = "https://myc2c.gamer.gd/index.php?page=viewMyProfile";
        request({url:'actions/addVerificationRequest.php',data:new FormData(this),method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage})
	});
</script>