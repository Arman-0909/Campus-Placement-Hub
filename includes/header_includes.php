<!-- Fonts: Outfit (Headings), Inter (Body) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Flatpickr Date Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Main Stylesheet -->
<link rel="stylesheet" href="../assets/css/style.css?v=3.3">

<!-- Custom JS Select -->
<script src="../js/custom-select.js?v=1.7" defer></script>



<!-- Pagination -->
<script src="../js/pagination.js?v=1.0" defer></script>

<!-- Global Loading State -->
<script src="../js/loading.js?v=1.0" defer></script>

<!-- Global Confirmation Modal -->
<script src="../js/confirm.js?v=1.0" defer></script>

<!-- Mobile Layout Script -->
<script src="../js/layout.js?v=1.0" defer></script>

<!-- Initialize Flatpickr on all date inputs -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('input[type="date"]', {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        animate: true,
        disableMobile: true
    });
});
</script>
