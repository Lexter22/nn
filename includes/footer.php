<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2025 Ayisha's Clinic Record System. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <i class="bi bi-clock me-1"></i>
                    Opening Hours: Mon-Fri 8:00 AM - 5:00 PM
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? '../' : ''; ?>assets/js/chat.js"></script>

</body>
</html>