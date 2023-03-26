/**
 * @typedef Card
 * @property {string} question
 * @property {string} answer
 * @property {number} id
 * @property {number} decks_id
 */

let to_be_answered = cards;
/**
 * @type {Card[]}
 */
let wrong = [];
/**
 * @type {boolean[]}
 */
const stats = [];

let card_ptr = -1;
/**
 * @type {Card}
 */
let card;
let save_stats = true;

const exam = $(".exam");



if (cards.length === 0) {
  exam.classList.add("display-none");
  $(".no-cards").classList.remove("display-none");
}



const card_div = $(".card");
const initial_div = $(".initial");
const text_answer_div = $(".text-answer");
const thought_answer_div = $(".thought-answer");
const results = $(".results");
const percentage_span = $(".percentage");
const right_span = $(".right");
const wrong_span = $(".wrong");

const look_up = {
  "backspace": () => window.location.replace(AJAX.DOMAIN_HOME),
  " ": handle_next,
  "enter": handle_next,
  "a": handle_right,
  "s": handle_wrong,
};
window.addEventListener("keydown", evt => {
  if (look_up[evt.key.toLowerCase()] !== undefined) {
    look_up[evt.key.toLowerCase()]();
    evt.stopPropagation();
  }
});

const answer_input = $("#answer-input");
answer_input.addEventListener("keydown", evt => {
  evt.stopPropagation();
  
  if (evt.key !== "Enter") {
    return;
  }
  
  evt.preventDefault();
  
  handle_text_input();
});
function handle_text_input() {
  initial_div.classList.add("display-none");
  thought_answer_div.classList.add("display-none");
  
  text_answer_div.classList.remove("display-none");
  
  if (answer_input.value.trim() === card.answer) {
    card_div.classList.add("right");
    answer_right();
  } else {
    card_div.classList.add("wrong");
    answer_wrong();
  }
  
  card_div.textContent = card.answer;
}


text_answer_div.querySelector(".next").addEventListener("pointerdown", handle_next);


initial_div.querySelector(".next").addEventListener("pointerdown", () => {
  if (answer_input.value.trim() !== "") {
    handle_text_input();
    return;
  }
  
  handle_next();
});
function handle_next() {
  if (!initial_div.classList.contains("display-none")) {
    initial_div.classList.add("display-none");
    text_answer_div.classList.add("display-none");
  
    thought_answer_div.classList.remove("display-none");
  
    card_div.textContent = card.answer;
    return;
  }
  
  card_div.classList.remove("right", "wrong");
  next_card();
  
  text_answer_div.classList.add("display-none");
  thought_answer_div.classList.add("display-none");
  
  initial_div.classList.remove("display-none");
  
  answer_input.value = "";
  answer_input.focus();
}


thought_answer_div.querySelector("#answer-right").addEventListener("pointerdown", handle_right);
function handle_right() {
  if (thought_answer_div.classList.contains("display-none")) {
    return;
  }
  
  answer_right();
  next_card();
  
  text_answer_div.classList.add("display-none");
  thought_answer_div.classList.add("display-none");
  
  initial_div.classList.remove("display-none");
}


thought_answer_div.querySelector("#answer-wrong").addEventListener("pointerdown", handle_wrong);
function handle_wrong() {
  if (thought_answer_div.classList.contains("display-none")) {
    return;
  }
  
  answer_wrong();
  next_card();
  
  text_answer_div.classList.add("display-none");
  thought_answer_div.classList.add("display-none");
  
  initial_div.classList.remove("display-none");
}














function next_card() {
  if (card_ptr >= to_be_answered.length - 1) {
    if (wrong.length === 0) {
      show_stats();
      return;
    }

    card_ptr = -1;
    to_be_answered = wrong;
    wrong = [];
    save_stats = false;
  }
  
  card = to_be_answered[++card_ptr];
  card_div.textContent = card.question;
}

if (cards.length !== 0) {
  next_card();
}



function answer_wrong() {
  if (save_stats === true) {
    stats.push(false);
  }
  
  wrong.push(card);
}



function answer_right() {
  if (save_stats === false) {
    return;
  }
  
  stats.push(true);
}



async function show_stats() {
  results.classList.remove("display-none");
  exam.classList.add("display-none");
  
  // [wrong, right]
  let outcomes = [0, 0];
  
  for (const stat of stats) {
    outcomes[+stat] = outcomes[+stat] + 1;
  }
  
  const percentage = (outcomes[1] / stats.length) * 100;
  
  right_span.textContent = String(outcomes[1]);
  wrong_span.textContent = String(outcomes[0]);
  percentage_span.textContent = Math.round(percentage * 100) / 100 + "%";
  
  const response = await AJAX.post("/exam/result", JSONHandler(), {
    body: JSON.stringify({
      fraction: percentage,
      stack_id
    })
  });
  
  if (response.error !== undefined) {
    console.log(response.error);
    // return;
  }
  
  // const stack_results = await AJAX.get("/exam/results/" + stack_id, JSONHandler());
  // todo render graph
}




$(".back").addEventListener("pointerdown", () => window.location.replace(AJAX.DOMAIN_HOME))