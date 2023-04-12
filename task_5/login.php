<?php

/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// Начинаем сессию.
session_start();

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.


// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['exit'])){
        session_destroy();
        foreach ($_COOKIE as $item => $value) {
            setcookie($item, '', 1);
        }
        header('Location: ./login.php');
    }

    if (!empty($_SESSION['login'])) {
        print ('<div>Вы авторизованы как '. $_SESSION['login'] . ', uid ' . $_SESSION['uid'] . '</div>')
        ?>
        <a href="./login.php?exit=1">Выйти</a>
        <?php
        exit();
    } else {
    ?>

    <form action="./login.php" method="post">
        <input name="login" required>
        <input name="pass" required>
        <input type="submit" value="Войти">
    </form>

    <?php
    }
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {

    $user = 'u53012';
    $pass = '2656986';
    $db = new PDO('mysql:host=localhost;dbname=u53012', $user, $pass, [PDO::ATTR_PERSISTENT => true]);
    $stmt = $db->prepare("SELECT * FROM Person_Login WHERE p_login = :p_login && p_pass = :p_pass;");
    $stmtErr = $stmt->execute(['p_login' => $_POST['login'], 'p_pass' => hash("adler32",$_POST['pass'])]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        print ("Такого пользователя нет");
        print ('<p><a href="./login.php">попробовать снова</a></p>');
        exit();
    }

    // Если все ок, то авторизуем пользователя.
    $_SESSION['login'] = $_POST['login'];
    // Записываем ID пользователя.
    $_SESSION['uid'] = $result['p_id'];

    $stmt = $db->prepare("SELECT * FROM Person WHERE p_id = :p_id;");
    $stmtErr = $stmt->execute(['p_id' => $_SESSION['uid']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    setcookie('name_value', $result['p_name'], time() + 30 * 24 * 60 * 60);
    setcookie('email_value', $result['mail'], time() + 30 * 24 * 60 * 60);
    setcookie('year_value', $result['year'], time() + 30 * 24 * 60 * 60);
    setcookie('gender_value', $result['gender'], time() + 30 * 24 * 60 * 60);
    setcookie('limbs_value', $result['limbs_num'], time() + 30 * 24 * 60 * 60);
    setcookie('biography_value', $result['biography'], time() + 30 * 24 * 60 * 60);
    $stmt = $db->prepare("SELECT * FROM Person_Ability WHERE p_id = :p_id;");
    $stmtErr = $stmt->execute(['p_id' => $_SESSION['uid']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    setcookie('invincibility_value', '', 100000);
    setcookie('noclip_value', '', 100000);
    setcookie('levitation_value', '', 100000);
    if ($result) {
        foreach ($result as $item) {
            switch ($item['a_id']) {
                case 1:
                    setcookie('invincibility_value', '1', time() + 30 * 24 * 60 * 60);
                    break;
                case 3:
                    setcookie('noclip_value', '1', time() + 30 * 24 * 60 * 60);
                    break;
                case 2:
                    setcookie('levitation_value', '1', time() + 30 * 24 * 60 * 60);
                    break;
            }
        }
    }


    // Делаем перенаправление.
    header('Location: ./login.php');
}