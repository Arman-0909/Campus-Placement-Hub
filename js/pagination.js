/**
 * Simple Client-side Pagination
 * Campus Placement Hub
 */

class TablePagination {
    constructor(tableId, rowsPerPage = 10) {
        this.table = document.querySelector(tableId);
        if (!this.table) return;

        this.tbody = this.table.querySelector('tbody');
        this.rows = Array.from(this.tbody.querySelectorAll('tr'));
        this.rowsPerPage = rowsPerPage;
        this.currentPage = 1;
        this.totalPages = Math.ceil(this.rows.length / this.rowsPerPage);

        // Only initialize if we have more rows than fits on one page
        if (this.rows.length > this.rowsPerPage) {
            this.init();
        }
    }

    init() {
        // Create pagination controls
        this.createControls();
        // Show initial page
        this.showPage(1);

        // Listen for search events to reset/update pagination
        const searchInputs = document.querySelectorAll('input[type="text"][id$="-search"]');
        searchInputs.forEach(input => {
            input.addEventListener('keyup', () => {
                // Determine visible rows after search filter runs (handled by other scripts)
                setTimeout(() => {
                    this.updateForSearch();
                }, 100);
            });
        });

        // Listen for filter changes
        const filterSelects = document.querySelectorAll('select[id^="filter-"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                setTimeout(() => {
                    this.updateForSearch();
                }, 100);
            });
        });
    }

    createControls() {
        const container = document.createElement('div');
        container.className = 'pagination-controls pagination-container';
        // container.style.cssText is removed in favor of CSS class

        // Prev Button
        this.prevBtn = document.createElement('button');
        this.prevBtn.innerHTML = '<i data-lucide="chevron-left" width="16" height="16"></i>';
        this.prevBtn.className = 'btn btn-sm btn-secondary page-btn';
        this.prevBtn.onclick = () => this.prevPage();

        // Page Info
        this.pageInfo = document.createElement('span');
        this.pageInfo.className = 'text-sm text-muted font-medium page-info';
        this.pageInfo.style.margin = '0 0.5rem';

        // Next Button
        this.nextBtn = document.createElement('button');
        this.nextBtn.innerHTML = '<i data-lucide="chevron-right" width="16" height="16"></i>';
        this.nextBtn.className = 'btn btn-sm btn-secondary page-btn';
        this.nextBtn.onclick = () => this.nextPage();

        container.appendChild(this.prevBtn);
        container.appendChild(this.pageInfo);
        container.appendChild(this.nextBtn);

        this.table.parentNode.appendChild(container); // Add after table container or table

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    showPage(page) {
        this.currentPage = page;

        // Calculate start and end indices
        const start = (page - 1) * this.rowsPerPage;
        const end = start + this.rowsPerPage;

        // Loop through all rows
        this.rows.forEach((row, index) => {
            // Respect existing display:none from search
            if (row.style.display === 'none' && !row.dataset.paginationHidden) {
                return;
            }

            // If row implies it should be visible (matches search), check pagination
            // We use a custom attribute to track if PAGINATION hid it
            if (index >= start && index < end) {
                row.style.display = '';
                row.dataset.paginationHidden = 'false';
            } else {
                row.style.display = 'none';
                row.dataset.paginationHidden = 'true';
            }
        });

        this.updateControls();
    }

    updateForSearch() {
        // Re-evaluate visible rows
        // This is a simplified approach: efficient for < 1000 rows
        // Pagination + Search interaction is complex on client-side without framework
        // So we'll just show all filtered results if search is active

        const isSearching = Array.from(document.querySelectorAll('input[type="text"][id$="-search"]')).some(i => i.value.trim() !== '') ||
            Array.from(document.querySelectorAll('select[id^="filter-"]')).some(s => s.value !== '');

        const controls = this.table.parentNode.querySelector('.pagination-controls');

        if (isSearching) {
            if (controls) controls.style.display = 'none';
            // Show all rows that match search (search script handles display)
            this.rows.forEach(row => {
                if (row.dataset.paginationHidden === 'true') {
                    // If it was hidden by pagination, let search script control it
                    // search script usually sets display='' or 'none' based on match
                }
            });
        } else {
            if (controls) controls.style.display = 'flex';
            this.showPage(1); // Reset to page 1
        }
    }

    updateControls() {
        this.pageInfo.innerText = `Page ${this.currentPage} of ${this.totalPages}`;
        this.prevBtn.disabled = this.currentPage === 1;
        this.nextBtn.disabled = this.currentPage === this.totalPages;

        this.prevBtn.style.opacity = this.currentPage === 1 ? '0.5' : '1';
        this.nextBtn.style.opacity = this.currentPage === this.totalPages ? '0.5' : '1';
    }

    prevPage() {
        if (this.currentPage > 1) this.showPage(this.currentPage - 1);
    }

    nextPage() {
        if (this.currentPage < this.totalPages) this.showPage(this.currentPage + 1);
    }
}
