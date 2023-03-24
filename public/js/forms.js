/** @type {Map<string, HTMLDivElement>} */
const forms = new Map();

$$(".form").forEach(form => {
  forms.set(form.classList.item(1), form);
  
  const submitter = form.querySelector("button[type=submit]");
  form.addEventListener("keydown", evt => {
    if (
      evt.key !== "Enter"
      || (evt.altKey || evt.ctrlKey || evt.shiftKey)
      || evt.target.getAttribute("do-submit") === "never"
    ) return;
    
    evt.preventDefault();
    
    submitter.dispatchEvent(new Event("submit"));
    submitter.dispatchEvent(new Event("click"));
    submitter.dispatchEvent(new Event("pointerdown"));
  });
});

$$("button[link-to]").forEach(e => {
  const linkTo = e.getAttribute("link-to");
  e.addEventListener("pointerdown", evt => {
    e.closest(".form").classList.add("hide");
    forms.get(linkTo).classList.remove("hide");
  });
});


/**
 * @param {HTMLElement} currentForm 
 * @param {string} formToken 
 */
function switchForm (currentForm, formToken) {
  currentForm.classList.add("hide");
  forms.get(formToken).classList.remove("hide");
}