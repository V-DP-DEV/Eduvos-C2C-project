// Add an event listener to the input
export function addPreviewListener(fileInput,imagePreview,defaultSrc){
    console.log("JS FILE VERSION 2");
   fileInput.addEventListener('change', function(event) {
			const file = event.target.files[0]; // Get the first selected file

			if (file && file.type.match('image.*')) {
					const reader = new FileReader(); // Create a new FileReader instance

					// Set the onload function: executed when the file reading is complete
					reader.onload = function(e) {
							// Set the image src to the result of the file reading (a data URL)
							imagePreview.src = e.target.result;
					};

					// Read the image file as a Data URL (Base64 encoded string)
					reader.readAsDataURL(file);
			}
       		
       		else {
                	if(defaultSrc!==null){
                        // Handle cases where a non-image file is selected or no file
                        imagePreview.src = defaultSrc // Reset image source
                    }
			}
            
            
	}); 
}