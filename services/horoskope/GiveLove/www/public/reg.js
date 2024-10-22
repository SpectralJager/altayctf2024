const form = document.querySelector('.authorization');

const valname = form.querySelector('.name');
const valsurname = form.querySelector('.surname');
const valfathername = form.querySelector('.fathername');
const valdate = form.querySelector('.date');
const vallogin = form.querySelector('.login');
const valpass = form.querySelector('.password');
const valpassrepeat = form.querySelector('.passwordrepeat');
const valman = form.querySelector('.man');
const valwomen = form.querySelector('.women');
var div = document.querySelector('.divmessage'),
    message = document.querySelector('.message');

form.addEventListener('submit', (evt) => {
    // Отменяем действие по умолчанию
    evt.preventDefault();

    // Получаем значения полей формы
    const name = valname.value;
    const surname = valsurname.value;
    const fathername = valfathername.value;
    const date = valdate.value;
    const login = vallogin.value;
    const password = valpass.value;
    const passwordrepeat = valpassrepeat.value;
    const man = valman.value;
    const women = valwomen.value;

    // Проверяем, что поля заполнены
    if (!login || !password || !name || !surname || !fathername || !date || !passwordrepeat || (man.checked && women.checked)) {
        console.log(man.checked)
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

    if (password != passwordrepeat) {
        message.textContent = "Пароли должны совпадать";
        div.classList.add('divmessageOpen');
        message.classList.add('messageOpen');
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