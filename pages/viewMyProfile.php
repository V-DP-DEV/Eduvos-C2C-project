<?php
require_once __DIR__ . "/../bootstrap.php";

try {

    $cities = Db::usingDbConnection(function () {
        return City::all();
    });

    $user = Db::usingDbConnection(function () {
        return User::withBuyerSellerInfo($_SESSION['id']);
    });
    
    $validForVerification = Db::usingDbConnection(function () use($user){
        return UserSummary::validForVerification($user->getId());
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
	<div class="formContainer">
		<h1>Your profile</h1>
		<form action="" method="post" id="myForm">
			<div class="inputGroup">
				<label for ="profilePic" class="profileButton" ><img src="actions/image.php?id=<?= $user->getBuyerSellerInfo()->getImageId()?>" id="profileImg">Profile</label>
				<input type="file" name="profilePic" id="profilePic" style="display:none">
                <span class="error" id="eProfilePic"></span>
                
			</div>
            <div class="inputGroup">
				<label for ="username" >Username</label>
				<input type="text" name="username" id="username" required value='<?= htmlspecialchars($user->getUsername()) ?>'>
				<span class="error" id="eUsername"></span>
			</div>
			<div class="inputGroup">
				<label for ="phoneNumber" >Phone Number</label>
				<input type="tel" name="phoneNumber" id="phoneNumber" required value='<?= htmlspecialchars($user->getPhone() )?>'>
                <span class="error" id="ePhoneNumber"></span>
			</div>
			<div class="inputGroup">
				<label for ="email" >Email</label>
				<input type="email" name="email" id="email" value='<?= htmlspecialchars($user->getEmail()) ?>' required>
                <span class="error" id="eEmail"></span>
			</div>
			<div class="inputGroup">
				<label for ="city">City</label>
				<select id="citySelect">
                    <?php foreach($cities as $city): ?>
                    	<option value="<?=$city->getId()?>"><?=htmlspecialchars($city->getName())?></option>
                    <?php endforeach;?>
				</select>
			</div>
			<div class="inputGroup">
				<label for ="suburb">Suburb</label>
				<select id="suburb" name="suburb">
                <span class="error" id="eSuburb"></span>
				</select>
			</div>
			
			<div class="buttonContainerCenter">
				<input class="wideButton" type="submit" value="update">
            </div>
            <?php if($validForVerification):?>
            	<a href="index.php?page=verificationRequestPage">Verify yourself?</a>
			<?php else:?>
            	<div>You dont meet the current requirements for verification. You might have made a request recently!</div>
            <?php endif;?>
		</form>
	</div>
</main>
<script type="module">
    import {addPreviewListener} from '/pages/previewImg.js';
    import {request} from '/pages/apiClient.js';
    const form = document.getElementById('myForm');
    document.querySelectorAll('.error').forEach(el => {
   		el.classList.add('hidden');
	});
    
    
    form.addEventListener('submit', async function(event){
		event.preventDefault();
        document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
		const usernameInput = document.getElementById('username');
		const phoneNumberInput = document.getElementById('phoneNumber');
		const emailInput = document.getElementById('email');
		const passwordInput = document.getElementById('password');
		const confirmPasswordInput = document.getElementById('confirmPassword');
        const mapToErrorFields={"username":"eUsername","email":"eEmail","phoneNumber":"ePhoneNumber","suburb":"eSuburb","profilePic":"eProfilePic"}
        const successMessage="Profile updated!";
        request({url:'actions/updateUser.php',data:new FormData(this),method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage})
        
	});
    
	const fileInput = document.getElementById('profilePic');
	const imagePreview = document.getElementById('profileImg');
	addPreviewListener(fileInput,imagePreview,imagePreview.src);
    //addPreviewListener(fileInput,imagePreview,"https://cdn.mos.cms.futurecdn.net/4wwQNKxhra9z9oUaPfwkP3.jpg");
    const citySelect = document.getElementById('citySelect');
    const suburbSelect = document.getElementById('suburb');
    
    citySelect.value = <?=$user->getBuyerSellerInfo()->getCityId() ?>;
    citySelect.addEventListener('change',async function(event){
        const value = event.target.value;
        getSuburbs(value,suburbSelect);
    });
    
   
    
    await getSuburbs(citySelect.value,suburbSelect);
    suburbSelect.value=<?=$user->getBuyerSellerInfo()->getSuburbId() ?>
    
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
            data.data.suburbs.forEach(suburb => {
    			const option = document.createElement("option");
    			option.value = suburb.id;
    			option.textContent = suburb.name;
    			suburbE.appendChild(option);
  			});
        }
    }
</script>
