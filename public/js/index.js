$(".logout").addEventListener("pointerdown", async () => {
  const response = await AJAX.delete("/auth", JSONHandler());
  
  if (response.error !== undefined || response.next === undefined) {
    alert("Could not logout.");
    return;
  }
  
  window.location.replace(response.next);
});



let state = "deck";

$(".add").addEventListener("pointerdown", () => {
  show_window("create-" + state);
});



const deck_name_input = $("#deck-name");
const deck_control = new FormControl("create-deck");
$("#create-deck button[type=submit]").addEventListener("pointerdown", async () => {
  const deck_name = deck_name_input.value.trim();
  
  if (deck_name === "") {
    deck_control.invalidate("Deck name must not be left empty.");
    return;
  }
  
  const response = await AJAX.post("/deck", JSONHandler());
  
  if (response.error !== undefined) {
    deck_control.invalidate(response.error);
    return;
  }
  
  // todo change to newly created deck
});



function stay_logged_in() {
  const setting = localStorage.getItem("stay_logged_in");
  
  if (setting === "true" || (setting === null && confirm("Do you want to stay logged in?"))) {
    AJAX.get("/auth/stay-logged-in", JSONHandler())
      .then(response => {
        if (response.error) {
          return;
        }
      
        localStorage.setItem("stay_logged_in", "true");
      });
    return;
  }
  
  localStorage.setItem("stay_logged_in", "false");
}
stay_logged_in();