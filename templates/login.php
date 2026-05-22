<?php
/* Template: Frontend Login */
get_header();
?>

<div class="lokivo-auth-wrapper">

    <div class="lokivo-auth-box">

        <h1>Logowanie</h1>
        <p class="lokivo-auth-sub">Zaloguj się, aby zarządzać ogłoszeniami</p>

        <?php if (!empty($_GET['error'])): ?>
            <div class="lokivo-auth-error">
                Nieprawidłowy e-mail lub hasło.
            </div>
        <?php endif; ?>

        <form method="post" class="lokivo-auth-form">

            <label>
                <span>Adres e-mail</span>
                <input type="email" name="email" required>
            </label>

            <label>
                <span>Hasło</span>
                <input type="password" name="password" required>
            </label>

            <button type="submit" name="lokivo_login" class="lokivo-auth-btn">
                Zaloguj się
            </button>

        </form>

        <p class="lokivo-auth-bottom">
            Nie masz konta?
            <a href="/rejestracja">Zarejestruj się</a>
        </p>

    </div>

</div>

<?php get_footer(); ?>
