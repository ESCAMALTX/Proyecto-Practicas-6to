        </main>

        <footer class="pie">
            <span><?= Helper::e(NOMBRE_SISTEMA) ?> · v<?= Helper::e(VERSION_SISTEMA) ?></span>
            <span>
                Sesion iniciada: <?= Helper::e(Helper::fechaHora(date('Y-m-d H:i:s', $_SESSION['inicio_sesion'] ?? time()))) ?>
                · <?= Helper::e((string) Sesion::dato('area', 'Sin area')) ?>
            </span>
        </footer>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
