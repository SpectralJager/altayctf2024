const form1 = document.querySelector('.authorization');
const vallog = form1.querySelector('.login'),
    valpass = form1.querySelector('.password');
var div = document.querySelectorAll('.divmessage'),
    message = document.querySelectorAll('.message');

let err = document.cookie.match(/err=(.+?)(;|$)/);


if (err[1] == "errdel") {
    let x = document.querySelector('.overlay');
    x.classList.add('active');
    form1.closest('.modal').classList.add('active');
    message[0].textContent = "Неверный логин или пароль";
    div[0].classList.add('divmessageOpen');
    message[0].classList.add('messageOpen');

}

form1.addEventListener('submit', (evt) => {
    // Отменяем действие по умолчанию
    evt.preventDefault();

    // Получаем значения полей формы
    const login = vallog.value;
    const password = valpass.value;

    // Проверяем, что поля заполнены
    if (!login || !password) {
        message[0].textContent = "Пожалуйста, заполните все поля";
        div[0].classList.add('divmessageOpen');
        message[0].classList.add('messageOpen');
        //alert('Пожалуйста, заполните все поля');
        return;
    }

    // Проверяем, что имя пользователя содержит только буквы и цифры
    if (!isValidLogin(login)) {
        message[0].textContent = "Логин может содержать только буквы на латинице и цифры";
        div[0].classList.add('divmessageOpen');
        message[0].classList.add('messageOpen');
        //alert('Логин может содержать только буквы на латинице и цифры');
        return;
    }

    // Проверяем, что пароль содержит хотя бы одну заглавную букву, одну строчную букву и одну цифру
    if (!isValidPassword(password)) {
        message[0].textContent = "Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру";
        div[0].classList.add('divmessageOpen');
        message[0].classList.add('messageOpen');
        //alert('Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру');
        return;
    }

    // Если всё в порядке, отправляем форму
    form1.submit();
});

const form2 = document.querySelector('.form2');
const loginval = form2.querySelector('.login'),
    passval = form2.querySelector('.pass'),
    newpassval = form2.querySelector('.newpass'),
    newpassrepval = form2.querySelector('.newpassrep');

if (err[1] == "errpass") {
    let x = document.querySelector('.overlay');
    x.classList.add('active');
    form2.closest('.modal').classList.add('active');
    message[1].textContent = "Неверный логин или пароль";
    div[1].classList.add('divmessageOpen');
    message[1].classList.add('messageOpen');

}

form2.addEventListener('submit', (evn) => {
    evn.preventDefault();

    const login = loginval.value,
        pass = passval.value,
        newpass = newpassval.value,
        newpassrep = newpassrepval.value;

    if (!pass || !newpass || !newpassrep || !login) {
        message[1].textContent = "Пожалуйста, заполните все поля";
        div[1].classList.add('divmessageOpen');
        message[1].classList.add('messageOpen');
        //alert('Пожалуйста, заполните все поля');
        return;
    }
    if (!isValidLogin(login)) {
        message.textContent = "Логин может содержать только буквы на латинице и цифры";
        div[1].classList.add('divmessageOpen');
        message[1].classList.add('messageOpen');
        //alert('Логин может содержать только буквы на латинице и цифры');
        return;
    }
    if (!isValidPassword(newpass)) {
        message[1].textContent = "Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру";
        div[1].classList.add('divmessageOpen');
        message[1].classList.add('messageOpen');
        //alert('Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру');
        return;
    }
    if (newpass != newpassrep) {
        message[1].textContent = "Пароли должны совпадать";
        div[1].classList.add('divmessageOpen');
        message[1].classList.add('messageOpen');
        return;
    }

    form2.submit();

})

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

document.addEventListener('DOMContentLoaded', function() {
    var modalButtons = document.querySelectorAll('.js-open-modal'),
        overlay = document.querySelector('#overlay-modal'),
        closeButtons = document.querySelectorAll('.modal__cross');

    /* открытие окон. */
    modalButtons.forEach(function(item) {

        item.addEventListener('click', function(e) {

            e.preventDefault();

            var modalId = this.getAttribute('data-modal'),
                modalElem = document.querySelector('.modal[data-modal="' + modalId + '"]');

            modalElem.classList.add('active');
            overlay.classList.add('active');

        }); // end click
    }); // end foreach

    /* закрытие окон */
    closeButtons.forEach((item) => {

        item.addEventListener('click', function(e) {
            var parentModal = this.closest('.modal');
            console.log(parentModal);
            parentModal.classList.remove('active');
            overlay.classList.remove('active');
            document.cookie = "err=no;path=/";
        });

    }); // end foreach

    /* закрытие по ESC */
    document.body.addEventListener('keyup', function(e) {
        var key = e.keyCode;

        if (key == 27) {
            document.querySelector('.modal.active').classList.remove('active');
            document.querySelector('.overlay.active').classList.remove('active');
            document.cookie = "err=no;path=/";
        };
    }, false);

    /* скрытие окна при клике на подложку */
    overlay.addEventListener('click', function() {
        document.querySelector('.modal.active').classList.remove('active');
        this.classList.remove('active');
        document.cookie = "err=no;path=/";
    });

}); // end ready