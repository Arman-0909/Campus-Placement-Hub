<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="../assets/css/style.css?v=3.3">
<script src="../js/custom-select.js?v=1.7" defer></script>
<script src="../js/pagination.js?v=1.0" defer></script>
<script src="../js/loading.js?v=1.0" defer></script>
<script src="../js/confirm.js?v=1.0" defer></script>
<script src="../js/layout.js?v=1.0" defer></script>
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
