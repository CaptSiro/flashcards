class Signal {
    /** @type {(()=>{})[]} */
    #subs;

    /** @type {S} */
    #value;

    /**
     * @template S
     * @param {S} value
     */
    constructor(value) {
        this.#value = value;
        this.#subs = [];
    }

    get value() {
        return this.#value;
    }

    /**
     * @param {S} value
     */
    set value(value) {
        this.#value = value;
        this.notify();
    }

    subscribe(callback) {
        this.#subs.push(callback);
    }

    notify() {
        for (let i = 0; i < this.#subs.length; i++) {
            this.#subs[i]();
        }
    }
}



class GraphicalEffect {
    static #attached = 0;
    static #ctx;
    static #canvas;
    static #do_clear = false;
    /** @type {Signal<boolean>} */
    #cancel_signal;
    /** @type {Signal<boolean>} */
    #finish_signal;
    #render_function;
    #params;
    #bound_update;
    #start_timestamp;
    #duration;

    /**
     * @param {(duration: number, ...params: any)=>{}} render_function
     * @param {number} duration
     * @param {any} params
     */
    constructor(render_function, duration, ...params) {
        this.#cancel_signal = new Signal(false);
        this.#finish_signal = new Signal(false);
        this.#render_function = render_function;
        this.#duration = duration;
        this.#params = params;

        this.#bound_update = this.#update.bind(this);
    }

    /**
     * @returns {CanvasRenderingContext2D}
     */
    static get ctx() {
        return this.#ctx;
    }

    get cancel_signal() {
        return this.#cancel_signal;
    }

    get finish_signal() {
        return this.#finish_signal;
    }

    /**
     * @param {HTMLCanvasElement} canvas
     */
    static canvas(canvas) {
        this.#canvas = canvas;
        this.#ctx = canvas.getContext("2d");
    }

    static #attach() {
        this.#do_clear = true;


        if (this.#attached === 0) {
            requestAnimationFrame(this.#clear.bind(this));
        }

        this.#attached++;
    }

    static #detach() {
        this.#attached--;

