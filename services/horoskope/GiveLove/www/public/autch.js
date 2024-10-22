const form = document.querySelector('.authorization');

const vallogin = form.querySelector('.login');
const valpass = form.querySelector('.password');
var div = document.querySelector('.divmessage'),
    message = document.querySelector('.message');

let err = document.cookie.match(/err=(.+?)(;|$)/);

if (err[1] == "logorpass") {
    message.textContent = "Неверный пароль или логин";
    div.classList.add('divmessageOpen');
    message.classList.add('messageOpen');
}

form.addEventListener('submit', (evt) => {
    // Отменяем действие по умолчанию
    evt.preventDefault();

    // Получаем значения полей формы
    const login = vallogin.value;
    const password = valpass.value;

    // Проверяем, что поля заполнены
    if (!login || !password) {
        message.textContent = "Пожалуйста, заполните все поля";
        div.classList.add('divmessageOpen');
        message.classList.add('messageOpen');
        //alert('Пожалуйста, заполните все поля');
        return;
    }

    // Проверяем, что имя пользователя содержит только буквы и цифры
    if (!isValidLogin(login)) {
        message.textContent = "Логин может содержать только буквы на латинице и цифры";
        div.classList.add('divmessageOpen');
        message.classList.add('messageOpen');
        //alert('Логин может содержать только буквы на латинице и цифры');
        return;
    }

    // Проверяем, что пароль содержит хотя бы одну заглавную букву, одну строчную букву и одну цифру
    if (!isValidPassword(password)) {
        message.textContent = "Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру";
        div.classList.add('divmessageOpen');
        message.classList.add('messageOpen');
        //alert('Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру');
        return;
    }

    // Если всё в порядке, отправляем форму
    form.submit();
});

function isValidLogin(login) {
    // Проверка имени регулярным выражением
    const pattern = /^[a-zA-Z0-9]+$/;
    return pattern.test(login);
}

function isValidPassword(password) {
    // Проверка пароля регулярным выражением
    const pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z0-9]{8,20}$/;
    return pattern.test(password);
}