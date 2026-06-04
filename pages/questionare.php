<?php
require_once __DIR__ . "/../bootstrap.php";

if (!isset($_SESSION['doQuestionare'])) {
    echo "<h1>No questionare to be completed at the moment </h1>";
    return;
}

try {

    $questions = Db::usingDbConnection(function () {
        return Question::withChoices($_SESSION['doQuestionare']);
    });

} catch (PDOException $e) {
    Db::handleException($e);
    echo "<h1>Something went wrong. Try again later!</h1>";
    return;
} catch (Exception $e) {
    echo "<h1>Something went wrong. Try again later!</h1>";
    return;
}
?>
<main>
	<div class=formContainer>
		<h1>Questionare</h1>
		<form method='POST' id="myForm">
            	<?php foreach($questions as $question): ?>
            		<label><?= htmlspecialchars($question->getQuestion()) ?></label>
            
            		<?php foreach($question->getChoices() as $choice): ?>
            			<label for="q1a"><input type="radio" name="answers[<?=$question->getId()?>]" id="q1a" value="<?=$choice->getId() ?>" required><?= htmlspecialchars($choice->getChoice())?></label>
            		<?php endforeach;?>
            		<br>
            	<?php endforeach;?>
            	<div class="buttonContainer">
					<input type="submit">
                    <button id="continue" hidden>Continue</button>
            	</div>
		</form>
	</div>
</main>

<script type="module">
    import {request} from '/pages/apiClient.js';
	const form = document.getElementById('myForm');
	form.addEventListener('submit', async function(event){
		event.preventDefault();
        const mapToErrorFields={}
        request({url:'actions/questionare.php',data:new FormData(this),method:'POST',mapToErrorFields:mapToErrorFields,preventRedirect:true ,onSuccess:(result)=>{
            highlightAnswers(result.data);
            const button = document.getElementById("continue");
            button.hidden = false;
            button.addEventListener("click", function() {
    		window.location.href = result.redirect;
                
        })
        }});
	});
    
    function highlightAnswers(data) {
    for (const questionId in data) {
        const { correct, selected } = data[questionId];

        const inputs = document.querySelectorAll(`input[name="answers[${questionId}]"]`);

        inputs.forEach(input => {
            const label = input.parentElement;

            if (input.value == correct) {
                label.style.color = "#5acc3d";
            }

            if (input.value == selected && selected != correct) {
                label.style.color = "#e83e2e";
            }
        });
    }
}
    
</script>