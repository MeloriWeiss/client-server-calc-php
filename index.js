// реализуем более правильную структуру с помощью класса
class App {
    constructor() {
        this.init();
    }

    init() {
        this.getElements();
        this.addEvents();
    }

    getElements() {
        this.inputField = document.getElementById('input');
        this.existingNamesFiled = document.getElementById("txtAntwort");
    }

    addEvents() {
        // на каждый ввод символа будем отправлять запрос с введённой строкой
        this.inputField.addEventListener('input', (event) => {
            this.ajaxRequest(event.target.value);
        });
    }

    ajaxRequest(value) {
        $.ajax({
            type: "POST",
            url: "http://localhost:8000/server_bearbeiter.php",
            contentType: 'application/json',
            data: JSON.stringify({expression: value}),
            success: this.ajaxSuccess.bind(this)
        });
    }

    ajaxSuccess(msg) {
        // обработаем успешный ответ
        this.existingNamesFiled.innerText = JSON.parse(msg).result;
    }
}
new App();