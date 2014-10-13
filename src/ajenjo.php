<?php

// namespace ajenjo;

class ajenjo {

	static $d;
	static $token;
	static $user;
	static $auth;
	static $updated_at;
	static $created_at;

	static function __load() {
		$aje = new ajenjo\connect(
			Config::get('ajenjo.base_uri', 'http://ajenjo.localhost/'),
			Config::get('ajenjo.path_url', '/api/json')
		);// Carga el objeto ajenjo conect
		if (Session::has(Config::get('ajenjo.cookie', 'ajenjo_sesion'))) {// Verifica si existe un token en la sesion actual
			//Asigna el token almacenada en la sesión dentro de la variable token
			$aje->token = Session::get(Config::get('ajenjo.cookie', 'ajenjo_sesion'));
		} else {
			// Genera un token nuevo
			$aje->token = $aje->generateToken();
			// Almacena el token en la variable de sesion
			Session::put(Config::get('ajenjo.cookie', 'ajenjo_sesion'),$aje->token);
		}

		if (Session::has('ajenjo_sesion$d')) {// verifica si existe almacenada una variable dentro de esta sesión
			self::$d = Session::get('ajenjo_sesion$d');// Captura la memoria de la sesion y la carga a la variable $d
			self::$user = self::$d->user;// almacena al usuario
			self::$auth = self::$d->auth;// almacena la memoria dentro la variable $d
		} else {
			self::$auth = $aje->auth;// almacena al usuario
			self::$d = $aje;// almacena la memoria dentro la variable $d
		}
	}

	/*
	 * Genera un chequeo por medio de las variables que componen la sesión actual
	 */
	static function check() {
		self::__load();// carga a __load
		// identifica si se ha generado un nuevo token artificialmente
		if (true
			&& Input::has('session_lock')
			&& Input::has('token')
			) {
			self::$d->token = Input::get('token');
		}
		$return = self::$d->check();// Captura check original
		Session::put('ajenjo_sesion$d',self::$d);// carga la memoria $d a la memoria de sesion

		return $return;
	}

	/*
	 * Inicia la sesión indicando el usuario y la contraseña
	 */
	static function login($user, $password) {
		self::__load();
		$return = self::$d->login($user, $password);// Carga login original
		self::$user = self::$d->user;
		self::$auth = self::$d->auth;
		Session::put('ajenjo_sesion$d',self::$d);// carga la memoria $d a la memoria de sesion
		return $return;
	}

	/*
	 * Sierra la sesión actual
	 */
	static function logout() {
		self::__load();
		$return = self::$d->logout();// carga logout original
		self::$user = self::$d->user;
		self::$auth = self::$d->auth;
		Session::put('ajenjo_sesion$d',self::$d);// carga la memoria $d a la memoria de sesion
		self::destroy();
		return $return;
	}

	/*
	 * Destruye las memorias que conforman la sesión actual, Incluye las memorias de la sesión. 
	 */
	static function destroy() {
		// self::__load();
		// Vasia las variables locales
		self::$d = null;
		self::$token = null;
		self::$user = null;
		self::$auth = null;
		self::$updated_at = null;
		self::$created_at = null;
		// Elimina las variables de sesion actual
		Session::forget('ajenjo_sesion$d');
		Session::forget(Config::get('ajenjo.cookie', 'ajenjo_sesion'));
		return true;
	}

	static function openLogin() {
		self::__load();
		$rurl = self::$d->urls->login
		 . '?token='
		 . self::$d->token
		 . '&p='
		 . URL::full()
		 ;
		return $rurl;
	}

	static function checkLogin() {
		return (true
			&& Input::has('session_lock')
			&& Input::has('token')
		);
	}

	static function clearURL() {
		return Redirect::to(Request::url());
	}
}