<main>
	<div class="formContainer">
		<h1>Bank recepient</h1>

		<form method="post" id="myForm">
            
            <div class="inputGroup">
				<label for="accountNumber">Card Number</label>
				<input type="text" name="accountNumber" id="accountNumber" required>
			</div>
			<div class="inputGroup">
				<label for ="bankCode" >Select a bankcode</label>
				<select id="bankCode" name="bankCode"></select>
			</div>
            <div class="buttonContainerCenter">
				<input class="wideButton" type="submit" value="Add">
            </div>
		</form>
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    
async function loadBanks() {
    try {
        const response = await fetch("https://api.paystack.co/bank?country=nigeria", {
            method: "GET",
            headers: {
                "Authorization": "Bearer pk_test_bbce6b58b1da1f6f27f28e96232b59ac8c97c9a2"
            }
        });

        const result = await response.json();
        console.log(result);

        if (!result.status) {
            console.error("Failed to load banks:", result.message);
            return;
        }

        const select = document.getElementById("bankCode");

        // clear existing options
        select.innerHTML = '<option value="001">Test bank</option>';
        

        result.data.forEach(bank => {
            const option = document.createElement("option");
            option.value = bank.code;
            option.textContent = bank.name;

            select.appendChild(option);
        });

    } catch (error) {
        console.error("Error loading banks:", error);
    }
}

    
    
// run on page load
window.addEventListener("DOMContentLoaded", loadBanks);
    
const accountInput = document.getElementById("accountNumber");
const bankSelect = document.getElementById("bankCode");
const form = document.getElementById('myForm');
form.addEventListener('submit', async function(event){
    event.preventDefault();

    const accountNumber = accountInput.value.trim();
    const bankCode = bankSelect.value;
    
    if (!accountNumber || !bankCode) {
        alert("Enter account number and select a bank");
        return;
    }

    try {
        const response = await fetch(`actions/checkBankDetails.php?accountNumber=${accountNumber}&bankCode=${bankCode}`, {
            method: "GET"
        });

        const result = await response.json();
        
        if (!result.success) {
            alert("Failed: " + result.message);
            return;
        }

        const accountName = result.accountName;
        if(!confirm("Is this your account: " + accountName)){
            return;
        }
      
        const fd= new FormData();
        fd.append("accountNumber",accountNumber);
        fd.append("bankCode","001");
        const mapToErrorFields={}
        const successMessage ="Bank recepient saved";
        const redirect = "https://myc2c.gamer.gd/index.php?page=transactions";
        request({url:'actions/createBankRecepient.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage:successMessage,redirect:redirect});
    
    } catch (err) {
        console.error(err);
        alert("Error resolving account");
    }
	})
</script>