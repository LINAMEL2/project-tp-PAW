//Fonction de mise à jour du tableau
function updateTable() {
    const rows = document.querySelectorAll(".table tr");

    rows.forEach((row, index) => {
    if (index < 2) return;

    const checkboxes = row.querySelectorAll("input[type='checkbox']");
    let absences = 0;
    let participations = 0;

    for (let i = 0; i < checkboxes.length; i += 2) {
        const presenceBox = checkboxes[i];
        const participationBox = checkboxes[i + 1];
        if (!presenceBox.checked) absences++;
        if (participationBox.checked) participations++;
    }

    let messageCell = row.querySelector(".message-cell");
    if (!messageCell) {
        messageCell = document.createElement("td");
        messageCell.classList.add("message-cell");
        row.appendChild(messageCell);
    }

    let message = "";
    if (absences === 0) {
        row.style.backgroundColor = "#a9e7a9";
        message = "Perfect attendance!";
    } else if (absences <= 2) {
        row.style.backgroundColor = "#b5f0b5";
        message = "Good attendance";
    } else if (absences >= 3 && absences <= 4) {
        row.style.backgroundColor = "#f8e98f";
        message = "Warning – attendance low";
    } else {
        row.style.backgroundColor = "#f4a7a7";
        message = "Excluded – too many absences";
    }

    messageCell.textContent = `${absences} Abs, ${participations} Par — ${message}`;
    });
}

updateTable();

document.querySelectorAll("input[type='checkbox']").forEach(box => {
    box.addEventListener("change", updateTable);
});

//Validation + Ajout de l’étudiant
const form = document.querySelector("form");
form.addEventListener("submit", function (event) {
    event.preventDefault();
    document.querySelectorAll(".error").forEach(e => e.remove());

    let valid = true;
    const id = document.getElementById("student-id").value.trim();
    const last = document.getElementById("lastname").value.trim();
    const first = document.getElementById("firstname").value.trim();
    const email = document.getElementById("email").value.trim();

    function showError(input, message) {
        const span = document.createElement("span");
        span.className = "error";
        span.style.color = "red";
        span.textContent = message;
        input.insertAdjacentElement("afterend", span);
    }

    if (id === "" || !/^[0-9]+$/.test(id)) {
        showError(document.getElementById("student-id"), "ID must be numbers only");
        valid = false;
    }
    if (last === "" || !/^[A-Za-z]+$/.test(last)) {
        showError(document.getElementById("lastname"), "Last name only letters");
        valid = false;
    }
    if (first === "" || !/^[A-Za-z]+$/.test(first)) {
        showError(document.getElementById("firstname"), "First name only letters");
        valid = false;
    }
    if (email === "" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError(document.getElementById("email"), "Invalid email");
        valid = false;
    }

    if (valid) {
        const table = document.querySelector(".table");
        const newRow = document.createElement("tr");

    newRow.innerHTML = `
        <td>${id}</td>
        <td>${last}</td>
        <td>${first}</td>
        ${Array(6)
            .fill('<td><input type="checkbox"></td><td><input type="checkbox"></td>')
            .join("")}
        <td class="message-cell"></td>
    `;

    table.appendChild(newRow);

    newRow.querySelectorAll("input[type='checkbox']").forEach(box => {
        box.addEventListener("change", updateTable);
    });

    updateTable();

    const confirmation = document.getElementById("confirmation");
    confirmation.classList.add("show");

    setTimeout(() => confirmation.classList.remove("show"), 4000);

    form.reset();
    }
});
