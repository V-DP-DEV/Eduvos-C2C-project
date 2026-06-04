export async function request({
  url,
  method,
  data = null,
  onSuccess = () => {},
  onError = () => {},
  successMessage = null,
  preventRedirect = false,
  redirect = null,
  mapToErrorFields = null
}) {
  try {

    // build fetch options
    const options = {
      method,
      headers: {}
    };

    // GET handling → convert data into query string
    if (method.toUpperCase() === "GET" && data && !(data instanceof FormData)) {
      const query = new URLSearchParams(data).toString();
      url += (url.includes("?") ? "&" : "?") + query;
    }

    // POST/PUT/etc handling
    else if (data) {
      if (data instanceof FormData) {
        options.body = data;
      } else {
        options.headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(data);
      }
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (result.success) {
      
      if(successMessage){
          alert(successMessage);
      }  
      
      onSuccess(result);

      if (result.redirect && !preventRedirect) {
        window.location.href = result.redirect;
        return;
      }
      else if(redirect){
          window.window.location.href = redirect;
      }
    }

    if (result.error) {
      console.error(result.error);
      alert(result.error.general_error || "Error");
      handleErrors(mapToErrorFields, result.error);
      onError(result.error);
    }

  } catch (err) {
    console.error("Network error:", err);
  }
}

// helper function (not exported)
function handleErrors(map, error) {
  if (!map || !error.fields) return;
  Object.keys(error.fields).forEach(field => {
    const outputId = map[field];
    const el = document.getElementById(outputId);
    if (el) {
        el.innerHTML = error.fields[field];
        el.classList.remove("hidden");    
            }
  });
}