<?php
	require_once __DIR__ . "/../bootstrap.php";
	Db::connect();
	$cities = City::all();
?>

<main>
	<div class="formContainer">
		<h1>Sign up</h1>
		<form action="actions/signup.php" method="post" id="myForm">
			<div class="inputGroup">
				<label for ="username" >Username</label>
				<input type="text" name="username" id="username" required>
				<span class="error" id="eUsername"></span>
			</div>
			<div class="inputGroup">
				<label for ="phoneNumber" >Phone Number</label>
				<input type="tel" name="phoneNumber" id="phoneNumber" required>
                <span class="error" id="ePhoneNumber"></span>
			</div>
			<div class="inputGroup">
				<label for ="email" >Email</label>
				<input type="email" name="email" id="email" required>
                <span class="error" id="eEmail"></span>
			</div>
			<div class="inputGroup">
				<label for ="password" >Password</label>
				<input type="password" name="password" id="password" required>
                <span class="error" id="ePasswaord"></span>
			</div>
			<div class="inputGroup">
				<label for ="confirmPassword" >Confirm Password</label>
				<input type="password" name="confirmPassword" id="confirmPassword" required>
                <span class="error" id="eConfirmPassword"></span>
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
				<select id="suburb" name ="suburb">
				</select>
                <span class="error" id="eSuburb"></span>
			</div>
            <div class="buttonContainerCenter">
				<input class="wideButton" type="submit" value="signup">
            </div>
			<a href="index.php?page=loginPage">Already have an account? Sign in here!</a>
		</form>
	</div>
</main>

<script type="module">
    import {request} from '/pages/apiClient.js';
	const form = document.getElementById('myForm');
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
		alert('Submitted');
        const mapToErrorFields={"username":"eUsername","email":"eEmail","confirmPassword":"eConfirmPassword",'password':'ePassword','suburb':'eSuburb','phoneNumber':'ePhoneNumber'}
        request({url:'actions/signup.php',data:new FormData(this),method:'POST',mapToErrorFields:mapToErrorFields})
        
	});
    
    document.querySelectorAll('.error').forEach(el => {
   		el.classList.add('hidden');
	});
    
    const citySelect = document.getElementById('citySelect');
    const suburbSelect = document.getElementById('suburb');
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