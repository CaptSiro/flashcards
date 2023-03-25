$(".logout").addEventListener("pointerdown", async () => {
  const response = await AJAX.delete("/auth", JSONHandler());
  
  if (response.error !== undefined || response.next === undefined) {
    alert("Could not logout.");
    return;
  }
  
  window.location.replace(response.next);
});



/**
 * @typedef State
 * @property {string} section
 * @property {HTMLElement} label
 */
/**
 * @type {State[]}
 */
const states = [{
  section: "deck",
  label: Span()
}];

$(".back").addEventListener("pointerdown", () => {
  states.pop();
  state_change();
});

window.addEventListener("popstate", () => {
  states.pop();
  if (states.length === 0) {
    return;
  }
  
  state_change();
});

/**
 * @return {State}
 */
function get_state() {
  if (states.length - 1 < 0) {
    return {
      section: "deck",
      label: Span()
    };
  }
  
  return states[states.length - 1];
}

/**
 * @param {State} state
 */
function set_state(state) {
  if (objectEqual(state, get_state(), ["label"])) {
    return;
  }
  
  state_label.textContent = "";
  state_label.append(state.label);
  
  states.push(state);
}

const loaders = new Map([
  ["deck", load_decks],
  ["stack", load_stacks],
  ["card", load_cards]
]);

const state_label = $(".state-label");
function state_change() {
  const s = get_state();
  loaders.get(s.section)(s);
  
  state_label.textContent = "";
  state_label.append(s.label);
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
    deck_control.invalidate("Stack name must not be left empty.");
    return;
  }
  
  const s = get_state();
  if (s?.deck.id === undefined) {
    return;
  }
  
  const response = await AJAX.post("/stack", JSONHandler(), {
    body: JSON.stringify({
      name: stack_name,
      deck_id: s?.deck.id
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



const card_question_input = $("#card-question");
const card_answer_input = $("#card-answer");
const card_control = new FormControl("create-card");
$("#create-card button[type=submit]").addEventListener("pointerdown", async () => {
  const card_question = card_question_input.value.trim();
  
  if (card_question === "") {
    deck_control.invalidate("Card question must not be left empty.");
    return;
  }
  
  const card_answer = card_answer_input.value.trim();
  
  if (card_answer === "") {
    deck_control.invalidate("Card answer must not be left empty.");
    return;
  }
  
  const s = get_state();
  if (s?.stack.id === undefined) {
    return;
  }
  
  const response = await AJAX.post("/card", JSONHandler(), {
    body: JSON.stringify({
      question: card_question,
      answer: card_answer,
      stack_id: s.stack.id
    })
  });
  
  if (response.error !== undefined) {
    card_control.invalidate(response.error);
    return;
  }
  
  card_control.clear();
  load_cards(get_state());
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
            load_stacks({ deck })
          }
        }
      })
    )
  }
}



async function load_stacks(s) {
  if (s.deck === undefined) {
    return;
  }
  
  set_state({
    section: "stack",
    label: Span(_, [
      "Deck: ",
      Span("important", s.deck.name)
    ]),
    deck: s.deck
  });
  
  const deck_id = s.deck.id;
  
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
            load_cards({ stack })
          }
        }
      })
    )
  }
}



async function load_cards(s) {
  if (s.stack === undefined) {
    return;
  }
  
  set_state({
    section: "card",
    label: Span(_, [
      "Stack: ",
      Span("important", s.stack.name)
    ]),
    stack: s.stack
  });
  
  const stack_id = s.stack.id;
  
  const cards = await AJAX.get("/card/in-stack/" + stack_id, JSONHandler());
  if (cards.error !== undefined) {
    console.log(cards);
    return;
  }
  
  grid.textContent = "";
  grid.append(add_button);
  
  for (const card of cards) {
    grid.append(
      Div("card", [
        Heading(3, _, card.question),
        Span(_, card.answer)
      ], {
        listeners: {
          click: () => {
            edit_card(card)
          }
        }
      })
    )
  }
}



function edit_card(card) {
  // todo edit card
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
      show_window("create-" + get_state().section)
    })
  )
}