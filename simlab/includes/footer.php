<?php if (isLoggedIn()): ?>
    </div> <!-- end container -->
</main> <!-- end main -->
<?php else: ?>
    </div> <!-- end container -->
</main> <!-- end main -->
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="<?= $base_url ?? '../' ?>assets/js/script.js"></script>
</body>
</html>
