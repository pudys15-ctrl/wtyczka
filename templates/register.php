<?php
/* Template: Frontend Registration */
get_header();
?>

<div class="lokivo-auth-wrapper">

    <div class="lokivo-auth-box">

        <h1>Załóż konto</h1>
        <p class="lokivo-auth-sub">Dołącz do Lokivo i dodawaj ogłoszenia</p>

        <!-- 🔥 Logowanie Google -->
        <a href="<?php echo wp_login_url(); ?>?loginSocial=google" class="lokivo-social-btn lokivo-google">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="">
            Kontynuuj z Google
        </a>

        <!-- 🔥 Logowanie Facebook -->
        <a href="<?php echo wp_login_url(); ?>?loginSocial=facebook" class="lokivo-social-btn lokivo-facebook">
            <img src="https://www.svgrepo.com/show/448224/facebook.svg" alt="">
            Kontynuuj z Facebook
        </a>

        <div class="lokivo-auth-divider"><span>lub</span></div>

        <!-- Formularz rejestracji -->
        <form method="post" class="lokivo-auth-form">

            <label>
                <span>Adres e-mail</span>
                <input type="email" name="email" required>
            </label>

            <label>
                <span>Hasło</span>
                <input type="password" name="password" required>
            </label>

            <button type="submit" name="lokivo_register" class="lokivo-auth-btn">
                Utwórz konto
            </button>

        </form>

        <p class="lokivo-auth-bottom">
            Masz już konto?
            <a href="/logowanie">Zaloguj się</a>
        </p>

    </div>

</div>

<?php get_footer(); ?>
