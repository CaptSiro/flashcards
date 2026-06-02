const CREATOR = 0;
const EDITOR = 1;
const GUEST = 2;

const STATE_ROOT = 'root';
const STATE_DECK = 'deck';
const STATE_STACK = 'stack';

const STATE_DEFAULT = {
    id: "",
    section: STATE_ROOT,
};

const buttonBack = $(".back");



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
 * @property {string} id
 */

buttonBack.addEventListener("pointerup", () => {
    history.back();
});

window.addEventListener("load", () => {
    const s = get_state();
    loaders.get(s.section)(s);
});

window.addEventListener("hashchange", () => {
    const s = get_state();
    loaders.get(s.section)(s);
});


const hash_regex = /#([\w_]+)-(\d+)/;
/**
 * @return {State}
 */
function get_state() {
    const groups = hash_regex.exec(location.hash);
    if (groups === null) {
        return STATE_DEFAULT;
    }

    return {
        section: groups[1],
        id: groups[2],
    };
}

/**
 * @param {State} state
 * @param {HTMLElement} label
 */
function set_state(state, label = undefined) {
    state_label.textContent = "";

    if (label !== undefined) {
        state_label.append(label);
    }

    buttonBack.style.opacity = state.section === STATE_ROOT
        ? "0"
        : "1";
}

const loaders = new Map([
    [STATE_ROOT, load_decks],
    [STATE_DECK, load_stacks],
    [STATE_STACK, load_cards]
]);

const state_label = $(".state-label");



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



const deck_win = $("#create-root");
const deck_name_input = $("#deck-name");
const deck_control = new FormControl("create-root");
$("#create-root button[type=submit]").addEventListener("pointerup", async () => {
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
    await load_decks(STATE_DEFAULT);
    clear_windows();
});



const stack_win = $("#create-deck");
const stack_name_input = $("#stack-name");
const stack_control = new FormControl("create-deck");
$("#create-deck button[type=submit]").addEventListener("pointerup", async () => {
    const stack_name = stack_name_input.value.trim();

    if (stack_name === "") {
        deck_control.invalidate("Stack name must not be left empty.");
        return;
    }

    const body = JSON.stringify({
        name: stack_name,
        deck_id: get_state().id
    });

    const response = stack_win.dataset.mode === "PUT"
        ? await AJAX.put("/stack/" + stack_win.dataset.id, JSONHandler(), { body })
        : await AJAX.post("/stack", JSONHandler(), { body });

    if (response.error !== undefined) {
        stack_control.invalidate(response.error);
        return;
    }

    stack_control.clear();
    await load_stacks(get_state());
    clear_windows();
});



const card_win = $("#create-stack");
const card_question = card_win.querySelector("#question");
const card_question_input = $("#card-question");
const card_question_images = $("#card-question-images");
const card_answer = card_win.querySelector("#answer");
const card_answer_input = $("#card-answer");
const card_answer_images = $("#card-answer-images");
const card_control = new FormControl("create-stack");

card_question_input.addEventListener("keydown", event => {
    switch (event.key.toLowerCase()) {
        case "tab": {
            event.preventDefault();
            view_answer_form();
            break;
        }

        case "escape": {
            event.preventDefault();
            card_question_input.blur();
            clear_windows();
            break;
        }
    }
});

card_answer_input.addEventListener("keydown", event => {
    if (event.key.toLowerCase() === "escape") {
        event.preventDefault();
        card_answer_input.blur();
        clear_windows();
    }
});

$("#create-stack .next").addEventListener("pointerup", view_answer_form);

$("#create-stack .previous").addEventListener("pointerup", view_question_form);

