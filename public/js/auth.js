const error = $(".error");
const email = $("#l-email");
$("button[type=submit]").addEventListener("pointerdown", async () => {
  const response = await AJAX.post("/auth", JSONHandler(), {
    body: JSON.stringify({ email: email.value })
  });
  
  if (response.error !== undefined) {
    error.textContent = response.error;
    error.classList.add("show");
    return;
  }
  
  error.textContent = "";
  error.classList.remove("show");
  
  console.log(response);
  switch_form(email.closest(".form"), "confirmation");
});