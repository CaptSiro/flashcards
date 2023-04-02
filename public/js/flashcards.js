const CREATOR = 0;
const EDITOR = 1;
const GUEST = 2;



$(".logout").addEventListener("pointerup", async () => {
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

$(".back").addEventListener("pointerup", () => {
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
let did_user_scroll = false;
grid.addEventListener("pointerdown", () => {
  did_user_scroll = false;
});
grid.addEventListener("pointermove", () => {
  did_user_scroll = true;
});
const add_button = AddButton();
window.addEventListener("keydown", evt => {
  if (evt.key !== undefined && evt.key.toLowerCase() === "a" && evt.altKey) {
    add_button.dispatchEvent(new Event("pointerup"));
    evt.preventDefault();
  }
});



const deck_win = $("#create-deck");
const deck_name_input = $("#deck-name");
const deck_control = new FormControl("create-deck");
$("#create-deck button[type=submit]").addEventListener("pointerup", async () => {
  const deck_name = deck_name_input.value.trim();
  
  if (deck_name === "") {
    deck_control.invalidate("Deck name must not be left empty.");
    return;
  }
  
  const body = JSON.stringify({
    name: deck_name
  });
  
  const response = deck_win.dataset.mode === "PUT"
    ? await AJAX.put("/deck/" + deck_win.dataset.id, JSONHandler(), { body })
    : await AJAX.post("/deck", JSONHandler(), { body });
  
  if (response.error !== undefined) {
    deck_control.invalidate(response.error);
    return;
  }
  
  deck_control.clear();
  load_decks();
  clear_windows();
});



const stack_win = $("#create-stack");
const stack_name_input = $("#stack-name");
const stack_control = new FormControl("create-stack");
$("#create-stack button[type=submit]").addEventListener("pointerup", async () => {
  const stack_name = stack_name_input.value.trim();
  
  if (stack_name === "") {
    deck_control.invalidate("Stack name must not be left empty.");
    return;
  }
  
  const s = get_state();
  if (s?.deck.id === undefined) {
    return;
  }
  
  const body = JSON.stringify({
    name: stack_name,
    deck_id: s?.deck.id
  });
  
  const response = stack_win.dataset.mode === "PUT"
    ? await AJAX.put("/stack/" + stack_win.dataset.id, JSONHandler(), { body })
    : await AJAX.post("/stack", JSONHandler(), { body });
  
  if (response.error !== undefined) {
    stack_control.invalidate(response.error);
    return;
  }
  
  stack_control.clear();
  load_stacks(get_state());
  clear_windows();
});



const card_win = $("#create-card");
const card_question_input = $("#card-question");
const card_answer_input = $("#card-answer");
const card_control = new FormControl("create-card");
$("#create-card button[type=submit]").addEventListener("pointerup", async () => {
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
  
  const body = JSON.stringify({
    question: card_question,
    answer: card_answer,
    stack_id: s.stack.id
  });
  
  const response = card_win.dataset.mode === "PUT"
    ? await AJAX.put("/card/" + card_win.dataset.id, JSONHandler(), { body })
    : await AJAX.post("/card", JSONHandler(), { body });
  
  if (response.error !== undefined) {
    card_control.invalidate(response.error);
    return;
  }
  
  card_control.clear();
  load_cards(get_state());
  clear_windows();
});



const share = $("#share");
const share_control = new FormControl("share");
const share_username = share.querySelector("#share-username");
const share_privilege = share.querySelector("#share-privilege");
share.querySelector("button[type=submit]").addEventListener("pointerup", async () => {
  const username = share_username.value.trim();
  
  if (username === "") {
    share_control.invalidate("Username is required.");
    return;
  }
  
  const response = await AJAX.post("/privilege", JSONHandler(), {
    body: JSON.stringify({
      deck_id: share.dataset.deck_id,
      username,
      rank: +share_privilege.value
    })
  });
  
  if (response.error !== undefined) {
    share_control.invalidate(response.error);
    return;
  }
  
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
            const win = show_window("create-deck");
            win.dataset.mode = "PUT";
            win.dataset.id = deck.id;
            win.querySelector("button[type=submit]").textContent = "Edit";
            win.querySelector("#deck-name").value = deck.name;
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
                return;
              }
              
              team.textContent = "";
              for (const member of members) {
                team.append(Member(member, controller));
              }
            }),
            Opt("Delete", async evt => {
              if (!confirm("Do you really want to remove deck: '" + deck.name + "'?")) {
                return;
              }
              
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
    const item = Item(
      "stack",
      stack.name,
      [Button("button-like", "Test", evt => {
        evt.stopImmediatePropagation();
    
        window.location.replace(AJAX.DOMAIN_HOME + "/exam/?stack=" + stack.id);
      })],
      OptionalComponents(can_user_edit, [
        Opt("Edit", () => {
          const win = show_window("create-stack");
          win.dataset.mode = "PUT";
          win.dataset.id = stack.id;
          win.querySelector("button[type=submit]").textContent = "Edit";
          win.querySelector("#stack-name").value = stack.name;
        }),
        Opt("Delete", async evt => {
          if (!confirm("Do you really want to remove stack: '" + stack.name + "'?")) {
            return;
          }
          
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
    );
  
    if (stack.fraction !== undefined && stack.fraction !== null) {
      item.style.backgroundColor = `hsl(${stack.fraction / 100 * 120}, ${50 - stack.fraction / 100 * 10}%, ${40 - stack.fraction / 100 * 10}%)`;
    }
    
    grid.append(item);
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
            const win = show_window("create-card");
            win.dataset.mode = "PUT";
            win.dataset.id = card.id;
            win.querySelector("button[type=submit]").textContent = "Edit";
            win.querySelector("#card-question").value = card.question;
            win.querySelector("#card-answer").value = card.answer;
          }),
          Opt("Delete", async evt => {
            if (!confirm("Do you really want to remove card: '" + card.question + "'?")) {
              return;
            }
            
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
 * @return {HTMLButtonElement}
 */
function AddButton() {
  return (
    Button("add button-like", [
      Span("mono", "+")
    ], () => {
      const win = show_window("create-" + get_state().section);
      win.dataset.mode = "POST";
      win.querySelector("button[type=submit]").textContent = "Create";
    })
  )
}


/**
 * @param {string} type
 * @param {string} label
 * @param {HTMLElement[]} additional
 * @param {HTMLElement[] | undefined} options
 * @param {(evt: Event)=>any | undefined} action
 * @param {boolean} add_radius
 * @returns {HTMLElement}
 */
function Item(type, label, additional = [], options = undefined, action = undefined, add_radius = false) {
  const abs = (
    Div("abs" + (add_radius ? " border-radius" : ""), [
      Div("visible",
        SVG("icon-options", "icon")
      ),
      Div("options", options, {
        listeners: {
          pointerup: evt => evt.stopImmediatePropagation()
        }
      })
    ], {
      listeners: {
        mouseenter: () => abs.classList.add("hover"),
        mouseleave: () => abs.classList.remove("hover"),
        pointerup: evt => {
          evt.stopImmediatePropagation();
          if (evt.pointerType === "mouse") return;
          abs.classList.toggle("hover");
        }
      }
    })
  );
  
  return (
    Div(type, [
      Heading(3, _, label),
      ...additional,
      OptionalComponent(options !== undefined && options.length !== 0, abs)
    ], {
      listeners: {
        pointerup: evt => {
          if (did_user_scroll) return;
          action(evt);
        }
      }
    })
  );
}



window.addEventListener("pointerup", () => {
  for (const item of document.querySelectorAll("main .hover")) {
    item.classList.remove("hover");
  }
});



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
          pointerup: action
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
 * @property {string} username
 */

/**
 * @param {Member} member
 * @param {FormControl} controller
 * @returns {HTMLElement}
 */
function Member(member, controller) {
  const element = (
    Div("member", [
      Span(_, member.username),
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