const CREATOR = 0;
const EDITOR = 1;
const GUEST = 2;



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
  grid.style.setProperty("--grid-item--min-width", "250px");
  grid.classList.add("add-able");
  
  grid.append(add_button);
  
  for (const deck of decks) {
    grid.append(
      Item(
        "deck",
        deck.name,
        [],
        OptionalComponents(deck.rank === CREATOR || deck.rank === EDITOR, [
          Opt("Edit", () => {
            console.log("edit: " + deck.name);
          }),
          ...OptionalComponents(deck.rank === CREATOR, [
            Opt("Share", () => {
              const win = show_window("share");
              win.dataset.deck_id = deck.id;
            }),
            Opt("Manage privileges", async () => {
              const win = show_window("manage-privileges");
              const team = win.querySelector("#team");
              team.textContent = "Loading members...";
              
              win.querySelector("#deck-name").textContent = deck.name;
              
              const controller = new FormControl("manage-privileges");
              const members = await AJAX.get("/privilege/deck-team/" + deck.id, JSONHandler());
              
              if (members.error !== undefined) {
                controller.invalidate("Failed to load members.");
                team.textContent = "Failed to lead members.";
                return;
              }
              
              if (members.length === 0) {
                team.textContent = "There are no members for this team.";
              }
  
              console.log(members)
              
              team.textContent = "";
              for (const member of members) {
                team.append(Member(member, controller));
              }
            }),
            Opt("Delete", async evt => {
              const response = await AJAX.delete("/deck/" + deck.id, JSONHandler());
              if (response.error !== undefined) {
                console.log(response);
                return;
              }
    
              evt.target.closest(".deck").remove();
            })
          ])
        ]),
        () => load_stacks({ deck })
      )
    )
  }
}

const share = $("#share");
const share_control = new FormControl("share");
const share_email = share.querySelector("#share-email");
const share_privilege = share.querySelector("#share-privilege");
share.querySelector("button[type=submit]").addEventListener("pointerdown", async () => {
  const email = share_email.value.trim();
  
  if (email === "") {
    share_control.invalidate("Email is required.");
    return;
  }
  
  const response = await AJAX.post("/privilege", JSONHandler(), {
    body: JSON.stringify({
      deck_id: share.dataset.deck_id,
      email,
      rank: +share_privilege.value
    })
  });
  
  if (response.error !== undefined) {
    share_control.invalidate(response.error);
    return;
  }
  
  clear_windows();
});



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
  
  const responses = await Promise.all([
    AJAX.get("/stack/in-deck/" + deck_id, JSONHandler()),
    AJAX.get("/privilege/deck/" + deck_id, JSONHandler())
  ]);
  
  for (const response of responses) {
    if (response.error === undefined) continue;
    
    console.log(response);
    return;
  }
  
  const [stacks, privilege] = responses;
  const can_user_edit = privilege.rank === CREATOR || privilege.rank === EDITOR;
  
  grid.textContent = "";
  grid.style.setProperty("--grid-item--min-width", "280px");
  grid.classList.toggle("add-able", can_user_edit);
  
  if (can_user_edit) {
    grid.append(add_button);
  }
  
  for (const stack of stacks) {
    grid.append(
      Item(
        "stack",
        stack.name,
        [Button("button-like", "Test", evt => {
          evt.stopImmediatePropagation();
  
          window.location.replace(AJAX.DOMAIN_HOME + "/exam/?stack=" + stack.id);
        })],
        OptionalComponents(can_user_edit, [
          Opt("Edit", () => {
    
          }),
          Opt("Delete", async evt => {
            const response = await AJAX.delete("/stack/" + stack.id, JSONHandler());
            if (response.error !== undefined) {
              console.log(response);
              return;
            }
    
            evt.target.closest(".stack").remove();
          })
        ]),
        () => load_cards({ stack }),
        true
      )
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
  
  const responses = await Promise.all([
    AJAX.get("/card/in-stack/" + stack_id, JSONHandler()),
    AJAX.get("/privilege/stack/" + stack_id, JSONHandler())
  ]);
  
  for (const response of responses) {
    if (response.error === undefined) continue;

    console.log(response);
    return;
  }
  
  const [cards, privilege] = responses;
  const can_user_edit = privilege.rank === CREATOR || privilege.rank === EDITOR;
  
  grid.textContent = "";
  grid.style.setProperty("--grid-item--min-width", "300px");
  grid.classList.toggle("add-able", can_user_edit);
  
  if (can_user_edit === true) {
    grid.append(add_button);
  }
  
  
  for (const card of cards) {
    grid.append(
      Item(
        "card",
        card.question,
        [Span(_, card.answer)],
        OptionalComponents(can_user_edit, [
          Opt("Edit", () => {
          
          }),
          Opt("Delete", async evt => {
            const response = await AJAX.delete("/card/" + card.id, JSONHandler());
            if (response.error !== undefined) {
              console.log(response);
              return;
            }
            
            evt.target.closest(".card").remove();
          })
        ]),
        _,
        true
      )
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


/**
 * @param {"deck" | "stack" | "card"} type
 * @param {string} label
 * @param {HTMLElement[]} additional
 * @param {HTMLElement[] | undefined} options
 * @param {(evt: Event)=>any | undefined} action
 * @param {boolean} add_radius
 * @returns {HTMLElement}
 */
function Item(type, label, additional = [], options = undefined, action = undefined, add_radius = false) {
  return (
    Div(type, [
      Heading(3, _, label),
      ...additional,
      OptionalComponent(options !== undefined && options.length !== 0,
        Div("abs" + (add_radius ? " border-radius" : ""), [
          Div("visible",
            SVG("icon-options", "icon")
          ),
          Div("options", options, {
            listeners: {
              pointerdown: evt => evt.stopImmediatePropagation()
            }
          })
        ])
      )
    ], {
      listeners: {
        pointerdown: action
      }
    })
  );
}


/**
 * @param {string} label
 * @param {(evt: Event)=>any | undefined} action
 * @returns {HTMLElement}
 */
function Opt(label, action) {
  return (
    Div("option",
      Span(_, label),
      {
        listeners: {
          pointerdown: action
        }
      }
    )
  );
}



/**
 * @typedef Member
 * @property {number} id
 * @property {number} users_id
 * @property {number} rank
 * @property {number} decks_id
 * @property {string} email
 */

/**
 * @param {Member} member
 * @param {FormControl} controller
 * @returns {HTMLElement}
 */
function Member(member, controller) {
  console.log(member)
  
  const element = (
    Div("member", [
      Span(_, member.email),
      Component("select", _, [
        new Option("Editor", "1", false, member.rank === EDITOR),
        new Option("Guest", "2", true, member.rank === GUEST),
      ], {
        listeners: {
          change: async evt => {
            const response = await AJAX.patch("/privilege/" + member.id, JSONHandler(), {
              body: JSON.stringify({
                rank: evt.target.value,
                deck_id: member.decks_id
              })
            });
  
            if (response.error) {
              controller.invalidate(response.error);
            }
          }
        }
      }),
      Button("button-like delete", "✕", async () => {
        const response = await AJAX.delete("/privilege/" + member.id, JSONHandler(), {
          body: JSON.stringify({ deck_id: member.decks_id })
        });
        
        if (response.error) {
          controller.invalidate(response.error);
          return;
        }
        
        element.remove();
      })
    ])
  );
  return element;
}