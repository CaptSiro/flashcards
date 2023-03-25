$(".logout").addEventListener("pointerdown", async () => {
  const response = await AJAX.delete("/auth", JSONHandler());
  
  if (response.error !== undefined || response.next === undefined) {
    alert("Could not logout.");
    return;
  }
  
  window.location.replace(response.next);
});



const states = [{
  section: "deck"
}];

$(".back").addEventListener("pointerdown", () => {
  states.pop();
  state_change();
});

function get_state() {
  if (states.length - 1 === 0) {
    return {
      section: "deck"
    };
  }
  
  return states[states.length - 1];
}

function set_state(state) {
  if (objectEqual(state, get_state())) {
    return;
  }
  
  states.push(state);
}

const loaders = new Map([
  ["deck", load_decks],
  ["stack", load_stacks],
  ["card", load_cards]
]);

function state_change() {
  const s = get_state();
  loaders.get(s.section)(s);
}

state_change();



const grid = $("main");
const add_button = AddButton();



const deck_name_input = $("#deck-name");
const deck_control = new FormControl("create-deck");
$("#create-deck button[type=submit]").addEventListener("pointerdown", async () => {
  const deck_name = deck_name_input.value.trim();
  
  if (deck_name === "") {
    deck_control.invalidate("Deck name must not be left empty.");
    return;
  }
  
  const response = await AJAX.post("/deck", JSONHandler(), {
    body: JSON.stringify({
      name: deck_name
    })
  });
  
  if (response.error !== undefined) {
    deck_control.invalidate(response.error);
    return;
  }
  
  deck_control.clear();
  load_decks();
  clear_windows();
});



const stack_name_input = $("#stack-name");
const stack_control = new FormControl("create-stack");
$("#create-stack button[type=submit]").addEventListener("pointerdown", async () => {
  const stack_name = stack_name_input.value.trim();
  
  if (stack_name === "") {
    deck_control.invalidate("Deck name must not be left empty.");
    return;
  }
  
  const s = get_state();
  if (s.deck_id === undefined) {
    return;
  }
  
  const response = await AJAX.post("/stack", JSONHandler(), {
    body: JSON.stringify({
      name: stack_name,
      deck_id: s.deck_id
    })
  });
  
  if (response.error !== undefined) {
    stack_control.invalidate(response.error);
    return;
  }
  
  stack_control.clear();
  load_stacks(get_state());
  clear_windows();
});



async function load_decks() {
  const decks = await AJAX.get("/deck/users/", JSONHandler());
  if (decks.error !== undefined) {
    console.log(decks);
    return;
  }
  
  grid.textContent = "";
  grid.append(add_button);
  
  for (const deck of decks) {
    grid.append(
      Div("deck", [
        Heading(3, _, deck.name)
      ], {
        listeners: {
          click: () => {
            load_stacks({
              deck_id: deck.id
            })
          }
        }
      })
    )
  }
}



async function load_stacks(s) {
  set_state({
    section: "stack",
    deck_id: s.deck_id
  });
  
  const deck_id = s.deck_id;
  
  const stacks = await AJAX.get("/stack/in-deck/" + deck_id, JSONHandler());
  if (stacks.error !== undefined) {
    console.log(stacks);
    return;
  }
  
  grid.textContent = "";
  grid.append(add_button);
  
  for (const stack of stacks) {
    grid.append(
      Div("stack", [
        Heading(3, _, stack.name)
      ], {
        listeners: {
          click: () => {
            load_cards({
              stack_id: stack.id
            })
          }
        }
      })
    )
  }
}



function load_cards(s) {
  set_state({
    section: "card",
    stack_id: s.stack_id
  });
  
  const deck_id = s.stack_id;
  
  grid.textContent = "";
  grid.append(add_button);
}



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



/**
 * @return {HTMLElement}
 */
function AddButton() {
  return (
    Button("add button-like", [
      Span("mono", "+")
    ], () => {
      show_window("create-" + states.section)
    })
  )
}