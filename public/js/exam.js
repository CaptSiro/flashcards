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
const progress_bar = $(".progress-bar");
const canvas = $("canvas");
const ctx = canvas.getContext("2d");
let stack_results = JSON.parse(`[{"id":1,"fraction":30,"users_id":2,"stacks_id":1},{"id":3,"fraction":75,"users_id":2,"stacks_id":1},{"id":10,"fraction":100,"users_id":2,"stacks_id":1}]`);



window.addEventListener("resize", () => {
  reset_progress_bar(true);
  
  resize_canvas();
  draw_graph();
});

function resize_canvas() {
  const w = clamp(300, 600, window.innerWidth * 0.4);
  const h = w / 16 * 10;
  
  canvas.style.width = w + "px";
  canvas.style.height = h + "px";
  canvas.width = w;
  canvas.height = h;
}



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


text_answer_div.querySelector(".next").addEventListener("pointerup", handle_next);


initial_div.querySelector(".next").addEventListener("pointerup", () => {
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


thought_answer_div.querySelector("#answer-right").addEventListener("pointerup", handle_right);
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


thought_answer_div.querySelector("#answer-wrong").addEventListener("pointerup", handle_wrong);
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
    reset_progress_bar();
    
    wrong = [];
    save_stats = false;
  }
  
  card = to_be_answered[++card_ptr];
  card_div.textContent = card.question;
  
  const current_cell = progress_bar.children[card_ptr];
  if (current_cell !== undefined) {
    current_cell.classList.add("current");
    current_cell.scrollIntoView({ behavior: "smooth" });
  }
}

function reset_progress_bar(do_styles_only = false) {
  const max_cells = (window.innerWidth - 36) / 12; //! dependence on padding (left, right), gap size, and minimum cell width (css)
  progress_bar.style.gridTemplateColumns = "repeat(" + Math.min(to_be_answered.length, Math.floor(max_cells)) + ", 1fr)";
  
  if (do_styles_only === true) {
    return;
  }
  
  progress_bar.textContent = "";
  for (let i = 0; i < to_be_answered.length; i++) {
    progress_bar.append(document.createElement("div"));
  }
}

if (cards.length !== 0) {
  reset_progress_bar();
  next_card();
}

// show_stats();



function answer_wrong() {
  progress_bar.children[card_ptr]?.classList.add("wrong");
  
  if (save_stats === true) {
    stats.push(false);
  }
  
  wrong.push(card);
}



function answer_right() {
  progress_bar.children[card_ptr]?.classList.add("right");
  
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

  right_span.textContent = Number(outcomes[1]).toLocaleString();
  wrong_span.textContent = Number(outcomes[0]).toLocaleString();
  percentage_span.textContent = Number(Math.round(percentage * 100) / 100).toLocaleString() + "%";

  const response = await AJAX.post("/exam/result", JSONHandler(), {
    body: JSON.stringify({
      fraction: percentage,
      stack_id
    })
  });

  if (response.error !== undefined) {
    alert("Unable to save result.");
    console.log(response.error);
    return;
  }
  
  const response_results = await AJAX.get("/exam/results/" + stack_id, JSONHandler());
  if (response_results.error !== undefined) {
    alert("Unable to load results.");
    console.log(response_results);
    return;
  }

  stack_results = response_results;
  resize_canvas();
  draw_graph();
}



function draw_graph() {
  if (stack_results === undefined || !stack_results instanceof Array) {
    return;
  }
  
  if (stack_results.length === 0) {
    return;
  }
  
  const w = canvas.width;
  const h = canvas.height;
  
  const offset = 48;
  const font_size = 16;
  const guides = 4;
  
  ctx.clearRect(0, 0, w, h);
  
  ctx.font = `normal ${font_size}px sans-serif`;
  ctx.lineWidth = 1;
  const text_color = ctx.strokeStyle = ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-color-2');
  
  for (let i = 0; i <= guides; i++) {
    ctx.fillText(
      Math.round(i / guides * 100).toLocaleString() + "%",
      0,
      h - (i / guides * (h - font_size))
    );
    
    ctx.beginPath();
    ctx.moveTo(
      offset,
      h - (i / guides * (h - font_size)) - font_size / 2
    );
    ctx.lineTo(
      w,
      h - (i / guides * (h - font_size)) - font_size / 2
    );
    ctx.stroke();
  }
  
  const segment_len = (w - offset) / stack_results.length;
  
  ctx.beginPath();
  ctx.moveTo(offset, font_size / 2);
  ctx.lineTo(offset, h - font_size / 2);
  ctx.stroke();
  
  ctx.lineWidth = 5;
  const line_color = ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue("--container-opposite-0");
  
  ctx.beginPath();
  ctx.moveTo(offset, h - font_size / 2);
  
  for (let i = 0; i < stack_results.length; i++) {
    ctx.lineTo(
      offset + segment_len * (i + 1),
      h - (stack_results[i].fraction / 100 * (h - font_size)) - font_size / 2
    );
    ctx.strokeStyle = line_color;
    ctx.lineWidth = 5;
    ctx.stroke();
    
    ctx.beginPath();
    ctx.moveTo(offset + segment_len * (i + 1), font_size / 2);
    ctx.lineTo(offset + segment_len * (i + 1), h - font_size / 2);
    ctx.strokeStyle = text_color;
    ctx.lineWidth = 1;
    ctx.stroke();
  
    ctx.beginPath();
    ctx.moveTo(
      offset + segment_len * (i + 1),
      h - (stack_results[i].fraction / 100 * (h - font_size)) - font_size / 2
    );
  }
}




$(".back").addEventListener("pointerup", () => window.location.replace(AJAX.DOMAIN_HOME + "/"))