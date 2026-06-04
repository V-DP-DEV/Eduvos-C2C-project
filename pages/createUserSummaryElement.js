export function createUserSummaryElement(userSummary) {
    const a = document.createElement("a");
    a.href = `index.php?page=viewProfile&id=${userSummary.id}`;

    const username = document.createElement("span");
    username.textContent = userSummary.username;
    a.appendChild(username);

    if (userSummary.verified === 1) {
        const verified = document.createElement("span");
        verified.className = "verified";
        verified.textContent = "✔";
        a.appendChild(verified);
    }

    if (userSummary.banned === 1) {
        const banned = document.createElement("span");
        banned.className = "banned";
        banned.textContent = "X";
        a.appendChild(banned);
    }

    const avg = document.createElement("span");
    avg.textContent = " " + Number(userSummary.averageReviews).toFixed(2);
    a.appendChild(avg);

    const total = document.createElement("span");
    total.textContent = `(${userSummary.totalReviews} reviews)`;
    a.appendChild(total);

    const rating = document.createElement("div");
    rating.className = "rating";
    rating.style.setProperty("--rating", userSummary.averageReviews);
    a.appendChild(rating);

    return a;
}