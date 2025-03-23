<header>
    <!-- <div class="container"> -->
    <div id="site_logo">
        <h2><?= APP_NAME ?></h2>
    </div>

    <div id="header_user" title="<?= $userData['user'] ?>">
        <img src="https://t3.ftcdn.net/jpg/05/53/79/60/360_F_553796090_XHrE6R9jwmBJUMo9HKl41hyHJ5gqt9oz.jpg"
            alt="User LOGO">

        <span><?= $userData['name'] ?></span>

        <div class="pop-down">
            <ul>
                <li>
                    <a href="<?= ROOT ?>logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
    <!-- </div> -->
</header>