        if (this.#attached === 0) {
            this.#do_clear = false;
        }
    }

    static #clear() {
        if (this.#ctx !== undefined) {
            this.#ctx.clearRect(0, 0, this.#canvas.width, this.#canvas.height);
        }

        if (this.#do_clear !== true) {
            return;
        }

        requestAnimationFrame(this.#clear.bind(this));
    }

    start() {
        this.#start_timestamp = window.performance.now();
        GraphicalEffect.#attach();
        requestAnimationFrame(this.#bound_update);
    }

    cancel() {
        if (this.#cancel_signal.value === false && this.#finish_signal.value === false) {
            GraphicalEffect.#detach();
        }

        this.#cancel_signal.value = true;
    }

    finish() {
        if (this.#cancel_signal.value === false && this.#finish_signal.value === false) {
            GraphicalEffect.#detach();
        }

        this.#finish_signal.value = true;
    }

    #update() {
        if (this.#cancel_signal.value === true || this.finish_signal.value === true) {
            return;
        }

        const delta = (window.performance.now() - this.#start_timestamp) / this.#duration;
        if (delta > 1) {
            this.finish();
            return;
        }

        this.#render_function(delta, ...this.#params);

        requestAnimationFrame(this.#bound_update);
    }
}



/**
 * @typedef Card
 * @property {string} question
 * @property {string | null} question_images
 * @property {string[]} question_images_array
 * @property {string} answer
 * @property {string | null} answer_images
 * @property {string[]} answer_images_array
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


/** @type {HTMLCanvasElement} */
const effects = $(".effects");
GraphicalEffect.canvas(effects);
resize_canvas_effects();

const card_div = $(".card");
const image_showcase = $(".image-showcase");
const image_display = image_showcase.querySelector("img");
image_showcase.querySelector(".exit").addEventListener("pointerup", () => image_showcase.classList.add("display-none"));

let image_ptr = -1;
let timestamp;
let timeout_id;
/** @type {GraphicalEffect} */
let loading_animation;
const LOADING_DURATION = 900;
const LOADING_TIMEOUT = 100;



card_div.addEventListener("contextmenu", evt => {
    evt.preventDefault();
});
card_div.addEventListener("pointerdown", evt => {
    timestamp = window.performance.now();
    card_div.dataset.ptrdown = "true";

    timeout_id = setTimeout(() => {
        if (card_div.dataset.content_type !== "image") {
            return;
        }

        loading_animation = effect_loading(
            evt.pointerType === "touch" ? 64 : 32,
            evt.x,
            evt.y,
            LOADING_DURATION
        );
        loading_animation.start();
        loading_animation.finish_signal.subscribe(upscale_image);
    }, LOADING_TIMEOUT);
});
card_div.addEventListener("pointerup", () => {
    clearTimeout(timeout_id);
    loading_animation?.cancel();

    if (card_div.dataset.ptrdown !== "true" || !card_div.classList.contains("attachment")) {
        return;
    }

    card_div.dataset.ptrdown = "false";

    const now = window.performance.now();
    if (now - timestamp >= LOADING_TIMEOUT + LOADING_DURATION && card_div.dataset.content_type === "image") {
        return;
    }

    cycle_attachments();
});

function cycle_attachments() {
    image_ptr++;
    if (image_ptr === card[card_div.dataset.array].length) {
        image_ptr = -1;
    }

    const image = card[card_div.dataset.array][image_ptr];

    if (image === undefined) {
        card_div.dataset.content_type = "text";
        card_div.textContent = card_div.dataset.array === "question_images_array"
            ? card.question
            : card.answer;

        if (card_div.textContent === "") {
            cycle_attachments();
        }

        return;
    }

    card_div.textContent = "";
    card_div.dataset.content_type = "image";
    card_div.append(
        Img(AJAX.DOMAIN_HOME + "/file/" + image + "?width=420", "card-attachment", _, {
            attributes: {
                draggable: "false"
            }
        })
    );
}

function upscale_image() {
    const src = new URL(card_div.querySelector("img").src);
    src.search = "";
    src.searchParams.set("width", String(Math.floor((document.body.clientWidth * 0.8) + 50)));

    if (image_display.src !== src.href) {
        image_display.src = src.href;
    }

    image_showcase.classList.remove("display-none");
}



const initial_div = $(".initial");
const text_answer_div = $(".text-answer");
const thought_answer_div = $(".thought-answer");
const results = $(".results");
const percentage_span = $(".percentage");
const right_span = $(".right");
const wrong_span = $(".wrong");
const progress_bar = $(".progress-bar");
const graph = $(".graph");
const graph_ctx = graph.getContext("2d");
let stack_results = [];



window.addEventListener("resize", () => {
    reset_progress_bar(true);

    resize_canvas_effects();

    resize_canvas_graph();
    draw_graph();
});

function resize_canvas_graph() {
    const w = clamp(300, 600, document.body.clientWidth * 0.4);
    const h = w / 16 * 10;

    graph.style.width = w + "px";
    graph.style.height = h + "px";
    graph.width = w;
    graph.height = h;
}

function resize_canvas_effects() {
    effects.width = document.body.clientWidth;
    effects.height = document.body.clientHeight;
}



const look_up = {
    "backspace": () => window.location.replace(AJAX.DOMAIN_HOME),
    " ": handle_next,
    "enter": handle_next,
    "a": handle_right,
    "s": handle_wrong
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
        card_div.dataset.array = "answer_images_array";
        card_div.classList.toggle("attachment", card.answer_images !== null);
        image_ptr = -1;

        if (card.answer_images !== null && card.answer_images_array === undefined) {
            card.answer_images_array = card.answer_images.split("/");
        }

        if (card.answer === "") {
            cycle_attachments();
        }

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

    card_div.dataset.array = "question_images_array";
    card_div.classList.toggle("attachment", card.question_images !== null);
    image_ptr = -1;

    if (card.question_images !== null && card.question_images_array === undefined) {
        card.question_images_array = card.question_images.split("/");
    }

    if (card.question === "") {
        cycle_attachments();
    }

    const current_cell = progress_bar.children[card_ptr];
    if (current_cell !== undefined) {
        current_cell.classList.add("current");

        if (current_cell.offsetTop >= progress_bar.scrollTop + progress_bar.getBoundingClientRect().height) {
            progress_bar.scrollTo({ behavior: "smooth", top: progress_bar.scrollTop + 12 }); //! gap + line height
        }
    }
}

function reset_progress_bar(do_styles_only = false) {
    const max_cells = (document.body.clientWidth - 36) / 12; //! dependence on padding (left, right), gap size, and minimum cell width (css)
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
    resize_canvas_graph();
    draw_graph();
}



function draw_graph() {
    if (stack_results === undefined || !stack_results instanceof Array) {
        return;
    }

    if (stack_results.length === 0) {
        return;
    }

    const w = graph.width;
    const h = graph.height;

    const offset = 48;
    const font_size = 16;
    const guides = 4;

    graph_ctx.clearRect(0, 0, w, h);

    graph_ctx.font = `normal ${font_size}px sans-serif`;
    graph_ctx.lineWidth = 1;
    const text_color = graph_ctx.strokeStyle = graph_ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue("--text-color-2");

    for (let i = 0; i <= guides; i++) {
        graph_ctx.fillText(
            Math.round(i / guides * 100).toLocaleString() + "%",
            0,
            h - (i / guides * (h - font_size))
        );

        graph_ctx.beginPath();
        graph_ctx.moveTo(
            offset,
            h - (i / guides * (h - font_size)) - font_size / 2
        );
        graph_ctx.lineTo(
            w,
            h - (i / guides * (h - font_size)) - font_size / 2
        );
        graph_ctx.stroke();
    }

    const segment_len = (w - offset) / stack_results.length;

    graph_ctx.beginPath();
    graph_ctx.moveTo(offset, font_size / 2);
    graph_ctx.lineTo(offset, h - font_size / 2);
    graph_ctx.stroke();

    graph_ctx.lineWidth = 5;
    const line_color = graph_ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue("--container-opposite-0");

    graph_ctx.beginPath();
    graph_ctx.moveTo(offset, h - font_size / 2);

    for (let i = 0; i < stack_results.length; i++) {
        graph_ctx.lineTo(
            offset + segment_len * (i + 1),
            h - (stack_results[i].fraction / 100 * (h - font_size)) - font_size / 2
        );
        graph_ctx.strokeStyle = line_color;
        graph_ctx.lineWidth = 5;
        graph_ctx.stroke();

        graph_ctx.beginPath();
        graph_ctx.moveTo(offset + segment_len * (i + 1), font_size / 2);
        graph_ctx.lineTo(offset + segment_len * (i + 1), h - font_size / 2);
        graph_ctx.strokeStyle = text_color;
        graph_ctx.lineWidth = 1;
        graph_ctx.stroke();

        graph_ctx.beginPath();
        graph_ctx.moveTo(
            offset + segment_len * (i + 1),
            h - (stack_results[i].fraction / 100 * (h - font_size)) - font_size / 2
        );
    }
}



function effect_loading(radius, x, y, duration) {
    return new GraphicalEffect(delta => {
        GraphicalEffect.ctx.beginPath();
        GraphicalEffect.ctx.strokeStyle = "black";
        GraphicalEffect.ctx.lineWidth = 3;
        GraphicalEffect.ctx.arc(x, y, radius - 2, -Math.PI / 2, (Math.PI * 2 * delta) - Math.PI / 2);
        GraphicalEffect.ctx.stroke();

        GraphicalEffect.ctx.beginPath();
        GraphicalEffect.ctx.arc(x, y, radius + 2, -Math.PI / 2, (Math.PI * 2 * delta) - Math.PI / 2);
        GraphicalEffect.ctx.stroke();

        GraphicalEffect.ctx.beginPath();
        GraphicalEffect.ctx.strokeStyle = "white";
        GraphicalEffect.ctx.lineWidth = 5;
        GraphicalEffect.ctx.arc(x, y, radius, -Math.PI / 2, (Math.PI * 2 * delta) - Math.PI / 2);
        GraphicalEffect.ctx.stroke();
    }, duration);
}



$(".back").addEventListener("pointerup", () => window.location.replace(AJAX.DOMAIN_HOME + "/"));