$("#create-stack button[type=submit]").addEventListener("pointerup", async () => {
    const question = card_question_input.value.trim();
    const question_images = card_question_images.files;

    if (question === "" && question_images.length === 0) {
        card_control.invalidate("You must ask a question. Either type it in or append image with the question.");
        return;
    }

    const answer = card_answer_input.value.trim();
    const answer_images = card_answer_images.files;

    if (answer === "" && answer_images.length === 0) {
        card_control.invalidate("You must answer the question. Either type it in or append image with the answer.");
        return;
    }

    const body = toFormData({
        question,
        answer,
        stack_id: get_state().id
    });

    for (const file of question_images) {
        body.append("question_images[]", file);
    }

    for (const file of answer_images) {
        body.append("answer_images[]", file);
    }

    const response = card_win.dataset.mode === "PUT"
        ? await AJAX.post("/card/put/" + card_win.dataset.id, JSONHandler(), { body })
        : await AJAX.post("/card", JSONHandler(), { body });

    if (response.error !== undefined) {
        card_control.invalidate(response.error);
        return;
    }

    card_control.clear();

    view_question_form();
    card_question_images.value = "";
    card_answer_images.value = "";

    await load_cards(get_state());
    clear_windows();
});

function view_answer_form() {
    card_question.classList.add("display-none");
    card_answer.classList.remove("display-none");
    card_answer_input.focus();
}

function view_question_form() {
    card_answer.classList.add("display-none");
    card_question.classList.remove("display-none");
}



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



function deck_label(deck) {
    return `${deck.name} (${deck.creator})`;
}

async function load_decks(state) {
    set_state(state);

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
                deck_label(deck),
                [Button("button-like", "Test", evt => {
                    evt.stopImmediatePropagation();

                    window.location.replace(AJAX.DOMAIN_HOME + "/exam/?deck=" + deck.id);
                })],
                OptionalComponents(deck.rank === CREATOR || deck.rank === EDITOR, [
                    Opt("Edit", () => {
                        const win = show_window("create-root");
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

                            win.querySelector("#team-deck-name").textContent = deck.name;

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
                () => {
                    location.hash = STATE_DECK + "-" + deck.id;
                }
            )
        );
    }
}



async function get_deck(id) {
    return await AJAX.get("/deck/" + id, JSONHandler());
}

async function load_stacks(state) {
    const deck = await get_deck(state.id);
    console.log(deck);

    set_state(state, Span(_, [
        "Deck: ",
        Span("important", deck_label(deck))
    ]));

    const deck_id = deck.id;

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
                    const win = show_window("create-deck");
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
            () => {
                location.hash = STATE_STACK + "-" + stack.id;
            },
            true
        );

        if (stack.fraction !== undefined && stack.fraction !== null) {
            item.style.backgroundColor = `hsl(${stack.fraction / 100 * 120}, ${50 - stack.fraction / 100 * 10}%, ${40 - stack.fraction / 100 * 10}%)`;
        }

        grid.append(item);
    }
}



async function get_stack(id) {
    return await AJAX.get("/stack/" + id, JSONHandler());
}

async function load_cards(state) {
    const stack = await get_stack(state.id);

    set_state(state, Span(_, [
        "Stack: ",
        Span("important", stack.name)
    ]));

    const stack_id = stack.id;

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
        const has_attachment = (card.answer_images !== null && card.answer_images !== undefined)
            || (card.question_images !== null && card.question_images !== undefined);

        const edit = () => {
            const win = show_window("create-stack");
            win.dataset.mode = "PUT";
            win.dataset.id = card.id;
            win.querySelector("button[type=submit]").textContent = "Edit";
            win.querySelector("#card-question").value = card.question;
            win.querySelector("#card-answer").value = card.answer;
        };

        grid.append(
            Item(
                "card" + (has_attachment ? " attachment" : ""),
                card.question,
                [Span(_, card.answer)],
                OptionalComponents(can_user_edit, [
                    Opt("Edit", edit),
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
                edit,
                true
            )
        );
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
            win.querySelector('[name]')?.focus();
        })
    );
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
                new Option("Guest", "2", true, member.rank === GUEST)
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