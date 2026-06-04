<main>
	<div class="formContainer">
		<h1>Login</h1>
		<form  id="myForm">
				<div class="inputGroup">
					<label for="email">Email</label>
					<input  name="email" id="email" type="email" required>
				</div>
			<div>
                <div class="inputGroup">
					<label for="password">Password</label>
					<input  name="password" id="password" type="password" required>
                </div>
			</div>
				<div class="buttonContainerCenter">
					<input class="wideButton" type="submit" value="login">
                </div>
		</form>
        <a href="index.php?page=signupPage">Dont have an account? Sign up here!</a>
	</div>
</main>

<script type="module">
    //imported my custom request handler
    import {request} from '/pages/apiClient.js';
	//get the form element and make it call actions login php
	const form = document.getElementById('myForm');
	form.addEventListener('submit', async function(event){
		event.preventDefault();
        const mapToErrorFields={}
        request({url:'actions/login.php',data:new FormData(this),method:'POST',mapToErrorFields:mapToErrorFields})
	});
												
</script>
