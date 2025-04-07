document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchInput');
    const rows = document.querySelectorAll('#productsTable tbody tr');

    input.addEventListener('keyup', () => {
        const filter = input.value.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
