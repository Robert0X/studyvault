        </main>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token()) ?>';
</script>
<!-- App JS -->
<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
