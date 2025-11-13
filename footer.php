<footer class="text-center py-4 bg-dark text-white">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> ওয়াচ শপ। সর্বস্বত্ব সংরক্ষিত।</p>
        <a href="admin-login.php" class="text-muted" style="text-decoration: none; font-size: 0.8rem;">অ্যাডমিন?</a>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.product-card:not(.extra-product)').each(function(index) {
                setTimeout(() => { $(this).addClass('fade-in'); }, index * 100);
            });
            $('#see-more-btn').on('click', function() {
                var hiddenProducts = $('.extra-product.d-none');
                if (hiddenProducts.length > 0) {
                    hiddenProducts.removeClass('d-none').each(function(index) {
                        setTimeout(() => { $(this).addClass('fade-in'); }, index * 100);
                    });
                    $(this).text('কম দেখুন');
                } else {
                    $('.extra-product').addClass('d-none').removeClass('fade-in');
                    $(this).text('আরও দেখুন');
                }
            });
        });
    </script>
</body>
</html>