<style>
/* =========================================================
   JUTTAPASAL FOOTER
========================================================= */

#footer {
    width: 100%;
    margin-top: 60px;
    background: #111827;
    color: #ffffff;
}


/* Main container */

.footer-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 50px 40px 25px;
    box-sizing: border-box;
}


/* =========================================================
   FOOTER BRAND
========================================================= */

.footer-brand {
    text-align: center;
    margin-bottom: 35px;
}

.footer-brand h3 {
    margin: 0 0 12px;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.footer-brand p {
    max-width: 550px;
    margin: 0 auto;
    color: #cbd5e1;
    font-size: 14px;
    line-height: 1.7;
}


/* =========================================================
   FOOTER BOTTOM
========================================================= */

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.12);

    padding-top: 22px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 20px;
    flex-wrap: wrap;
}

.footer-bottom p {
    margin: 0;
    color: #9ca3af;
    font-size: 13px;
    line-height: 1.6;
}

.footer-bottom strong {
    color: #ffffff;
    font-weight: 600;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 768px) {

    #footer {
        margin-top: 45px;
    }

    .footer-container {
        padding: 40px 25px 22px;
    }

    .footer-brand {
        margin-bottom: 28px;
    }

    .footer-brand h3 {
        font-size: 25px;
    }

    .footer-brand p {
        font-size: 13px;
    }

    .footer-bottom {
        justify-content: center;
        text-align: center;
        flex-direction: column;
        gap: 5px;
    }

    .footer-bottom p {
        text-align: center;
        width: 100%;
    }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 480px) {

    #footer {
        margin-top: 35px;
    }

    .footer-container {
        padding: 35px 18px 20px;
    }

    .footer-brand {
        margin-bottom: 25px;
    }

    .footer-brand h3 {
        font-size: 23px;
        margin-bottom: 10px;
    }

    .footer-brand p {
        font-size: 13px;
        line-height: 1.6;
        padding: 0 5px;
    }

    .footer-bottom {
        padding-top: 18px;
        gap: 5px;
    }

    .footer-bottom p {
        font-size: 12px;
        text-align: center;
        width: 100%;
    }
}
</style>


<!-- =========================================================
     JUTTAPASAL FOOTER
========================================================= -->

<footer id="footer">

    <div class="footer-container">

        <!-- Footer Brand -->
        <div class="footer-brand">

            <h3>JuttaPasal</h3>

            <p>
                Your trusted online footwear store for quality,
                style and comfort.
            </p>

        </div>


        <!-- Footer Bottom -->
        <div class="footer-bottom">

            <p>
                © 2026
                <strong>JuttaPasal</strong>.
                All Rights Reserved.
            </p>

            <p>
                All Rights Reserved by
                <strong>Sangyan Shrestha</strong>.
            </p>

        </div>

    </div>

</footer>