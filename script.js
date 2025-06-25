document.getElementById("regForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const nickname = this.elements[0].value;
    const age = parseInt(this.elements[3].value);

    if (age < 16) {
        alert("Регистрация доступна только с 16 лет!");
        return;
    }

    // Эффект отправки
    const btn = this.querySelector("button");
    btn.innerHTML = "Отправка...";
    btn.style.background = "linear-gradient(45deg, #4b0082, #8a2be2)";

    setTimeout(() => {
        btn.innerHTML = "✅ Готово!";
        setTimeout(() => {
            btn.innerHTML = "Отправить заявку";
            btn.style.background = "linear-gradient(45deg, var(--purple), var(--pink))";
            this.reset();
        }, 1500);
    }, 1000);
});