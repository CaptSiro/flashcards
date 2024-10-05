/** @type {Map<string, HTMLDivElement>} */
const windows = new Map();

const container = $(".modals");
for (const win of container.children) {
    windows.set(win.id, win);
    const errors = win.querySelectorAll(".error-modal");
    win.querySelectorAll(".cancel-modal").forEach(button => {
        button.addEventListener("click", () => {
            clear_windows();
            errors.forEach(error => {
                error.textContent = "";
                error.classList.remove("show");
            });
        });
    });
}

/**
 * @param {string} id
 * @param {boolean} doClearInputs
 * @returns {HTMLDivElement}
 */
function show_window(id, doClearInputs = true) {
    clear_windows(false);

    const win = windows.get(id);

    if (doClearInputs) {
        win.querySelectorAll("input").forEach(input => {
            switch (input.getAttribute("type")) {
                case "text": {
                    input.value = "";
                    break;
                }
                case "checkbox": {
                    input.checked = false;
                }
            }
        });

        win.querySelectorAll("textarea").forEach(area => area.value = "");
    }

    container.classList.add("darken");
    win.classList.add("show");

    return win;
}

function clear_windows(includeDarken = true) {
    for (const win of container.children) {
        win.classList.remove("show");
    }

    if (includeDarken) {
        container.classList.remove("darken");
    }
}