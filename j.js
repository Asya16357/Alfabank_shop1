function addToCart(id) {

    let qty = document.querySelector("input[type='number']").value;

    fetch("cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "action=add&id=" + id + "&qty=" + qty
    })
    .then(res => res.json())
    .then(data => {

        document.getElementById("cart-count").innerText = data.count;

        // 🔥 ВИЗУАЛЬНАЯ РЕАКЦИЯ
        showToast("Товар добавлен в корзину ✔");

        // 🔥 эффект кнопки
        let btn = document.querySelector(".btn");
        btn.style.background = "#28a745";
        btn.innerText = "Добавлено ✔";

        setTimeout(() => {
            btn.style.background = "";
            btn.innerText = "Добавить в корзину";
        }, 1500);
    });
}