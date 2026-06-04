<?php
$verificationId = $_GET['id'] ?? "";

if ($verificationId === "") {
    echo "<h1>No verification specified</h1>";
    exit;
}

require_once __DIR__ . "/../bootstrap.php";

try {

    $verification = Db::usingDbConnection(function () use ($verificationId) {

        $verification = VerificationRequest::find($verificationId);

        return $verification;
    });

    if (!$verification) {
        echo "<h1>This is not a valid verification request</h1>";
        exit;
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
	<div class="formContainer">
		<h1>Verification Request</h1>
        <h2><?= createUserSummaryElement($verification->getUserSummary())?></h2>
        <div class="imageBox cardImage">
                <img alt="idPhoto" id="id" class="portrait" src="actions/image.php?id=<?= $verification->getImageId() ?>" class="portrait">
        </div>
        <div class="imageBox cardImage">
               <img alt="selfiePhoto" id="selfie" class="portrait" src="actions/image.php?id=<?= $verification->getImageSelfie() ?>" class="portrait">
        </div>
        <div class="buttonContainer">
                <button id="btnDecline">Decline</button>
                <button id="btnAccept">Accept</button>
        </div>
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
	const buttonAccept = document.getElementById('btnAccept');
	buttonAccept.addEventListener('click', async function(event){
        updateRequest("accepted");
	});
    
    const buttonDecline = document.getElementById('btnDecline');
	buttonDecline.addEventListener('click', async function(event){
        updateRequest("declined");
	});
    
    function updateRequest(status){
        const fd = new FormData();
        fd.append("id",<?=$verificationId ?>);
        fd.append("status",status)
        
        const mapToErrorFields={}
        const successMessage ="Verification " + status;
        const redirect = "https://myc2c.gamer.gd/index.php?page=adminVerificationTable";
        request({url:'actions/updateVerificationRequest.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect}) 
    }
</script>

