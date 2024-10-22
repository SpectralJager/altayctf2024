<?php
namespace Pages\InfTrain;
use Pages\InfTrain;

class Login extends InfTrain{

	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = 'login';
	
	/** @var string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается */
	protected static $title = 'Вход и регистрация';

	/**
	 * Производит редирект на страницу admin если пользователь зарегистрирован
	 * @return bool - true - страница отобразится, false - будет отображена 404 страница
	 */
	public function test_view():bool{
		global $USER, $URL;
		if($USER->id){
			//пользователь зарегистрирован и не может видеть отладочную информацию
			$URL->redirect(Admin::class);
		}
		
		return true;
	}

	public function get_title(){
		return self::$title;
	}

	public function prepare_page_data(){
		if($this->page_data_prepared)
			return;

		InfTrain::prepare_page_data();
		
		$this->page_data->add_script('login.js');

		$this->page_data_prepared = true;
	}

	protected function render_body(){
	?>
		<body class="login">
			<div class="page-section">
				<div class="login-form">
					<div class="login-form__toggle-buttons">
						<a data-target="form[name=login]" class="btn btn-primary" href="#login">Вход</a>
						<a data-target="form[name=registration]" class="btn btn-primary" href="#registration">Регистрация</a>
					</div>
					<form data-ajax-reload="true" name="login" class="login-form__container" style="display:none;" method="post" action="/api/auth/">
						<input required="required" type="text" placeholder="Имя" name="inf-train_first_name">
						<input required="required" type="text" placeholder="Фамилия" name="inf-train_last_name">
						<input required="required" autocomplete="off" type="password" placeholder="Пароль" name="inf-train_password">
						<label><input type="checkbox" value="1" name="remember"> Запомнить меня</label>
						<input class="btn btn-primary" type="submit" value="Войти">
					</form>
					<form data-ajax-reload="true" name="registration" class="login-form__container" style="display:none;" method="put" action="/api/auth/">
						<input autocomplete="off" required="required" pattern="^.{1,100}$" type="text" placeholder="Имя" name="inf-train_first_name">
						<input autocomplete="off" required="required" pattern="^.{1,100}$" type="text" placeholder="Фамилия" name="inf-train_last_name">
						<input autocomplete="off" required="required" pattern="^.{6,}$" type="password" placeholder="Пароль" name="inf-train_password">
						<div style="height:24px"></div>
						<input class="btn btn-primary" type="submit" value="Зарегистрироваться">
					</form>
				</div>
			</div>
		</body>
	<?php
	}
}