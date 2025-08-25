function addSongField() {
    let container = document.getElementById("songs-container");
    let input = document.createElement("input");
    input.type = "text";
    input.name = "songs[]";
    input.placeholder = "Enter Song Name";
    input.required = true;
    container.appendChild(document.createElement("br"));
    container.appendChild(input);
}

// simple pre-check for duplicates in one lineup
function validateSongs() {
    let inputs = document.querySelectorAll("input[name='songs[]']");
    let seen = new Set();
    for (let i of inputs) {
        if (seen.has(i.value.trim())) {
            alert("⚠️ Duplicate song in this lineup: " + i.value);
            return false;
        }
        seen.add(i.value.trim());
    }
    return true;
}
