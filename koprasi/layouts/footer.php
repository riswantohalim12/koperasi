<?php if (isset($_SESSION['user_id'])): ?>
    </div> <!-- End Container Fluid -->
</div> <!-- End Main Content -->
<?php else: ?>
    </div> <!-- End Container -->
<?php endif; ?>

    <!-- JQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('.table-datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
                }
            });

            // Sidebar Toggle for Mobile
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    
    <!-- Custom Scripts Placeholder -->
    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
