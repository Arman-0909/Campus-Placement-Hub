
document.addEventListener('DOMContentLoaded', () => {

    if (!document.getElementById('global-confirm-modal')) {
        const modalHtml = `
        <div id="global-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
            <div style="background: white; padding: 2rem; border-radius: 1rem; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); transform: scale(0.95); opacity: 0; transition: all 0.2s ease;" id="global-modal-content">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="background: #fef2f2; color: #ef4444; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;" id="confirm-title">Confirm Deletion</h3>
                    <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;" id="confirm-message">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="flex gap-3">
                    <button id="global-btn-cancel" class="btn btn-secondary w-full" style="flex: 1; justify-content: center;">Cancel</button>
                    <a id="global-btn-confirm" href="#" class="btn btn-danger w-full" style="flex: 1; justify-content: center;">Delete</a>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    const modal = document.getElementById('global-confirm-modal');
    const modalContent = document.getElementById('global-modal-content');
    const confirmBtn = document.getElementById('global-btn-confirm');
    const cancelBtn = document.getElementById('global-btn-cancel');
    const titleEl = document.getElementById('confirm-title');
    const msgEl = document.getElementById('confirm-message');

    window.showDeleteModal = function (event, url, title, message) {
        event.preventDefault();

        if (title) titleEl.textContent = title;
        if (message) msgEl.textContent = message;

        confirmBtn.href = url;

        modal.style.display = 'flex';

        requestAnimationFrame(() => {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        });

        return false;
    };

    function closeModal() {
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    }

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
});
