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
  e.addEventListener("pointerup", () => {
    e.closest(".form").classList.add("hide");
    forms.get(linkTo).classList.remove("hide");
  });
});


/**
 * @param {HTMLElement} current_form
 * @param {string} form_token
 */
function switch_form (current_form, form_token) {
  current_form.classList.add("hide");
  forms.get(form_token).classList.remove("hide");
}


class FormControl {
  error;
  form_token;
  
  /**
   * @param {string} form_token
   */
  constructor(form_token) {
    this.error = $(`#${form_token} .error-modal`);
    this.form_token = form_token;
  }
  
  /**
   * @param {string} message
   */
  invalidate(message) {
    if (this.error === null) {
      console.error("Cannot set message because there is no error modal for token: " + this.form_token);
      return;
    }
    
    this.error.textContent = message;
    this.error.classList.add("show");
  }
  
  clear() {
    if (this.error === null) {
      console.error("Cannot perform clear because there is no error modal for token: " + this.form_token);
      return;
    }
    
    this.error.textContent = "";
    this.error.classList.remove("show");
  }
}